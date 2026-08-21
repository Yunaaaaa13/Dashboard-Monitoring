@php
    $currentStep = $currentStep ?? 1;
    $steps = [
        ['num' => '01', 'stepIndex' => 1, 'label' => 'Forecast',            'route' => 'purchasing.outstanding',       'icon' => 'bi-graph-up-arrow'],
        ['num' => '02', 'stepIndex' => 2, 'label' => 'Master PO',           'route' => 'purchasing.master-po',         'icon' => 'bi-box-seam'],
        ['num' => '03', 'stepIndex' => 3, 'label' => 'Incoming',            'route' => 'purchasing.input',             'icon' => 'bi-truck'],
        ['num' => '04', 'stepIndex' => 4, 'label' => 'Outstanding',         'route' => 'purchasing.outstanding-po',    'icon' => 'bi-clock-history'],
        ['num' => '05', 'stepIndex' => 5, 'label' => 'Actual Production',   'route' => 'purchasing.actual-production', 'icon' => 'bi-gear-wide-connected'],
        ['num' => '06', 'stepIndex' => 6, 'label' => 'Actual Stock',        'route' => 'purchasing.actual-inventory',  'icon' => 'bi-boxes'],
        ['num' => '07', 'stepIndex' => 7, 'label' => 'Hasil Akhir',         'route' => 'purchasing.analysis',          'icon' => 'bi-pie-chart-fill'],
    ];
@endphp

<div class="workflow-stepper-wrapper" id="workflowStepperWrapper">
    <div class="workflow-stepper">
        @foreach($steps as $idx => $s)
            @php
                $isActive = $s['stepIndex'] === (int)$currentStep;
                $statusClass = $isActive ? 'is-active' : 'is-inactive';
            @endphp

            <a href="{{ route($s['route']) }}" class="stepper-item {{ $statusClass }}" id="step-item-{{ $s['num'] }}" title="Navigasi ke {{ $s['num'] }} {{ $s['label'] }}">
                <div class="stepper-circle {{ $statusClass }}">
                    @if($isActive)
                        <span class="stepper-bullet active-bullet">●</span>
                    @else
                        <span class="stepper-bullet inactive-bullet">○</span>
                    @endif
                </div>
                <div class="stepper-label {{ $statusClass }}">
                    <span class="stepper-num">{{ $s['num'] }}</span> {{ $s['label'] }}
                </div>
            </a>

            @if(!$loop->last)
                <div class="stepper-line {{ $isActive ? 'line-near-active' : '' }}"></div>
            @endif
        @endforeach
    </div>
</div>

<script>
    // Ensure active step is scrolled into view smoothly on smaller screens
    document.addEventListener('DOMContentLoaded', function() {
        var wrapper = document.getElementById('workflowStepperWrapper');
        var activeItem = wrapper ? wrapper.querySelector('.stepper-item.is-active') : null;
        if (wrapper && activeItem) {
            var wrapperRect = wrapper.getBoundingClientRect();
            var itemRect = activeItem.getBoundingClientRect();
            if (itemRect.left < wrapperRect.left || itemRect.right > wrapperRect.right) {
                activeItem.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            }
        }
    });
</script>
