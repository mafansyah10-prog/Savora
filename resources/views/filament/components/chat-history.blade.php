@php
    $record = $getRecord();
    $messages = $record ? $record->messages()->orderBy('created_at', 'asc')->get() : collect();
@endphp

<div class="space-y-4 p-4 bg-gray-950 border border-gray-800 rounded-2xl max-h-[400px] overflow-y-auto custom-scrollbar">
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
