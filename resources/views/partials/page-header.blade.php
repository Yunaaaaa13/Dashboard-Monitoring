@php
    $icon = $icon ?? 'bi-layers';
    $iconColor = $iconColor ?? 'text-warning';
    $iconBg = $iconBg ?? 'rgba(226, 179, 74, 0.15)';
    $iconBorder = $iconBorder ?? 'rgba(226, 179, 74, 0.35)';
    $title = $title ?? 'Purchasing Monitoring';
    $subtitle = $subtitle ?? '';
@endphp

<div class="kawai-page-header">
    <div class="kawai-page-header-left">
        <div class="page-icon-box" style="background: {{ $iconBg }}; border: 1px solid {{ $iconBorder }};">
            <i class="bi {{ $icon }} {{ $iconColor }}"></i>
        </div>
        <div>
            <h1 class="page-title-text">{{ $title }}</h1>
            @if(!empty($subtitle))
                <p class="page-subtitle-text">{{ $subtitle }}</p>
            @endif
        </div>
    </div>
    <div class="kawai-page-actions">
        {{ $actions ?? '' }}
    </div>
</div>
