<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use App\Models\ProductionLog;
use App\Models\PurchasingOutstanding;
use App\Models\PurchasingLog;
use App\Models\PurchasingCategory;
use App\Models\MasterPo;
use App\Models\ActualProduction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $selectedLineId = $request->get('line_id');
        $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
        $selectedYear = (int) $request->get('year', 2026);
        $selectedCategoryId = $request->get('category_id');
        $selectedUserId = $request->get('user_id');

        $buyerUsers = \App\Models\User::orderBy('id', 'asc')->get();

        // ── 1. Production Line & Logs ──
        $linesQuery = ProductionLine::query();
        $allLines = $linesQuery->get();

        $logsQuery = ProductionLog::with('line')
            ->whereDate('log_time', $selectedDate);

        if ($selectedLineId) {
            $logsQuery->where('production_line_id', $selectedLineId);
        }

        $logs = $logsQuery->orderBy('log_time', 'asc')->get();

        $totalProduksi = $logs->sum('actual_output');
        $targetProduksi = $logs->sum('target_output');
        $achievementPercentage = $targetProduksi > 0 
            ? round(($totalProduksi / $targetProduksi) * 100, 1) 
            : 0;

        $totalLinesCount = $allLines->count();
        $activeLinesCount = $allLines->where('status', 'Running')->count();

        $statusProduksi = 'On Progress';
        if ($achievementPercentage >= 100) {
            $statusProduksi = 'Target Achieved';
        } elseif ($achievementPercentage < 85) {
            $statusProduksi = 'Attention Needed';
        }

        $totalDefects = $logs->sum('defect_count');
        $qualityRate = $totalProduksi > 0 
            ? round(100 - (($totalDefects / $totalProduksi) * 100), 2) 
            : 100;

        // Hourly Actual vs Target
        $hourlyGrouped = $logs->groupBy(function ($item) {
            return Carbon::parse($item->log_time)->format('H:00');
        });

        $hourlyLabels = [];
        $hourlyActual = [];
        $hourlyTarget = [];

        foreach ($hourlyGrouped as $hourLabel => $hourLogs) {
            $hourlyLabels[] = $hourLabel;
            $hourlyActual[] = $hourLogs->sum('actual_output');
            $hourlyTarget[] = $hourLogs->sum('target_output');
        }

        // Line Performances
        $lineNames = [];
        $lineActuals = [];
        $lineTargets = [];
        $linePerformances = [];

        foreach ($allLines as $line) {
            $lineLogs = $logs->where('production_line_id', $line->id);
            $lineActual = $lineLogs->sum('actual_output');
            $lineTarget = $lineLogs->sum('target_output');
            $lineAch = $lineTarget > 0 ? round(($lineActual / $lineTarget) * 100, 1) : 0;

            $lineNames[] = $line->line_code . ' (' . $line->line_name . ')';
            $lineActuals[] = $lineActual;
            $lineTargets[] = $lineTarget;

            $linePerformances[] = [
                'id' => $line->id,
                'code' => $line->line_code,
                'name' => $line->line_name,
                'category' => $line->product_category,
                'supervisor' => $line->supervisor,
                'status' => $line->status,
                'actual' => $lineActual,
                'target' => $lineTarget,
                'achievement' => $lineAch,
                'defects' => $lineLogs->sum('defect_count'),
            ];
        }

        // ── 2. Purchasing Outstandings ──
        $allOutstandingsQuery = PurchasingOutstanding::query();
        if ($selectedCategoryId) {
            $allOutstandingsQuery->where('category_id', $selectedCategoryId);
        }
        $allOutstandings = $allOutstandingsQuery->get();
        $poTotalCount    = $allOutstandings->count();
        $poTotalAmount   = $allOutstandings->sum('amount');
        $poOrderUnits    = $allOutstandings->sum('order_qty');
        $poCompleteUnits = $allOutstandings->sum('complete');
        $poPendingUnits  = max(0, $poOrderUnits - $poCompleteUnits);
        $poProgress      = $poOrderUnits > 0 ? round(($poCompleteUnits / $poOrderUnits) * 100, 1) : 0;

        $poWaitingManager  = $allOutstandings->whereIn('workflow_stage', ['waiting_manager', 'revision_manager'])->count();
        $poWaitingSupplier = $allOutstandings->whereIn('workflow_stage', ['approved_manager', 'waiting_supplier', 'revision_supplier'])->count();
        $poShipped         = $allOutstandings->where('workflow_stage', 'material_shipped')->count();
        $poIadCheck        = $allOutstandings->whereIn('workflow_stage', ['iad_check', 'iad_rejected'])->count();
        $poCompleted       = $allOutstandings->where('workflow_stage', 'completed')->count();

        $outstandingsTotalAmount = $allOutstandings->sum(function($po) {
            $amt = (float) ($po->amount ?? 0);
            if ($amt <= 0) {
                $q = (float) ($po->order_qty ?? 0);
                $p = (float) ($po->price ?? 0);
                $amt = $q * $p;
            }
            return $amt;
        });

        $latestOutstandings = PurchasingOutstanding::orderBy('updated_at', 'desc')->take(6)->get()->map(function($po) {
            $po->computed_order_qty = (int) ($po->order_qty ?? 0);
            $amt = (float) ($po->amount ?? 0);
            if ($amt <= 0) {
                $amt = $po->computed_order_qty * (float) ($po->price ?? 0);
            }
            $po->computed_amount = $amt;
            return $po;
        });

        // ── 3. Purchasing Logs & Realisasi Bulanan ──
        $logsQuery = PurchasingLog::with('category');
        if ($selectedCategoryId) {
            $logsQuery->where('purchasing_category_id', $selectedCategoryId);
        }
        if ($selectedUserId) {
            $logsQuery->where('user_id', $selectedUserId);
        }
        $allInputLogs = $logsQuery->get();

        // Filter logs for selected year
        $yearLogs = $allInputLogs->filter(function($item) use ($selectedYear) {
            $p = (string) ($item->period_month ?: $item->receipt_date);
            if (empty($p)) return true;
            return str_starts_with($p, (string)$selectedYear);
        });

        if ($yearLogs->isEmpty() && $allInputLogs->isNotEmpty()) {
            // Fallback to all logs if selected year has no logs
            $yearLogs = $allInputLogs;
        }

        $inputTotalLogs     = $yearLogs->count();
        $inputTargetUnits   = $yearLogs->sum('target_order');
        $inputReceivedUnits = $yearLogs->sum('actual_received');
        $inputPendingUnits  = max(0, $inputTargetUnits - $inputReceivedUnits);
        $inputAchievement   = $inputTargetUnits > 0 ? round(($inputReceivedUnits / $inputTargetUnits) * 100, 1) : 0;

        $totalAmountReceived = $yearLogs->sum(function($l) {
            $p = (float) ($l->price ?? 0);
            $rec = (int) ($l->actual_received ?? 0);
            return (float) ($l->amount > 0 ? $l->amount : ($rec * $p));
        });

        $totalAmountTarget = $yearLogs->sum(function($l) {
            $p = (float) ($l->price ?? 0);
            $tgt = (int) ($l->target_order ?? 0);
            return $tgt * $p;
        });

        // Grouping Log Input Realisasi per (period_month + purchasing_category_id)
        $groupedLogsCollDash = $yearLogs->groupBy(function ($l) {
            return $l->period_month . '_' . $l->purchasing_category_id;
        });

        $latestInputLogs = [];
        foreach ($groupedLogsCollDash as $gKey => $logsGrp) {
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
                        'actual_received' => (int) $lItem->actual_received,
                        'target_order'    => (int) ($lItem->target_order ?: $mTarget),
                        'pending_diff'    => max(0, ($lItem->target_order ?: $mTarget) - $lItem->actual_received),
                        'user_name'       => $lItem->user->name ?? 'System',
                    ];
                })->values()->toArray(),
            ];
        }
        $latestInputLogs = collect($latestInputLogs)->take(6);

        // ── 4. Categories & Category Performances ──
        $categories = PurchasingCategory::all();
        $totalCategoriesCount = $categories->count();
        $activeCategoriesCount = $categories->where('status', 'Active')->count() ?: $totalCategoriesCount;
        $statusPurchasing = $inputAchievement >= 90 ? 'Target Achieved' : 'On Progress';

        $categoryPerformances = [];
        $categoryNames        = [];
        $categoryReceived     = [];
        $categoryLogCounts    = [];

        foreach ($categories as $cat) {
            $catLogs   = $yearLogs->where('purchasing_category_id', $cat->id);
            $cTarget   = $catLogs->sum('target_order');
            $cReceived = $catLogs->sum('actual_received');
            if ($cTarget == 0 && $cReceived == 0) {
                $cStatus = 'Standby';
            } elseif ($cAch >= 100) {
                $cStatus = 'Fulfilled';
            } elseif ($cAch >= 75) {
                $cStatus = 'On Track';
            } else {
                $cStatus = 'Supply Alert';
            }

            $categoryPerformances[] = [
                'code'        => $cat->category_code,
                'name'        => $cat->category_name,
                'buyer'       => $cat->pic_buyer ?: ($cat->buyer->name ?? 'Procurement KI'),
                'buyer_role'  => 'Leader',
                'target'      => $cTarget,
                'received'    => $cReceived,
                'pending'     => $cPending,
                'achievement' => $cAch,
                'log_count'   => $catLogs->count(),
                'status'      => $cStatus,
            ];

            $categoryNames[]     = $cat->category_code . ' (' . $cat->category_name . ')';
            $categoryReceived[]  = (int) $cReceived;
            $categoryLogCounts[] = $catLogs->count();
        }

        // ── 5. Multi-User Contributions (Konsolidasi Global Hasil Seluruh User) ──
        $buyerContributions = [];
        foreach ($buyerUsers as $u) {
            $uLogs = $allInputLogs->where('user_id', $u->id);
            $uRec  = (int) $uLogs->sum('actual_received');
            $uTgt  = (int) $uLogs->sum('target_order');
            $uPen  = max(0, $uTgt - $uRec);
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

        // ── 6. Monthly Aggregations (12 Bulan Jan - Des: Konsolidasi Global Master Forecast) ──
        $monthlyLabels   = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyReceived = [];
        $monthlyTarget   = [];
        $monthlyPending  = [];

        for ($m = 1; $m <= 12; $m++) {
            $mStr = sprintf('%04d-%02d', $selectedYear, $m);
            $mLogs = $yearLogs->filter(function ($item) use ($m) {
                $p = (string) ($item->period_month ?: $item->receipt_date);
                if (empty($p)) return false;
                try {
                    return Carbon::parse($p)->month === $m;
                } catch (\Throwable $e) {
                    return false;
                }
            });

            $rec = (int) $mLogs->sum('actual_received');

            $fcQuery = \Illuminate\Support\Facades\DB::table('forecastings')->where(function($q) use ($mStr) {
                $q->where('periode', $mStr)->orWhere('period_month', $mStr);
            });
            if ($selectedUserId) {
                $fcQuery->where('user_id', $selectedUserId);
            }
            $mFc = (int) $fcQuery->sum('po_qty');
            if ($mFc <= 0) {
                $rawFc = (int) $fcQuery->sum('forecast_qty');
                $mFc = ($rawFc > 500000) ? 0 : $rawFc;
            }

            $tgt = max($mFc, (int) $mLogs->sum('target_order'));
            $pen = max(0, $tgt - $rec);

            $monthlyReceived[] = $rec;
            $monthlyTarget[]   = $tgt;
            $monthlyPending[]  = $pen;
        }

        // ── 7. Monthly Stock Breakdown & Categories Used ──
        $prodLogs = ActualProduction::all();
        $runningStock = 0;
        $runningStockUsd = 0.0;
        $monthlyStockBreakdown = [];

        // Build price dictionary for DashboardController
        $itemPriceMap = [];
        foreach (\App\Models\PurchasingOutstanding::where('price', '>', 0)->select(['part_number', 'drawing', 'price', 'currency'])->get() as $po) {
            $curr = \App\Services\Normalization\CurrencyNormalizer::detectCurrency($po->currency, (float)$po->price);
            if ($po->part_number) $itemPriceMap[strtoupper(trim($po->part_number))] = ['price' => (float)$po->price, 'currency' => $curr];
            if ($po->drawing) $itemPriceMap[strtoupper(trim($po->drawing))] = ['price' => (float)$po->price, 'currency' => $curr];
        }

        for ($m = 1; $m <= 12; $m++) {
            $mLogs = $yearLogs->filter(function ($item) use ($m) {
                $p = (string) ($item->period_month ?: $item->receipt_date);
                if (empty($p)) return false;
                try {
                    return Carbon::parse($p)->month === $m;
                } catch (\Throwable $e) {
                    return false;
                }
            });

            $mProds = $prodLogs->filter(function ($item) use ($m, $selectedYear) {
                $p = (string) ($item->tanggal_produksi ?: $item->production_date ?: $item->period);
                if (empty($p)) return false;
                try {
                    $c = Carbon::parse($p);
                    return $c->year == $selectedYear && $c->month == $m;
                } catch (\Throwable $e) {
                    return false;
                }
            });

            $recQty = (int) $mLogs->sum('actual_received');
            $prodQty = (int) $mProds->sum('qty');
            $runningStock = max(0, $runningStock + $recQty - $prodQty);

            // Compute USD Amounts
            $recUsd = 0.0;
            foreach ($mLogs as $l) {
                $q = (float) $l->actual_received;
                $p = (float) $l->price;
                $c = $l->currency ?: 'USD';
                if ($p <= 0 && $l->item_code) {
                    $k = strtoupper(trim($l->item_code));
                    $p = $itemPriceMap[$k]['price'] ?? 0;
                    $c = $itemPriceMap[$k]['currency'] ?? $c;
                }
                if ($q > 0 && $p > 0) {
                    $recUsd += \App\Services\Normalization\CurrencyNormalizer::convertToUsd($q * $p, $c, (int)$selectedYear, $m);
                }
            }

            $prodUsd = 0.0;
            foreach ($mProds as $ap) {
                $q = (float) $ap->qty;
                $k = strtoupper(trim($ap->item_code ?? ''));
                $p = $itemPriceMap[$k]['price'] ?? 0;
                $c = $ap->currency ?: ($itemPriceMap[$k]['currency'] ?? 'USD');
                if ($q > 0 && $p > 0) {
                    $prodUsd += \App\Services\Normalization\CurrencyNormalizer::convertToUsd($q * $p, $c, (int)$selectedYear, $m);
                }
            }

            $runningStockUsd = max(0.0, $runningStockUsd + $recUsd - $prodUsd);

            $catsUsed = $mLogs->pluck('category.category_name')->filter()->unique()->values()->toArray();
            if (empty($catsUsed) && $recQty > 0) {
                $catsUsed = ['Material Umum'];
            }

            $monthlyStockBreakdown[] = [
                'num'                   => sprintf('%02d', $m),
                'label'                 => $monthlyLabels[$m - 1],
                'received_qty'          => $recQty,
                'production_qty'        => $prodQty,
                'stock_end'             => $runningStock,
                'received_amount_usd'   => $recUsd,
                'production_amount_usd' => $prodUsd,
                'stock_end_usd'         => $runningStockUsd,
                'categories_used'       => $catsUsed,
            ];
        }

        $totalStockReceivedUsd   = array_sum(array_column($monthlyStockBreakdown, 'received_amount_usd'));
        $totalStockProductionUsd = array_sum(array_column($monthlyStockBreakdown, 'production_amount_usd'));
        $latestStockEndUsd       = count($monthlyStockBreakdown) > 0 ? end($monthlyStockBreakdown)['stock_end_usd'] : 0.0;

        // ── 8. Master PO Data ──
        $masterPosQuery     = MasterPo::query();
        $masterPoTotalCount = $masterPosQuery->count();
        $masterPoTotalQty   = (int) $masterPosQuery->sum('qty');
        $latestMasterPos    = MasterPo::orderBy('id', 'desc')->take(6)->get();

        // ── 9. User Matrix & RBAC ──
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
            'categories'              => $categories,
            'buyerUsers'              => $buyerUsers,
            'selectedUserId'          => $selectedUserId,
            'buyerContributions'      => $buyerContributions,
            'selectedYear'            => $selectedYear,
            'selectedCategoryId'      => $selectedCategoryId,
            'totalReceived'           => $inputReceivedUnits,
            'targetOrder'             => $inputTargetUnits,
            'fulfillmentPercentage'   => $inputAchievement,
            'totalPending'            => $inputPendingUnits,
            'totalAmountReceived'     => $totalAmountReceived,
            'totalAmountTarget'       => $totalAmountTarget,
            'statusPurchasing'        => $statusPurchasing,
            'totalCategoriesCount'    => $totalCategoriesCount,
            'activeCategoriesCount'   => $activeCategoriesCount,
            'categoryPerformances'    => $categoryPerformances,
            'userRolesAndAndil'       => $userRolesAndAndil,
            'categoryNames'           => $categoryNames,
            'categoryReceived'        => $categoryReceived,
            'categoryReceiveds'       => $categoryReceived,
            'categoryLogCounts'       => $categoryLogCounts,
            'monthlyLabels'           => $monthlyLabels,
            'monthlyReceived'         => $monthlyReceived,
            'monthlyTarget'           => $monthlyTarget,
            'monthlyPending'          => $monthlyPending,
            'monthlyStockBreakdown'   => $monthlyStockBreakdown,
            'totalStockReceivedUsd'   => $totalStockReceivedUsd,
            'totalStockProductionUsd' => $totalStockProductionUsd,
            'latestStockEndUsd'       => $latestStockEndUsd,
            'poTotalCount'            => $poTotalCount,
            'poTotalAmount'           => $poTotalAmount,
            'poOrderUnits'            => $poOrderUnits,
            'poCompleteUnits'         => $poCompleteUnits,
            'poPendingUnits'          => $poPendingUnits,
            'poProgress'              => $poProgress,
            'poWaitingManager'        => $poWaitingManager,
            'poWaitingSupplier'       => $poWaitingSupplier,
            'poShipped'               => $poShipped,
            'poIadCheck'              => $poIadCheck,
            'poCompleted'             => $poCompleted,
            'outstandingsTotalAmount' => $outstandingsTotalAmount,
            'latestOutstandings'      => $latestOutstandings,
            'masterPoTotalCount'      => $masterPoTotalCount,
            'masterPoTotalQty'        => $masterPoTotalQty,
            'latestMasterPos'         => $latestMasterPos,
            'inputTotalLogs'          => $inputTotalLogs,
            'inputTargetUnits'        => $inputTargetUnits,
            'inputReceivedUnits'      => $inputReceivedUnits,
            'inputPendingUnits'       => $inputPendingUnits,
            'inputAchievement'        => $inputAchievement,
            'latestInputLogs'         => $latestInputLogs,
            'lastUpdated'             => Carbon::now('Asia/Jakarta')->format('d M Y, H:i:s') . ' WIB'
        ]);
    }

    /**
     * API endpoint live feed EZRunner
     */
    public function liveFeed(Request $request)
    {
        $selectedLineId = $request->get('line_id');
        $today = Carbon::today();
        
        $logsQuery = ProductionLog::whereDate('log_time', $today);
        if ($selectedLineId) {
            $logsQuery->where('production_line_id', $selectedLineId);
        }
        $logs = $logsQuery->get();

        $totalProduksi = $logs->sum('actual_output');
        $targetProduksi = $logs->sum('target_output');
        $achievement = $targetProduksi > 0 ? round(($totalProduksi / $targetProduksi) * 100, 1) : 0;

        return response()->json([
            'status' => 'success',
            'ezrunner_sync' => 'Connected (Live)',
            'timestamp' => Carbon::now()->format('H:i:s'),
            'metrics' => [
                'total_produksi' => number_format($totalProduksi, 0, ',', '.'),
                'target_produksi' => number_format($targetProduksi, 0, ',', '.'),
                'achievement' => $achievement . '%',
                'status_produksi' => $achievement >= 90 ? 'On Progress' : 'Attention Needed'
            ]
        ]);
    }
}
