<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Durum</div>
            <div class="mt-1 flex items-center gap-2">
                <x-filament::badge 
                    :color="match($deployment->status->value) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'running' => 'info',
                        default => 'gray'
                    }"
                >
                    {{ $deployment->status->label() }}
                </x-filament::badge>
            </div>
        </div>

        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Süre</div>
            <div class="mt-1 font-semibold">
                {{ $deployment->duration ? gmdate('i:s', $deployment->duration) . ' dakika' : 'Devam ediyor...' }}
            </div>
        </div>

        @if($deployment->commit_hash)
        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Commit Hash</div>
            <div class="mt-1 font-mono text-sm">{{ substr($deployment->commit_hash, 0, 8) }}</div>
        </div>
        @endif

        @if($deployment->commit_author)
        <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Yazar</div>
            <div class="mt-1">{{ $deployment->commit_author }}</div>
        </div>
        @endif
    </div>

    @if($deployment->commit_message)
    <div class="p-4 bg-gray-100 dark:bg-gray-800 rounded-lg">
        <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Commit Mesajı</div>
        <div class="text-sm">{{ $deployment->commit_message }}</div>
    </div>
    @endif

    @if($deployment->output)
    <div class="space-y-2">
        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">Deployment Çıktısı</div>
        <div class="p-4 bg-gray-900 dark:bg-black rounded-lg overflow-auto max-h-96">
            <pre class="text-green-400 text-xs font-mono whitespace-pre-wrap">{{ $deployment->output }}</pre>
        </div>
    </div>
    @endif

    @if($deployment->error)
    <div class="space-y-2">
        <div class="text-sm font-semibold text-red-600 dark:text-red-400">Hata Mesajı</div>
        <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <pre class="text-red-600 dark:text-red-400 text-xs font-mono whitespace-pre-wrap">{{ $deployment->error }}</pre>
        </div>
    </div>
    @endif

    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="text-xs text-gray-500">
            <span>Başlangıç: {{ $deployment->started_at?->format('d M Y, H:i:s') ?? '-' }}</span>
            @if($deployment->finished_at)
            <span class="ml-4">Bitiş: {{ $deployment->finished_at->format('d M Y, H:i:s') }}</span>
            @endif
        </div>
    </div>
</div>

