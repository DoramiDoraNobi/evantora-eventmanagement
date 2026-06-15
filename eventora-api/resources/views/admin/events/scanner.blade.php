<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('events.index') }}" class="text-gray-500 hover:text-gray-700 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Scanner: ') }} {{ $event->title }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <!-- Scanner Controls Area -->
                        <div class="flex flex-col space-y-6">
                            
                            <!-- Main Video Output Container -->
                            <div class="w-full bg-black rounded-xl overflow-hidden shadow-inner relative" style="min-height: 300px;">
                                <div id="reader" class="w-full h-full"></div>
                                
                                <!-- Idle Overlay -->
                                <div id="camera-idle-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-900 bg-opacity-80 text-white z-10">
                                    <svg class="w-16 h-16 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <p class="text-sm font-medium">Kamera Belum Aktif</p>
                                </div>
                            </div>
                            
                            <!-- Control Buttons -->
                            <div class="grid grid-cols-2 gap-4">
                                <button id="btn-start-camera" class="flex flex-col items-center justify-center py-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md transition font-semibold">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                    Mulai Kamera
                                </button>
                                
                                <button id="btn-stop-camera" class="hidden flex flex-col items-center justify-center py-4 bg-red-600 hover:bg-red-700 text-white rounded-xl shadow-md transition font-semibold">
                                    <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path></svg>
                                    Stop Kamera
                                </button>

                                <label for="qr-upload" class="cursor-pointer flex flex-col items-center justify-center py-4 bg-gray-100 hover:bg-gray-200 text-gray-800 border-2 border-dashed border-gray-300 rounded-xl transition font-semibold">
                                    <svg class="w-8 h-8 mb-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Upload Foto QR
                                    <input type="file" id="qr-upload" accept="image/*" class="hidden">
                                </label>
                            </div>
                            
                            <!-- Manual Input Fallback -->
                            <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <h4 class="font-bold text-gray-800 mb-1 text-sm">Manual Input (Fallback)</h4>
                                <div class="flex gap-2 mt-2">
                                    <input type="text" id="manual-qr-input" placeholder="Masukkan ID / UUID tiket..." class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <button id="manual-submit-btn" class="px-4 py-2 bg-gray-800 text-white rounded-md hover:bg-gray-900 font-medium text-sm transition">Proses</button>
                                </div>
                            </div>
                        </div>

                        <!-- Result Area -->
                        <div class="flex flex-col justify-start">
                            <h3 class="text-lg font-bold border-b pb-2 mb-4 text-gray-800">Hasil Scan</h3>
                            
                            <div id="result-card" class="hidden p-6 rounded-xl shadow-lg border-l-4 transition-all duration-300">
                                <h3 id="result-status" class="text-2xl font-black mb-2 tracking-tight"></h3>
                                <p id="result-message" class="text-gray-700 mb-6 font-medium"></p>
                                
                                <div id="attendee-info" class="hidden bg-white/50 backdrop-blur-sm p-4 rounded-lg border border-black/5">
                                    <div class="text-xs text-gray-500 uppercase tracking-wider font-bold mb-1">Pengunjung</div>
                                    <div id="attendee-name" class="font-black text-xl text-gray-900 mb-4"></div>
                                    <div class="text-xs text-gray-500 uppercase tracking-wider font-bold mb-1">Tiket</div>
                                    <div id="attendee-ticket" class="font-bold text-lg text-gray-800"></div>
                                </div>
                            </div>
                            
                            <div id="idle-state" class="flex flex-col items-center justify-center p-12 text-gray-400 bg-gray-50 rounded-xl border border-dashed border-gray-200 h-full">
                                <svg class="w-20 h-20 mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                <p class="font-medium text-lg">Siap Memindai</p>
                                <p class="text-sm mt-2 text-center">Nyalakan kamera atau upload foto QR tiket untuk melakukan Check-in.</p>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Move Scripts Here so they run inline with the component -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let isProcessing = false;
            let html5QrCode = null;
            let isScanning = false;
            
            const btnStart = document.getElementById('btn-start-camera');
            const btnStop = document.getElementById('btn-stop-camera');
            const fileInput = document.getElementById('qr-upload');
            const cameraOverlay = document.getElementById('camera-idle-overlay');
            
            const resultCard = document.getElementById('result-card');
            const idleState = document.getElementById('idle-state');
            const resultStatus = document.getElementById('result-status');
            const resultMessage = document.getElementById('result-message');
            const attendeeInfo = document.getElementById('attendee-info');
            const attendeeName = document.getElementById('attendee-name');
            const attendeeTicket = document.getElementById('attendee-ticket');
            
            const manualInput = document.getElementById('manual-qr-input');
            const manualSubmitBtn = document.getElementById('manual-submit-btn');

            // Initialize Scanner instance
            html5QrCode = new Html5Qrcode("reader");

            function resetUI() {
                setTimeout(() => {
                    resultCard.classList.add('hidden');
                    idleState.classList.remove('hidden');
                    isProcessing = false;
                }, 4000); 
            }

            function processQrCode(qrData, method = 'qr_scan') {
                if (isProcessing) return;
                isProcessing = true;
                
                // Show processing UI
                idleState.classList.add('hidden');
                resultCard.classList.remove('hidden');
                resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-blue-500 bg-blue-50 transition-all duration-300";
                resultStatus.innerText = "⏳ Memproses...";
                resultStatus.className = "text-2xl font-black mb-2 text-blue-700 tracking-tight";
                resultMessage.innerText = "Memverifikasi tiket ke server...";
                attendeeInfo.classList.add('hidden');

                // Use relative URL to ensure same origin/port cookie propagation
                const apiUrl = "/events/checkin";

                fetch(apiUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        qr_code: qrData,
                        method: method
                    })
                })
                .then(async response => {
                    if (response.status === 419) {
                        throw new Error('PAGE_EXPIRED');
                    }
                    if (response.status === 401) {
                        throw new Error('UNAUTHENTICATED');
                    }
                    
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.indexOf("application/json") !== -1) {
                        const data = await response.json();
                        return {status: response.status, body: data};
                    } else {
                        throw new Error(`SERVER_ERROR|${response.status}`);
                    }
                })
                .then(res => {
                    const data = res.body;
                    
                    // Audio feedback
                    if (data.success) {
                        playSuccessSound();
                    } else {
                        playErrorSound();
                    }
                    
                    if (data.success) {
                        // Success: Green
                        resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-green-500 bg-green-100 transition-all duration-300";
                        resultStatus.innerText = "✅ AKSES DITERIMA";
                        resultStatus.className = "text-2xl font-black mb-2 text-green-700 tracking-tight";
                        resultMessage.innerText = "Tiket valid dan telah diverifikasi.";
                        
                        attendeeInfo.classList.remove('hidden');
                        attendeeName.innerText = data.attendee.name;
                        attendeeTicket.innerText = data.attendee.ticket_type + " (" + data.attendee.ticket_number + ")";
                    } else {
                        // Failed: Yellow/Red
                        const isWarning = res.status === 409;
                        resultCard.className = `p-6 rounded-xl shadow-lg border-l-8 transition-all duration-300 ${isWarning ? 'border-yellow-500 bg-yellow-100' : 'border-red-500 bg-red-100'}`;
                        resultStatus.innerText = isWarning ? "⚠️ SUDAH DIPAKAI" : "❌ AKSES DITOLAK";
                        resultStatus.className = `text-2xl font-black mb-2 tracking-tight ${isWarning ? 'text-yellow-700' : 'text-red-700'}`;
                        resultMessage.innerText = data.message;
                        
                        if (data.attendee) {
                            attendeeInfo.classList.remove('hidden');
                            attendeeName.innerText = data.attendee.name;
                            attendeeTicket.innerText = data.attendee.ticket_type;
                        } else {
                            attendeeInfo.classList.add('hidden');
                        }
                    }
                    
                    manualInput.value = ''; // clear input
                    resetUI();
                })
                .catch(err => {
                    console.error("Check-in Error: ", err);
                    playErrorSound();
                    
                    if (err.message === 'PAGE_EXPIRED') {
                        resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-yellow-500 bg-yellow-100 transition-all duration-300";
                        resultStatus.innerText = "⚠️ SESI BERAKHIR";
                        resultStatus.className = "text-2xl font-black mb-2 text-yellow-700 tracking-tight";
                        resultMessage.innerText = "Sesi login Anda telah kedaluwarsa. Silakan refresh halaman (F5).";
                    } else if (err.message === 'UNAUTHENTICATED') {
                        resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-yellow-500 bg-yellow-100 transition-all duration-300";
                        resultStatus.innerText = "🔒 BELUM LOGIN";
                        resultStatus.className = "text-2xl font-black mb-2 text-yellow-700 tracking-tight";
                        resultMessage.innerText = "Anda belum login. Silakan login terlebih dahulu dan refresh halaman ini.";
                    } else if (err.message.startsWith('SERVER_ERROR|')) {
                        const status = err.message.split('|')[1];
                        resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-red-500 bg-red-100 transition-all duration-300";
                        resultStatus.innerText = `❌ ERROR SERVER (HTTP ${status})`;
                        resultStatus.className = "text-2xl font-black mb-2 text-red-700 tracking-tight";
                        resultMessage.innerText = "Terjadi kesalahan internal pada server. Silakan coba lagi.";
                    } else {
                        resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-red-500 bg-red-100 transition-all duration-300";
                        resultStatus.innerText = "❌ ERROR JARINGAN";
                        resultStatus.className = "text-2xl font-black mb-2 text-red-700 tracking-tight";
                        resultMessage.innerText = "Gagal terhubung ke server. Periksa koneksi internet Anda.";
                    }
                    resetUI();
                });
            }

            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }

            // Start Camera
            btnStart.addEventListener('click', () => {
                if(!isScanning) {
                    html5QrCode.start(
                        { facingMode: "environment" }, // Prefer back camera
                        {
                            fps: 10,    // Optional, frame per seconds for qr code scanning
                            qrbox: { width: 250, height: 250 }  // Optional, if you want bounded box UI
                        },
                        (decodedText, decodedResult) => {
                            // Successfully decoded
                            processQrCode(decodedText, 'qr_scan');
                        },
                        (errorMessage) => {
                            // parse error, ignore it.
                        }
                    )
                    .then(() => {
                        isScanning = true;
                        cameraOverlay.classList.add('hidden');
                        btnStart.classList.add('hidden');
                        btnStop.classList.remove('hidden');
                    })
                    .catch((err) => {
                        alert("Gagal mengakses kamera. Pastikan izin kamera telah diberikan.");
                        console.error("Camera start failed:", err);
                    });
                }
            });

            // Stop Camera
            btnStop.addEventListener('click', () => {
                if(isScanning) {
                    html5QrCode.stop().then(() => {
                        isScanning = false;
                        cameraOverlay.classList.remove('hidden');
                        btnStop.classList.add('hidden');
                        btnStart.classList.remove('hidden');
                    }).catch(err => {
                        console.error("Failed to stop scanning.", err);
                    });
                }
            });

            // Scan via Image Upload
            fileInput.addEventListener('change', e => {
                if(e.target.files.length == 0) {
                    return;
                }
                const imageFile = e.target.files[0];
                
                // Show processing indicator before decoding
                idleState.classList.add('hidden');
                resultCard.classList.remove('hidden');
                resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-gray-500 bg-gray-50 transition-all duration-300";
                resultStatus.innerText = "Membaca Gambar...";
                resultStatus.className = "text-2xl font-black mb-2 text-gray-700 tracking-tight";
                resultMessage.innerText = "Sedang mencari kode QR pada gambar...";
                attendeeInfo.classList.add('hidden');

                html5QrCode.scanFile(imageFile, true)
                .then(decodedText => {
                    // fileInput.value = ''; // Reset input
                    processQrCode(decodedText, 'image_upload');
                })
                .catch(err => {
                    // fileInput.value = ''; // Reset input
                    resultCard.className = "p-6 rounded-xl shadow-lg border-l-8 border-red-500 bg-red-100 transition-all duration-300";
                    resultStatus.innerText = "❌ QR TIDAK DITEMUKAN";
                    resultStatus.className = "text-2xl font-black mb-2 text-red-700 tracking-tight";
                    resultMessage.innerText = "Tidak ada kode QR valid yang terdeteksi di gambar ini. Pastikan gambar jelas.";
                    playErrorSound();
                    resetUI();
                });
            });
            
            // Manual Submit Event
            manualSubmitBtn.addEventListener('click', () => {
                if(manualInput.value.trim() !== '') {
                    processQrCode(manualInput.value.trim(), 'manual');
                }
            });

            // Simple beep sounds using Web Audio API
            function playSuccessSound() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(800, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0, ctx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.5, ctx.currentTime + 0.05);
                    gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.2);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.2);
                } catch (e) { console.log("Audio not supported"); }
            }

            function playErrorSound() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(300, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(150, ctx.currentTime + 0.3);
                    gain.gain.setValueAtTime(0, ctx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.5, ctx.currentTime + 0.05);
                    gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.3);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.3);
                } catch (e) { console.log("Audio not supported"); }
            }
        });
    </script>
</x-app-layout>
