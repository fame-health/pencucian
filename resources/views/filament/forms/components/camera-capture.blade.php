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
        photoSizeKb: null,
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
        async capturePhoto() {
            const video = this.$refs.video;
            const canvas = this.$refs.canvas;
            const maxBytes = 200 * 1024;

            if (! video.videoWidth || ! video.videoHeight) {
                this.error = 'Kamera belum siap. Tunggu sebentar lalu ambil foto kembali.';
                return;
            }

            this.uploading = true;
            this.error = null;

            try {
                const sourceCanvas = document.createElement('canvas');
                const sourceScale = Math.min(1, 1600 / Math.max(video.videoWidth, video.videoHeight));

                sourceCanvas.width = Math.round(video.videoWidth * sourceScale);
                sourceCanvas.height = Math.round(video.videoHeight * sourceScale);

                const sourceContext = sourceCanvas.getContext('2d');

                if (! sourceContext) {
                    throw new Error('Kamera tidak dapat menyiapkan gambar untuk dikompres.');
                }

                sourceContext.drawImage(video, 0, 0, sourceCanvas.width, sourceCanvas.height);
                this.stopCamera();

                let width = sourceCanvas.width;
                let height = sourceCanvas.height;
                let blob = null;

                for (let resizeAttempt = 0; resizeAttempt < 12; resizeAttempt++) {
                    canvas.width = width;
                    canvas.height = height;

                    const context = canvas.getContext('2d');

                    if (! context) {
                        throw new Error('Kamera tidak dapat memproses gambar.');
                    }

                    context.drawImage(sourceCanvas, 0, 0, width, height);

                    for (const quality of [0.82, 0.72, 0.62, 0.52, 0.42, 0.32, 0.24]) {
                        blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', quality));

                        if (blob?.size <= maxBytes) {
                            break;
                        }
                    }

                    if (blob?.size <= maxBytes) {
                        break;
                    }

                    width = Math.round(width * 0.85);
                    height = Math.round(height * 0.85);
                }

                if (! blob || blob.size > maxBytes) {
                    throw new Error('Foto tidak dapat dikompres hingga maksimal 200 KB. Silakan ambil ulang dengan pencahayaan yang cukup.');
                }

                const file = new File([blob], `foto-pencucian-${Date.now()}.jpg`, { type: 'image/jpeg' });
                this.photoSizeKb = Math.ceil(blob.size / 1024);

                this.$wire.upload(
                    this.statePath,
                    file,
                    () => {
                        this.previewUrl = URL.createObjectURL(blob);
                        this.captured = true;
                        this.uploading = false;
                    },
                    () => {
                        this.uploading = false;
                        this.error = 'Foto gagal diunggah. Periksa koneksi internet lalu coba lagi.';
                    },
                );
            } catch (compressionError) {
                this.uploading = false;
                this.error = compressionError instanceof Error
                    ? compressionError.message
                    : 'Foto gagal diproses. Silakan ambil ulang.';
            }
        },
        async retakePhoto() {
            this.captured = false;
            this.error = null;
            this.photoSizeKb = null;
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
        <p class="wash-camera-help">Pastikan kendaraan terlihat jelas. Foto akan dikompres otomatis menjadi JPEG maksimal 200 KB.</p>

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
        <p class="wash-camera-success" x-show="captured && ! uploading">
            Foto berhasil diambil dan dikompres (<span x-text="photoSizeKb"></span> KB).
        </p>
        <p class="wash-camera-help" x-show="uploading">Mengompres dan mengunggah foto...</p>

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
