<div class="space-y-6">
    {{-- Genel Bilgiler --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Site</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                {{ $record->site->name }}
            </p>
        </div>

        @if($record->deployment)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Deployment ID</p>
            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-gray-100">
                #{{ $record->deployment_id }}
            </p>
        </div>
        @endif

        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Seviye</p>
            <div class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->level === 'success') bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400
                    @elseif($record->level === 'info') bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400
                    @elseif($record->level === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400
                    @elseif($record->level === 'error') bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400
                    @endif">
                    @if($record->level === 'success')
                        <x-heroicon-o-check-circle class="w-4 h-4 mr-1" />
                    @elseif($record->level === 'info')
                        <x-heroicon-o-information-circle class="w-4 h-4 mr-1" />
                    @elseif($record->level === 'warning')
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-1" />
                    @elseif($record->level === 'error')
                        <x-heroicon-o-x-circle class="w-4 h-4 mr-1" />
                    @endif
                    {{ ucfirst($record->level) }}
                </span>
            </div>
        </div>

        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tarih</p>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $record->created_at->format('d M Y, H:i:s') }}
            </p>
        </div>
    </div>

    {{-- Mesaj --}}
    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Mesaj</p>
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <pre class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap font-mono">{{ $record->message }}</pre>
        </div>
    </div>

    {{-- Context (varsa) --}}
    @if($record->context)
    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Ek Bilgiler</p>
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <pre class="text-xs text-gray-900 dark:text-gray-100 font-mono overflow-x-auto">{{ json_encode($record->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>
    @endif
</div>

