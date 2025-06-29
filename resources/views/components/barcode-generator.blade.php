@props([
    'value' => '',
    'type' => 'CODE128',
    'width' => 2,
    'height' => 50,
    'displayValue' => true,
])

<div class="barcode-container">
    @if($value)
        <div class="text-center">
            <div id="barcode-{{ Str::random(8) }}" class="inline-block"></div>
            @if($displayValue)
                <div class="mt-2 text-sm font-mono text-gray-600">{{ $value }}</div>
            @endif
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof JsBarcode !== 'undefined') {
                JsBarcode("#barcode-{{ Str::random(8) }}", "{{ $value }}", {
                    format: "{{ $type }}",
                    width: {{ $width }},
                    height: {{ $height }},
                    displayValue: {{ $displayValue ? 'true' : 'false' }}
                });
            } else {
                // Fallback jika JsBarcode tidak tersedia
                document.getElementById("barcode-{{ Str::random(8) }}").innerHTML = 
                    '<div class="bg-gray-100 p-4 rounded border-2 border-dashed border-gray-300">' +
                    '<div class="text-center text-gray-500">' +
                    '<svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">' +
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>' +
                    '</svg>' +
                    '<p class="text-sm">Barcode: {{ $value }}</p>' +
                    '</div>' +
                    '</div>';
            }
        });
        </script>
    @else
        <div class="bg-gray-100 p-4 rounded border-2 border-dashed border-gray-300">
            <div class="text-center text-gray-500">
                <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                </svg>
                <p class="text-sm">Barcode akan di-generate otomatis</p>
            </div>
        </div>
    @endif
</div>
