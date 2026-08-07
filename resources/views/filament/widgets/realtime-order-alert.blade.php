<x-filament-widgets::widget>
    <div x-data="{
        soundEnabled: true,
        init() {
            setInterval(() => {
                $wire.checkNewOrders();
            }, 4000);
        }
    }"
    @play-order-sound.window="
        if (soundEnabled) {
            let audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            audio.play().catch(e => console.log('Audio playback waiting for user interaction'));
        }
    "
    class="bg-gradient-to-r from-emerald-950/60 via-teal-950/40 to-slate-950/60 border border-emerald-500/30 rounded-2xl p-4 shadow-lg flex flex-col md:flex-row items-center justify-between gap-4">
        
        <div class="flex items-center gap-3.5">
            <div class="relative flex h-4 w-4">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500"></span>
            </div>
            <div>
                <h4 class="text-sm font-black text-emerald-400 uppercase tracking-wider flex items-center gap-2">
                    <span>Monitor Orderan Real-Time Aktif</span>
                </h4>
                <p class="text-xs text-gray-300">
                    Sistem mendeteksi pesanan baru setiap 4 detik & membunyikan lonceng pemberitahuan secara otomatis.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" 
                    @click="soundEnabled = !soundEnabled" 
                    :class="soundEnabled ? 'bg-emerald-500/20 border-emerald-500/50 text-emerald-300' : 'bg-rose-500/20 border-rose-500/50 text-rose-300'"
                    class="border px-4 py-2 rounded-xl text-xs font-black tracking-wider transition-all flex items-center gap-2 cursor-pointer hover:scale-105 active:scale-95">
                <template x-if="soundEnabled">
                    <span class="flex items-center gap-1.5">🔔 Suara Lonceng: ON</span>
                </template>
                <template x-if="!soundEnabled">
                    <span class="flex items-center gap-1.5">🔕 Suara Lonceng: OFF</span>
                </template>
            </button>
        </div>
    </div>
</x-filament-widgets::widget>
