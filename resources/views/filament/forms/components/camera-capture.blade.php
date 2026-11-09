<div
    class="wash-camera"
    x-data="{
        statePath: @js($getStatePath()),
        stream: null,
        cameraActive: false,
        cameraStarting: false,
        cameraRequested: false,
        stepObserver: null,
        captured: false,
        uploading: false,
        previewUrl: null,
        error: null,
        async startCamera() {
            if (this.cameraActive || this.cameraStarting) {
                return;
            }

            this.error = null;
            this.cameraStarting = true;
            this.cameraRequested = true;

            if (! navigator.mediaDevices?.getUserMedia) {
                this.error = 'Kamera tidak tersedia. Buka halaman ini melalui HTTPS atau localhost dan gunakan browser yang mendukung kamera.';
                this.cameraStarting = false;
                return;
            }

            try {
                const stream = await navigator.mediaDevices.getUserMedia({
                    video: { facingMode: { ideal: 'environment' } },
                    audio: false,
                });

                if (! this.cameraRequested) {
                    stream.getTracks().forEach((track) => track.stop());
                    return;
                }

                this.stream = stream;
                this.$refs.video.srcObject = this.stream;
                await this.$refs.video.play();
                this.cameraActive = true;
            } catch (cameraError) {
                this.stopCamera();
                this.error = cameraError.name === 'NotAllowedError'
                    ? 'Izin kamera ditolak. Izinkan akses kamera di browser lalu coba lagi.'
                    : 'Kamera tidak dapat dibuka. Pastikan kamera tidak sedang digunakan aplikasi lain.';
            } finally {
                this.cameraStarting = false;
            }
        },
        stopCamera() {
            this.cameraRequested = false;
            this.stream?.getTracks().forEach((track) => track.stop());
            this.stream = null;
            this.cameraActive = false;
        },
        capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;

            if (! video.videoWidth || ! video.videoHeight) {
                this.error = 'Kamera belum siap. Tunggu sebentar lalu ambil foto kembali.';
                return;
            }

            const scale = Math.min(1, 1600 / video.videoWidth);

            canvas.width = Math.round(video.videoWidth * scale);
            canvas.height = Math.round(video.videoHeight * scale);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob((blob) => {
                if (! blob) {
                    this.error = 'Foto gagal diproses. Silakan ambil ulang.';
                    return;
                }

                const file = new File([blob], `foto-pencucian-${Date.now()}.jpg`, { type: 'image/jpeg' });
                this.uploading = true;
                this.error = null;

                this.$wire.upload(
                    this.statePath,
                    file,
                    () => {
                        this.previewUrl = URL.createObjectURL(blob);
                        this.captured = true;
                        this.uploading = false;
                        this.stopCamera();
                    },
                    () => {
                        this.uploading = false;
                        this.error = 'Foto gagal diunggah. Periksa koneksi internet lalu coba lagi.';
                    },
                );
            }, 'image/jpeg', 0.85);
        },
        async retakePhoto() {
            this.captured = false;
            this.error = null;
            this.$wire.set(this.statePath, null);

            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
                this.previewUrl = null;
            }

            await this.startCamera();
        },
        destroy() {
            this.stopCamera();
            this.stepObserver?.disconnect();

            if (this.previewUrl) {
                URL.revokeObjectURL(this.previewUrl);
            }
        },
    }"
    x-init="$nextTick(() => {
        const wizardStep = $el.closest('.fi-sc-wizard-step');
        const syncCamera = () => {
            if (wizardStep?.classList.contains('fi-active')) {
                startCamera();
            } else {
                stopCamera();
            }
        };
        if (! wizardStep) {
            return;
        }
        stepObserver = new MutationObserver(syncCamera);
        stepObserver.observe(wizardStep, { attributes: true, attributeFilter: ['class'] });
        syncCamera();
    })"
    x-on:pagehide.window="stopCamera()"
    x-on:livewire:navigating.window="stopCamera()"
>
    <div class="wash-camera-panel">
        <p class="wash-camera-heading">Foto kondisi kendaraan setelah dicuci</p>
        <p class="wash-camera-help">Pastikan kendaraan terlihat jelas. Foto diambil langsung dari kamera dan dibatasi maksimal 5 MB.</p>

        <div class="wash-camera-preview">
            <video x-ref="video" x-show="cameraActive && ! captured" autoplay playsinline></video>
            <img x-show="captured" :src="previewUrl" alt="Pratinjau foto kendaraan setelah dicuci">
            <div class="wash-camera-placeholder" x-show="! cameraActive && ! captured">
                <x-filament::icon icon="heroicon-o-camera" class="h-10 w-10" />
                <span x-text="cameraStarting ? 'Mengaktifkan kamera...' : 'Menyiapkan kamera...'"></span>
            </div>
        </div>

        <canvas x-ref="canvas" class="hidden"></canvas>

        <p class="wash-camera-error" x-show="error" x-text="error" role="alert"></p>
        <p class="wash-camera-success" x-show="captured && ! uploading">Foto berhasil diambil.</p>
        <p class="wash-camera-help" x-show="uploading">Mengunggah foto...</p>

        <div class="wash-camera-actions">
            <button type="button" class="wash-camera-button wash-camera-button-primary" x-show="! cameraActive && ! cameraStarting && ! captured" x-on:click="startCamera()">
                <span x-text="error ? 'Coba Buka Kamera Lagi' : 'Buka Kamera'"></span>
            </button>
            <button type="button" class="wash-camera-button wash-camera-button-primary" x-show="cameraActive && ! captured" x-on:click="capturePhoto()" x-bind:disabled="uploading">
                Ambil Foto
            </button>
            <button type="button" class="wash-camera-button wash-camera-button-secondary" x-show="captured" x-on:click="retakePhoto()" x-bind:disabled="uploading">
                Ambil Ulang
            </button>
        </div>
    </div>
</div>
