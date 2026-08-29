<div x-data="aiChatbot()" x-cloak class="fixed z-[9999] bottom-24 right-4 sm:bottom-6 sm:right-6 font-sans">
    <!-- Chat Toggle Button -->
    <button @click="toggleChat()" 
            class="relative w-14 h-14 rounded-full bg-gradient-to-tr from-brand-cyan to-gold-500 text-black flex items-center justify-center shadow-[0_10px_35px_rgba(78,205,196,0.45)] hover:shadow-[0_15px_45px_rgba(78,205,196,0.65)] hover:scale-110 active:scale-95 transition-all duration-300 group focus:outline-none">
        <!-- Pulse Glow -->
        <span class="absolute inset-0 rounded-full bg-brand-cyan animate-ping opacity-25 group-hover:opacity-40 transition-opacity"></span>
        
        <!-- Toggle Icons -->
        <span x-show="!isOpen" x-transition:enter="transition duration-200 transform" x-transition:enter-start="rotate-45 scale-0" x-transition:enter-end="rotate-0 scale-100">
            <i data-lucide="message-square" class="w-6 h-6 stroke-[2.5]"></i>
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
         class="absolute bottom-16 right-0 w-[90vw] max-w-[360px] sm:max-w-[400px] h-[520px] max-h-[78vh] bg-[#16181c]/95 backdrop-blur-2xl border border-white/10 rounded-[2rem] shadow-[0_30px_90px_rgba(0,0,0,0.65)] flex flex-col overflow-hidden"
         style="display: none;">
         
        <!-- Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-[#1b3c37]/60 to-black/20 border-b border-white/5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <!-- Avatar with pulsing green light -->
                <div class="relative w-10 h-10 rounded-full bg-[#1b3c37] border border-brand-cyan/20 flex items-center justify-center text-brand-cyan">
                    <i data-lucide="bot" class="w-5 h-5"></i>
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 border border-[#16181c] animate-pulse"></span>
                </div>
                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-white">Savvy - AI Assistant</h4>
                    <p class="text-[9px] text-emerald-400 font-bold uppercase tracking-widest flex items-center gap-1">
                        Online
                    </p>
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
                            : 'bg-white/5 border border-white/10 text-gray-200 rounded-tl-none'"
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

        <!-- Quick Replies -->
        <div class="px-3 pb-2 pt-1.5 flex gap-2 overflow-x-auto scrollbar-hide flex-shrink-0 border-t border-white/5 bg-black/10">
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
                   placeholder="Tanyakan sesuatu..." 
                   class="bg-black/40 border border-gray-800 focus:border-brand-cyan/80 focus:ring-1 focus:ring-brand-cyan/30 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none flex-1 placeholder-gray-600 transition-all">
            
            <button @click="sendChat()" 
                    :disabled="loading || !inputMsg.trim()"
                    class="w-9 h-9 rounded-xl bg-brand-cyan text-black flex items-center justify-center hover:scale-105 active:scale-95 disabled:opacity-40 disabled:scale-100 disabled:pointer-events-none transition-all duration-200">
                <i data-lucide="send-horizontal" class="w-4 h-4"></i>
            </button>
        </div>
    </div>
</div>

<script>
function aiChatbot() {
    return {
        isOpen: false,
        inputMsg: '',
        loading: false,
        messages: [],
        quickQuestions: [
            'Rekomendasi Menu Terpopuler',
            'Ada voucher diskon aktif?',
            'Bagaimana cara memesan?',
            'Apakah Savora buka sekarang?'
        ],
        init() {
            // Load messages from session storage to keep history across pages
            const savedMessages = sessionStorage.getItem('savora_ai_chat');
            if (savedMessages) {
                this.messages = JSON.parse(savedMessages);
            } else {
                this.messages = [
                    {
                        sender: 'bot',
                        text: 'Halo! Selamat datang di Savora. 😊\n\nSaya Savvy, asisten virtual pintar Savora yang siap membantu menjawab pertanyaan Anda seputar menu lezat kami, harga, promo voucher, hingga cara pemesanan. Ada yang bisa saya bantu?'
                    }
                ];
            }
        },
        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.scrollToBottom();
                // Refresh Lucide icons in case new elements rendered
                setTimeout(() => lucide.createIcons(), 50);
            }
        },
        askPreset(question) {
            this.inputMsg = question;
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

            // Extract last 10 messages for context history
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
                    history: history.slice(0, -1) // Excluding the one we just added
                })
            })
            .then(res => res.json())
            .then(data => {
                this.messages.push({
                    sender: 'bot',
                    text: data.response || 'Maaf, saya sedang mengalami kendala. Silakan coba kembali.'
                });
                this.saveHistory();
                this.loading = false;
                this.scrollToBottom();
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
            
            // Encode HTML special chars to prevent XSS
            let safeText = text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");

            // Format double newlines to paragraphs
            safeText = safeText.replace(/\n\n/g, '<br><br>');
            // Format single newlines to linebreaks
            safeText = safeText.replace(/\n/g, '<br>');

            // Match markdown bold: **text**
            safeText = safeText.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

            // Match inline code formatting: `code`
            safeText = safeText.replace(/`(.*?)`/g, '<code class="bg-white/10 px-1 py-0.5 rounded text-[11px] font-mono text-[#f7e1a0]">$1</code>');

            // Match markdown links: [link text](url)
            // Pattern handles standard links like [Sourdough](/produk/sourdough)
            safeText = safeText.replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" class="text-brand-cyan hover:text-teal-300 font-bold underline transition-colors">$1</a>');

            return safeText;
        }
    };
}
</script>

<style>
/* Custom thin scrollbar styling for Chat Box */
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
