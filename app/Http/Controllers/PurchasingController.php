<?php

namespace App\Http\Controllers;

use App\Models\PurchasingCategory;
use App\Models\PurchasingLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PurchasingController extends Controller
{
    /**
     * Dashboard Utama Monitoring Purchasing Bulanan
     */
    public function index(Request $request)
    {
        \App\Models\PurchasingOutstanding::clearCalcCaches();
        $selectedYear = $request->get('year', date('Y'));
        $selectedCategoryId = $request->get('category_id');
        $selectedUserId = $request->get('user_id');
        $selectedSupplier = $request->get('supplier');

        $buyerUsers = \App\Models\User::orderBy('id', 'asc')->get();
        $categories = PurchasingCategory::with('buyer')->get();

        // Ambil daftar unik seluruh vendor / supplier untuk filter dropdown
        $suppliers = PurchasingLog::whereNotNull('supplier_name')->where('supplier_name', '!=', '')->pluck('supplier_name')
            ->merge(\App\Models\PurchasingOutstanding::whereNotNull('supplier_name')->where('supplier_name', '!=', '')->pluck('supplier_name'))
            ->merge(\App\Models\MasterPo::whereNotNull('supplier')->where('supplier', '!=', '')->pluck('supplier'))
            ->map(fn($s) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($s))
            ->unique()->filter()->sort()->values();

        // Query log purchasing pada tahun yang dipilih
        $logsQuery = PurchasingLog::with(['category', 'user'])
            ->where('period_month', 'like', $selectedYear . '-%');

        if ($selectedCategoryId) {
            $logsQuery->where('purchasing_category_id', $selectedCategoryId);
        }
        if ($selectedUserId) {
            $logsQuery->where('user_id', $selectedUserId);
        }
        if ($selectedSupplier && $selectedSupplier !== 'All') {
            $variations = \App\Services\DataValidation\InputNormalizer::getSupplierVariations($selectedSupplier);
            $logsQuery->whereIn('supplier_name', $variations);
        }

        $logs = $logsQuery->orderBy('period_month', 'asc')->get();

        // Helper function: Hitung Target, Received, dan Pending secara akurat per PO + Item (mencegah duplikasi target saat ada multiple penerimaan parsial & menjaga item berbeda dalam 1 PO)
        $calculatePoMetrics = function ($logCollection) {
            $poGroups = [];
            foreach ($logCollection as $log) {
                $poKey   = !empty($log->po_reference) ? trim($log->po_reference) : (!empty($log->item_code) ? trim($log->item_code) : 'LOG-' . $log->id);
                $itemKey = !empty($log->item_code) ? trim($log->item_code) : 'ITEM-' . $log->id;
                $uniqueKey = $poKey . '___' . $itemKey;

                if (!isset($poGroups[$uniqueKey])) {
                    $poGroups[$uniqueKey] = [
                        'target' => 0,
                        'received' => 0,
                    ];
                }
                $poGroups[$uniqueKey]['target'] = max($poGroups[$uniqueKey]['target'], (int) $log->target_order);
                $poGroups[$uniqueKey]['received'] += (int) $log->actual_received;
            }

            $target = 0;
            $received = 0;
            $pending = 0;

            foreach ($poGroups as $group) {
                $target += $group['target'];
                $received += $group['received'];
                $pending += max(0, $group['target'] - $group['received']);
            }

            return [
                'target' => $target,
                'received' => $received,
                'pending' => $pending,
            ];
        };

        // 1 & 2 & 3. Total Metrics (Aktual Diterima, Target Order, Pending Order)
        $overallMetrics = $calculatePoMetrics($logs);
        $totalReceived = $overallMetrics['received'];
        $targetOrder   = $overallMetrics['target'];
        $totalPending  = $overallMetrics['pending'];

        $fulfillmentPercentage = $targetOrder > 0 
            ? round(($totalReceived / $targetOrder) * 100, 1) 
            : 0;

        $targetOutstandingQuery = \App\Models\PurchasingOutstanding::query();
        if ($selectedCategoryId) {
            $targetOutstandingQuery->where('category_id', $selectedCategoryId);
        }
        if ($selectedSupplier && $selectedSupplier !== 'All') {
            $variations = \App\Services\DataValidation\InputNormalizer::getSupplierVariations($selectedSupplier);
            $targetOutstandingQuery->whereIn('supplier_name', $variations);
        }
        $totalAmountTarget = (float) $targetOutstandingQuery->get()->sum(function($x) {
            return $x->computed_amount;
        });

        // Hitung totalAmountReceived dengan harga median per PO+Item untuk menghindari distorsi
        // ketika ada multiple log untuk item yang sama dengan harga yang sangat berbeda.
        $totalAmountReceived = (function() {
            $allLogsPricing = \App\Models\PurchasingLog::select(['id', 'po_reference', 'item_code', 'price', 'actual_received'])->get();
            $poItemPrices   = [];
            $poItemReceived = [];
            foreach ($allLogsPricing as $l) {
                $poKey   = !empty($l->po_reference) ? trim($l->po_reference) : 'LOG-' . $l->id;
                $itemKey = !empty($l->item_code)    ? trim($l->item_code)    : 'ITEM-' . $l->id;
                $uKey    = $poKey . '___' . $itemKey;
                $price   = (float) ($l->price > 0 ? $l->price : 0);
                if ($price > 0) {
                    $poItemPrices[$uKey][] = $price;
                }
                if (!isset($poItemReceived[$uKey])) {
                    $poItemReceived[$uKey] = 0;
                }
                $poItemReceived[$uKey] += (int) $l->actual_received;
            }
            $total = 0.0;
            foreach ($poItemReceived as $uKey => $received) {
                if (isset($poItemPrices[$uKey]) && count($poItemPrices[$uKey]) > 0) {
                    $prices = $poItemPrices[$uKey];
                    sort($prices);
                    // Gunakan harga minimum (terkecil) sebagai baseline untuk menghindari outlier
                    $basePrice = $prices[0];
                    $total += $received * $basePrice;
                }
            }
            return $total;
        })();

        // 4. Total Kategori & Kategori Aktif
        $totalCategoriesCount = $categories->count();
        $activeCategoriesCount = $categories->where('status', 'Active')->count();

        // 5. Status Pengadaan
        $statusPurchasing = 'On Track';
        if ($fulfillmentPercentage >= 100) {
            $statusPurchasing = 'Order Fulfilled';
        } elseif ($fulfillmentPercentage > 0 && $fulfillmentPercentage < 85) {
            $statusPurchasing = 'Supply Alert';
        }

        // --- Data untuk Chart.js Bulanan (Jan s/d Des) ---
        $monthsList = [
            '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
            '05' => 'Mei', '06' => 'Jun', '07' => 'Jul', '08' => 'Ags',
            '09' => 'Sep', '10' => 'Okt', '11' => 'Nov', '12' => 'Des'
        ];

        $monthlyLabels         = array_values($monthsList);
        $monthlyReceived       = [];
        $monthlyTarget         = [];
        $monthlyPending        = [];
        $monthlyStockBreakdown = [];

        // Map dari nomor bulan ke singkatan yang umum digunakan di kolom 'periode' tabel Forecasting
        // (field 'periode' menyimpan 'JAN', bukan '2026-01')
        $monthNumToAbbr = [
            '01' => ['JAN', 'Jan', 'jan'],
            '02' => ['FEB', 'Feb', 'feb'],
            '03' => ['MAR', 'Mar', 'mar'],
            '04' => ['APR', 'Apr', 'apr'],
            '05' => ['MEI', 'Mei', 'mei', 'MAY', 'May', 'may'],
            '06' => ['JUN', 'Jun', 'jun'],
            '07' => ['JUL', 'Jul', 'jul'],
            '08' => ['AGS', 'Ags', 'ags', 'AUG', 'Aug', 'aug'],
            '09' => ['SEP', 'Sep', 'sep'],
            '10' => ['OKT', 'Okt', 'okt', 'OCT', 'Oct', 'oct'],
            '11' => ['NOV', 'Nov', 'nov'],
            '12' => ['DES', 'Des', 'des', 'DEC', 'Dec', 'dec'],
        ];

        // Ambil stok awal dari Forecasting (stock_pre) bulan Januari tahun yang dipilih
        $initialStock = 0;
        $janAbbrs = array_merge($monthNumToAbbr['01'], [$selectedYear . '-01']);
        $firstPeriodFc = \App\Models\Forecasting::where(function($q) use ($janAbbrs) {
            $q->whereIn('periode', $janAbbrs)->orWhereIn('period_month', $janAbbrs);
        })->get();
        if ($firstPeriodFc->count() > 0) {
            $initialStock = (int) $firstPeriodFc->sum(function($f) {
                return (int) ($f->stock_pre ?? $f->getAttributes()['stock_qty'] ?? 0);
            });
        }
        $runningStock = $initialStock;

        // Pre-fetch ActualProduction sums by period month (single query)
        $actProdByMonth = \App\Models\ActualProduction::where('tanggal_produksi', 'like', $selectedYear . '-%')
            ->selectRaw("SUBSTRING(tanggal_produksi, 1, 7) as period, SUM(qty) as total_qty")
            ->groupBy('period')
            ->pluck('total_qty', 'period');

        // Pre-fetch Forecasting production_qty (single query)
        $fcProdList = \App\Models\Forecasting::select('periode', 'period_month', 'production_qty')->get();
        $fcProdMap = [];
        foreach ($fcProdList as $fc) {
            $pKey = trim($fc->periode ?? $fc->period_month ?? '');
            if ($pKey) {
                $fcProdMap[strtoupper($pKey)] = ($fcProdMap[strtoupper($pKey)] ?? 0) + (int)$fc->production_qty;
            }
        }

        // Pre-fetch PurchasingOutstanding delivery_category_code map (single query)
        $poDeliveryCatMap = \App\Models\PurchasingOutstanding::whereNotNull('delivery_category_code')
            ->select('part_number', 'drawing', 'delivery_category_code')
            ->get()
            ->flatMap(function($po) {
                $res = [];
                if ($po->part_number) $res[strtoupper(trim($po->part_number))] = $po->delivery_category_code;
                if ($po->drawing) $res[strtoupper(trim($po->drawing))] = $po->delivery_category_code;
                return $res;
            })->toArray();

        foreach ($monthsList as $num => $label) {
            $periodStr = $selectedYear . '-' . $num;
            $monthLogs = $logs->where('period_month', $periodStr);
            $mMetrics  = $calculatePoMetrics($monthLogs);

            $receivedQty = $mMetrics['received'];

            // Master planning forecast requirement across all users
            $fcQuery = \App\Models\Forecasting::where(function($q) use ($periodStr) {
                $q->where('periode', $periodStr)->orWhere('period_month', $periodStr);
            });
            if ($selectedUserId) {
                $fcQuery->where('user_id', $selectedUserId);
            }
            $fcPoTarget = (int) $fcQuery->sum('po_qty');
            if ($fcPoTarget <= 0) {
                $rawFc = (int) $fcQuery->sum('forecast_qty');
                $fcPoTarget = ($rawFc > 500000) ? 0 : $rawFc;
            }
            $targetQty = max($fcPoTarget, (int)$mMetrics['target']);
            $pendingQty = max(0, $targetQty - $receivedQty);

            // Ambil pemakaian produksi dari pre-fetched ActualProduction
            $prodQty = (int) ($actProdByMonth[$periodStr] ?? 0);

            // Fallback ke pre-fetched Forecasting jika tidak ada data aktual produksi
            if ($prodQty <= 0) {
                $abbrList = $monthNumToAbbr[$num] ?? [];
                $allMatchKeys = array_merge($abbrList, [$periodStr]);
                foreach ($allMatchKeys as $mKey) {
                    $uKey = strtoupper($mKey);
                    if (isset($fcProdMap[$uKey])) {
                        $prodQty += $fcProdMap[$uKey];
                    }
                }
            }

            $runningStock = max(0, $runningStock + $receivedQty - $prodQty);

            // Kumpulkan nama kategori dari log bulan ini (menggunakan pre-fetched map)
            $activeCatNames = $monthLogs->map(function($l) use ($poDeliveryCatMap) {
                if ($l->category) {
                    return $l->category->category_code . ' - ' . $l->category->category_name;
                }
                if ($l->item_code) {
                    $itemKey = strtoupper(trim($l->item_code));
                    if (isset($poDeliveryCatMap[$itemKey])) {
                        return $poDeliveryCatMap[$itemKey];
                    }
                }
                return null;
            })->filter()->unique()->values()->toArray();

            $monthlyStockBreakdown[] = [
                'num'             => $num,
                'label'           => $label,
                'period_month'    => $periodStr,
                'received_qty'    => $receivedQty,
                'target_qty'      => $targetQty,
                'production_qty'  => $prodQty,
                'stock_end'       => $runningStock,
                'categories_used' => $activeCatNames,
            ];

            $monthlyReceived[] = $receivedQty;
            $monthlyTarget[]   = $targetQty;
            $monthlyPending[]  = $pendingQty;
        }

        // Chart 2: Kontribusi & Frekuensi Pembelian per Kategori Material
        $categoryNames        = [];
        $categoryReceiveds    = [];
        $categoryLogCounts    = [];
        $categoryTargets      = [];
        $categoryPerformances = [];

        foreach ($categories as $index => $cat) {
            $catLogs     = $logs->where('purchasing_category_id', $cat->id);
            $cMetrics    = $calculatePoMetrics($catLogs);

            $catReceived = $cMetrics['received'];
            $catTarget   = $cMetrics['target'];
            $catPending  = $cMetrics['pending'];
            $catAch      = $catTarget > 0 ? round(($catReceived / $catTarget) * 100, 1) : 0;

            // PIC kategori berasal dari akun buyer yang ditautkan pada master kategori.
            $latestLog    = $catLogs->last();
            $assignedUser = $cat->buyer ?: ($latestLog?->user);
            
            $picName = $assignedUser ? $assignedUser->name : $cat->pic_buyer;
            $picRole = $assignedUser ? strtoupper($assignedUser->role) : 'BUYER';

            $categoryNames[]     = $cat->category_code . ' - ' . $cat->category_name;
            $categoryReceiveds[] = $catReceived;
            $categoryLogCounts[] = $catLogs->count();
            $categoryTargets[]   = $catTarget;

            $categoryPerformances[] = [
                'id'             => $cat->id,
                'code'           => $cat->category_code,
                'name'           => $cat->category_name,
                'buyer'          => $picName,
                'buyer_role'     => $picRole,
                'monthly_target' => $cat->monthly_target_units,
                'status'         => $cat->status,
                'received'       => $catReceived,
                'target'         => $catTarget,
                'achievement'    => $catAch,
                'pending'        => $catPending,
                'log_count'      => $catLogs->count(),
            ];
        }

        // Multi-User Contributions (Konsolidasi Global Hasil Seluruh User)
        $buyerContributions = [];
        $allYearLogs = PurchasingLog::where('period_month', 'like', $selectedYear . '-%')->get();
        foreach ($buyerUsers as $u) {
            $uLogs = $allYearLogs->where('user_id', $u->id);
            $uMetrics = $calculatePoMetrics($uLogs);
            $uRec  = $uMetrics['received'];
            $uTgt  = $uMetrics['target'];
            $uPen  = $uMetrics['pending'];
            $uAch  = $uTgt > 0 ? round(($uRec / $uTgt) * 100, 1) : ($uRec > 0 ? 100 : 0);

            $assignedCats = $categories->where('buyer_user_id', $u->id)->pluck('category_name')->toArray();
            $assignedCatName = !empty($assignedCats) ? implode(', ', $assignedCats) : ($u->role === 'supervisor' ? 'Semua Kategori (Supervisi Manager)' : ($u->role === 'admin' ? 'System Administrator' : 'Material Support'));

            $buyerContributions[] = [
                'user_id'       => $u->id,
                'name'          => $u->name,
                'email'         => $u->email,
                'role'          => ucfirst($u->role),
                'category_name' => $assignedCatName,
                'log_count'     => $uLogs->count(),
                'target'        => $uTgt,
                'received'      => $uRec,
                'pending'       => $uPen,
                'achievement'   => $uAch,
                'status'        => $uAch >= 90 ? 'Target Achieved' : ($uLogs->count() > 0 ? 'On Progress' : 'Standby'),
            ];
        }

        // --- Sinergi Outstanding PO & Alur IAD ---
        $outstandingsQuery = \App\Models\PurchasingOutstanding::query();
        if ($selectedCategoryId) {
            $outstandingsQuery->where('category_id', $selectedCategoryId);
        }
        $outstandings = $outstandingsQuery->get();
        $poTotalCount = $outstandings->count();
        $poTotalAmount = $outstandings->sum('amount');
        $poOrderUnits = $outstandings->sum('order_qty');
        // Kolom complete menyimpan jumlah unit, bukan persentase.
        $poCompleteUnits = $outstandings->sum('complete');
        $poPendingUnits = max(0, $poOrderUnits - $poCompleteUnits);
        $poProgress = $poOrderUnits > 0 ? round(($poCompleteUnits / $poOrderUnits) * 100, 1) : 0;

        $poWaitingManager  = $outstandings->whereIn('workflow_stage', ['waiting_manager', 'revision_manager'])->count();
        $poWaitingSupplier = $outstandings->whereIn('workflow_stage', ['approved_manager', 'waiting_supplier', 'revision_supplier'])->count();
        $poShipped         = $outstandings->where('workflow_stage', 'material_shipped')->count();
        $poIadCheck        = $outstandings->whereIn('workflow_stage', ['iad_check', 'iad_rejected'])->count();
        $poCompleted       = $outstandings->where('workflow_stage', 'completed')->count();

        $latestOutstandings = \App\Models\PurchasingOutstanding::orderBy('updated_at', 'desc')->take(6)->get();
        $outstandingsTotalAmount = $outstandings->sum(function($x) {
            return $x->computed_amount;
        });
        
        // Grouping Log Input Realisasi berdasarkan (period_month + purchasing_category_id)
        $rawLogsForDashboard = PurchasingLog::with(['category', 'user'])
            ->orderBy('period_month', 'desc')
            ->orderBy('receipt_date', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();

        $groupedLogsColl = $rawLogsForDashboard->groupBy(function ($l) {
            return $l->period_month . '_' . $l->purchasing_category_id;
        });

        $latestInputLogs = [];
        foreach ($groupedLogsColl as $gKey => $logsGrp) {
            $fLog = $logsGrp->first();

            $poItemTargets = [];
            foreach ($logsGrp as $lItem) {
                $poKey = !empty($lItem->po_reference) ? trim($lItem->po_reference) : (!empty($lItem->item_code) ? trim($lItem->item_code) : 'LOG-' . $lItem->id);
                $itemKey = !empty($lItem->item_code) ? trim($lItem->item_code) : 'ITEM-' . $lItem->id;
                $uKey = $poKey . '___' . $itemKey;
                if (!isset($poItemTargets[$uKey])) {
                    $poItemTargets[$uKey] = (int) $lItem->target_order;
                } else {
                    $poItemTargets[$uKey] = max($poItemTargets[$uKey], (int) $lItem->target_order);
                }
            }
            $mTarget = array_sum($poItemTargets);
            if ($mTarget <= 0 && $fLog->category) {
                $mTarget = $fLog->category->monthly_target_units;
            }
            $totReceived = $logsGrp->sum('actual_received');
            $pendingDiff = max(0, $mTarget - $totReceived);
            $achPercent  = $mTarget > 0 ? round(($totReceived / $mTarget) * 100, 1) : 0;

            $latestInputLogs[] = [
                'group_key'       => 'grp_' . md5($gKey),
                'period_month'    => $fLog->period_month,
                'category_code'   => $fLog->category->category_code ?? '-',
                'category_name'   => $fLog->category->category_name ?? 'Material Umum',
                'category'        => $fLog->category,
                'target_order'    => $mTarget,
                'actual_received' => $totReceived,
                'pending_order'   => $pendingDiff,
                'achievement'     => $achPercent,
                'trans_count'     => $logsGrp->count(),
                'details'         => $logsGrp->map(function ($lItem) use ($mTarget) {
                    return [
                        'id'              => $lItem->id,
                        'receipt_date'    => $lItem->receipt_date ? Carbon::parse($lItem->receipt_date)->format('d/m/Y') : ($lItem->period_month ?? '-'),
                        'po_number'       => $lItem->po_reference ?: ($lItem->po_number ?: ($lItem->po_code ?: '-')),
                        'item_code'       => $lItem->item_code ?: '-',
                        'item_name'       => $lItem->item_name ?: '-',
                        'supplier_name'   => $lItem->supplier_name ?: null,
                        'actual_received' => $lItem->actual_received,
                        'target_order'    => $lItem->target_order ?: $mTarget,
                        'pending_diff'    => max(0, ($lItem->target_order ?: $mTarget) - $lItem->actual_received),
                        'user_name'       => $lItem->user->name ?? 'System',
                    ];
                })->values()->toArray(),
            ];
        }
        $latestInputLogs = collect($latestInputLogs)->take(6);

        // Data Master PO (Client-side / Bulk Master PO)
        $masterPos          = \App\Models\MasterPo::orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();
        $masterPoTotalCount = $masterPos->count();
        $masterPoTotalQty   = $masterPos->sum('qty');
        $latestMasterPos    = $masterPos->take(6);

        $dbUsers = \App\Models\User::orderBy('id', 'asc')->get();
        $userRolesAndAndil = [];

        foreach ($dbUsers as $u) {
            $rKey = strtolower($u->role);
            if ($rKey === 'supervisor') {
                $roleTitle  = 'Supervisor Purchasing / Manager';
                $roleBadge  = 'bg-danger text-white';
                $department = 'Executive Procurement & Supply Chain';
                $andil      = [
                    'Otorisasi & Approval Resmi Draft PO Baru (Tahap 1. Menunggu Approval Manager)',
                    'Keputusan Final Lolos / Reject Pemeriksaan Mutu IAD (Tahap 4. Pemeriksaan IAD)',
                    'Audit & Pengawasan Eksekutif terhadap Rasio Stok & Target Pemenuhan Tahunan'
                ];
                $modules    = ['Approval Manager PO', 'Audit & Riwayat PO', 'Dashboard Executive'];
            } elseif ($rKey === 'leader') {
                $roleTitle  = 'Leader Procurement Group';
                $roleBadge  = 'bg-info text-dark';
                $department = 'Procurement Control & ETA Schedule Group';
                $andil      = [
                    'Koordinasi Konfirmasi Jadwal Pengiriman Supplier & Pemantauan ETA (Tahap 2)',
                    'Monitoring Keseimbangan Rasio Stok terhadap Rencana Produksi Kawai (Ratio %)',
                    'Supervisi Pemeriksaan Fisik Material Bersama Tim IAD & Gudang Logistik'
                ];
                $modules    = ['Monitoring Outstanding PO', 'ETA Tracking', 'IAD Inspection System'];
            } elseif ($rKey === 'staff') {
                $roleTitle  = 'Staff Buyer Procurement';
                $roleBadge  = 'bg-success text-white';
                $department = 'Purchasing Division';
                $andil      = [
                    'Input & Pembuatan Draft Order PO Baru serta update Rencana PO/Produksi Bulanan',
                    'Komunikasi Rutin Pemesanan Material & Komponen Piano',
                    'Input Realisasi Penerimaan Material & Sinkronisasi Data Gudang Pabrik'
                ];
                $modules    = ['Tambah Outstanding Order', 'Input Realisasi Bulanan', 'Update Rencana Bulanan'];
            } else {
                $roleTitle  = 'Administrator System';
                $roleBadge  = 'bg-warning text-dark';
                $department = 'System & IT Administrator';
                $andil      = [
                    'Manajemen Pengguna & Privileges Hak Akses Multi-Role System',
                    'Pengaturan Konfigurasi Utama & Monitoring Log Aktivitas Sistem'
                ];
                $modules    = ['User Management', 'Hak Akses RBAC', 'System Settings'];
            }

            $userRolesAndAndil[] = [
                'id'          => $u->id,
                'name'        => $u->name,
                'nip'         => $u->employee_id ?: ('KI-PROC-00' . $u->id),
                'role_key'    => $rKey,
                'email'       => $u->email,
                'role'        => $roleTitle,
                'role_badge'  => $roleBadge,
                'department'  => $department,
                'andil'       => $andil,
                'modules'     => $modules,
                'status'      => 'Active ' . ucfirst($rKey),
            ];
        }

        return view('dashboard', [
            'totalReceived'         => $totalReceived,
            'targetOrder'           => $targetOrder,
            'totalAmountTarget'     => $totalAmountTarget,
            'totalAmountReceived'   => $totalAmountReceived,
            'fulfillmentPercentage' => $fulfillmentPercentage,
            'totalCategoriesCount'  => $totalCategoriesCount,
            'activeCategoriesCount' => $activeCategoriesCount,
            'statusPurchasing'      => $statusPurchasing,
            'totalPending'          => $totalPending,
            'categories'            => $categories,
            'buyerUsers'            => $buyerUsers,
            'selectedUserId'        => $selectedUserId,
            'buyerContributions'    => $buyerContributions,
            'selectedCategoryId'    => $selectedCategoryId,
            'selectedYear'          => $selectedYear,
            'monthlyLabels'         => $monthlyLabels,
            'monthlyReceived'       => $monthlyReceived,
            'monthlyTarget'         => $monthlyTarget,
            'monthlyPending'        => $monthlyPending,
            'monthlyStockBreakdown' => $monthlyStockBreakdown,
            'categoryNames'         => $categoryNames,
            'categoryReceiveds'     => $categoryReceiveds,
            'categoryReceived'      => $categoryReceiveds,
            'categoryLogCounts'     => $categoryLogCounts,
            'categoryTargets'       => $categoryTargets,
            'categoryPerformances'  => $categoryPerformances,
            'userRolesAndAndil'     => $userRolesAndAndil,
            'poTotalCount'          => $poTotalCount,
            'poTotalAmount'         => $poTotalAmount,
            'poOrderUnits'          => $poOrderUnits,
            'poCompleteUnits'       => $poCompleteUnits,
            'poPendingUnits'        => $poPendingUnits,
            'poProgress'            => $poProgress,
            'poWaitingManager'      => $poWaitingManager,
            'poWaitingSupplier'     => $poWaitingSupplier,
            'poShipped'             => $poShipped,
            'poIadCheck'            => $poIadCheck,
            'poCompleted'           => $poCompleted,
            'latestOutstandings'    => $latestOutstandings,
            'outstandingsTotalAmount' => $outstandingsTotalAmount,
            'latestInputLogs'       => $latestInputLogs,
            'masterPos'             => $masterPos,
            'masterPoTotalCount'    => $masterPoTotalCount,
            'masterPoTotalQty'      => $masterPoTotalQty,
            'latestMasterPos'       => $latestMasterPos,
            'suppliers'             => $suppliers,
            'selectedSupplier'      => $selectedSupplier,
            'lastUpdated'           => Carbon::now('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB',
        ]);
    }

    /**
     * Tampilkan form input data pengadaan material real
     */
    public function createLog(Request $request)
    {
        $categories = PurchasingCategory::where('status', 'Active')->get();
        $selectedDeliveryCategory = $request->get('delivery_category');
        $selectedSupplier = $request->get('supplier');

        $suppliers = PurchasingLog::whereNotNull('supplier_name')->where('supplier_name', '!=', '')->pluck('supplier_name')
            ->merge(\App\Models\MasterPo::whereNotNull('supplier')->where('supplier', '!=', '')->pluck('supplier'))
            ->map(fn($s) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($s))
            ->unique()->filter()->sort()->values();

        $recentLogsQuery = PurchasingLog::with(['category', 'user']);
        if ($selectedDeliveryCategory) {
            $recentLogsQuery->where('delivery_category_code', $selectedDeliveryCategory);
        }
        if ($selectedSupplier && $selectedSupplier !== 'All') {
            $variations = \App\Services\DataValidation\InputNormalizer::getSupplierVariations($selectedSupplier);
            $recentLogsQuery->whereIn('supplier_name', $variations);
        }

        $recentLogs = $recentLogsQuery
            ->orderBy('receipt_date', 'desc')
            ->orderBy('period_month', 'desc')
            ->orderBy('id', 'desc')
            ->get();
        
        // Build masterList as plain array for clean JSON serialization in @json() in Blade JS
        $masterList = \App\Models\MasterPo::orderBy('item_code')->orderBy('po')->get()
            ->map(function ($po) {
                return [
                    'id'                     => $po->id,
                    'item_code'              => (string) $po->item_code,
                    'po'                     => (string) $po->po,
                    'drawing'                => (string) $po->item_code,
                    'part_number'            => (string) $po->item_code,
                    'po_number'              => (string) $po->po,
                    'description'            => (string) ($po->name ?: 'Material Item'),
                    'supplier_name'          => $po->supplier ? (string) $po->supplier : '',
                    'order_qty'              => (int) $po->qty,
                    'po_date'                => !empty($po->tanggal) ? date('Y-m-d', strtotime($po->tanggal)) : date('Y-m-d'),
                    'purchasing_category_id' => null,
                ];
            })->values()->toArray();

        // Build masterListObjects (stdClass) for Blade template arrow-notation ($master->part_number)
        $masterListObjects = collect($masterList)->map(fn($arr) => (object) $arr);

        // 1. Akumulasi Penerimaan Kumulatif Parsial per No. PO & Item Code
        $poCumulativeGroups = $recentLogs->groupBy(function($log) {
            $poKey   = strtoupper(trim($log->po_reference ?? ''));
            $itemKey = strtoupper(trim($log->item_code ?? ''));
            return ($poKey ?: 'NO_PO') . '___' . ($itemKey ?: 'NO_ITEM');
        });

        $poGroupSummaries = collect();

        foreach ($poCumulativeGroups as $groupKey => $groupLogs) {
            $first = $groupLogs->first();
            $target = (int) $groupLogs->max('target_order');
            if ($target <= 0) {
                $target = (int) $first->target_order;
            }

            $totalReceived = (int) $groupLogs->sum('actual_received');
            $diffCum = $totalReceived - $target;

            $shipments = $groupLogs->sortBy('receipt_date')->map(function($l) {
                return (object)[
                    'id'              => $l->id,
                    'receipt_date'    => $l->receipt_date ? $l->receipt_date : ($l->period_month . '-15'),
                    'actual_received' => (int) $l->actual_received,
                    'supplier_name'   => $l->supplier_name,
                ];
            })->values();

            $poGroupSummaries->put($groupKey, (object)[
                'group_key'       => $groupKey,
                'po_reference'    => $first->po_reference ?: '-',
                'item_code'       => $first->item_code ?: '-',
                'period_month'    => $first->period_month ?: '-',
                'description'     => $first->item_name ?: ($first->category->category_name ?? 'Material Item'),
                'supplier_name'   => $first->supplier_name ?: null,
                'target_order'    => $target,
                'total_received'  => $totalReceived,
                'diff_cumulative' => $diffCum,
                'is_completed'    => ($target > 0 && $totalReceived >= $target),
                'shipment_count'  => $groupLogs->count(),
                'shipments'       => $shipments,
            ]);
        }

        // 2. Rincian Monitoring Kelebihan & Kekurangan Muatan per No. PO (Dengan perhitungan Kumulatif Multi-Pengiriman)
        $poMonitoringList = collect();
        foreach ($recentLogs as $log) {
            $master = $masterListObjects->firstWhere('part_number', strtoupper($log->item_code))
                   ?? $masterListObjects->firstWhere('drawing', strtoupper($log->item_code))
                   ?? $masterListObjects->firstWhere('part_number', strtoupper($log->po_reference)) 
                   ?? $masterListObjects->firstWhere('po_number', strtoupper($log->po_reference));
            
            $poKey   = strtoupper(trim($log->po_reference ?? ''));
            $itemKey = strtoupper(trim($log->item_code ?? ''));
            $groupKey  = ($poKey ?: 'NO_PO') . '___' . ($itemKey ?: 'NO_ITEM');
            $summary   = $poGroupSummaries->get($groupKey);

            $target = (int) ($log->target_order > 0 ? $log->target_order : ($master->order_qty ?? ($summary->target_order ?? 0)));
            $totalCumReceived = $summary ? $summary->total_received : (int) $log->actual_received;
            $diff = $totalCumReceived - $target;
            $shipmentCount = $summary ? $summary->shipment_count : 1;

            $received = (int) $log->actual_received;
            
            $logPrice = (float) ($log->price ?? 0.0);
            if ($logPrice <= 0 && $master) {
                $logPrice = (float) ($master->price ?? 0.0);
            }
            if ($logPrice <= 0) {
                $itemKeyClean = strtoupper(trim($log->item_code ?: $log->po_reference));
                $fc = \App\Models\Forecasting::where('part_number', $itemKeyClean)->where('price', '>', 0)->first();
                if ($fc) {
                    $logPrice = (float) $fc->price;
                }
            }
            $logAmount = $received * $logPrice;
            $logCurrency = !empty($log->currency) ? strtoupper(trim($log->currency)) : ($master ? strtoupper(trim($master->currency ?? 'USD')) : 'USD');

            $statusMuatan = 'Complete / Pas 100%';
            $badgeClass = 'bg-success';
            $alertMsg = 'Sesuai pesanan PO';
            
            if ($diff > 0) {
                $statusMuatan = 'Berlebih Muatan (Over +' . number_format($diff) . ')';
                $badgeClass = 'bg-warning text-dark';
                $alertMsg = '⚠️ Berlebih +' . number_format($diff) . ' unit di PO: ' . ($log->po_reference ?: '-');
            } elseif ($diff < 0) {
                $statusMuatan = 'Kurang ' . number_format(abs($diff));
                $badgeClass = 'bg-danger';
                $alertMsg = '⚠️ Kurang ' . number_format(abs($diff)) . ' unit di PO: ' . ($log->po_reference ?: '-');
            } else {
                $statusMuatan = 'Complete (Lunas ' . number_format($totalCumReceived) . '/' . number_format($target) . ')';
                $badgeClass = 'bg-success';
                $alertMsg = '✅ Lunas / Complete ' . number_format($totalCumReceived) . ' unit';
            }

            $poMonitoringList->push((object)[
                'id'                  => $log->id,
                'receipt_date'        => $log->receipt_date ? $log->receipt_date : ($log->period_month . '-15'),
                'item_code'           => $log->item_code ?: ($master ? ($master->part_number ?: $master->drawing) : '-'),
                'drawing'             => $master ? $master->drawing : null,
                'po_reference'        => $log->po_reference ?: ($master ? ($master->po_number ?: $master->part_number) : '-'),
                'description'         => $log->item_name ?: ($master ? $master->description : ($log->category->category_name ?? 'Material Item')),
                'supplier_name'       => $log->supplier_name ?: ($master ? ($master->supplier_name ?? null) : null),
                'period_month'        => $log->period_month,
                'target_order'        => $target,
                'actual_received'     => $received,
                'price'               => $logPrice,
                'currency'            => $logCurrency,
                'amount'              => $logAmount,
                'cumulative_received' => $totalCumReceived,
                'diff'                => $diff,
                'shipment_count'      => $shipmentCount,
                'status_muatan'       => $statusMuatan,
                'badge_class'         => $badgeClass,
                'alert_msg'           => $alertMsg,
                'status_note'         => $log->status_note,
                'purchasing_category_id' => $log->purchasing_category_id,
                'delivery_category_code'  => $log->delivery_category_code ?? 'LOC',
                'delivery_category_badge' => $log->delivery_category_badge,
                'created_by'          => $log->user ? $log->user->name : 'System',
                'role'                => $log->user ? ucfirst($log->user->role) : 'Staff',
            ]);
        }

        // 3. Rekapitulasi Tabel Target Order, Aktual Masuk, dan Pending Per Kategori & Bulan
        $rekapTable = $recentLogs->groupBy(function($item) {
            return ($item->category ? $item->category->category_name : 'Lainnya') . '___' . $item->period_month;
        })->map(function($group, $key) {
            $parts = explode('___', $key);
            $catName = $parts[0];
            $month = $parts[1] ?? '-';
            
            $target = $group->sum('target_order');
            $actual = $group->sum('actual_received');
            $pending = max(0, $target - $actual);
            $pct = $target > 0 ? round(($actual / $target) * 100, 1) : ($actual > 0 ? 100 : 0);
            
            $status = 'Kurang';
            $badge = 'bg-danger';
            if ($pct >= 100 && $target > 0) {
                $status = ($actual > $target) ? 'Berlebih Muatan' : 'Complete / Pas';
                $badge = ($actual > $target) ? 'bg-warning text-dark' : 'bg-success';
            } elseif ($pct >= 80) {
                $status = 'Material Cukup';
                $badge = 'bg-info text-dark';
            }

            return (object)[
                'category_name'  => $catName,
                'period_month'   => $month,
                'target_order'   => $target,
                'actual_received'=> $actual,
                'pending_order'  => $pending,
                'fulfillment_pct'=> $pct,
                'status'         => $status,
                'badge'          => $badge,
                'count_items'    => $group->count(),
            ];
        })->values();

        // Build plain arrays guaranteed for JSON serialization in Blade @json()
        $masterListForJs = $masterList; // already a plain array

        $poMonitoringListForJs = $poMonitoringList->map(function ($item) {
            return [
                'id'                     => $item->id,
                'item_code'              => (string) ($item->item_code ?? ''),
                'po_reference'           => (string) ($item->po_reference ?? ''),
                'drawing'                => (string) ($item->drawing ?? ''),
                'description'            => (string) ($item->description ?? ''),
                'supplier_name'          => (string) ($item->supplier_name ?? ''),
                'receipt_date'           => (string) ($item->receipt_date ?? ''),
                'period_month'           => (string) ($item->period_month ?? ''),
                'target_order'           => (int) ($item->target_order ?? 0),
                'actual_received'        => (int) ($item->actual_received ?? 0),
                'price'                  => (float) ($item->price ?? 0),
                'currency'               => (string) ($item->currency ?? 'USD'),
                'purchasing_category_id' => $item->purchasing_category_id,
                'delivery_category_code'  => (string) ($item->delivery_category_code ?? 'LOC'),
                'delivery_category_badge' => (string) ($item->delivery_category_badge ?? ''),
            ];
        })->values()->toArray();

        return view('purchasing.input', [
            'categories'             => $categories,
            'recentLogs'             => $recentLogs,
            'masterList'             => $masterList,
            'masterListObjects'      => $masterListObjects,
            'masterListForJs'        => $masterListForJs,
            'poMonitoringList'       => $poMonitoringList,
            'poMonitoringListForJs'  => $poMonitoringListForJs,
            'poGroupSummaries'       => $poGroupSummaries->values(),
            'rekapTable'             => $rekapTable,
            'selectedDeliveryCategory' => $selectedDeliveryCategory,
            'deliveryCategories'     => \App\Models\DeliveryCategory::all(),
            'suppliers'              => $suppliers,
            'selectedSupplier'       => $selectedSupplier,
        ]);
    }

    /**
     * Simpan data real purchasing ke database
     */
    public function storeLog(Request $request)
    {
        $validated = $request->validate([
            // Kategori hanya sebagai metadata. Realisasi harus tetap bisa
            // dicatat berdasarkan PO dan item meskipun master kategori belum ada.
            'purchasing_category_id' => 'nullable|exists:purchasing_categories,id',
            'receipt_date' => 'nullable|date',
            'period_month' => 'required|string', // e.g. 2026-07
            'item_code' => 'required|string',
            'po_reference' => 'required|string',
            'item_name' => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'target_order' => 'nullable|integer|min:0',
            'actual_received' => 'required|integer|min:0',
            'price' => 'nullable',
            'production_qty' => 'nullable|integer|min:0',
            'pending_order' => 'nullable|integer|min:0',
            'status_note' => 'nullable|string|max:255',
        ]);

        $userRole = Auth::user() ? Auth::user()->role : 'staff';
        $userName = Auth::user() ? Auth::user()->name : 'System';
        $userNote = $validated['status_note'] ?? 'Diterima lengkap di Gudang Bahan KIIC';

        if ($userRole === 'staff') {
            // Jika diinput oleh Staff, status catatan harus melewati verifikasi Leader / Supervisor
            $statusNote = "⏳ Menunggu Approval (Staff: $userName) - $userNote";
        } else {
            // Jika diinput langsung oleh Leader / Supervisor, otomatis diverifikasi & disetujui
            $roleTitle = ucfirst($userRole);
            $statusNote = "✅ Disetujui Diterima ($roleTitle: $userName) - $userNote";
        }

        $receiptDate = $validated['receipt_date'] ?? date('Y-m-d');
        $itemCodeClean = $this->normalizePoValue($validated['item_code'] ?? '');
        $poClean = $this->normalizePoValue($validated['po_reference']);
        $masterPo = \App\Models\MasterPo::where('item_code', $itemCodeClean)
            ->where('po', $poClean)
            ->first();

        if (!$masterPo) {
            throw ValidationException::withMessages([
                'po_reference' => 'Pasangan Item Code dan No. PO tidak ditemukan di Step 2. Pilih data PO yang sudah dibuat.',
            ]);
        }

        // Validasi over-delivery terhadap Qty PO dari Master PO (Step 2)
        $totalPoQty = (int)$masterPo->qty;
        if ($totalPoQty > 0) {
            $alreadyReceived = (int)PurchasingLog::where('po_reference', $poClean)
                ->where('item_code', $itemCodeClean)
                ->sum('actual_received');
            
            $newReceipt = (int)$validated['actual_received'];
            if (($alreadyReceived + $newReceipt) > $totalPoQty) {
                $remaining = max(0, $totalPoQty - $alreadyReceived);
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Gagal menyimpan: Jumlah aktual penerimaan ($newReceipt unit) melebihi batas Qty PO yang dibutuhkan (Maksimal sisa yang boleh diterima: $remaining unit, Total Qty PO: $totalPoQty unit).");
            }
        }

        $periodMonth = !empty($masterPo->tanggal)
            ? date('Y-m', strtotime($masterPo->tanggal))
            : $validated['period_month'];

        $receiptDate = !empty($validated['receipt_date'])
            ? $validated['receipt_date']
            : (!empty($masterPo->tanggal) ? date('Y-m-d', strtotime($masterPo->tanggal)) : date('Y-m-d'));

        $rawPrice = $request->input('price');
        $price = ($rawPrice !== null && $rawPrice !== '') ? $this->parseCleanPrice($rawPrice) : 0.0;
        if ($price <= 0) {
            $poMaster = \App\Models\PurchasingOutstanding::where('part_number', $itemCodeClean)->orWhere('drawing', $itemCodeClean)->first();
            if ($poMaster) {
                $price = (float) $poMaster->price;
            }
        }
        if ($price <= 0) {
            $fc = \App\Models\Forecasting::where('part_number', $itemCodeClean)->where('price', '>', 0)->first();
            if ($fc) {
                $price = (float) $fc->price;
            }
        }
        $amount = ((int)$validated['actual_received']) * $price;

        $inputCurrency = strtoupper(trim($request->input('currency', '')));
        $finalCurrency = !empty($inputCurrency) ? $inputCurrency : strtoupper(trim($masterPo->currency ?? 'USD'));

        $log = PurchasingLog::create([
            'purchasing_category_id' => $validated['purchasing_category_id'] ?? null,
            'user_id' => Auth::id(),
            'receipt_date' => $receiptDate,
            'item_code' => $itemCodeClean,
            'po_reference' => $poClean,
            'item_name' => $masterPo->name ?: ($validated['item_name'] ?? 'Material Item'),
            'supplier_name' => $masterPo->supplier ?: ($validated['supplier_name'] ?? null),
            'period_month' => $periodMonth,
            'target_order' => (int) $masterPo->qty,
            'actual_received' => $validated['actual_received'],
            'price' => $price,
            'currency' => $finalCurrency,
            'amount' => $amount,
            'production_qty' => (int) ($validated['production_qty'] ?? 0),
            'status_note' => $statusNote,
            'delivery_category_code' => $request->input('delivery_category_code', $masterPo->delivery_category_code ?? 'LOC'),
        ]);

        $this->syncForecastFromMasterPo($itemCodeClean, $receiptDate);

        // Auto-sync completed qty to PurchasingOutstanding jika ada record yang cocok
        $poMasterItem = \App\Models\PurchasingOutstanding::where('part_number', $itemCodeClean)
            ->orWhere('drawing', $itemCodeClean)
            ->orWhere('po_number', $poClean)
            ->first();

        if ($poMasterItem) {
            $totalRec = (int) PurchasingLog::where(function($q) use ($itemCodeClean, $poClean) {
                if ($itemCodeClean) $q->where('item_code', $itemCodeClean)->orWhere('po_reference', $itemCodeClean);
                if ($poClean) $q->orWhere('po_reference', $poClean)->orWhere('item_code', $poClean);
            })->sum('actual_received');

            $poMasterItem->update([
                'complete' => $totalRec,
                'status'   => ($totalRec >= (int)$poMasterItem->order_qty && (int)$poMasterItem->order_qty > 0) ? 'Complete' : (($totalRec > 0) ? 'On Progress' : 'Pending')
            ]);
        }

        $target = (int) $masterPo->qty;
        $received = (int) $validated['actual_received'];
        if ($received < $target) {
            $diff = number_format($target - $received, 0, ',', '.');
            session()->flash('warning', "⚠️ <strong>Peringatan Selisih Target (Under-Delivery):</strong> Aktual Diterima (" . number_format($received, 0, ',', '.') . " unit) <strong>kurang dari Target PO</strong> (" . number_format($target, 0, ',', '.') . " unit). Terdapat selisih kekurangan sebesar <strong>$diff unit</strong> di Item/PO <strong>" . ($itemCodeClean ?: $poClean) . "</strong> (Tanggal " . date('d/m/Y', strtotime($receiptDate)) . ").");
        } elseif ($received > $target) {
            $diff = number_format($received - $target, 0, ',', '.');
            session()->flash('warning', "⚠️ <strong>Peringatan Kelebihan Muatan (Over-Delivery):</strong> Aktual Diterima (" . number_format($received, 0, ',', '.') . " unit) <strong>melebihi Target PO</strong> (" . number_format($target, 0, ',', '.') . " unit). Terdapat <strong>kelebihan muatan +" . $diff . " unit di Item/PO " . ($itemCodeClean ?: $poClean) . " (Tanggal " . date('d/m/Y', strtotime($receiptDate)) . ")</strong>. Harap periksa surat jalan & verifikasi bersama tim IAD.");
        }

        return redirect()->route('purchasing.input')
            ->with('success', 'Realisasi Item ' . ($itemCodeClean ?: '') . ' / PO ' . $poClean . ' berhasil disimpan & diproses ke pemantauan muatan oleh: ' . (Auth::user()->name ?? 'System'));
    }

    /**
     * Hapus log pengadaan
     */
    /**
     * Simpan 1 baris baru Master PO langsung ke database (tanpa perlu tombol simpan server terpisah)
     */
    public function storeMasterPo(Request $request)
    {
        $validated = $request->validate([
            'tanggal'   => 'nullable|date',
            'supplier'  => 'nullable|string',
            'po'        => 'required|string',
            'item_code' => 'required|string',
            'name'      => 'nullable|string',
            'qty'       => 'nullable|integer|min:0',
        ]);

        $itemCode = $this->normalizePoValue($validated['item_code']);
        $poNumber = $this->normalizePoValue($validated['po']);
        if (!\App\Models\Forecasting::where('part_number', $itemCode)->exists() && !\App\Models\PurchasingOutstanding::where('part_number', $itemCode)->orWhere('drawing', $itemCode)->exists()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Item Code harus terlebih dahulu terdaftar pada Master Forecast (Step 1).'], 422);
            }
            return redirect()->back()->with('error', 'Item Code harus terlebih dahulu terdaftar pada Master Forecast (Step 1).');
        }
        if (\App\Models\MasterPo::where('po', $poNumber)->where('item_code', $itemCode)->exists()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Pasangan Nomor PO dan Item Code sudah digunakan.'], 422);
            }
            return redirect()->back()->with('error', 'Pasangan Nomor PO dan Item Code sudah digunakan.');
        }

        if (empty($itemCode) || empty($poNumber)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Mohon isi minimal Item Code atau PO Number.'], 422);
            }
            return redirect()->back()->with('error', 'Mohon isi minimal Item Code atau PO Number.');
        }

        $mp = \App\Models\MasterPo::create([
            'tanggal'    => !empty($validated['tanggal']) ? $validated['tanggal'] : date('Y-m-d'),
            'supplier'   => !empty($validated['supplier']) ? $validated['supplier'] : null,
            'po'         => $poNumber,
            'item_code'  => $itemCode,
            'factory_code' => strtoupper(trim($request->input('factory_code', 'KIP 1'))),
            'name'       => !empty($validated['name']) ? $validated['name'] : null,
            'qty'        => !empty($validated['qty']) ? (int) $validated['qty'] : 0,
            'price'      => !empty($request->input('price')) ? (float) str_replace(',', '.', (string) $request->input('price')) : 0.0,
            'currency'   => strtoupper(trim($request->input('currency', 'USD'))),
            'created_by' => Auth::id(),
            'user_id'    => Auth::id(),
            'delivery_category_code' => $request->input('delivery_category_code', 'LOC'),
        ]);

        $this->syncForecastFromMasterPo($itemCode, $validated['tanggal'] ?? null);

        \App\Models\PurchasingOutstanding::clearCalcCaches();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data Master PO berhasil disimpan ke database.',
                'data' => $mp
            ]);
        }

        return redirect()->back()->with('success', 'Data Master PO berhasil ditambahkan.');
    }

    /**
     * Simpan Master PO secara bulk (dari dashboard Master PO client-side)
     */
    public function storeMasterPoBulk(Request $request)
    {
        $rows = $request->input('rows');
        if (!is_array($rows) || count($rows) === 0) {
            return response()->json(['message' => 'Tidak ada data yang dikirim.'], 422);
        }

        $toInsert = [];
        $errors = [];
        $rowNumber = 0;

        $registeredItemCodes = \App\Models\PurchasingOutstanding::pluck('part_number')
            ->concat(\App\Models\PurchasingOutstanding::pluck('drawing'))
            ->concat(\App\Models\Forecasting::pluck('part_number'))
            ->filter()
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->toArray();

        foreach ($rows as $r) {
            $rowNumber++;
            $tanggal = !empty($r['tanggal']) ? $r['tanggal'] : null;
            $supplier = !empty($r['supplier']) ? $r['supplier'] : null;
            $po = !empty($r['po']) ? $this->normalizePoValue($r['po']) : null;
            $item_code = !empty($r['itemcode']) ? $this->normalizePoValue($r['itemcode']) : null;
            $name = !empty($r['name']) ? $r['name'] : null;
            $qty = isset($r['qty']) ? $this->parseImportQuantity($r['qty']) : 0;

            if (empty($po) || empty($item_code) || $qty < 0) {
                $errors[] = "Baris {$rowNumber}: PO Number dan Item Code wajib diisi; Qty tidak boleh negatif.";
                continue;
            }

            if (!in_array($item_code, $registeredItemCodes)) {
                \App\Models\PurchasingOutstanding::create([
                    'po_number'     => 'PO-' . $item_code,
                    'po_date'       => date('Y-m-d'),
                    'part_number'   => $item_code,
                    'drawing'       => $item_code,
                    'description'   => $name ?: $item_code,
                    'plan_stock'    => 0,
                    'plan_outstand' => 0,
                    'price'         => $qty > 0 ? 0.0 : 0.0,
                    'currency'      => 'USD',
                ]);
                $registeredItemCodes[] = $item_code;
            }

            $tanggal = $this->parseExcelDate($tanggal);

            $price = isset($r['price']) ? (float) $this->parseCleanPrice($r['price']) : 0.0;
            $curr = !empty($r['currency']) ? strtoupper(trim($r['currency'])) : 'USD';
            if ($price > 300) {
                $curr = 'IDR';
            }

            $toInsert[] = [
                'tanggal' => $tanggal,
                'supplier' => $supplier,
                'po' => $po,
                'item_code' => $item_code,
                'name' => $name,
                'qty' => $qty,
                'price' => $price,
                'currency' => $curr,
                'delivery_category_code' => !empty($r['delivery_category_code']) ? strtoupper(trim($r['delivery_category_code'])) : 'LOC',
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (count($errors) > 0) {
            return response()->json([
                'message' => 'Gagal mengimpor data. Beberapa kesalahan ditemukan:',
                'errors' => array_slice($errors, 0, 5)
            ], 422);
        }

        try {
            // Bulk input browser mengikuti aturan yang sama dengan impor Excel:
            // beberapa baris PO + Item yang sama adalah rincian satu total PO.
            $masterRows = [];
            foreach ($toInsert as $row) {
                $key = $row['po'] . '___' . $row['item_code'];
                if (!isset($masterRows[$key])) {
                    $masterRows[$key] = $row;
                } else {
                    $masterRows[$key]['qty'] += $row['qty'];
                    if ($row['price'] > 0) {
                        $masterRows[$key]['price'] = $row['price'];
                        $masterRows[$key]['currency'] = $row['currency'];
                    }
                }
            }
            \DB::transaction(function () use ($masterRows) {
            foreach ($masterRows as $row) {
                \App\Models\MasterPo::updateOrCreate(
                    [
                        'po'        => $row['po'],
                        'item_code' => $row['item_code'],
                    ],
                    [
                        'tanggal'    => $row['tanggal'],
                        'supplier'   => $row['supplier'],
                        'name'       => $row['name'],
                        'qty'        => $row['qty'],
                        'price'      => $row['price'] ?? 0.0,
                        'currency'   => $row['currency'] ?? 'USD',
                        'delivery_category_code' => $row['delivery_category_code'] ?? 'LOC',
                        'user_id'    => $row['created_by'],
                        'created_by' => $row['created_by'],
                    ]
                );
            }
            });

            $insertedCodes = collect($masterRows)->pluck('item_code')->unique();
            foreach ($insertedCodes as $code) {
                $this->syncForecastFromMasterPo($code);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Master PO berhasil disimpan ('.count($toInsert).' baris).']);
    }

    public function destroyLog($id)
    {
        $log = PurchasingLog::findOrFail($id);
        $itemCode = $log->item_code;
        $poRef = $log->po_reference;
        $period = $log->period_month;
        
        $log->delete();

        // Re-calculate and update Actual totals
        $receiptQuery = PurchasingLog::where('period_month', $period);
        if (!empty($itemCode)) {
            $receiptQuery->where('item_code', $itemCode);
        } else {
            $receiptQuery->where('po_reference', $poRef);
        }
        $receiptTotal = (int) $receiptQuery->sum('actual_received');

        // Auto-sync to legacy actual table disabled by user request

        return redirect()->back()->with('success', 'Data log purchasing berhasil dihapus.');
    }

    public function updateLog(Request $request, $id)
    {
        $log = PurchasingLog::findOrFail($id);

        $validated = $request->validate([
            'purchasing_category_id' => 'nullable|exists:purchasing_categories,id',
            'receipt_date' => 'nullable|date',
            'period_month' => 'required|string',
            'item_code' => 'nullable|string',
            'po_reference' => 'nullable|string',
            'item_name' => 'nullable|string',
            'supplier_name' => 'nullable|string',
            'target_order' => 'nullable|integer|min:0',
            'actual_received' => 'required|integer|min:0',
            'price' => 'nullable',
            'production_qty' => 'nullable|integer|min:0',
            'status_note' => 'nullable|string|max:255',
        ]);

        $userRole = Auth::user() ? Auth::user()->role : 'staff';
        $userName = Auth::user() ? Auth::user()->name : 'System';
        $userNote = $validated['status_note'] ?? 'Diperbarui di Gudang';

        if ($userRole === 'staff') {
            $statusNote = "⏳ Menunggu Approval (Staff: $userName) - $userNote";
        } else {
            $roleTitle = ucfirst($userRole);
            $statusNote = "✅ Disetujui Diterima ($roleTitle: $userName) - $userNote";
        }

        $receiptDate = $validated['receipt_date'] ?? date('Y-m-d');
        $itemCodeClean = $this->normalizePoValue($validated['item_code'] ?? '');
        $poClean = $this->normalizePoValue($validated['po_reference'] ?? $log->po_reference);

        // Validasi over-delivery terhadap Qty PO dari Master PO (Step 2)
        if (!empty($poClean) && !empty($itemCodeClean)) {
            $masterPo = \App\Models\MasterPo::where('po', $poClean)
                ->where('item_code', $itemCodeClean)
                ->first();
            if ($masterPo) {
                $totalPoQty = (int)$masterPo->qty;
                $alreadyReceived = (int)PurchasingLog::where('po_reference', $poClean)
                    ->where('item_code', $itemCodeClean)
                    ->where('id', '!=', $log->id)
                    ->sum('actual_received');
                
                $newReceipt = (int)$validated['actual_received'];
                if (($alreadyReceived + $newReceipt) > $totalPoQty) {
                    $remaining = max(0, $totalPoQty - $alreadyReceived);
                    return redirect()->back()
                        ->withInput()
                        ->with('error', "Gagal memperbarui: Jumlah aktual penerimaan ($newReceipt unit) melebihi batas Qty PO yang dibutuhkan (Maksimal sisa yang boleh diterima: $remaining unit, Total Qty PO: $totalPoQty unit).");
                }
            }
        }

        // Keep track of old item/period to re-sync them
        $oldItemCode = $log->item_code;
        $oldPoRef = $log->po_reference;
        $oldPeriod = $log->period_month;

        $rawPrice = $request->input('price');
        $price = ($rawPrice !== null && $rawPrice !== '') ? $this->parseCleanPrice($rawPrice) : (float)($log->price ?? 0.0);
        if ($price <= 0) {
            $poMaster = \App\Models\PurchasingOutstanding::where('part_number', $itemCodeClean)->orWhere('drawing', $itemCodeClean)->first();
            if ($poMaster) {
                $price = (float) $poMaster->price;
            }
        }
        if ($price <= 0) {
            $fc = \App\Models\Forecasting::where('part_number', $itemCodeClean)->where('price', '>', 0)->first();
            if ($fc) {
                $price = (float) $fc->price;
            }
        }
        $amount = ((int)$validated['actual_received']) * $price;

        $inputCurrency = strtoupper(trim($request->input('currency', '')));
        $finalCurrency = !empty($inputCurrency) ? $inputCurrency : strtoupper(trim($log->currency ?? 'USD'));

        $log->update([
            'purchasing_category_id' => $validated['purchasing_category_id'] ?? null,
            'receipt_date' => $receiptDate,
            'item_code' => $itemCodeClean,
            'po_reference' => $poClean,
            'item_name' => $validated['item_name'] ?? $log->item_name,
            'supplier_name' => $validated['supplier_name'] ?? $log->supplier_name,
            'period_month' => $validated['period_month'],
            'target_order' => isset($masterPo) ? (int) $masterPo->qty : (int) ($validated['target_order'] ?? 0),
            'actual_received' => $validated['actual_received'],
            'price' => $price,
            'currency' => $finalCurrency,
            'amount' => $amount,
            'production_qty' => (int) ($validated['production_qty'] ?? 0),
            'status_note' => $statusNote,
            'delivery_category_code' => $request->input('delivery_category_code', $log->delivery_category_code ?? 'LOC'),
        ]);

        $this->syncForecastFromMasterPo($itemCodeClean, $receiptDate);

        // Auto-sync to legacy actual table disabled by user request

        return redirect()->route('purchasing.input')->with('success', 'Data realisasi penerimaan PO berhasil diperbarui!');
    }

    /**
     * Dashboard Khusus Master PO (Terpisah dari Realisasi Aktual)
     */
    public function masterPoIndex(Request $request)
    {
        $search = $request->get('search');
        $periode = $request->get('periode', 'All');
        $selectedDeliveryCategory = $request->get('delivery_category');
        $selectedSupplier = $request->get('supplier');
        $targetUserId = $request->get('user_id');
        $user = Auth::user();

        $suppliers = \App\Models\MasterPo::whereNotNull('supplier')->where('supplier', '!=', '')
            ->distinct()->pluck('supplier')
            ->map(fn($s) => \App\Services\DataValidation\InputNormalizer::normalizeSupplierName($s))
            ->unique()->filter()->sort()->values();

        $masterPoQuery = \App\Models\MasterPo::orderBy('tanggal', 'desc')->orderBy('id', 'desc');

        if ($selectedDeliveryCategory) {
            $masterPoQuery->where('delivery_category_code', $selectedDeliveryCategory);
        }

        if ($selectedSupplier && $selectedSupplier !== 'All') {
            $variations = \App\Services\DataValidation\InputNormalizer::getSupplierVariations($selectedSupplier);
            $masterPoQuery->whereIn('supplier', $variations);
        }

        if ($targetUserId) {
            $masterPoQuery->where(function($q) use ($targetUserId) {
                $q->where('user_id', $targetUserId)->orWhere('created_by', $targetUserId);
            });
        }

        if ($search) {
            $masterPoQuery->where(function ($q) use ($search) {
                $q->where('po', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        if ($periode && $periode !== 'All') {
            $masterPoQuery->where('tanggal', 'like', "{$periode}%");
        }
        $masterPoList = $masterPoQuery->get();

        $periodes = \App\Models\MasterPo::selectRaw('SUBSTRING(tanggal, 1, 7) as period_month')
            ->whereNotNull('tanggal')
            ->where('tanggal', '!=', '')
            ->groupBy('period_month')
            ->orderBy('period_month', 'desc')
            ->pluck('period_month');

        $masterPoTotalCount = $masterPoList->count();
        $masterPoTotalQty   = $masterPoList->sum('qty');

        // Realisasi Step 3 dihitung dari surat jalan/penerimaan, bukan sekadar
        // tanda bahwa sebuah item pernah masuk. Kunci utamanya adalah PO + item
        // (fallback ke salah satunya bila data lama tidak lengkap).
        $receipts = PurchasingLog::select('po_reference', 'item_code', 'actual_received')->get();
        $receiptByPoItem = [];
        $receiptByPo = [];
        $receiptByItem = [];
        foreach ($receipts as $receipt) {
            $receiptPo = strtoupper(trim((string) $receipt->po_reference));
            $receiptItem = strtoupper(trim((string) $receipt->item_code));
            $qty = (int) $receipt->actual_received;
            if ($receiptPo !== '') $receiptByPo[$receiptPo] = ($receiptByPo[$receiptPo] ?? 0) + $qty;
            if ($receiptItem !== '') $receiptByItem[$receiptItem] = ($receiptByItem[$receiptItem] ?? 0) + $qty;
            if ($receiptPo !== '' && $receiptItem !== '') {
                $key = $receiptPo . '|' . $receiptItem;
                $receiptByPoItem[$key] = ($receiptByPoItem[$key] ?? 0) + $qty;
            }
        }

        // Korelasi dengan Master Outstanding & Forecast
        $outstandingItemCodes = \App\Models\PurchasingOutstanding::pluck('part_number')->filter()->map(fn($v) => strtoupper(trim($v)))->unique()->toArray();
        if (class_exists(\App\Models\PurchasingForecast::class)) {
            $outstandingItemCodes = array_merge($outstandingItemCodes, \App\Models\PurchasingForecast::pluck('part_number')->filter()->map(fn($v) => strtoupper(trim($v)))->unique()->toArray());
        }
        $outstandingItemCodes = array_unique($outstandingItemCodes);

        $matchedActualCount = 0;
        $matchedActualQty = 0;
        $matchedMasterDataCount = 0;

        foreach ($masterPoList as $mp) {
            $code = strtoupper(trim((string)$mp->item_code));
            $po   = strtoupper(trim((string)$mp->po));
            $receivedQty = $po !== '' && $code !== ''
                ? ($receiptByPoItem[$po . '|' . $code] ?? 0)
                : ($po !== '' ? ($receiptByPo[$po] ?? 0) : ($receiptByItem[$code] ?? 0));
            $isMatchedActual = $receivedQty > 0;
            $isMatchedMaster = in_array($code, $outstandingItemCodes);

            if ($isMatchedActual) {
                $matchedActualCount++;
                $matchedActualQty += $receivedQty;
            }
            if ($isMatchedMaster) {
                $matchedMasterDataCount++;
            }

            $mp->is_matched_actual = $isMatchedActual;
            $mp->is_matched_master = $isMatchedMaster;
            $mp->received_qty = $receivedQty;
            $mp->pending_qty = max(0, (int) $mp->qty - $receivedQty);
            $mp->receipt_percentage = (int) $mp->qty > 0 ? round(($receivedQty / (int) $mp->qty) * 100, 1) : 0;
            $mp->receipt_status = $receivedQty <= 0 ? 'Belum Diterima' : ($receivedQty < (int) $mp->qty ? 'Diterima Sebagian' : ($receivedQty === (int) $mp->qty ? 'Diterima Lengkap' : 'Over Receipt'));
        }

        $fulfillmentPercentage = $masterPoTotalQty > 0 ? round(($matchedActualQty / $masterPoTotalQty) * 100, 1) : 0;

        return view('purchasing.master_po', [
            'masterPoList'           => $masterPoList,
            'masterPoTotalCount'     => $masterPoTotalCount,
            'masterPoTotalQty'       => $masterPoTotalQty,
            'matchedActualCount'     => $matchedActualCount,
            'matchedActualQty'       => $matchedActualQty,
            'matchedMasterDataCount' => $matchedMasterDataCount,
            'fulfillmentPercentage'  => $fulfillmentPercentage,
            'periodes'               => $periodes,
            'availableMonths'        => $periodes,
            'periode'                => $periode,
            'search'                 => $search,
            'selectedDeliveryCategory' => $selectedDeliveryCategory,
            'deliveryCategories'     => \App\Models\DeliveryCategory::all(),
            'suppliers'              => $suppliers,
            'selectedSupplier'       => $selectedSupplier,
        ]);
    }

    /**
     * Helper privat untuk parsing angka harga (Price) baik desimal USD maupun ribuan IDR (misal: 600.000 -> 600000)
     */
    private function parseCleanPrice($val): float
    {
        if ($val === null || $val === '' || $val === '-') return 0.0;
        $str = trim((string)$val);
        $str = preg_replace('/[^\d.,-]/', '', $str);
        if (empty($str)) return 0.0;

        if (strpos($str, '.') !== false && strpos($str, ',') !== false) {
            if (strrpos($str, ',') > strrpos($str, '.')) {
                // Format Indonesia: 600.000,00 -> 600000.00
                $str = str_replace('.', '', $str);
                $str = str_replace(',', '.', $str);
            } else {
                // Format US: 600,000.00 -> 600000.00
                $str = str_replace(',', '', $str);
            }
        } elseif (strpos($str, '.') !== false) {
            $parts = explode('.', $str);
            if (count($parts) > 2) {
                // Cth: 1.500.000 -> 1500000
                $str = str_replace('.', '', $str);
            } elseif (count($parts) === 2 && strlen($parts[1]) === 3) {
                // Cth: 600.000 -> 600000
                $str = str_replace('.', '', $str);
            }
        } elseif (strpos($str, ',') !== false) {
            $parts = explode(',', $str);
            if (count($parts) > 2) {
                $str = str_replace(',', '', $str);
            } elseif (count($parts) === 2 && strlen($parts[1]) === 3) {
                $str = str_replace(',', '', $str);
            } else {
                $str = str_replace(',', '.', $str);
            }
        }

        return (float) $str;
    }

    /**
     * Helper privat untuk sinkronisasi PO & Delivery dari Master PO ke Forecast
     */
    private function syncForecastFromMasterPo(string $itemCode, ?string $tanggal = null): void
    {
        $itemCodeClean = strtoupper(trim($itemCode));
        if (empty($itemCodeClean)) return;

        // Clear static cache in PurchasingOutstanding so getPoForMonth recalculates fresh per month
        \App\Models\PurchasingOutstanding::clearCalcCaches();

        $matchedStep1List = \App\Models\PurchasingOutstanding::where('part_number', $itemCodeClean)
            ->orWhere('drawing', $itemCodeClean)
            ->orWhere('description', 'like', "%{$itemCodeClean}%")
            ->get();

        foreach ($matchedStep1List as $matchedStep1) {
            $targetPartNumber = $matchedStep1->part_number ?: $itemCodeClean;
            $targetDrawing    = $matchedStep1->drawing;

            for ($i = 1; $i <= 36; $i++) {
                $period = $matchedStep1->getPeriodForMonth($i);
                $mNum   = $matchedStep1->getMonthNum($i);
                $poForMonth = (int) \App\Models\MasterPo::where(function($q) use ($itemCodeClean, $targetPartNumber, $targetDrawing) {
                        $q->where('item_code', $itemCodeClean)
                          ->orWhere('item_code', $targetPartNumber)
                          ->when($targetDrawing, fn($q2) => $q2->orWhere('item_code', $targetDrawing));
                    })
                    ->where(function($q) use ($period, $mNum) {
                        $q->where('tanggal', 'like', "{$period}%")
                          ->orWhere('tanggal', 'like', "%-{$mNum}-%");
                    })
                    ->sum('qty');

                $matchedStep1->{"m{$i}_po"} = $poForMonth;
            }
            $matchedStep1->save();
        }

        // Scope legacy Forecasting model to first month's period (e.g. 2026-01)
        $m1Period = $matchedStep1 ? $matchedStep1->getPeriodForMonth(1) : date('Y-m');
        $m1PoSum  = (int) \App\Models\MasterPo::where(function($q) use ($itemCodeClean, $targetPartNumber, $targetDrawing) {
                $q->where('item_code', $itemCodeClean)
                  ->orWhere('item_code', $targetPartNumber)
                  ->when($targetDrawing, fn($q2) => $q2->orWhere('item_code', $targetDrawing));
            })
            ->where('tanggal', 'like', "{$m1Period}%")
            ->sum('qty');

        $logSum = (int) \App\Models\PurchasingLog::where(function($q) use ($itemCodeClean, $targetPartNumber, $targetDrawing) {
                $q->where('item_code', $itemCodeClean)
                  ->orWhere('item_code', $targetPartNumber)
                  ->when($targetDrawing, fn($q2) => $q2->orWhere('item_code', $targetDrawing));
            })
            ->where('period_month', 'like', "{$m1Period}%")
            ->sum('actual_received');

        $delSum = $logSum > 0 ? $logSum : $m1PoSum;

        $forecasts = \App\Models\Forecasting::where('part_number', $targetPartNumber)
            ->when($targetDrawing, function($q) use ($targetDrawing) {
                $q->orWhere('part_number', $targetDrawing);
            })
            ->get();

        if ($forecasts->count() > 0) {
            foreach ($forecasts as $fc) {
                $outPre = (int) ($fc->outstanding_pre ?? 0);
                $stockPre = (int) ($fc->stock_pre ?? $fc->stock_qty ?? 0);
                $prod = (int) ($fc->production_qty ?? $fc->production ?? 0);

                $fc->update([
                    'po'             => $m1PoSum,
                    'po_qty'         => $m1PoSum,
                    'delivery'       => $delSum,
                    'outstanding'    => $outPre + $m1PoSum - $delSum,
                    'stock'          => $stockPre + $delSum - $prod,
                    'stock_qty'      => $stockPre + $delSum - $prod,
                ]);
            }
        }
    }

    public function updateMasterPo(Request $request, $id)
    {
        $mp = \App\Models\MasterPo::findOrFail($id);
        $itemCode = $this->normalizePoValue($request->input('item_code'));
        $poNumber = $this->normalizePoValue($request->input('po'));

        if (\App\Models\MasterPo::where('po', $poNumber)->where('item_code', $itemCode)->where('id', '!=', $mp->id)->exists()) {
            return redirect()->back()->with('error', 'Pasangan Nomor PO dan Item Code sudah digunakan.');
        }
        $mp->update([
            'tanggal'   => $request->input('tanggal'),
            'supplier'  => $request->input('supplier'),
            'po'        => $poNumber,
            'item_code' => $itemCode,
            'name'      => $request->input('name'),
            'qty'       => (int) $request->input('qty', 0),
            'price'     => $request->has('price') ? (float) str_replace(',', '.', (string) $request->input('price')) : ($mp->price ?? 0.0),
            'currency'  => strtoupper(trim($request->input('currency', $mp->currency ?? 'USD'))),
            'delivery_category_code' => $request->input('delivery_category_code', $mp->delivery_category_code ?? 'LOC'),
        ]);

        $this->syncForecastFromMasterPo($itemCode, $request->input('tanggal'));

        \App\Models\PurchasingOutstanding::clearCalcCaches();

        return redirect()->back()->with('success', 'Data Master PO berhasil diperbarui.');
    }

    public function destroyMasterPo($id)
    {
        $mp = \App\Models\MasterPo::findOrFail($id);
        $itemCode = $mp->item_code;
        $mp->delete();

        $this->syncForecastFromMasterPo($itemCode);

        \App\Models\PurchasingOutstanding::clearCalcCaches();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Baris Master PO berhasil dihapus.']);
        }

        return redirect()->back()->with('success', 'Baris Master PO berhasil dihapus.');
    }

    /**
     * Halaman Manajemen Kategori Material
     */
    public function categories()
    {
        $categories = PurchasingCategory::with(['buyer'])
            ->withSum('logs', 'actual_received')
            ->withSum('logs', 'target_order')
            ->withCount('logs')
            ->orderBy('id', 'desc')
            ->get();
        $buyers = User::orderBy('name')->get();

        return view('purchasing.categories', compact('categories', 'buyers'));
    }


    /**
     * Simpan Kategori Baru
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'category_code' => 'required|string|unique:purchasing_categories,category_code',
            'category_name' => 'required|string',
            'buyer_user_id' => 'required|exists:users,id',
            'monthly_target_units' => 'required|integer|min:1',
            'status' => 'required|in:Active,Review,Hold',
        ], [
            'category_code.unique' => 'Kode kategori tersebut sudah ada. Silakan gunakan kode lain (misal: PUR-05).',
            'category_code.required' => 'Kode kategori wajib diisi.',
            'category_name.required' => 'Nama kategori wajib diisi.',
            'buyer_user_id.required' => 'Silakan pilih PIC Procurement / Buyer.',
            'monthly_target_units.required' => 'Target pengadaan bulanan wajib diisi.'
        ]);

        $buyer = User::findOrFail($validated['buyer_user_id']);
        $validated['pic_buyer'] = $buyer->name;

        PurchasingCategory::create($validated);

        return redirect()->route('purchasing.categories')
            ->with('success', 'Kategori material baru (' . $validated['category_code'] . ' - ' . $validated['category_name'] . ') berhasil ditambahkan.');
    }

    /**
     * Update Kategori Material
     */
    public function updateCategory(Request $request, $id)
    {
        $category = PurchasingCategory::findOrFail($id);

        $validated = $request->validate([
            'category_code' => 'required|string|unique:purchasing_categories,category_code,' . $category->id,
            'category_name' => 'required|string',
            'buyer_user_id' => 'required|exists:users,id',
            'monthly_target_units' => 'required|integer|min:1',
            'status' => 'required|in:Active,Review,Hold',
        ], [
            'category_code.unique' => 'Kode kategori tersebut sudah ada. Silakan gunakan kode lain.',
            'category_code.required' => 'Kode kategori wajib diisi.',
            'category_name.required' => 'Nama kategori wajib diisi.',
            'buyer_user_id.required' => 'Silakan pilih PIC Procurement / Buyer.',
            'monthly_target_units.required' => 'Target pengadaan bulanan wajib diisi.'
        ]);

        $buyer = User::findOrFail($validated['buyer_user_id']);
        $validated['pic_buyer'] = $buyer->name;

        $category->update($validated);

        return redirect()->route('purchasing.categories')
            ->with('success', 'Kategori material (' . $category->category_code . ') berhasil diperbarui.');
    }

    /**
     * Hapus Kategori Material
     */
    public function destroyCategory($id)
    {
        $category = PurchasingCategory::findOrFail($id);
        $code = $category->category_code;
        $category->delete();

        return redirect()->route('purchasing.categories')
            ->with('success', 'Kategori material (' . $code . ') berhasil dihapus.');
    }

    /**
     * Bersihkan semua log testing / dummy
     */
    public function clearLogs()
    {
        PurchasingLog::truncate();
        return redirect()->route('dashboard.overview')
            ->with('success', 'Seluruh data log testing dihapus. Dashboard siap untuk data asli pengadaan.');
    }

    public function downloadTemplate()
    {
        $headers = [
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=template_master_po_ezrunner.xls",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $existingData = \App\Models\MasterPo::orderBy('tanggal', 'desc')->get();

            echo '<?xml version="1.0"?>
<?mso-application classid="g:Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Worksheet ss:Name="Sheet1">
  <Table>
   <Row>
    <Cell><Data ss:Type="String">Supplier Name</Data></Cell>
    <Cell><Data ss:Type="String">Delivery Date</Data></Cell>
    <Cell><Data ss:Type="String">Material Code</Data></Cell>
    <Cell><Data ss:Type="String">Description</Data></Cell>
    <Cell><Data ss:Type="String">PO No.</Data></Cell>
    <Cell><Data ss:Type="String">Currency</Data></Cell>
    <Cell><Data ss:Type="String">Price</Data></Cell>
    <Cell><Data ss:Type="String">Plan</Data></Cell>
   </Row>';

            if ($existingData->isEmpty()) {
                echo '<Row>
    <Cell><Data ss:Type="String">PT SUPPLIER INDAH</Data></Cell>
    <Cell><Data ss:Type="String">01-Jul-26</Data></Cell>
    <Cell><Data ss:Type="String">1312006</Data></Cell>
    <Cell><Data ss:Type="String">Bracket Compou</Data></Cell>
    <Cell><Data ss:Type="String">KI-TJT-0023/2026</Data></Cell>
    <Cell><Data ss:Type="String">IDR</Data></Cell>
    <Cell><Data ss:Type="Number">8470</Data></Cell>
    <Cell><Data ss:Type="Number">210</Data></Cell>
   </Row>';
            } else {
                foreach ($existingData as $po) {
                    $tanggal = $po->tanggal ? date('d-M-y', strtotime($po->tanggal)) : '';
                    $supplier = htmlspecialchars($po->supplier ?? '', ENT_XML1, 'UTF-8');
                    $poNum = htmlspecialchars($po->po ?? '', ENT_XML1, 'UTF-8');
                    $item = htmlspecialchars($po->item_code ?? '', ENT_XML1, 'UTF-8');
                    $name = htmlspecialchars($po->name ?? '', ENT_XML1, 'UTF-8');
                    $currency = htmlspecialchars($po->currency ?? 'USD', ENT_XML1, 'UTF-8');
                    $price = (float)($po->price ?? 0);
                    $qty = (int)$po->qty;

                    echo "
   <Row>
    <Cell><Data ss:Type=\"String\">{$supplier}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$tanggal}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$item}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$name}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$poNum}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$currency}</Data></Cell>
    <Cell><Data ss:Type=\"Number\">{$price}</Data></Cell>
    <Cell><Data ss:Type=\"Number\">{$qty}</Data></Cell>
   </Row>";
                }
            }

            echo '
  </Table>
 </Worksheet>
</Workbook>';
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importMasterPo(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        try {
            $file = $request->file('file');
            $realPath = $file->getRealPath();
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($realPath);
                if (method_exists($reader, 'setReadDataOnly')) {
                    $reader->setReadDataOnly(true);
                }
                $spreadsheet = $reader->load($realPath);
            } catch (\Throwable $e) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($realPath);
            }

            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, false, true, true);

            if (empty($rows)) {
                return redirect()->back()->with('error', 'File Excel kosong atau tidak terbaca.');
            }

            // Temukan baris header secara intuitif berdasarkan skor kecocokan label kolom terbesar
            $headerRowIdx = null;
            $bestMatchCount = 0;
            foreach ($rows as $idx => $row) {
                if ($idx > 25) break;
                $matchCount = 0;
                foreach ($row as $col => $val) {
                    $cleanVal = strtoupper(trim($val ?? ''));
                    if (
                        str_contains($cleanVal, 'MATERIAL CODE') || str_contains($cleanVal, 'ITEM CODE') || str_contains($cleanVal, 'ITEM_CODE') ||
                        str_contains($cleanVal, 'PO NO') || str_contains($cleanVal, 'PO NUMBER') || str_contains($cleanVal, 'NO. PO') ||
                        str_contains($cleanVal, 'SUPPLIER') || str_contains($cleanVal, 'DELIVERY DATE') || str_contains($cleanVal, 'TANGGAL')
                    ) {
                        $matchCount++;
                    }
                }
                if ($matchCount > $bestMatchCount) {
                    $bestMatchCount = $matchCount;
                    $headerRowIdx = $idx;
                }
            }

            if (!$headerRowIdx) {
                $headerRowIdx = 1;
            }

            $headerRow = $rows[$headerRowIdx];
            $tglCol = 'C'; $suppCol = 'B'; $poCol = 'F'; $itemCol = 'D'; $nameCol = 'E';
            $qtyCol = 'I'; $priceCol = 'H'; $currCol = 'G';

            foreach ($headerRow as $col => $val) {
                $aboveVal = isset($rows[$headerRowIdx - 1][$col]) ? strtoupper(trim($rows[$headerRowIdx - 1][$col])) : '';
                $cleanVal = strtoupper(trim($val ?? ''));
                $combined = trim($aboveVal . ' ' . $cleanVal);

                if (str_contains($combined, 'TANGGAL') || str_contains($combined, 'DATE')) {
                    $tglCol = $col;
                } elseif (str_contains($combined, 'SUPPLIER') || str_contains($combined, 'VENDOR') || str_contains($combined, 'TRADE')) {
                    $suppCol = $col;
                } elseif (
                    str_contains($combined, 'MATERIAL CODE') || str_contains($combined, 'ITEM CODE') || str_contains($combined, 'ITEM_CODE') ||
                    str_contains($combined, 'PART NUMBER') || str_contains($combined, 'DRAWING') || $combined === 'PN' || $combined === 'PART NO'
                ) {
                    $itemCol = $col;
                } elseif (
                    str_contains($combined, 'DESCRIPTION') || str_contains($combined, 'DESKRIPSI') || $combined === 'ITEM_NAME' || $combined === 'ITEM NAME' ||
                    (str_contains($combined, 'NAMA') && !str_contains($combined, 'SUPPLIER'))
                ) {
                    $nameCol = $col;
                } elseif (
                    $combined === 'PO' || $combined === 'NO PO' || $combined === 'PO NO.' || $combined === 'NO. PO' ||
                    str_contains($combined, 'PO_NO') || str_contains($combined, 'PO NO') || str_contains($combined, 'PO NUMBER') || str_contains($combined, 'NOMOR PO')
                ) {
                    $poCol = $col;
                } elseif (str_contains($combined, 'CURRENCY') || str_contains($combined, 'MATA UANG') || str_contains($combined, 'KURS')) {
                    $currCol = $col;
                } elseif (str_contains($combined, 'PRICE') || str_contains($combined, 'HARGA')) {
                    $priceCol = $col;
                } elseif ((str_contains($combined, 'PLAN') || str_contains($combined, 'QTY') || str_contains($combined, 'TARGET')) && !str_contains($combined, 'AMOUNT') && !str_contains($combined, 'AMT')) {
                    $qtyCol = $col;
                }
            }

            $defaultCurrency = strtoupper(trim($request->input('import_currency', $request->input('currency', 'USD'))));
            if (!in_array($defaultCurrency, ['USD', 'IDR'])) {
                $defaultCurrency = 'USD';
            }

            // Pre-fetch in-memory maps for Step 1 (Outstanding) & Forecasting to eliminate N+1 queries during bulk import
            // Pre-fetch in-memory maps for Step 1 (Outstanding) & Forecasting with lightweight column selection
            $outstandingMap = [];
            foreach (\App\Models\PurchasingOutstanding::select(['id', 'part_number', 'drawing', 'description', 'category_id', 'factory_code'])->get() as $o) {
                if (!empty($o->part_number)) $outstandingMap[strtoupper(trim($o->part_number))] = $o;
                if (!empty($o->drawing))     $outstandingMap[strtoupper(trim($o->drawing))] = $o;
                if (!empty($o->description)) $outstandingMap[strtoupper(trim($o->description))] = $o;
            }

            $forecastingMap = [];
            foreach (\App\Models\Forecasting::select(['id', 'part_number', 'description', 'currency', 'price'])->get() as $f) {
                if (!empty($f->part_number)) $forecastingMap[strtoupper(trim($f->part_number))] = $f;
                if (!empty($f->description)) $forecastingMap[strtoupper(trim($f->description))] = $f;
            }

            $toInsert = [];
            $errors = [];
            $dataRows = array_slice($rows, $headerRowIdx, null, true);

            foreach ($dataRows as $rowNum => $row) {
                $rawPo = trim((string)($row[$poCol] ?? ''));
                $rawItem = trim((string)($row[$itemCol] ?? ''));
                $rawSupp = trim((string)($row[$suppCol] ?? ''));

                $poUpper = strtoupper($rawPo);
                $itemUpper = strtoupper($rawItem);
                $suppUpper = strtoupper($rawSupp);

                if (
                    $rawPo === '' || $rawItem === '' ||
                    in_array($poUpper, ['PO', 'PO NO', 'PO NO.', 'PO NUMBER', 'PO_NO', 'NOMOR PO', 'DELIVERY DATE', 'TANGGAL', 'DATE', 'NO. PO']) ||
                    in_array($itemUpper, ['MATERIAL CODE', 'ITEM CODE', 'ITEM_CODE', 'PART NUMBER', 'DRAWING', 'PART NO', 'MATERIAL_CODE']) ||
                    in_array($suppUpper, ['SUPPLIER NAME', 'SUPPLIER', 'VENDOR NAME', 'VENDOR'])
                ) {
                    continue;
                }

                $po = $this->normalizePoValue($rawPo);
                $item_code = $this->normalizePoValue($rawItem);

                $tanggal = trim($row[$tglCol] ?? '');
                $supplier = !empty($row[$suppCol]) ? trim($row[$suppCol]) : null;
                $name = trim($row[$nameCol] ?? '-');
                $qty = $this->parseImportQuantity($row[$qtyCol] ?? 0);
                if ($qty < 0) {
                    $errors[] = "Baris {$rowNum}: Qty PO tidak boleh negatif.";
                    continue;
                }
                $price = !empty($priceCol) && isset($row[$priceCol]) ? $this->parseImportPrice($row[$priceCol]) : 0.0;

                $currencyVal = $defaultCurrency;
                if (!empty($currCol) && !empty($row[$currCol])) {
                    $rawC = strtoupper(trim((string)$row[$currCol]));
                    if (in_array($rawC, ['IDR', 'RUPIAH', 'RP', 'IDR (RP)']) || str_contains($rawC, 'RP') || str_contains($rawC, 'RUPIAH')) {
                        $currencyVal = 'IDR';
                    } elseif (in_array($rawC, ['USD', 'DOLLAR', '$', 'USD ($)']) || str_contains($rawC, 'USD') || str_contains($rawC, '$')) {
                        $currencyVal = 'USD';
                    }
                } elseif (!empty($priceCol) && !empty($row[$priceCol])) {
                    $rawP = strtoupper(trim((string)$row[$priceCol]));
                    if (str_contains($rawP, 'RP') || str_contains($rawP, 'RUPIAH')) {
                        $currencyVal = 'IDR';
                    } elseif (str_contains($rawP, '$') || str_contains($rawP, 'USD')) {
                        $currencyVal = 'USD';
                    }
                }

                // In-memory matching (0 ms) vs N+1 queries
                $itemClean = strtoupper(trim($item_code));
                $nameClean = strtoupper(trim($name));

                $matchedStep1 = $outstandingMap[$itemClean] ?? ($outstandingMap[$nameClean] ?? null);
                $matchedForecast = !$matchedStep1 ? ($forecastingMap[$itemClean] ?? ($forecastingMap[$nameClean] ?? null)) : null;

                if ($matchedStep1) {
                    $canonicalItemCode = !empty($item_code) ? $item_code : ($matchedStep1->part_number ?: $matchedStep1->drawing);
                    $canonicalName = (!empty($name) && $name !== '-') ? $name : ($matchedStep1->description ?: $canonicalItemCode);
                } elseif ($matchedForecast) {
                    $canonicalItemCode = !empty($item_code) ? $item_code : $matchedForecast->part_number;
                    $canonicalName = (!empty($name) && $name !== '-') ? $name : ($matchedForecast->description ?: $canonicalItemCode);
                } else {
                    $canonicalItemCode = $item_code;
                    $canonicalName = !empty($name) && $name !== '-' ? $name : $item_code;
                }

                $toInsert[] = [
                    'tanggal'    => $this->parseExcelDate($tanggal),
                    'supplier'   => $supplier,
                    'po'         => $po,
                    'item_code'  => $canonicalItemCode,
                    'name'       => $canonicalName,
                    'qty'        => $qty,
                    'price'      => $price,
                    'currency'   => $currencyVal,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($errors)) {
                return redirect()->back()->with('error', 'Gagal mengimpor data. Beberapa baris tidak valid:<br>' . implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '<br>...dan kesalahan lainnya.' : ''));
            }

            if (count($toInsert) > 0) {
                // Setiap baris pada file Excel merupakan jadwal pengiriman/kontrak tersendiri.
                // Disimpan secara presisi 1:1 tanpa penggabungan baris.
                \DB::transaction(function () use ($toInsert) {
                    foreach (array_chunk($toInsert, 500) as $chunk) {
                        foreach ($chunk as $row) {
                            \App\Models\MasterPo::create([
                                'tanggal'    => $row['tanggal'],
                                'supplier'   => $row['supplier'],
                                'po'         => $row['po'],
                                'item_code'  => $row['item_code'],
                                'name'       => $row['name'],
                                'qty'        => $row['qty'],
                                'price'      => $row['price'],
                                'currency'   => $row['currency'],
                                'user_id'    => $row['created_by'],
                                'created_by' => $row['created_by'],
                            ]);
                        }
                    }
                });

                $importedItemCodes = collect($toInsert)->pluck('item_code')->unique();
                foreach ($importedItemCodes as $code) {
                    $this->syncForecastFromMasterPo($code);
                }
            }

            return redirect()->back()->with('success', 'Berhasil mengimpor ' . count($toInsert) . ' data Master PO dari file Excel/CSV.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses file Excel/CSV: ' . $e->getMessage());
        }
    }

    public function downloadInputTemplate()
    {
        $headers = [
            "Content-type"        => "application/vnd.ms-excel",
            "Content-Disposition" => "attachment; filename=template_realisasi_po_ezrunner.xls",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() {
            $existingLogs = \App\Models\PurchasingLog::orderBy('receipt_date', 'desc')->get();

            echo '<?xml version="1.0"?>
<?mso-application classid="g:Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <Worksheet ss:Name="Sheet1">
  <Table>
   <Row>
    <Cell><Data ss:Type="String">Supplier Name</Data></Cell>
    <Cell><Data ss:Type="String">Delivery Date</Data></Cell>
    <Cell><Data ss:Type="String">Material Code</Data></Cell>
    <Cell><Data ss:Type="String">Description</Data></Cell>
    <Cell><Data ss:Type="String">PO No.</Data></Cell>
    <Cell><Data ss:Type="String">Currency</Data></Cell>
    <Cell><Data ss:Type="String">Price</Data></Cell>
    <Cell><Data ss:Type="String">Plan</Data></Cell>
    <Cell><Data ss:Type="String">Result</Data></Cell>
   </Row>';

            if ($existingLogs->isEmpty()) {
                echo '<Row>
    <Cell><Data ss:Type="String">PT. TRI JAYA TEKNIK KARAWANG</Data></Cell>
    <Cell><Data ss:Type="String">01-Jul-26</Data></Cell>
    <Cell><Data ss:Type="String">1312006</Data></Cell>
    <Cell><Data ss:Type="String">Bracket Compou</Data></Cell>
    <Cell><Data ss:Type="String">KI-TJT-0023/2026</Data></Cell>
    <Cell><Data ss:Type="String">IDR</Data></Cell>
    <Cell><Data ss:Type="Number">8470</Data></Cell>
    <Cell><Data ss:Type="Number">210</Data></Cell>
    <Cell><Data ss:Type="Number">210</Data></Cell>
   </Row>';
            } else {
                foreach ($existingLogs as $log) {
                    $tanggal = $log->receipt_date ? date('d-M-y', strtotime($log->receipt_date)) : '';
                    $supplier = htmlspecialchars($log->supplier_name ?? '', ENT_XML1, 'UTF-8');
                    $item = htmlspecialchars($log->item_code ?? '', ENT_XML1, 'UTF-8');
                    $poNum = htmlspecialchars($log->po_reference ?? '', ENT_XML1, 'UTF-8');
                    $name = htmlspecialchars($log->item_name ?? '', ENT_XML1, 'UTF-8');
                    $currency = htmlspecialchars($log->currency ?? 'USD', ENT_XML1, 'UTF-8');
                    $price = (float)($log->price ?? 0);
                    $target = (int)$log->target_order;
                    $actual = (int)$log->actual_received;

                    echo "
   <Row>
    <Cell><Data ss:Type=\"String\">{$supplier}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$tanggal}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$item}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$name}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$poNum}</Data></Cell>
    <Cell><Data ss:Type=\"String\">{$currency}</Data></Cell>
    <Cell><Data ss:Type=\"Number\">{$price}</Data></Cell>
    <Cell><Data ss:Type=\"Number\">{$target}</Data></Cell>
    <Cell><Data ss:Type=\"Number\">{$actual}</Data></Cell>
   </Row>";
                }
            }

            echo '
  </Table>
 </Worksheet>
</Workbook>';
        };

        return response()->stream($callback, 200, $headers);
    }

    public function storeLogBulk(Request $request)
    {
        @set_time_limit(300);

        $rows = $request->input('rows');

        if ($request->hasFile('file') || $request->hasFile('excel_file')) {
            try {
                $file = $request->file('file') ?: $request->file('excel_file');
                $realPath = $file->getRealPath();
                try {
                    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($realPath);
                    if (method_exists($reader, 'setReadDataOnly')) {
                        $reader->setReadDataOnly(true);
                    }
                    $spreadsheet = $reader->load($realPath);
                } catch (\Throwable $e) {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($realPath);
                }

                $sheet = $spreadsheet->getActiveSheet();
                $fileRows = $sheet->toArray(null, false, true, true);

                if (!empty($fileRows)) {
                    $headerIdx = null;
                    $headerIdx = null;
                    $bestMatch = 0;
                    foreach ($fileRows as $idx => $r) {
                        if ($idx > 25) break;
                        $matchCount = 0;
                        foreach ($r as $col => $val) {
                            $cleanVal = strtoupper(trim($val ?? ''));
                            if (
                                str_contains($cleanVal, 'MATERIAL CODE') || str_contains($cleanVal, 'ITEM CODE') || str_contains($cleanVal, 'ITEM_CODE') ||
                                str_contains($cleanVal, 'PO NO') || str_contains($cleanVal, 'PO NUMBER') || str_contains($cleanVal, 'NO. PO') ||
                                str_contains($cleanVal, 'SUPPLIER') || str_contains($cleanVal, 'DELIVERY DATE') || str_contains($cleanVal, 'TANGGAL')
                            ) {
                                $matchCount++;
                            }
                        }
                        if ($matchCount > $bestMatch) {
                            $bestMatch = $matchCount;
                            $headerIdx = $idx;
                        }
                    }
                    if (!$headerIdx) $headerIdx = 1;

                    $header = $fileRows[$headerIdx];
                    $tglC = 'C'; $suppC = 'B'; $itemC = 'D'; $poC = 'F'; $nameC = 'E'; $targetC = 'I'; $actualC = 'K'; $priceC = 'H'; $amountC = 'J'; $currencyC = 'G';
                    foreach ($header as $c => $val) {
                        $aboveVal = isset($fileRows[$headerIdx - 1][$c]) ? strtoupper(trim($fileRows[$headerIdx - 1][$c])) : '';
                        $cv = strtoupper(trim($val ?? ''));
                        $combined = trim($aboveVal . ' ' . $cv);

                        if (str_contains($combined, 'TANGGAL') || str_contains($combined, 'RECEIPT') || str_contains($combined, 'DATE')) $tglC = $c;
                        elseif (str_contains($combined, 'SUPPLIER') || str_contains($combined, 'VENDOR') || str_contains($combined, 'TRADE')) $suppC = $c;
                        elseif ($cv === 'ITEM_CODE' || $cv === 'ITEM CODE' || str_contains($combined, 'MATERIAL CODE') || str_contains($combined, 'PART') || str_contains($combined, 'DRAWING') || $cv === 'PN') $itemC = $c;
                        elseif ($cv === 'ITEM_NAME' || $cv === 'ITEM NAME' || str_contains($combined, 'DESKRIPSI') || str_contains($combined, 'DESCRIPTION') || (str_contains($combined, 'NAMA') && !str_contains($combined, 'SUPPLIER'))) $nameC = $c;
                        elseif ($cv === 'PO' || str_contains($combined, 'PO NUMBER') || str_contains($combined, 'NOMOR PO') || str_contains($combined, 'PO_NO') || str_contains($combined, 'PO NO')) $poC = $c;
                        elseif (str_contains($combined, 'CURRENCY') || str_contains($combined, 'MATA UANG') || str_contains($combined, 'KURS')) $currencyC = $c;
                        elseif (str_contains($combined, 'PRICE') || str_contains($combined, 'HARGA')) $priceC = $c;
                        elseif ((str_contains($combined, 'PLAN') || str_contains($combined, 'TARGET')) && !str_contains($combined, 'AMOUNT') && !str_contains($combined, 'AMT')) $targetC = $c;
                        elseif (str_contains($combined, 'AMOUNT') || str_contains($combined, 'TOTAL')) { if (!$amountC) $amountC = $c; }
                        elseif (str_contains($combined, 'RESULT') || str_contains($combined, 'ACTUAL') || str_contains($combined, 'RECEIVED') || str_contains($combined, 'DITERIMA') || str_contains($combined, 'AKTU') || $cv === 'QTY') $actualC = $c;
                    }

                    $parsedRows = [];
                    foreach (array_slice($fileRows, $headerIdx, null, true) as $excelRow => $r) {
                        $ic = trim((string)($r[$itemC] ?? ''));
                        $poNum = trim((string)($r[$poC] ?? ''));
                        $suppVal = trim((string)($r[$suppC] ?? ''));

                        $poUpper = strtoupper($poNum);
                        $itemUpper = strtoupper($ic);
                        $suppUpper = strtoupper($suppVal);

                        if (
                            $ic === '' || $poNum === '' ||
                            in_array($poUpper, ['PO', 'PO NO', 'PO NO.', 'PO NUMBER', 'PO_NO', 'NOMOR PO', 'DELIVERY DATE', 'TANGGAL', 'DATE', 'NO. PO']) ||
                            in_array($itemUpper, ['MATERIAL CODE', 'ITEM CODE', 'ITEM_CODE', 'PART NUMBER', 'DRAWING', 'PART NO', 'MATERIAL_CODE']) ||
                            in_array($suppUpper, ['SUPPLIER NAME', 'SUPPLIER', 'VENDOR NAME', 'VENDOR'])
                        ) {
                            continue;
                        }

                        $parsedRows[] = [
                            'tanggal'  => trim($r[$tglC] ?? ''),
                            'supplier' => trim($r[$suppC] ?? ''),
                            'itemcode' => $ic,
                            'po'       => $poNum,
                            'name'     => trim($r[$nameC] ?? ''),
                            'target_order' => $this->parseImportQuantity($r[$targetC] ?? 0),
                            'actual'       => $this->parseImportQuantity($r[$actualC] ?? 0),
                            '_excel_row'   => $excelRow,
                            'price'    => $priceC ? trim($r[$priceC] ?? '') : null,
                            'currency' => $currencyC ? trim($r[$currencyC] ?? '') : null,
                            'amount'   => $amountC ? trim($r[$amountC] ?? '') : null,
                        ];
                    }
                    $rows = $parsedRows;
                }
            } catch (\Exception $e) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Gagal membaca berkas Excel: ' . $e->getMessage()], 422);
                }
                return redirect()->back()->with('error', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            }
        }

        if (!is_array($rows) || count($rows) === 0) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Tidak ada data yang dikirim.'], 422);
            }
            return redirect()->back()->with('error', 'Tidak ada data yang terbaca dari file.');
        }

        $defaultCurrency = strtoupper(trim($request->input('import_currency', $request->input('currency', 'USD'))));
        if (!in_array($defaultCurrency, ['USD', 'IDR'])) {
            $defaultCurrency = 'USD';
        }

        // High performance pre-fetching to prevent query overload on large Excel files
        $masterPoMap = [];
        foreach (\App\Models\MasterPo::all() as $m) {
            $key = strtoupper(trim($m->po)) . '___' . strtoupper(trim($m->item_code));
            $masterPoMap[$key] = $m;
        }

        $receivedSumsMap = [];
        $logSums = \App\Models\PurchasingLog::selectRaw('po_reference, item_code, SUM(actual_received) as total_received')
            ->groupBy('po_reference', 'item_code')
            ->get();
        foreach ($logSums as $ls) {
            $key = strtoupper(trim($ls->po_reference)) . '___' . strtoupper(trim($ls->item_code));
            $receivedSumsMap[$key] = (int)$ls->total_received;
        }

        // File realisasi SGS/SRN kadang memuat PO baru yang belum muncul di
        // snapshot Master PO. Bentuk *satu* master rekonsiliasi per PO + Item
        // dari total satu file, bukan per baris (cara lama yang membuat target
        // hanya sebesar penerimaan pertama dan memicu overstanding).
        $missingMasterCandidates = [];
        foreach ($rows as $candidate) {
            $candidatePo = !empty($candidate['po']) ? $this->normalizePoValue($candidate['po']) : '';
            $candidateItem = !empty($candidate['itemcode']) ? $this->normalizePoValue($candidate['itemcode']) : '';
            if ($candidatePo === '' || $candidateItem === '') continue;

            $candidateKey = $candidatePo . '___' . $candidateItem;
            if (isset($masterPoMap[$candidateKey])) continue;

            if (!isset($missingMasterCandidates[$candidateKey])) {
                $missingMasterCandidates[$candidateKey] = [
                    'po' => $candidatePo,
                    'item_code' => $candidateItem,
                    'tanggal' => !empty($candidate['tanggal']) ? $this->parseExcelDate($candidate['tanggal']) : date('Y-m-d'),
                    'supplier' => !empty($candidate['supplier']) ? trim($candidate['supplier']) : null,
                    'name' => !empty($candidate['name']) ? trim($candidate['name']) : $candidateItem,
                    'qty' => 0,
                ];
            }
            $rowTarget = isset($candidate['target_order']) && $candidate['target_order'] > 0 ? (int)$candidate['target_order'] : (int)($candidate['actual'] ?? 0);
            $missingMasterCandidates[$candidateKey]['qty'] += max(0, $rowTarget);
        }

        foreach ($missingMasterCandidates as $candidateKey => $candidate) {
            // Jika pernah ada realisasi lama tanpa Master, target minimalnya
            // juga harus menampung realisasi lama tersebut.
            $candidate['qty'] += max(0, $receivedSumsMap[$candidateKey] ?? 0);
            $masterPoMap[$candidateKey] = (object) $candidate;
        }

        $outstandingMap = [];
        foreach (\DB::table('purchasing_outstandings')->get() as $o) {
            if (!empty($o->drawing)) $outstandingMap[strtoupper(trim($o->drawing))] = $o;
            if (!empty($o->part_number)) $outstandingMap[strtoupper(trim($o->part_number))] = $o;
        }

        $forecastingMap = [];
        foreach (\App\Models\Forecasting::where('price', '>', 0)->get() as $f) {
            if (!empty($f->part_number)) $forecastingMap[strtoupper(trim($f->part_number))] = (float)$f->price;
        }

        $firstCat = \App\Models\PurchasingCategory::where('status', 'Active')->first() 
                 ?? \App\Models\PurchasingCategory::first();
        $fallbackCategoryId = $firstCat ? $firstCat->id : 1;

        $toInsert = [];
        $errors = [];
        $rowNumber = 0;

        foreach ($rows as $r) {
            $rowNumber++;
            $tanggal = !empty($r['tanggal']) ? $r['tanggal'] : null;
            $supplier = !empty($r['supplier']) ? $r['supplier'] : null;
            $item_code = !empty($r['itemcode']) ? $this->normalizePoValue($r['itemcode']) : null;
            $po = !empty($r['po']) ? $this->normalizePoValue($r['po']) : null;
            $name = !empty($r['name']) ? $r['name'] : null;
            $actual = isset($r['actual']) ? $this->parseImportQuantity($r['actual']) : 0;
            $displayRowNumber = $r['_excel_row'] ?? $rowNumber;

            if (empty($item_code) || empty($po)) {
                $errors[] = "Baris {$displayRowNumber}: Item Code dan No PO wajib diisi.";
                continue;
            }
            if ($actual < 0) {
                $errors[] = "Baris {$displayRowNumber}: Qty Penerimaan tidak boleh negatif.";
                continue;
            }

            $rawPrice = $r['price'] ?? null;
            $price = ($rawPrice !== null && $rawPrice !== '') ? $this->parseImportPrice($rawPrice) : 0.0;

            $currencyVal = $defaultCurrency;
            if (!empty($r['currency'])) {
                $rawC = strtoupper(trim((string)$r['currency']));
                if (in_array($rawC, ['IDR', 'RUPIAH', 'RP', 'IDR (RP)']) || str_contains($rawC, 'RP') || str_contains($rawC, 'RUPIAH')) {
                    $currencyVal = 'IDR';
                } elseif (in_array($rawC, ['USD', 'DOLLAR', '$', 'USD ($)']) || str_contains($rawC, 'USD') || str_contains($rawC, '$')) {
                    $currencyVal = 'USD';
                }
            } elseif ($rawPrice !== null && $rawPrice !== '') {
                $rawP = strtoupper(trim((string)$rawPrice));
                if (str_contains($rawP, 'RP') || str_contains($rawP, 'RUPIAH')) {
                    $currencyVal = 'IDR';
                } elseif (str_contains($rawP, '$') || str_contains($rawP, 'USD')) {
                    $currencyVal = 'USD';
                }
            }

            $poItemKey = $po . '___' . $item_code;
            $masterPo = $masterPoMap[$poItemKey] ?? null;

            $alreadyReceived = $receivedSumsMap[$poItemKey] ?? 0;

            // Target Order: Gunakan target_order dari baris Excel (Plan) jika ada, atau fallback ke Master PO
            $targetOrderRow = isset($r['target_order']) && $r['target_order'] > 0 ? (int)$r['target_order'] : (int)($masterPo->qty ?? 0);
            if ($targetOrderRow <= 0) {
                $targetOrderRow = $actual;
            }

            if ($masterPo && isset($masterPo->qty) && (int)$masterPo->qty > 0 && ($alreadyReceived + $actual) > (int)$masterPo->qty) {
                $remaining = max(0, (int)$masterPo->qty - $alreadyReceived);
                $itemNameStr = $name ?: ($masterPo->name ?: $item_code);
                $errors[] = "Baris {$displayRowNumber} [Item: {$item_code} - {$itemNameStr} | PO: {$po}]: Qty Penerimaan ({$actual} unit) melebihi batas sisa PO (Sisa boleh diterima: {$remaining} unit, Total Qty PO: {$masterPo->qty} unit).";
                continue;
            }

            // Update in-memory sum for subsequent rows in same Excel batch
            $receivedSumsMap[$poItemKey] = $alreadyReceived + $actual;

            if (!empty($r['tanggal'])) {
                $tanggal = $this->parseExcelDate($r['tanggal']);
            } elseif ($masterPo && !empty($masterPo->tanggal)) {
                $tanggal = $this->parseExcelDate($masterPo->tanggal);
            } else {
                $tanggal = date('Y-m-d');
            }
            $periodMonth = date('Y-m', strtotime($tanggal));

            $outstandingItem = $outstandingMap[$item_code] ?? null;
            $categoryId = $outstandingItem ? $outstandingItem->category_id : $fallbackCategoryId;

            if ($price <= 0 && $masterPo && isset($masterPo->price) && (float)$masterPo->price > 0) {
                $price = (float) $masterPo->price;
            }
            if ($price <= 0 && $outstandingItem && (float)$outstandingItem->price > 0) {
                $price = (float) $outstandingItem->price;
            }
            if ($price <= 0 && isset($forecastingMap[$item_code])) {
                $price = $forecastingMap[$item_code];
            }

            $rawAmount = $r['amount'] ?? null;
            $amount = ($rawAmount !== null && $rawAmount !== '') ? $this->parseImportPrice($rawAmount) : ($actual * $price);
            if ($amount <= 0 && $price > 0 && $actual > 0) {
                $amount = $actual * $price;
            }

            $toInsert[] = [
                'purchasing_category_id' => $categoryId,
                'user_id' => Auth::id(),
                'receipt_date' => $tanggal,
                'item_code' => $item_code,
                'po_reference' => $po,
                'item_name' => $masterPo->name ?: ($name ?: 'Material Item'),
                'supplier_name' => !empty($masterPo->supplier) ? $masterPo->supplier : (!empty($supplier) ? $supplier : null),
                'period_month' => $periodMonth,
                'target_order' => $targetOrderRow,
                'actual_received' => $actual,
                'price' => $price,
                'currency' => $currencyVal,
                'amount' => $amount,
                'production_qty' => 0,
                'status_note' => 'Impor via Excel Bulk',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (count($errors) > 0) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Gagal mengimpor data. Beberapa kesalahan ditemukan:',
                    'errors' => array_slice($errors, 0, 5)
                ], 422);
            }
            return redirect()->back()->with('error', 'Gagal mengimpor data. Kesalahan: <br>' . implode('<br>', array_slice($errors, 0, 5)));
        }

        $reconciledMasterCount = count($missingMasterCandidates);

        if (count($toInsert) > 0) {
            try {
                // Semua baris disimpan sebagai satu unit kerja: tidak boleh ada impor
                // setengah berhasil saat satu batch besar mengalami kegagalan database.
                \DB::transaction(function () use ($toInsert, $missingMasterCandidates) {
                    foreach ($missingMasterCandidates as $key => $candidate) {
                        \App\Models\MasterPo::updateOrCreate(
                            ['po' => $candidate['po'], 'item_code' => $candidate['item_code']],
                            [
                                'tanggal' => $candidate['tanggal'],
                                'supplier' => $candidate['supplier'],
                                'name' => $candidate['name'],
                                'qty' => $candidate['qty'],
                                'created_by' => Auth::id(), 'user_id' => Auth::id(),
                            ]
                        );
                    }
                    foreach (array_chunk($toInsert, 500) as $chunk) {
                        PurchasingLog::insert($chunk);
                    }
                });
            } catch (\Exception $e) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['message' => 'Gagal menyimpan data ke database: ' . $e->getMessage()], 422);
                }
                return redirect()->back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Realisasi Penerimaan PO berhasil disimpan ('.count($toInsert).' baris).' . ($reconciledMasterCount ? " {$reconciledMasterCount} pasangan PO + Item baru direkonsiliasi ke Master PO." : '')]);
        }

        return redirect()->back()->with('success', 'Berhasil mengimpor ' . count($toInsert) . ' data Realisasi Penerimaan PO dari Excel.' . ($reconciledMasterCount ? " {$reconciledMasterCount} pasangan PO + Item baru direkonsiliasi ke Master PO." : ''));
    }

    /**
     * Hapus terpilih Master PO (Bulk Delete).
     */
    public function destroyMasterPoBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return redirect()->back()->with('error', 'Tidak ada data terpilih untuk dihapus.');
            }

            \App\Models\MasterPo::whereIn('id', $ids)->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus massal.']);
            }
            return redirect()->back()->with('success', 'Data terpilih berhasil dihapus massal.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus terpilih Log Realisasi Penerimaan PO (Bulk Delete).
     */
    public function destroyLogBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return redirect()->back()->with('error', 'Tidak ada data terpilih untuk dihapus.');
            }

            PurchasingLog::whereIn('id', $ids)->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus massal.']);
            }
            return redirect()->back()->with('success', 'Data terpilih berhasil dihapus massal.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus seluruh data Master PO (Reset Total).
     */
    public function destroyMasterPoAll(Request $request)
    {
        try {
            \App\Models\MasterPo::query()->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Seluruh data Master PO (Step 2) berhasil dikosongkan.']);
            }
            return redirect()->route('purchasing.master-po')->with('success', 'Seluruh data Master PO (Step 2) berhasil dikosongkan.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengosongkan data Master PO: ' . $e->getMessage());
        }
    }

    /**
     * Hapus seluruh data Realisasi Penerimaan PO / Log Masuk (Reset Total).
     */
    public function destroyLogAll(Request $request)
    {
        try {
            PurchasingLog::query()->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Seluruh data Realisasi Penerimaan PO (Step 3) berhasil dikosongkan.']);
            }
            return redirect()->route('purchasing.input')->with('success', 'Seluruh data Realisasi Penerimaan PO (Step 3) berhasil dikosongkan.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengosongkan log penerimaan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus terpilih Kategori Material (Bulk Delete).
     */
    public function destroyCategoryBulk(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (empty($ids)) {
                return redirect()->back()->with('error', 'Tidak ada data terpilih untuk dihapus.');
            }

            PurchasingCategory::whereIn('id', $ids)->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Data terpilih berhasil dihapus massal.']);
            }
            return redirect()->back()->with('success', 'Data terpilih berhasil dihapus massal.');
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Helper privat untuk mengonversi berbagai format tanggal dari Excel/CSV menjadi format YYYY-MM-DD yang valid.
     */
    private function parseExcelDate($val): string
    {
        if (empty($val)) {
            return date('Y-m-d');
        }

        $val = trim((string)$val);

        // Strip time if present (e.g. "10/01/2026 00:00:00" -> "10/01/2026")
        if (preg_match('/^(\d{1,2}[\/\.\-]\d{1,2}[\/\.\-]\d{4})/', $val, $mTime)) {
            $val = $mTime[1];
        } elseif (preg_match('/^(\d{4}[\/\.\-]\d{1,2}[\/\.\-]\d{1,2})/', $val, $mTime)) {
            $val = $mTime[1];
        }

        if (is_numeric($val)) {
            $num = (float)$val;
            if ($num > 1000) {
                $unixDate = ($num - 25569) * 86400;
                return date('Y-m-d', (int)$unixDate);
            }
        }

        if (preg_match('/^(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{4})$/', $val, $matches)) {
            $first = (int)$matches[1];
            $second = (int)$matches[2];
            $year = (int)$matches[3];

            // If second number is > 12, it must be MM/DD/YYYY (first is month, second is day)
            if ($second > 12 && checkdate($first, $second, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $first, $second);
            }
            
            // If first number is > 12, it must be DD/MM/YYYY (first is day, second is month)
            if ($first > 12 && checkdate($second, $first, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $second, $first);
            }

            // Standard Indonesian Excel format: DD/MM/YYYY ($first = Day, $second = Month)
            if (checkdate($second, $first, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $second, $first);
            }
            if (checkdate($first, $second, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $first, $second);
            }
        }

        if (preg_match('/^(\d{4})[\/\.\-](\d{1,2})[\/\.\-](\d{1,2})$/', $val, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('/^(\d{4})[\/\.\-](\d{1,2})$/', $val, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            if ($month >= 1 && $month <= 12) {
                return sprintf('%04d-%02d-01', $year, $month);
            }
        }

        $cleanDate = str_replace(['/', '.'], '-', $val);
        $ts = strtotime($cleanDate);
        if ($ts !== false && $ts > 0) {
            return date('Y-m-d', $ts);
        }

        return date('Y-m-d');
    }

    /** Normalisasi identitas bisnis agar " PO-01 ", NBSP, dan "po-01" adalah satu PO. */
    private function normalizePoValue($value): string
    {
        $value = str_replace(["\xC2\xA0", "\u{200B}"], ' ', (string) $value);
        return strtoupper(trim((string) preg_replace('/\s+/u', ' ', $value)));
    }

    /**
     * Qty Excel kadang dikirim sebagai 100000, 100.000, 2.000,00, atau 100,000.
     */
    private function parseImportQuantity($value): int
    {
        if (is_int($value)) return $value;
        if (is_float($value)) return (int) round($value);

        $value = trim((string) $value);
        if ($value === '' || $value === '-') return 0;

        $value = preg_replace('/[^\d,\.\-]/', '', $value);
        if ($value === '' || $value === '-') return 0;

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');
            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma) {
            $parts = explode(',', $value);
            if (count($parts) > 1 && strlen(end($parts)) === 3) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif ($hasDot) {
            $parts = explode('.', $value);
            if (count($parts) > 2) {
                $value = str_replace('.', '', $value);
            } elseif (count($parts) === 2) {
                $decimals = end($parts);
                if (strlen($decimals) === 3) {
                    $value = str_replace('.', '', $value);
                } elseif ($decimals === '00' || $decimals === '0') {
                    $value = $parts[0];
                }
            }
        }

        return (int) round((float) $value);
    }

    /**
     * Price Excel (e.g. 8.470, 41.000, 226.013, 1.778.700, 8470).
     * Mencegah "8.470" terpotong menjadi 8.47 karena PHP native float casting.
     */
    private function parseImportPrice($value): float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }
        $value = trim((string) $value);
        if ($value === '' || $value === '-') return 0.0;

        $value = preg_replace('/[^\d,\.\-]/', '', $value);
        if ($value === '' || $value === '-') return 0.0;

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            $lastComma = strrpos($value, ',');
            $lastDot = strrpos($value, '.');
            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma) {
            $parts = explode(',', $value);
            if (count($parts) > 1 && strlen(end($parts)) === 3) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif ($hasDot) {
            $parts = explode('.', $value);
            if (count($parts) > 2) {
                $value = str_replace('.', '', $value);
            } elseif (count($parts) === 2) {
                $decimals = end($parts);
                if (strlen($decimals) === 3) {
                    $value = str_replace('.', '', $value);
                }
            }
        }

        return (float) $value;
    }
}
