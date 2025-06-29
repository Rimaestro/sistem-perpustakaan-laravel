<x-app-layout title="Kartu Anggota">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kartu Anggota</h1>
                <p class="mt-1 text-sm text-gray-600">Kartu anggota untuk {{ $member->name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <x-button onclick="window.print()" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Kartu
                </x-button>
                <x-button href="{{ route('members.show', $member) }}" variant="secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </x-button>
            </div>
        </div>

        <!-- Member Card -->
        <div class="flex justify-center">
            <div class="member-card bg-gradient-to-br from-blue-600 to-blue-800 rounded-xl shadow-2xl overflow-hidden" style="width: 350px; height: 220px;">
                <!-- Card Header -->
                <div class="bg-white bg-opacity-10 px-6 py-3 border-b border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div class="text-white">
                            <h3 class="text-sm font-semibold">PERPUSTAKAAN</h3>
                            <p class="text-xs opacity-90">SMA Negeri 1 Sampang</p>
                        </div>
                        <div class="text-white text-right">
                            <p class="text-xs opacity-90">Member Card</p>
                            <p class="text-sm font-mono">{{ $cardData['card_number'] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="px-6 py-4 flex items-center space-x-4">
                    <!-- Avatar -->
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Member Info -->
                    <div class="flex-1 text-white">
                        <h4 class="text-lg font-bold truncate">{{ $cardData['name'] }}</h4>
                        <p class="text-sm opacity-90 font-mono">{{ $cardData['member_id'] }}</p>
                        <p class="text-xs opacity-75 mt-1">Bergabung: {{ $cardData['join_date'] }}</p>
                        <div class="mt-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-white bg-opacity-20">
                                {{ $cardData['status'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="px-6 py-2 bg-white bg-opacity-10 border-t border-white border-opacity-20">
                    <div class="flex items-center justify-between">
                        <div class="text-white text-xs opacity-75">
                            <p>Valid sampai: {{ now()->addYear()->format('m/Y') }}</p>
                        </div>
                        <div class="text-white text-xs opacity-75">
                            <p>ID: {{ $cardData['qr_code_data'] }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code Anggota</h3>
                <div class="flex justify-center mb-4">
                    <div id="qrcode" class="border border-gray-200 rounded-lg p-4"></div>
                </div>
                <p class="text-sm text-gray-600">
                    Scan QR code ini untuk identifikasi cepat anggota
                </p>
                <p class="text-xs text-gray-500 mt-2 font-mono">
                    Data: {{ $cardData['qr_code_data'] }}
                </p>
            </div>
        </div>

        <!-- Member Information -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Anggota</h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">ID Anggota</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $member->member_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor Kartu</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $member->card_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Tanggal Bergabung</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $member->join_date->format('d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <x-status-badge :status="$member->status" :color="$member->status_color">
                                {{ $member->status_label }}
                            </x-status-badge>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Print Styles -->
    <style>
        @media print {
            .member-card {
                break-inside: avoid;
                page-break-inside: avoid;
            }
            
            /* Hide navigation and other elements when printing */
            nav, .no-print {
                display: none !important;
            }
            
            /* Ensure card is properly sized for printing */
            .member-card {
                width: 85.6mm !important; /* Standard credit card width */
                height: 53.98mm !important; /* Standard credit card height */
                font-size: 10px !important;
            }
        }
    </style>

    <!-- QR Code Generation Script -->
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generate QR Code
            QRCode.toCanvas(document.getElementById('qrcode'), '{{ $cardData['qr_code_data'] }}', {
                width: 150,
                height: 150,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#FFFFFF'
                }
            }, function (error) {
                if (error) console.error(error);
            });
        });
    </script>
</x-app-layout>
