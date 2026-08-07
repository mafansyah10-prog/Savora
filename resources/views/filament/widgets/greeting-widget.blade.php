<div 
    x-data="{
        time: '',
        date: '',
        greeting: '',
        icon: 'sun',
        updateClock() {
            const now = new Date();
            this.time = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            this.date = now.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' });
            
            const hrs = now.getHours();
            if (hrs >= 5 && hrs < 11) {
                this.greeting = 'Pagi';
                this.icon = 'sunrise';
            } else if (hrs >= 11 && hrs < 15) {
                this.greeting = 'Siang';
                this.icon = 'sun';
            } else if (hrs >= 15 && hrs < 18) {
                this.greeting = 'Sore';
                this.icon = 'sunset';
            } else {
                this.greeting = 'Malam';
                this.icon = 'moon';
            }
        }
    }"
    x-init="updateClock(); setInterval(() => updateClock(), 1000)"
    style="
        background-color: #16181d; 
        border: 1px solid rgba(255, 255, 255, 0.08); 
        border-radius: 12px; 
        padding: 16px; 
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); 
        display: flex; 
        flex-direction: column; 
        justify-content: space-between; 
        min-height: 120px; 
        position: relative; 
        overflow: hidden;
        transition: all 0.3s ease;
    "
    onmouseover="this.style.transform='scale(1.02)'; this.style.borderColor='rgba(249, 115, 22, 0.3)';"
    onmouseout="this.style.transform='scale(1)'; this.style.borderColor='rgba(255, 255, 255, 0.08)';"
>
    <!-- Subtle glow effect -->
    <div style="
        position: absolute; 
        right: -20px; 
        top: -20px; 
        width: 80px; 
        height: 80px; 
        border-radius: 50%; 
        background-color: rgba(249, 115, 22, 0.08); 
        filter: blur(20px); 
        pointer-events: none;
    "></div>

    <!-- Top Section: Greeting and Icon -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; z-index: 10;">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; background-color: rgba(249, 115, 22, 0.1); padding: 4px 8px; border-radius: 6px;">
                <span style="width: 6px; height: 6px; background-color: #f97316; border-radius: 50%; display: inline-block;"></span>
                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; color: #f97316; letter-spacing: 0.05em;">
                    Selamat <span x-text="greeting"></span>
                </span>
            </div>
            
            <h3 style="font-size: 14px; font-weight: 700; color: #ffffff; margin-top: 10px; margin-bottom: 0;">
                Hi, {{ auth()->user()->name ?? 'Admin Savora' }} 👋
            </h3>
        </div>
        
        <!-- Icon container -->
        <div style="
            width: 36px; 
            height: 36px; 
            background-color: rgba(0, 0, 0, 0.3); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
        ">
            <template x-if="icon === 'sunrise'">
                <svg style="color: #f97316;" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </template>
            <template x-if="icon === 'sun'">
                <svg style="color: #f97316;" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </template>
            <template x-if="icon === 'sunset'">
                <svg style="color: #f97316;" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                </svg>
            </template>
            <template x-if="icon === 'moon'">
                <svg style="color: #f97316;" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                </svg>
            </template>
        </div>
    </div>

    <!-- Bottom Section: Clock and Date -->
    <div style="
        border-top: 1px solid rgba(255, 255, 255, 0.08); 
        padding-top: 12px; 
        margin-top: 12px; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        gap: 8px;
        z-index: 10;
    ">
        <!-- Live Time with flashing green dot -->
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="position: relative; display: flex; width: 6px; height: 6px;">
                <span style="
                    position: absolute; 
                    display: inline-flex; 
                    height: 100%; 
                    width: 100%; 
                    border-radius: 50%; 
                    background-color: #10b981; 
                    opacity: 0.75; 
                    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
                "></span>
                <span style="position: relative; inline-size: 6px; block-size: 6px; border-radius: 50%; background-color: #10b981; width: 6px; height: 6px;"></span>
            </span>
            <span style="font-family: monospace; font-size: 12px; font-weight: bold; color: #ffffff; letter-spacing: 0.05em;" x-text="time"></span>
        </div>
        
        <!-- Date Badge -->
        <div style="
            display: flex; 
            align-items: center; 
            gap: 4px; 
            font-size: 10px; 
            color: #a0aec0; 
            background-color: rgba(0, 0, 0, 0.25); 
            padding: 3px 8px; 
            border-radius: 6px; 
            border: 1px solid rgba(255, 255, 255, 0.05);
        ">
            <svg style="color: #718096;" width="10" height="10" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
            </svg>
            <span style="font-weight: 700; color: #cbd5e0;" x-text="date"></span>
        </div>
    </div>
</div>

<!-- Ping Animation Keyframe -->
<style>
@keyframes ping {
    75%, 100% {
        transform: scale(2.5);
        opacity: 0;
    }
}
</style>
