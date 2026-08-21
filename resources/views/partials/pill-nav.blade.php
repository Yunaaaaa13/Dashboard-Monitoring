{{--
  PillNav Partial — Premium Dark Glassmorphism Navigation Bar
  Usage:
    @include('partials.pill-nav', [
        'activeRoute' => 'dashboard.overview',
        'hasFaqModal' => true,
    ])
--}}
@php
    $activeRoute = $activeRoute ?? request()->route()?->getName() ?? '';
    $hasFaqModal = $hasFaqModal ?? false;
    $workflowSteps = [
        ['num' => '1', 'label' => 'Master Data (Forecast)',          'route' => 'purchasing.outstanding',       'color' => '#3b82f6'],
        ['num' => '2', 'label' => 'Master PO',                       'route' => 'purchasing.master-po',         'color' => '#10b981'],
        ['num' => '3', 'label' => 'Incoming Penerimaan PO',          'route' => 'purchasing.input',             'color' => '#06b6d4'],
        ['num' => '4', 'label' => 'Dashboard Outstanding PO',        'route' => 'purchasing.outstanding-po',    'color' => '#06b6d4'],
        ['num' => '5', 'label' => 'Aktual Produksi',                 'route' => 'purchasing.actual-production', 'color' => '#f59e0b'],
        ['num' => '6', 'label' => 'Aktual Stock',                    'route' => 'purchasing.actual-inventory',  'color' => '#8b5cf6'],
        ['num' => '7', 'label' => 'Hasil Akhir & Komparasi',        'route' => 'purchasing.analysis',          'color' => '#ef4444'],
    ];
    $workflowRoutes = array_column($workflowSteps, 'route');
    $isWorkflowActive = in_array($activeRoute, $workflowRoutes);
@endphp

<style>
/* ──── PillNav Premium Glassmorphism Theme ──── */
.pnav-container {
    display: inline-flex;
    align-items: center;
    background: rgba(15, 23, 42, 0.85);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 999px;
    padding: 4px 6px;
    gap: 4px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    position: relative;
    z-index: 1100;
}

/* Individual Pill Button */
.pnav-btn {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 38px;
    padding: 0 18px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #cbd5e1;
    font-family: 'Outfit', 'Inter', sans-serif;
    font-size: 0.81rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    text-decoration: none;
    white-space: nowrap;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
}

/* Rising Warm Glow Circle Effect */
.pnav-btn .pnav-glow {
    position: absolute;
    left: 50%;
    bottom: -100%;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.35) 0%, rgba(245, 158, 11, 0) 70%);
    pointer-events: none;
    transform: translateX(-50%) scale(0);
    transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 0;
}

.pnav-btn:hover .pnav-glow {
    transform: translateX(-50%) scale(1.6);
}

.pnav-btn:hover {
    color: #ffffff !important;
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.4);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

/* Label text wrapper */
.pnav-btn-label {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* Active Tab Style */
.pnav-btn.is-active {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    color: #000000 !important;
    font-weight: 800 !important;
    border: 1px solid rgba(251, 191, 36, 0.8) !important;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.45) !important;
}

.pnav-btn.is-active .pnav-glow {
    display: none;
}

.pnav-btn.is-active i {
    color: #000000 !important;
}

/* Dropdown Menu Container */
.pnav-dropdown-wrapper {
    position: relative;
    display: inline-block;
}

.pnav-dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% + 10px);
    right: 0;
    min-width: 320px;
    background: #090d16;
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 18px;
    padding: 10px;
    z-index: 2500;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(20px);
}

