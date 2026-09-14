<script>
    window.i18n = @js([
        'close' => __('Close'),
        'chooseOperator' => __('Choose Orange Money or MTN MoMo.'),
        'invalidPhone' => __('Enter a valid Cameroon mobile number, e.g. 677 11 22 33.'),
        'approvePrompt' => __('Approve the :operator prompt sent to :phone'),
    ]);

    // Toasts: flash messages and Livewire `status` events
    window.showToast = function (message) {
        const stack = document.getElementById('toast-stack');
        if (!stack || !window.bootstrap) return;
        const el = document.createElement('div');
        el.className = 'toast align-items-center border-0 app-toast';
        el.setAttribute('role', 'status');
        el.innerHTML = '<div class="d-flex"><div class="toast-body"><i class="bi bi-check-circle-fill me-2"></i></div>' +
            '<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button></div>';
        el.querySelector('.btn-close').setAttribute('aria-label', window.i18n.close);
        el.querySelector('.toast-body').append(document.createTextNode(message));
        stack.append(el);
        el.addEventListener('hidden.bs.toast', () => el.remove());
        new bootstrap.Toast(el, { delay: 4000 }).show();
    };

    document.addEventListener('livewire:init', () => {
        Livewire.on('status', ({ message }) => window.showToast(message));
    });

    document.addEventListener('alpine:init', () => {
        const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

        // Simulated Orange Money / MTN MoMo checkout (see components/mobile-money-pay)
        Alpine.data('mobileMoneyPay', (cfg) => ({
            method: cfg.method || null,
            phone: cfg.phone || '',
            amount: Number(cfg.amount) || 0,
            stage: null, // null | 'waiting' | 'success'
            error: '',

            init() {
                if (cfg.mode === 'form' && cfg.amountField) {
                    const field = this.$root.closest('form')?.elements[cfg.amountField];
                    if (field) {
                        this.amount = Number(field.value) || 0;
                        field.addEventListener('input', () => this.amount = Number(field.value) || 0);
                    }
                }
            },

            get busy() { return this.stage !== null; },
            get operator() { return this.method === 'mtn_momo' ? 'MTN MoMo' : 'Orange Money'; },
            get buttonLabel() {
                return this.amount > 0 ? `${cfg.label} ${new Intl.NumberFormat('en-US').format(this.amount)} XAF` : cfg.label;
            },
            get promptText() {
                return window.i18n.approvePrompt.replace(':operator', this.operator).replace(':phone', this.maskedPhone);
            },
            digits() { return (this.phone || '').replace(/\D/g, '').replace(/^237/, ''); },
            get maskedPhone() { const d = this.digits(); return d.slice(0, 1) + '•• •• •• ' + d.slice(-2); },

            async start() {
                this.error = '';
                if (!this.method) { this.error = window.i18n.chooseOperator; return; }
                if (!/^6\d{8}$/.test(this.digits())) { this.error = window.i18n.invalidPhone; return; }

                const form = this.$root.closest('form');
                if (cfg.mode === 'form') {
                    if (form && !form.reportValidity()) return;
                } else {
                    this.$wire.payMethod = this.method;
                    this.$wire.payPhone = this.phone;
                    if (cfg.validate && !(await this.$wire.call(cfg.validate))) return;
                }

                this.stage = 'waiting';
                await wait(3000);
                this.stage = 'success';
                await wait(800);

                if (cfg.mode === 'form') {
                    form.submit();
                } else {
                    await this.$wire.call(cfg.action);
                    this.stage = null;
                }
            },
        }));

        // Signature pad: pointer events cover mouse, touch and pen
        Alpine.data('signaturePad', () => ({
            hasInk: false,
            drawing: false,
            ctx: null,

            init() {
                this.ctx = this.$refs.canvas.getContext('2d');
                this.resize();
                window.addEventListener('resize', () => this.resize());
            },
            resize() {
                const canvas = this.$refs.canvas;
                const snapshot = this.hasInk ? canvas.toDataURL() : null;
                const rect = canvas.getBoundingClientRect();
                const ratio = window.devicePixelRatio || 1;
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                this.ctx.scale(ratio, ratio);
                Object.assign(this.ctx, { lineWidth: 2.2, lineCap: 'round', lineJoin: 'round', strokeStyle: '#14213d' });
                if (snapshot) {
                    const img = new Image();
                    img.onload = () => this.ctx.drawImage(img, 0, 0, rect.width, rect.height);
                    img.src = snapshot;
                }
            },
            point(e) {
                const rect = this.$refs.canvas.getBoundingClientRect();
                return { x: e.clientX - rect.left, y: e.clientY - rect.top };
            },
            down(e) {
                this.drawing = true;
                this.$refs.canvas.setPointerCapture(e.pointerId);
                const p = this.point(e);
                this.ctx.beginPath();
                this.ctx.moveTo(p.x, p.y);
                this.ctx.lineTo(p.x + 0.1, p.y + 0.1);
                this.ctx.stroke();
                this.hasInk = true;
            },
            move(e) {
                if (!this.drawing) return;
                const p = this.point(e);
                this.ctx.lineTo(p.x, p.y);
                this.ctx.stroke();
            },
            up() {
                if (!this.drawing) return;
                this.drawing = false;
                this.$refs.output.value = this.$refs.canvas.toDataURL('image/png');
            },
            clear() {
                const canvas = this.$refs.canvas;
                this.ctx.save();
                this.ctx.setTransform(1, 0, 0, 1, 0, 0);
                this.ctx.clearRect(0, 0, canvas.width, canvas.height);
                this.ctx.restore();
                this.hasInk = false;
                this.$refs.output.value = '';
            },
        }));
    });
</script>
