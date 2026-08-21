@php
    $icon = $icon ?? 'bi-inbox';
    $title = $title ?? 'Tidak Ada Data Ditemukan';
    $message = $message ?? 'Belum terdapat data pada filter atau periode yang Anda pilih.';
    $actionUrl = $actionUrl ?? null;
    $actionText = $actionText ?? 'Reset Filter';
    $actionIcon = $actionIcon ?? 'bi-arrow-counterclockwise';
@endphp

<div class="text-center py-5 px-3 my-3 rounded-4 border border-secondary border-opacity-20" style="background: rgba(15, 23, 42, 0.4);">
    <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); color: #9ca3af;">
        <i class="bi {{ $icon }} fs-3"></i>
    </div>
    <h6 class="fw-bold text-white mb-1" style="font-family: 'Outfit', sans-serif;">{{ $title }}</h6>
    <p class="text-muted small mx-auto mb-3" style="max-width: 420px; line-height: 1.5; color: #9ca3af !important;">
        {{ $message }}
    </p>
    @if($actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-kawai-secondary rounded-pill px-4 btn-sm">
            <i class="bi {{ $actionIcon }}"></i> {{ $actionText }}
        </a>
    @endif
</div>
