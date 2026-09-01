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
        const item = window.registeredItemsMap[val];
        const nameVal = (typeof item === 'object' && item !== null) ? item.name : item;
        if (descElem && (!descElem.value || descElem.value === '-' || descElem.value === 'Deskripsi material')) {
            descElem.value = nameVal;
        }
    }
}

function autoFillMasterPoFields(inputElem, prefix = 'm_') {
    if (!inputElem) return;
    const val = inputElem.value.trim().toUpperCase();
    if (!val) return;
    inputElem.value = val;

    if (window.registeredItemsMap && window.registeredItemsMap[val]) {
        const item = window.registeredItemsMap[val];
        const isObj = (typeof item === 'object' && item !== null);
        const name = isObj ? item.name : item;
        const supplier = isObj ? item.supplier : '';
        const price = isObj ? item.price : 0;
        const currency = isObj ? item.currency : 'USD';
        const categoryId = isObj ? item.category_id : '';
        const factoryCode = isObj ? item.factory_code : 'Plant 3';
        const deliveryCode = isObj ? item.delivery_category_code : 'LOC';

        const nameEl = document.getElementById(prefix + 'name') || document.getElementById(prefix + 'description');
        if (nameEl && (!nameEl.value || nameEl.value === '-' || nameEl.value === 'Deskripsi material')) {
            nameEl.value = name || '';
        }

        const supEl = document.getElementById(prefix + 'supplier');
        if (supEl && !supEl.value && supplier) {
            supEl.value = supplier;
        }

        const priceEl = document.getElementById(prefix + 'price');
        if (priceEl && (!priceEl.value || parseFloat(priceEl.value) === 0) && price > 0) {
            priceEl.value = price;
        }

        const currEl = document.getElementById(prefix + 'currency');
        if (currEl && currency) {
            currEl.value = currency;
        }

        const catEl = document.getElementById(prefix + 'category_id');
        if (catEl && categoryId) {
            catEl.value = categoryId;
        }

        const factoryEl = document.getElementById(prefix + 'factory_code');
        if (factoryEl && factoryCode) {
            factoryEl.value = factoryCode;
        }

        const delivEl = document.getElementById(prefix + 'delivery_category_code');
        if (delivEl && deliveryCode) {
            delivEl.value = deliveryCode;
        }
    }
}
</script>
