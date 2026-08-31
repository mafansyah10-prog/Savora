<div x-data="aiChatbot({{ \App\Models\Setting::getGlobal()->isStoreOpen() ? 'true' : 'false' }})" 
     x-cloak 
     class="fixed z-[9999] font-sans touch-none select-none"
     :style="'left: ' + x + 'px; top: ' + y + 'px; bottom: auto; right: auto;'">
    <!-- Chat Toggle Button -->
    <button @click="toggleChat()" 
            @mousedown="startDrag($event)"
            @touchstart="startDrag($event)"
            class="relative w-14 h-14 rounded-full bg-gradient-to-tr from-brand-cyan to-gold-500 text-black flex items-center justify-center shadow-[0_10px_35px_rgba(78,205,196,0.45)] hover:shadow-[0_15px_45px_rgba(78,205,196,0.65)] hover:scale-110 active:scale-95 transition-all duration-300 group focus:outline-none cursor-move">
        <!-- Pulse Glow -->
        <span class="absolute inset-0 rounded-full bg-brand-cyan animate-ping opacity-25 group-hover:opacity-40 transition-opacity"></span>
        
        <!-- Toggle Icons -->
        <span x-show="!isOpen" x-transition:enter="transition duration-200 transform" x-transition:enter-start="rotate-45 scale-0" x-transition:enter-end="rotate-0 scale-100">
            <div class="relative flex items-center justify-center font-serif text-lg font-black text-black border-2 border-black rounded-full w-8 h-8 select-none">
                S
                <!-- Tiny AI Badge -->
                <span class="absolute -top-1.5 -right-1.5 bg-black text-brand-cyan rounded-full p-0.5 border border-brand-cyan/30 shadow-md">
                    <i data-lucide="bot" class="w-3 h-3"></i>
                </span>
            </div>
        </span>
        <span x-show="isOpen" x-transition:enter="transition duration-200 transform" x-transition:enter-start="-rotate-45 scale-0" x-transition:enter-end="rotate-0 scale-100" style="display: none;">
            <i data-lucide="x" class="w-6 h-6 stroke-[2.5]"></i>
        </span>
    </button>

    <!-- Chat Window Container -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-8 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-8 scale-95"
         :class="(x > window.innerWidth / 2 ? 'right-0' : 'left-0') + ' ' + (y > window.innerHeight / 2 ? 'bottom-16' : 'top-16') + ' absolute w-[90vw] max-w-[360px] sm:max-w-[400px] h-[520px] max-h-[78vh] bg-[#16181c]/95 backdrop-blur-2xl border border-white/10 rounded-[2rem] shadow-[0_30px_90px_rgba(0,0,0,0.65)] flex flex-col overflow-hidden'"
         style="display: none;">
         
        <!-- Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-[#1b3c37]/60 to-black/20 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Avatar with pulsing light -->
                <div class="relative w-10 h-10 rounded-full bg-[#1b3c37] border border-brand-cyan/20 flex items-center justify-center text-brand-cyan">
                    <i :data-lucide="liveChatMode ? 'user-cog' : 'bot'" class="w-5 h-5"></i>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border border-[#16181c] animate-pulse"
                          :class="liveChatMode ? 'bg-orange-500' : (isStoreOpen ? 'bg-emerald-500' : 'bg-red-500/80')"></span>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-white" x-text="liveChatMode ? 'Live Chat Admin' : 'Savvy - AI Assistant'"></h4>
                    <div class="flex items-center gap-2">
                        <p class="text-[9px] font-bold uppercase tracking-widest flex items-center gap-1"
                           :class="liveChatMode ? 'text-orange-400' : (isStoreOpen ? 'text-emerald-400' : 'text-red-400/80')"
                           x-text="liveChatMode ? 'Terhubung dengan Admin' : (isStoreOpen ? 'Online' : 'Offline')"></p>
                        <span x-show="liveChatMode && secondsRemaining > 0" 
                              class="text-[9px] text-orange-400 font-bold bg-orange-500/10 px-1.5 py-0.5 rounded border border-orange-500/20"
                              x-text="Math.floor(secondsRemaining / 60) + 'm ' + (secondsRemaining % 60) + 's'"></span>
                    </div>
                </div>
            </div>
            <button @click="isOpen = false" class="text-gray-400 hover:text-white transition p-1">
                <i data-lucide="minus" class="w-4 h-4"></i>
            </button>
        </div>

        <!-- Messages Area -->
        <div x-ref="messageBox" class="flex-1 overflow-y-auto p-4 space-y-3.5 scrollbar-thin">
            <template x-for="(msg, index) in messages" :key="index">
                <div class="flex flex-col" :class="msg.sender === 'user' ? 'items-end' : 'items-start'">
                    <!-- Chat Bubble -->
                    <div class="px-4 py-2.5 rounded-2xl text-xs leading-relaxed max-w-[85%] break-words shadow-md transition-all duration-300"
                         :class="msg.sender === 'user' 
                            ? 'bg-gradient-to-br from-brand-cyan/20 to-brand-teal/40 border border-brand-cyan/35 text-white rounded-tr-none' 
                            : (msg.sender === 'system' 
                                ? 'bg-orange-500/10 border border-orange-500/20 text-orange-400 rounded-2xl text-center self-center max-w-[95%]'
                                : 'bg-white/5 border border-white/10 text-gray-200 rounded-tl-none')"
                         x-html="parseMessage(msg.text)">
                    </div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="loading" class="flex items-center space-x-1.5 bg-white/5 border border-white/10 px-4 py-3 rounded-2xl rounded-tl-none w-16 shadow-md" style="display: none;">
                <span class="w-1.5 h-1.5 bg-brand-cyan rounded-full animate-bounce" style="animation-delay: 0.1s"></span>
                <span class="w-1.5 h-1.5 bg-brand-cyan rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                <span class="w-1.5 h-1.5 bg-brand-cyan rounded-full animate-bounce" style="animation-delay: 0.3s"></span>
            </div>
        </div>

        <!-- Timeout Warning Banner -->
        <div x-show="liveChatMode && secondsRemaining > 0 && secondsRemaining <= 60"
             class="px-4 py-2 bg-amber-500/10 border-t border-b border-amber-500/30 text-[10px] text-amber-400 font-bold uppercase tracking-wider flex items-center justify-between flex-shrink-0"
             style="display: none;">
            <span class="flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                Batas Waktu Chat Hampir Habis!
            </span>
            <span x-text="secondsRemaining + 's'"></span>
        </div>

        <!-- Quick Replies (Hidden in Live Chat Mode) -->
        <div x-show="!liveChatMode" class="px-3 pb-2 pt-1.5 flex gap-2 overflow-x-auto scrollbar-hide flex-shrink-0 border-t border-white/5 bg-black/10">
            <template x-for="q in quickQuestions" :key="q">
                <button @click="askPreset(q)" 
                        class="flex-shrink-0 px-3 py-1.5 bg-white/5 hover:bg-brand-cyan/15 hover:border-brand-cyan/40 border border-white/10 text-[10px] text-gray-300 hover:text-white rounded-full transition-all duration-200 focus:outline-none">
                    <span x-text="q"></span>
                </button>
            </template>
        </div>

        <!-- Input Area -->
        <div class="p-3 bg-black/20 border-t border-white/5 flex gap-2 items-center">
            <input type="text" 
                   x-model="inputMsg" 
                   @keydown.enter="sendChat()" 
                   :disabled="!isStoreOpen"
                   :placeholder="liveChatMode ? 'Ketik pesan untuk Admin...' : (!isStoreOpen ? 'Asisten offline (toko sedang tutup)' : 'Tanyakan sesuatu...')" 
                   class="bg-black/40 border border-gray-800 focus:border-brand-cyan/80 focus:ring-1 focus:ring-brand-cyan/30 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none flex-1 placeholder-gray-600 transition-all disabled:opacity-50">
            
            <button @click="sendChat()" 
                    :disabled="loading || !inputMsg.trim() || !isStoreOpen"
                    class="w-9 h-9 rounded-xl bg-brand-cyan text-black flex items-center justify-center hover:scale-105 active:scale-95 disabled:opacity-40 disabled:scale-100 disabled:pointer-events-none transition-all duration-200">
                <i data-lucide="send-horizontal" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<script>
function aiChatbot(isStoreOpen) {
    return {
        isStoreOpen: isStoreOpen,
        isOpen: false,
        inputMsg: '',
        loading: false,
        messages: [],
        liveChatMode: false,
        sessionToken: '',
        pollIntervalId: null,
        expiresAt: null,
        secondsRemaining: 0,
        countdownIntervalId: null,
        isLoggedIn: {{ auth()->check() ? 'true' : 'false' }},
        x: 0,
        y: 0,
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        dragged: false,
        startDrag(e) {
            this.isDragging = true;
            this.dragged = false;
            
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            
            this.dragStartX = clientX - this.x;
            this.dragStartY = clientY - this.y;
            
            const onDrag = (moveEvent) => {
                if (!this.isDragging) return;
                this.dragged = true;
                const curX = moveEvent.touches ? moveEvent.touches[0].clientX : moveEvent.clientX;
                const curY = moveEvent.touches ? moveEvent.touches[0].clientY : moveEvent.clientY;
                
                let newX = curX - this.dragStartX;
                let newY = curY - this.dragStartY;
                
                newX = Math.max(10, Math.min(newX, window.innerWidth - 70));
                newY = Math.max(10, Math.min(newY, window.innerHeight - 70));
                
                this.x = newX;
                this.y = newY;
            };
            
            const stopDrag = () => {
                this.isDragging = false;
                window.removeEventListener('mousemove', onDrag);
                window.removeEventListener('mouseup', stopDrag);
                window.removeEventListener('touchmove', onDrag);
                window.removeEventListener('touchend', stopDrag);
            };
            
            window.addEventListener('mousemove', onDrag);
            window.addEventListener('mouseup', stopDrag);
            window.addEventListener('touchmove', onDrag, { passive: true });
            window.addEventListener('touchend', stopDrag);
        },
        quickQuestions: [
            'Rekomendasi Menu Terpopuler',
            'Ada voucher diskon aktif?',
            '📞 Hubungi Admin',
            'Apakah Savora buka sekarang?'
        ],
        init() {
            // Initial positioning: default near bottom right
            const isMobile = window.innerWidth < 640;
            this.x = window.innerWidth - (isMobile ? 70 : 80);
            this.y = window.innerHeight - (isMobile ? 160 : 100);

            // Keep it on screen when resized
            window.addEventListener('resize', () => {
                this.x = Math.max(10, Math.min(this.x, window.innerWidth - 70));
                this.y = Math.max(10, Math.min(this.y, window.innerHeight - 70));
            });

            // Filter out admin support option if guest or store is closed
            if (!this.isLoggedIn || !this.isStoreOpen) {
                this.quickQuestions = this.quickQuestions.filter(q => !q.includes('Hubungi Admin'));
            }

            // Retrieve or generate session token
            let token = localStorage.getItem('savora_support_token');
            if (!token) {
                token = 'token_' + Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                localStorage.setItem('savora_support_token', token);
            }
            this.sessionToken = token;

            // Load saved messages
            const savedMessages = sessionStorage.getItem('savora_ai_chat');
            if (savedMessages) {
                this.messages = JSON.parse(savedMessages);
            } else {
                this.messages = [
                    {
                        sender: 'bot',
                        text: this.isStoreOpen
                            ? 'Halo! Selamat datang di Savora. 😊\n\nSaya Savvy, asisten virtual pintar Savora yang siap membantu menjawab pertanyaan Anda seputar menu lezat kami, harga, promo voucher, hingga cara pemesanan.' + 
                              (this.isLoggedIn ? '\n\nJika ada kendala transaksi atau ingin langsung berbicara dengan admin, klik tombol **"📞 Hubungi Admin"** di bawah.' : '')
                            : 'Halo! Mohon maaf, saat ini toko kami sedang tutup. Asisten Savvy sedang offline dan tidak dapat menerima pesan saat ini. Silakan kunjungi kami kembali saat jam operasional toko buka! Terima kasih. 🙏'
                    }
                ];
            }

            // Check if there is an active support chat on backend
            this.checkSupportStatus();
        },
        toggleChat() {
            if (this.dragged) {
                this.dragged = false;
                return;
            }
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.scrollToBottom();
                setTimeout(() => lucide.createIcons(), 50);
            }
        },
        askPreset(question) {
            if (question.includes('Hubungi Admin')) {
                this.inputMsg = '9';
            } else {
                this.inputMsg = question;
            }
            this.sendChat();
        },
        sendChat() {
            if (!this.inputMsg.trim() || this.loading) return;

            const textToSend = this.inputMsg.trim();
            this.messages.push({
                sender: 'user',
                text: textToSend
            });
            this.inputMsg = '';
            this.loading = true;
            this.scrollToBottom();
            this.saveHistory();

            if (this.liveChatMode) {
                // Route message directly to Admin Support API
                fetch('/ai-chat/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: textToSend,
                        session_token: this.sessionToken
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data.success === false) {
                        this.messages.push({
                            sender: 'system',
                            text: 'Sesi chat telah selesai atau ditutup.'
                        });
                        this.liveChatMode = false;
                        this.stopPolling();
                        this.saveHistory();
                    }
                    this.scrollToBottom();
                })
                .catch(err => {
                    console.error(err);
                    this.messages.push({
                        sender: 'system',
                        text: 'Gagal mengirim pesan. Silakan periksa koneksi Anda.'
                    });
                    this.loading = false;
                    this.scrollToBottom();
                });
            } else {
                // Route message to normal AI bot API
                const history = this.messages.slice(-10).map(m => ({
                    sender: m.sender,
                    text: m.text
                }));

                fetch('/ai-chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: textToSend,
                        history: history.slice(0, -1),
                        session_token: this.sessionToken
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.loading = false;
                    if (data.live_chat === true) {
                        this.liveChatMode = true;
                        this.messages.push({
                            sender: 'bot',
                            text: data.response
                        });
                        this.startPolling();
                        this.startCountdown();
                    } else {
                        this.messages.push({
                            sender: 'bot',
                            text: data.response || 'Maaf, saya tidak dapat memahami respons tersebut. Ada hal lain yang bisa saya bantu?'
                        });
                    }
                    this.saveHistory();
                    this.scrollToBottom();
                    setTimeout(() => lucide.createIcons(), 50);
                })
                .catch(err => {
                    console.error(err);
                    this.messages.push({
                        sender: 'bot',
                        text: 'Aduh, sepertinya ada gangguan koneksi internet. Mohon pastikan koneksi Anda lancar dan coba lagi! 🙏'
                    });
                    this.loading = false;
                    this.scrollToBottom();
                });
            }
        },
        checkSupportStatus() {
            fetch(`/ai-chat/poll?session_token=${encodeURIComponent(this.sessionToken)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.live_chat === true) {
                        this.liveChatMode = true;
                        this.expiresAt = data.expires_at;
                        this.startPolling();
                        this.startCountdown();
                    }
                })
                .catch(err => console.error('Error checking chat status:', err));
        },
        startPolling() {
            if (this.pollIntervalId) clearInterval(this.pollIntervalId);
            
            this.pollIntervalId = setInterval(() => {
                fetch(`/ai-chat/poll?session_token=${encodeURIComponent(this.sessionToken)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.live_chat === false) {
                            if (this.liveChatMode) {
                                this.messages.push({
                                    sender: 'system',
                                    text: 'Hubungan Live Chat telah diselesaikan oleh Admin. Anda kembali berinteraksi dengan AI Assistant Savvy. 🤖'
                                });
                                this.liveChatMode = false;
                                this.stopPolling();
                                this.saveHistory();
                                this.scrollToBottom();
                            }
                        } else {
                            if (data.expires_at) {
                                this.expiresAt = data.expires_at;
                                this.startCountdown();
                            }

                            if (data.messages && data.messages.length > 0) {
                                data.messages.forEach(msg => {
                                    this.messages.push({
                                        sender: 'bot',
                                        text: msg.text
                                    });
                                });
                                this.saveHistory();
                                this.scrollToBottom();
                            }
                        }
                    })
                    .catch(err => console.error('Error polling support chat:', err));
            }, 3000);
        },
        stopPolling() {
            if (this.pollIntervalId) {
                clearInterval(this.pollIntervalId);
                this.pollIntervalId = null;
            }
            this.stopCountdown();
        },
        startCountdown() {
            if (this.countdownIntervalId) clearInterval(this.countdownIntervalId);
            
            this.countdownIntervalId = setInterval(() => {
                if (!this.expiresAt) {
                    this.secondsRemaining = 0;
                    return;
                }
                const diff = Math.floor((new Date(this.expiresAt) - new Date()) / 1000);
                this.secondsRemaining = Math.max(0, diff);
                
                if (this.secondsRemaining <= 0) {
                    if (this.liveChatMode) {
                        this.messages.push({
                            sender: 'system',
                            text: 'Sesi obrolan telah berakhir secara otomatis karena batas waktu kedaluwarsa. Anda kembali berinteraksi dengan AI Assistant Savvy. 🤖'
                        });
                        this.liveChatMode = false;
                        this.stopPolling();
                        this.saveHistory();
                        this.scrollToBottom();
                    }
                }
            }, 1000);
        },
        stopCountdown() {
            if (this.countdownIntervalId) {
                clearInterval(this.countdownIntervalId);
                this.countdownIntervalId = null;
            }
            this.secondsRemaining = 0;
            this.expiresAt = null;
        },
        saveHistory() {
            sessionStorage.setItem('savora_ai_chat', JSON.stringify(this.messages));
        },
        scrollToBottom() {
            this.$nextTick(() => {
                const box = this.$refs.messageBox;
                if (box) {
                    box.scrollTop = box.scrollHeight;
                }
            });
        },
        parseMessage(text) {
            if (!text) return '';
            
            let safeText = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            safeText = safeText.replace(/\n\n/g, '<br><br>');
            safeText = safeText.replace(/\n/g, '<br>');
            safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            safeText = safeText.replace(/`(.*?)`/g, '<code class="bg-white/10 px-1 py-0.5 rounded text-[11px] font-mono text-[#f7e1a0]">$1</code>');
            safeText = safeText.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="text-brand-cyan hover:text-teal-300 font-bold underline transition-colors">$1</a>');

            return safeText;
        }
    };
}
</script>

<style>
.scrollbar-thin::-webkit-scrollbar {
    width: 4px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 99px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.25);
}
</style>