.pnav-dropdown-wrapper.open .pnav-dropdown-menu {
    display: block;
    animation: pnavPopDown 0.25s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes pnavPopDown {
    from { opacity: 0; transform: translateY(-8px) scale(0.96); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

.pnav-dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 12px;
    color: #cbd5e1;
    text-decoration: none;
    font-family: 'Inter', sans-serif;
    font-size: 0.83rem;
    font-weight: 600;
    transition: all 0.2s ease;
}

.pnav-dropdown-item:hover {
    background: rgba(255, 255, 255, 0.08);
    color: #ffffff;
    transform: translateX(4px);
}

.pnav-dropdown-item.active-item {
    background: rgba(245, 158, 11, 0.18);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
    font-weight: 700;
}

.pnav-step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    font-size: 0.72rem;
    font-weight: 800;
    flex-shrink: 0;
}
</style>

{{-- ──── PillNav Markup ──── --}}
<div class="pnav-container">

    {{-- Home / Dashboard --}}
    <a href="{{ route('dashboard.overview') }}"
       class="pnav-btn {{ $activeRoute === 'dashboard.overview' ? 'is-active' : '' }}"
       title="Dashboard Utama">
        <span class="pnav-glow"></span>
        <span class="pnav-btn-label">
            <i class="bi bi-house-door-fill"></i> Home
        </span>
    </a>

    {{-- Alur Purchasing Dropdown --}}
    <div class="pnav-dropdown-wrapper" id="pnavWorkflowWrapper">
        <button type="button"
                class="pnav-btn {{ $isWorkflowActive ? 'is-active' : '' }}"
                onclick="pnavToggleWorkflow()"
                id="pnavWorkflowBtn">
            <span class="pnav-glow"></span>
            <span class="pnav-btn-label">
                <i class="bi bi-bezier2 text-warning me-1.5"></i>
                Alur Purchasing
                <i class="bi bi-chevron-down ms-1.5" style="font-size: 0.72rem;"></i>
            </span>
        </button>

        <div class="pnav-dropdown-menu" id="pnavWorkflowMenu">
            <div class="px-2 py-1 mb-2 text-muted fw-bold border-bottom border-secondary border-opacity-25 d-flex align-items-center justify-content-between" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                <span>ALUR WORKFLOW PURCHASING</span>
                <span class="badge bg-dark border border-secondary text-warning">Integrasi Data</span>
            </div>
            @foreach($workflowSteps as $step)
                <a href="{{ route($step['route']) }}"
                   class="pnav-dropdown-item {{ $activeRoute === $step['route'] ? 'active-item' : '' }}">
                    <span class="pnav-step-badge"
                          style="background: {{ $step['color'] }}25; color: {{ $step['color'] }}; border: 1px solid {{ $step['color'] }}66;">
                        {{ $step['num'] }}
                    </span>
                    <span>{{ $step['label'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Riwayat & Audit --}}
    <a href="{{ route('purchasing.history') }}"
       class="pnav-btn {{ $activeRoute === 'purchasing.history' ? 'is-active' : '' }}"
       title="Riwayat & Audit Log">
        <span class="pnav-glow"></span>
        <span class="pnav-btn-label">
            <i class="bi bi-clock-history"></i> Riwayat
        </span>
    </a>

    {{-- Tax Exchange Rate (Kurs) --}}
    @if(!Auth::check() || Auth::user()->hasPermission('exchange_rate'))
    <a href="{{ route('exchange-rate.index') }}"
       class="pnav-btn {{ $activeRoute === 'exchange-rate.index' ? 'is-active' : '' }}"
       title="Dashboard Tax Exchange Rate — Kurs Rupiah Mingguan & Bulanan">
        <span class="pnav-glow"></span>
        <span class="pnav-btn-label">
            <i class="bi bi-currency-exchange" style="{{ $activeRoute === 'exchange-rate.index' ? 'color:#000;' : 'color:#fbbf24;' }}"></i>
            Kurs
        </span>
    </a>
    @endif

    {{-- Master Kategori --}}
    <a href="{{ route('purchasing.categories') }}"
       class="pnav-btn {{ $activeRoute === 'purchasing.categories' ? 'is-active' : '' }}"
       title="Master Kategori Material">
        <span class="pnav-glow"></span>
        <span class="pnav-btn-label">
            <i class="bi bi-tags-fill"></i> Kategori
        </span>
    </a>

    {{-- Monitoring Performa Tim / User (Disetujui Admin) --}}
    @if(Auth::check() && Auth::user()->canViewUserMonitoring())
    <a href="{{ route('users.monitoring') }}"
       class="pnav-btn {{ $activeRoute === 'users.monitoring' ? 'is-active' : '' }}"
       title="Monitoring Pengisian & Performa Tim User">
        <span class="pnav-glow"></span>
        <span class="pnav-btn-label">
            <i class="bi bi-graph-up-arrow text-info"></i> Monitoring Tim
        </span>
    </a>
    @endif

    {{-- Users & Privileges (Khusus Admin) --}}
    @if(Auth::check() && Auth::user()->isAdmin())
    <a href="{{ route('users.index') }}"
       class="pnav-btn {{ $activeRoute === 'users.index' ? 'is-active' : '' }}"
       title="User Management & Privileges (Admin Only)">
        <span class="pnav-glow"></span>
        <span class="pnav-btn-label">
            <i class="bi bi-people-fill text-warning"></i> Users
        </span>
    </a>
    @endif

    {{-- Panduan & FAQ Modal Button (Selalu Diakses Semua User) --}}
    <button type="button"
            class="pnav-btn"
            data-bs-toggle="modal"
            data-bs-target="#modalFaqPurchasing"
            onclick="const el = document.getElementById('modalFaqPurchasing'); if (el) { const m = bootstrap.Modal.getOrCreateInstance(el); m.show(); }"
            title="Pusat Panduan & FAQ">
        <span class="pnav-glow"></span>
        <span class="pnav-btn-label">
            <i class="bi bi-question-circle-fill text-warning"></i> Panduan
        </span>
    </button>

</div>

<script>
function pnavToggleWorkflow() {
    var wrapper = document.getElementById('pnavWorkflowWrapper');
    if (wrapper) {
        wrapper.classList.toggle('open');
    }
}

document.addEventListener('click', function(e) {
    var wrapper = document.getElementById('pnavWorkflowWrapper');
    if (wrapper && !wrapper.contains(e.target)) {
        wrapper.classList.remove('open');
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var wrapper = document.getElementById('pnavWorkflowWrapper');
        if (wrapper) wrapper.classList.remove('open');
    }
});

// Instant Hover Prefetching Engine for Lightning-Fast Navigation
(function() {
    var prefetched = new Set();
    function prefetch(url) {
        if (!url || prefetched.has(url) || url.includes('#') || url.includes('javascript:')) return;
        prefetched.add(url);
        var link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        document.head.appendChild(link);
    }
    document.addEventListener('mouseover', function(e) {
        var a = e.target.closest('a[href]');
        if (a && a.href && a.origin === window.location.origin) {
            prefetch(a.href);
        }
    }, { passive: true });
    document.addEventListener('touchstart', function(e) {
        var a = e.target.closest('a[href]');
        if (a && a.href && a.origin === window.location.origin) {
            prefetch(a.href);
        }
    }, { passive: true });
})();
</script>
