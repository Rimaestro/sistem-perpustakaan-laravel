@props([
    'inputId' => 'barcode-input',
    'placeholder' => 'Scan atau ketik barcode...',
    'onScan' => null,
])

<div class="barcode-scanner-container">
    <div class="relative">
        <input 
            type="text" 
            id="{{ $inputId }}"
            name="barcode_scan"
            placeholder="{{ $placeholder }}"
            class="block w-full pl-10 pr-12 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            autocomplete="off"
            {{ $attributes }}
        >
        
        <!-- Barcode Icon -->
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
        </div>
        
        <!-- Scanner Buttons -->
        <div class="absolute inset-y-0 right-0 pr-3 flex items-center space-x-1">
            <button
                type="button"
                id="camera-scan-btn-{{ $inputId }}"
                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                title="Scan dengan kamera"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </button>
            <button
                type="button"
                id="clear-btn-{{ $inputId }}"
                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                title="Bersihkan input"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Scan Result Display -->
    <div id="scan-result-{{ $inputId }}" class="mt-2 hidden">
        <div class="bg-green-50 border border-green-200 rounded-md p-3">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">Barcode berhasil di-scan</p>
                    <p class="text-sm text-green-700 font-mono" id="scanned-value-{{ $inputId }}"></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('{{ $inputId }}');
    const resultDiv = document.getElementById('scan-result-{{ $inputId }}');
    const scannedValue = document.getElementById('scanned-value-{{ $inputId }}');
    
    let scanBuffer = '';
    let scanTimeout;
    
    // Detect barcode scanner input (rapid keystrokes ending with Enter)
    input.addEventListener('keydown', function(e) {
        // Clear previous timeout
        clearTimeout(scanTimeout);
        
        if (e.key === 'Enter') {
            e.preventDefault();
            
            // If we have accumulated characters, treat as scanned barcode
            if (scanBuffer.length > 0) {
                handleBarcodeScanned(scanBuffer);
                scanBuffer = '';
            } else if (input.value.length > 0) {
                // Manual input
                handleBarcodeScanned(input.value);
            }
        } else if (e.key.length === 1) {
            // Accumulate characters for potential scan
            scanBuffer += e.key;
            
            // Set timeout to clear buffer (scanner input is typically very fast)
            scanTimeout = setTimeout(() => {
                scanBuffer = '';
            }, 100);
        }
    });
    
    // Handle manual input change
    input.addEventListener('change', function() {
        if (this.value.length > 0) {
            handleBarcodeScanned(this.value);
        }
    });
    
    function handleBarcodeScanned(barcode) {
        // Clean and validate barcode
        barcode = barcode.trim();
        
        if (barcode.length === 0) return;
        
        // Update input value
        input.value = barcode;
        
        // Show scan result
        scannedValue.textContent = barcode;
        resultDiv.classList.remove('hidden');
        
        // Hide result after 3 seconds
        setTimeout(() => {
            resultDiv.classList.add('hidden');
        }, 3000);
        
        // Call custom callback if provided
        @if($onScan)
            {{ $onScan }}(barcode);
        @endif
        
        // Trigger change event for form handling
        input.dispatchEvent(new Event('change', { bubbles: true }));
        
        // Optional: Auto-submit form or search
        const form = input.closest('form');
        if (form && form.dataset.autoSubmit === 'true') {
            form.submit();
        }
    }
    
    // Clear button functionality
    const clearBtn = document.getElementById('clear-btn-{{ $inputId }}');
    if (clearBtn) {
        clearBtn.addEventListener('click', function() {
            input.value = '';
            input.focus();
            resultDiv.classList.add('hidden');
        });
    }

    // Camera scan button
    const cameraBtn = document.getElementById('camera-scan-btn-{{ $inputId }}');
    if (cameraBtn) {
        cameraBtn.addEventListener('click', function() {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                startCameraScanning();
            } else {
                alert('Browser Anda tidak mendukung akses kamera untuk scanning barcode.');
            }
        });
    }

    function startCameraScanning() {
        // Request camera access
        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'environment' // Prefer back camera
            }
        })
        .then(function(stream) {
            // Create camera modal
            const modal = createCameraModal();
            const video = modal.querySelector('video');
            video.srcObject = stream;

            document.body.appendChild(modal);

            // Close modal function
            const closeModal = function() {
                stream.getTracks().forEach(track => track.stop());
                document.body.removeChild(modal);
            };

            modal.querySelector('.close-camera').addEventListener('click', closeModal);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });

        })
        .catch(function(error) {
            console.error('Error accessing camera:', error);
            alert('Tidak dapat mengakses kamera: ' + error.message);
        });
    }

    function createCameraModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
        modal.innerHTML = \`
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Scan Barcode</h3>
                    <button class="close-camera text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <video autoplay playsinline class="w-full h-64 bg-black rounded"></video>
                <p class="text-sm text-gray-600 mt-2 text-center">
                    Arahkan kamera ke barcode. Ketik manual jika diperlukan.
                </p>
                <input type="text" placeholder="Atau ketik barcode manual..."
                       class="mt-2 w-full px-3 py-2 border rounded-md text-sm"
                       onkeypress="if(event.key==='Enter'){handleBarcodeScanned(this.value); this.closest('.fixed').querySelector('.close-camera').click();}">
            </div>
        \`;
        return modal;
    }
});
</script>
