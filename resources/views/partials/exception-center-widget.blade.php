@php
    $healthDiagnostics = \App\Services\Analytics\ExceptionCenterService::getHealthDiagnostics();
@endphp

@if(!empty($healthDiagnostics['exceptions']))
<div class="card mb-4 border-0 rounded-4 shadow-sm overflow-hidden" style="background: rgba(18, 26, 44, 0.85); border: 1px solid rgba(226, 179, 74, 0.25) !important;">
    <div class="card-header d-flex align-items-center justify-content-between px-4 py-3" style="background: rgba(14, 20, 36, 0.95); border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
        <div class="d-flex align-items-center gap-2.5">
            <span class="p-1.5 rounded-circle bg-warning bg-opacity-20 text-warning d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="bi bi-shield-exclamation fs-5"></i>
            </span>
            <div>
                <h6 class="fw-bold text-white mb-0" style="font-family: 'Outfit', sans-serif;">Pusat Diagnostik Kualitas Data (Exception Center)</h6>
                <small class="text-muted" style="font-size: 0.78rem;">Sistem mendeteksi {{ $healthDiagnostics['total_anomalies'] }} catatan yang memerlukan perhatian untuk menjaga integritas laporan.</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge {{ $healthDiagnostics['health_badge'] }} px-3 py-1.5 rounded-pill font-monospace" style="font-size: 0.75rem;">
                SKOR KUALITAS: {{ $healthDiagnostics['quality_score'] }}%
            </span>
        </div>
    </div>
    <div class="card-body p-3 p-md-4">
        <div class="row g-3">
            @foreach($healthDiagnostics['exceptions'] as $exc)
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 border border-secondary border-opacity-25 h-100 d-flex flex-column justify-content-between" style="background: rgba(10, 15, 29, 0.75);">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="badge {{ $exc['severity'] === 'DANGER' ? 'bg-danger' : ($exc['severity'] === 'WARNING' ? 'bg-warning text-dark' : 'bg-info text-dark') }} font-monospace px-2 py-0.5 rounded-pill" style="font-size: 0.68rem;">
                                    {{ $exc['category'] }}
                                </span>
                                <span class="text-muted font-monospace small">{{ $exc['count'] }} item</span>
                            </div>
                            <h6 class="fw-bold text-white mb-1" style="font-size: 0.9rem;">{{ $exc['title'] }}</h6>
                            <p class="text-muted small mb-3" style="font-size: 0.8rem; line-height: 1.5; color: #cbd5e1 !important;">
                                {{ $exc['description'] }}
                            </p>
                        </div>
                        @if($exc['action_url'] !== '#')
                            <div class="pt-2 border-top border-secondary border-opacity-20 text-end">
                                <a href="{{ $exc['action_url'] }}" class="btn btn-xs btn-outline-warning rounded-pill px-3 py-1 font-monospace" style="font-size: 0.75rem;">
                                    {{ $exc['action_label'] }} &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
