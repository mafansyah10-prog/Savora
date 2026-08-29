@php
    $record = $getRecord();
    $messages = $record ? $record->messages()->orderBy('created_at', 'asc')->get() : collect();
@endphp

<div id="admin-chat-messages-container" class="space-y-4 p-4 bg-gray-950 border border-gray-800 rounded-2xl max-h-[400px] overflow-y-auto custom-scrollbar" data-last-count="{{ $messages->count() }}">
    @if($messages->isEmpty())
        <div class="text-center py-6 text-sm text-gray-500 font-medium">
            Belum ada obrolan dalam sesi ini.
        </div>
    @else
        @foreach($messages as $msg)
            <div class="flex flex-col {{ $msg->sender === 'admin' ? 'items-end' : 'items-start' }} space-y-1">
                <!-- Message Bubble -->
                <div class="px-3.5 py-2 rounded-2xl text-xs max-w-[80%] leading-relaxed shadow-sm break-words
                    {{ $msg->sender === 'admin' 
                        ? 'bg-primary-600 text-white rounded-tr-none' 
                        : 'bg-gray-800 text-gray-100 border border-gray-700 rounded-tl-none' }}"
                >
                    {!! nl2br(e($msg->message)) !!}
                </div>
                <!-- Time & Sender Info -->
                <span class="text-[9px] text-gray-500 px-1 font-medium tracking-wide">
                    {{ $msg->sender === 'admin' ? 'Admin' : 'Pelanggan' }} • {{ $msg->created_at->setTimezone('Asia/Jakarta')->format('H:i') }}
                </span>
            </div>
        @endforeach
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('admin-chat-messages-container');
    const sessionId = "{{ $record ? $record->id : '' }}";
    
    if (!sessionId || !container) return;

    // Scroll to bottom initially
    container.scrollTop = container.scrollHeight;

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function fetchMessages() {
        fetch(`/ai-chat/admin-messages?session_id=${sessionId}`)
            .then(res => res.json())
            .then(data => {
                if (!data.messages) return;

                let html = '';
                if (data.messages.length === 0) {
                    html = '<div class="text-center py-6 text-sm text-gray-500 font-medium">Belum ada obrolan dalam sesi ini.</div>';
                } else {
                    data.messages.forEach(msg => {
                        const isAdmin = msg.sender === 'admin';
                        const bubbleClass = isAdmin 
                            ? 'bg-primary-600 text-white rounded-tr-none' 
                            : 'bg-gray-800 text-gray-100 border border-gray-700 rounded-tl-none';
                        const containerClass = isAdmin ? 'items-end' : 'items-start';
                        const senderLabel = isAdmin ? 'Admin' : 'Pelanggan';
                        const formattedText = escapeHtml(msg.message).replace(/\n/g, '<br>');

                        html += `
                            <div class="flex flex-col ${containerClass} space-y-1">
                                <div class="px-3.5 py-2 rounded-2xl text-xs max-w-[80%] leading-relaxed shadow-sm break-words ${bubbleClass}">
                                    ${formattedText}
                                </div>
                                <span class="text-[9px] text-gray-500 px-1 font-medium tracking-wide">
                                    ${senderLabel} • ${msg.time}
                                </span>
                            </div>
                        `;
                    });
                }

                // Update container HTML only if message count changed
                if (container.dataset.lastCount != data.messages.length) {
                    container.innerHTML = html;
                    container.dataset.lastCount = data.messages.length;
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(err => console.error('Error fetching admin messages:', err));
    }

    // Poll every 3 seconds
    setInterval(fetchMessages, 3000);
});
</script>

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.02);
    border-radius: 99px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.08);
    border-radius: 99px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.2);
}
</style>
