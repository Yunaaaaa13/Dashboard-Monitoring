<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PurchasingLog;
use App\Models\MasterPo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Tampilkan Halaman Manajemen User & Hak Akses (Privileges)
     */
    public function index(Request $request)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak. Hanya Admin yang diizinkan mengelola User Management & Hak Akses.');
        }

        $search = $request->get('search');
        $roleFilter = $request->get('role', 'ALL');

        $query = User::query();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== 'ALL') {
            $query->where('role', $roleFilter);
        }

        $users = $query->orderBy('id', 'asc')->get();

        // Metrics
        $totalUsers = User::count();
        $adminCount = User::whereIn('role', ['admin', 'administrator'])->count();
        $supervisorCount = User::where('role', 'supervisor')->count();
        $staffCount = User::whereIn('role', ['staff', 'leader'])->count();

        // Available modules/permissions list for checkboxes
        $allPermissions = [
            'step1' => 'Step 1: Forecast & Plan Stock',
            'step2' => 'Step 2: Master PO',
            'step3' => 'Step 3: Realisasi Penerimaan',
            'step4' => 'Step 4: Outstanding PO',
            'step5' => 'Step 5: Aktual Produksi',
            'step6' => 'Step 6: Hasil Akhir & Komparasi',
            'categories' => 'Manajemen Kategori Material',
            'exchange_rate' => 'Manajemen & Input Kurs Pajak',
            'user_management' => 'Manajemen User & Akses',
            'view_user_monitoring' => 'Izin Monitoring Pengisian User Lain',
            'history_audit' => 'History & Audit Log',
            'delete_rights' => 'Hak Hapus Data (Delete)',
        ];

        return view('users.index', compact(
            'users',
            'totalUsers',
            'adminCount',
            'supervisorCount',
            'staffCount',
            'allPermissions',
            'search',
            'roleFilter'
        ));
    }

    /**
     * Tambah User Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:admin,supervisor,leader,staff,viewer',
            'department' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'can_view_user_monitoring' => 'nullable|boolean',
        ], [
            'username.unique' => 'User Name sudah digunakan oleh pengguna lain.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 6 karakter.',
        ]);

        $permissions = $validated['permissions'] ?? [];
        if ($validated['role'] === 'admin') {
            $permissions = ['*'];
        }

        $canViewMonitoring = $request->has('can_view_user_monitoring') 
            ? (bool) $request->input('can_view_user_monitoring') 
            : ($validated['role'] === 'admin');

        User::create([
            'username' => strtolower(trim($validated['username'])),
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'employee_id' => 'EMP-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
            'role' => $validated['role'],
            'department' => $validated['department'] ?: 'Purchasing & Procurement',
            'password' => Hash::make($validated['password']),
            'permissions' => $permissions,
            'can_view_user_monitoring' => $canViewMonitoring,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User baru "' . $validated['name'] . '" (' . strtoupper($validated['role']) . ') berhasil dibuat!');
    }

    /**
     * Update User & Hak Akses (Privileges)
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|in:admin,supervisor,leader,staff,viewer',
            'department' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'can_view_user_monitoring' => 'nullable|boolean',
        ], [
            'username.unique' => 'User Name tersebut sudah digunakan oleh pengguna lain.',
            'email.unique' => 'Email tersebut sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $permissions = $validated['permissions'] ?? [];
        if ($validated['role'] === 'admin') {
            $permissions = ['*'];
        }

        $canViewMonitoring = $request->has('can_view_user_monitoring') 
            ? (bool) $request->input('can_view_user_monitoring') 
            : $user->can_view_user_monitoring;

        if ($validated['role'] === 'admin') {
            $canViewMonitoring = true;
        }

        $updateData = [
            'username' => strtolower(trim($validated['username'])),
            'name' => $validated['name'],
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'department' => array_key_exists('department', $validated) ? $validated['department'] : $user->department,
            'permissions' => $permissions,
            'can_view_user_monitoring' => $canViewMonitoring,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'Data & Hak Akses User "' . $user->name . '" berhasil diperbarui!');
    }

    /**
     * Toggle Akses Monitoring User Lain oleh Admin
     */
    public function toggleMonitoringAccess(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang dapat memodifikasi hak akses.'], 403);
        }

        $user = User::findOrFail($id);
        $user->can_view_user_monitoring = !$user->can_view_user_monitoring;
        $user->save();

        $statusText = $user->can_view_user_monitoring ? 'DIBERI HAK AKSES' : 'DIKEMBALIKAN KE NORMAL';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Hak akses monitoring pengisian user lain untuk {$user->name} berhasil {$statusText}.",
                'can_view' => $user->can_view_user_monitoring
            ]);
        }

        return redirect()->back()->with('success', "Hak akses monitoring pengisian user lain untuk {$user->name} berhasil {$statusText}.");
    }

    /**
     * Simpan Catatan Komunikasi Inter-Divisi Admin per User
     */
    public function updateNote(Request $request, $id)
    {
        if (!Auth::user() || !Auth::user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Hanya Admin yang dapat menyimpan catatan evaluasi.'], 403);
        }

        $user = User::findOrFail($id);
        $user->admin_note = $request->input('admin_note');
        $user->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Catatan komunikasi & evaluasi untuk {$user->name} berhasil diperbarui.",
                'note' => $user->admin_note
            ]);
        }

        return redirect()->back()->with('success', "Catatan komunikasi & evaluasi untuk {$user->name} berhasil diperbarui.");
    }

    /**
     * Dashboard Monitoring Performa Staff & Analisis Inter-Divisi Purchasing
     */
    public function monitoring(Request $request)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->canViewUserMonitoring()) {
            return redirect()->route('dashboard.overview')
                ->with('error', 'Akses Ditolak. Anda memerlukan persetujuan dan hak akses dari Admin untuk memonitoring hasil pengisian user lain.');
        }

        $search = $request->get('search');
        $selectedPeriod = $request->get('period');

        // Jika periode tidak dispesifikasikan oleh user, secara otomatis ambil bulan terbaru yang memiliki data di database
        if (!$selectedPeriod) {
            $latestLogPeriod = PurchasingLog::max('period_month');
            if (!$latestLogPeriod) {
                $latestReceipt = PurchasingLog::max('receipt_date');
                $latestLogPeriod = $latestReceipt ? date('Y-m', strtotime($latestReceipt)) : null;
            }
            if (!$latestLogPeriod) {
                $latestMasterPo = \App\Models\MasterPo::max('tanggal');
                $latestLogPeriod = $latestMasterPo ? date('Y-m', strtotime($latestMasterPo)) : null;
            }
            $selectedPeriod = $latestLogPeriod ?: date('Y-m');
        }

        $currentMonthStart = date('Y-m-01', strtotime($selectedPeriod . '-01'));
        $currentMonthEnd = date('Y-m-t', strtotime($selectedPeriod . '-01'));
        $lastMonthStr = date('Y-m', strtotime('-1 month', strtotime($currentMonthStart)));
        $lastMonthStart = date('Y-m-01', strtotime('-1 month', strtotime($currentMonthStart)));
        $lastMonthEnd = date('Y-m-t', strtotime('-1 month', strtotime($currentMonthStart)));

        $usersQuery = User::query();
        if ($search) {
            $usersQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $usersList = $usersQuery->orderBy('id', 'asc')->get();

        $staffAnalytics = [];
        $totalLogsCount = 0;
        $totalReceivedQtyOverall = 0;
        $activeStaffCount = 0;

        foreach ($usersList as $u) {
            $logsQuery = PurchasingLog::where('user_id', $u->id);

            $totalLogs = (clone $logsQuery)->count();
            $totalTargetOrder = (int) (clone $logsQuery)->sum('target_order');
            $totalActualReceived = (int) (clone $logsQuery)->sum('actual_received');
            $totalProductionQty = (int) (clone $logsQuery)->sum('production_qty');

            // Hitung Penerimaan PO (actual_received) Bulan Ini vs Bulan Lalu
            $currentMonthPo = (int) (clone $logsQuery)->where(function($q) use ($selectedPeriod, $currentMonthStart, $currentMonthEnd) {
                $q->where('period_month', $selectedPeriod)
                  ->orWhereBetween('receipt_date', [$currentMonthStart, $currentMonthEnd]);
            })->sum('actual_received');

            $lastMonthPo = (int) (clone $logsQuery)->where(function($q) use ($lastMonthStr, $lastMonthStart, $lastMonthEnd) {
                $q->where('period_month', $lastMonthStr)
                  ->orWhereBetween('receipt_date', [$lastMonthStart, $lastMonthEnd]);
            })->sum('actual_received');

            // Hitung Produksi/Output (jika production_qty di log 0, gunakan PO actual_received atau gabungkan dengan ActualProduction)
            $currentMonthProd = (int) (clone $logsQuery)->where(function($q) use ($selectedPeriod, $currentMonthStart, $currentMonthEnd) {
                $q->where('period_month', $selectedPeriod)
                  ->orWhereBetween('receipt_date', [$currentMonthStart, $currentMonthEnd]);
            })->sum('production_qty');

            if ($currentMonthProd <= 0) {
                $currentMonthProd = $currentMonthPo;
            }

            $lastMonthProd = (int) (clone $logsQuery)->where(function($q) use ($lastMonthStr, $lastMonthStart, $lastMonthEnd) {
                $q->where('period_month', $lastMonthStr)
                  ->orWhereBetween('receipt_date', [$lastMonthStart, $lastMonthEnd]);
            })->sum('production_qty');

            if ($lastMonthProd <= 0) {
                $lastMonthProd = $lastMonthPo;
            }

            // Periksa jika user menginput di ActualProduction
            $uActualProdCurrent = \App\Models\ActualProduction::where('user_id', $u->id)
                ->where('tanggal_produksi', 'like', $selectedPeriod . '-%')
                ->sum('qty');
            if ($uActualProdCurrent > 0) {
                $currentMonthProd = (int) $uActualProdCurrent;
            }

            $uActualProdLast = \App\Models\ActualProduction::where('user_id', $u->id)
                ->where('tanggal_produksi', 'like', $lastMonthStr . '-%')
                ->sum('qty');
            if ($uActualProdLast > 0) {
                $lastMonthProd = (int) $uActualProdLast;
            }

            // Hitung MoM Trend
            $prodDiff = $currentMonthProd - $lastMonthProd;
            $prodTrendPct = $lastMonthProd > 0 ? round(($prodDiff / $lastMonthProd) * 100, 1) : ($currentMonthProd > 0 ? 100 : 0);

            $poDiff = $currentMonthPo - $lastMonthPo;
            $poTrendPct = $lastMonthPo > 0 ? round(($poDiff / $lastMonthPo) * 100, 1) : ($currentMonthPo > 0 ? 100 : 0);

            // Under & Over delivery logs handled by user
            $underDeliveryCount = (clone $logsQuery)->whereRaw('actual_received < target_order')->count();
            $overDeliveryCount = (clone $logsQuery)->whereRaw('actual_received > target_order')->count();
            $lastActivity = (clone $logsQuery)->max('created_at');

            if ($totalLogs > 0) {
                $activeStaffCount++;
            }

            $totalLogsCount += $totalLogs;
            $totalReceivedQtyOverall += $totalActualReceived;

            $staffAnalytics[] = [
                'user'                  => $u,
                'total_logs'            => $totalLogs,
                'total_target'          => $totalTargetOrder,
                'total_received'        => $totalActualReceived,
                'total_production'      => $totalProductionQty,
                'current_month_prod'    => $currentMonthProd,
                'last_month_prod'       => $lastMonthProd,
                'prod_diff'             => $prodDiff,
                'prod_trend_pct'        => $prodTrendPct,
                'current_month_po'      => $currentMonthPo,
                'last_month_po'         => $lastMonthPo,
                'po_diff'               => $poDiff,
                'po_trend_pct'          => $poTrendPct,
                'under_delivery_count'  => $underDeliveryCount,
                'over_delivery_count'   => $overDeliveryCount,
                'last_activity'         => $lastActivity ? date('d/m/Y H:i', strtotime($lastActivity)) : '-',
            ];
        }

        return view('users.monitoring', compact(
            'staffAnalytics',
            'totalLogsCount',
            'totalReceivedQtyOverall',
            'activeStaffCount',
            'search',
            'selectedPeriod'
        ));
    }

    /**
     * Inspect Targeted User Dashboard (Step 1 s/d Step 6 khusus milik User tertentu)
     */
    public function inspectUserDashboard(Request $request, $id)
    {
        $currentUser = Auth::user();
        if (!$currentUser || !$currentUser->canViewUserMonitoring()) {
            return redirect()->route('dashboard.overview')
                ->with('error', 'Akses Ditolak. Anda tidak memiliki izin untuk menginspeksi dashboard user lain.');
        }

        $targetUser = User::findOrFail($id);

        // Fetch Step 1: Master Forecast by target user
        $forecasts = \App\Models\Forecasting::where('user_id', $targetUser->id)->get();

        // Fetch Step 2: Master PO by target user
        $masterPos = \App\Models\MasterPo::where('user_id', $targetUser->id)
            ->orWhere('created_by', $targetUser->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Fetch Step 3: Realisasi Penerimaan PO Logs by target user
        $purchasingLogs = \App\Models\PurchasingLog::where('user_id', $targetUser->id)
            ->orderBy('receipt_date', 'desc')
            ->get();

        // Kumpulkan item_code yang ditangani oleh user ini
        $userItemCodes = collect([])
            ->merge($forecasts->pluck('part_number'))
            ->merge($masterPos->pluck('item_code'))
            ->merge($purchasingLogs->pluck('item_code'))
            ->filter()
            ->map(fn($v) => strtoupper(trim($v)))
            ->unique()
            ->values()
            ->toArray();

        // Fetch Step 5: Aktual Produksi by target user (termasuk item_code terkait milik user tersebut)
        $actualProductions = \App\Models\ActualProduction::where(function($q) use ($targetUser, $userItemCodes) {
            $q->where('user_id', $targetUser->id);
            if (!empty($userItemCodes)) {
                $q->orWhere(function($subQ) use ($userItemCodes) {
                    $subQ->where(function($nullQ) {
                        $nullQ->whereNull('user_id')->orWhere('user_id', '')->orWhere('user_id', 0);
                    })->whereIn('item_code', $userItemCodes);
                });
            }
        })->orderBy('tanggal_produksi', 'desc')->get();

        // Fetch Step 4: Outstanding PO for target user
        $outstandingPos = [];
        foreach ($masterPos as $mp) {
            $received = (int) $purchasingLogs->where('po_reference', $mp->po)->sum('actual_received');
            if ($received < (int) $mp->qty) {
                $outstandingPos[] = [
                    'po' => $mp->po,
                    'item_code' => $mp->item_code,
                    'name' => $mp->name ?: $mp->item_code,
                    'supplier' => $mp->supplier ?: '-',
                    'tanggal' => $mp->tanggal,
                    'target_qty' => (int) $mp->qty,
                    'received_qty' => $received,
                    'outstanding_qty' => max(0, (int) $mp->qty - $received),
                ];
            }
        }

        // Fetch Step 6: Hasil Akhir & Komparasi for target user
        $allItems = collect([])
            ->merge($userItemCodes)
            ->merge($actualProductions->pluck('item_code')->map(fn($v) => strtoupper(trim($v))))
            ->filter()
            ->unique();

        $comparisonList = [];
        foreach ($allItems as $codeClean) {
            $fcRow = $forecasts->firstWhere('part_number', $codeClean);
            $mpSum = (int) $masterPos->where('item_code', $codeClean)->sum('qty');
            $logSum = (int) $purchasingLogs->where('item_code', $codeClean)->sum('actual_received');
            $prodSum = (int) $actualProductions->where('item_code', $codeClean)->sum('qty');
            if ($prodSum <= 0 && $fcRow) {
                $prodSum = (int) ($fcRow->production_qty ?? 0);
            }
            $fcQty = $fcRow ? (int) $fcRow->forecast_qty : 0;

            $outQty = max(0, $mpSum - $logSum);
            $status = $logSum >= $mpSum && $mpSum > 0 ? 'Lengkap' : ($logSum > 0 ? 'Diterima Sebagian' : 'Pending PO');

            $comparisonList[] = [
                'item_code' => $codeClean,
                'forecast_qty' => $fcQty,
                'po_qty' => $mpSum,
                'received_qty' => $logSum,
                'outstanding_qty' => $outQty,
                'production_qty' => $prodSum,
                'status' => $status,
            ];
        }

        // Step 6: Analytics Summary per Target User
        $totalPoTarget = $masterPos->sum('qty');
        $totalActualReceived = $purchasingLogs->sum('actual_received');
        $underDeliveryLogsCount = $purchasingLogs->filter(fn($l) => (int)$l->actual_received < (int)$l->target_order)->count();
        $overDeliveryLogsCount = $purchasingLogs->filter(fn($l) => (int)$l->actual_received > (int)$l->target_order)->count();
        $fulfillmentPct = $totalPoTarget > 0 ? round(($totalActualReceived / $totalPoTarget) * 100, 1) : 0;

        return view('users.inspect_dashboard', compact(
            'targetUser',
            'forecasts',
            'masterPos',
            'purchasingLogs',
            'outstandingPos',
            'actualProductions',
            'comparisonList',
            'totalPoTarget',
            'totalActualReceived',
            'underDeliveryLogsCount',
            'overDeliveryLogsCount',
            'fulfillmentPct'
        ));
    }

    /**
     * Hapus User
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return redirect()->back()->withErrors(['user' => 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.']);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User "' . $userName . '" telah berhasil dihapus dari sistem.');
    }
}
