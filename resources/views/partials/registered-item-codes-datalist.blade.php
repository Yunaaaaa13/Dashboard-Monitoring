<!-- ════════════ DATALIST AUTOCOMPLETE ITEM CODE ════════════ -->
<datalist id="registeredItemCodesList">
    @if(isset($registeredItems) && is_array($registeredItems))
        @foreach($registeredItems as $item)
            <option value="{{ $item['item_code'] }}">{{ $item['name'] }}</option>
        @endforeach
    @endif
</datalist>

<script>
if (typeof window.registeredItemsMap === 'undefined') {
    window.registeredItemsMap = {!! $registeredItemsMapJson ?? '{}' !!};
}

function autoFillItemDescription(inputElem, targetDescId) {
    if (!inputElem) return;
    const val = inputElem.value.trim().toUpperCase();
    if (!val) return;
    
    // Auto convert input value to uppercase
    inputElem.value = val;
    
    if (window.registeredItemsMap && window.registeredItemsMap[val] && targetDescId) {
        const descElem = typeof targetDescId === 'string' ? document.getElementById(targetDescId) : targetDescId;
        if (descElem && (!descElem.value || descElem.value === '-' || descElem.value === 'Deskripsi material')) {
            descElem.value = window.registeredItemsMap[val];
        }
    }
}
</script>
