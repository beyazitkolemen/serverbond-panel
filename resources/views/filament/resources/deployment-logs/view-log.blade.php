<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Site</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->site->name }}</p>
        </div>

        @if($record->deployment)
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Deployment ID</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">#{{ $record->deployment_id }}</p>
        </div>
        @endif

        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Seviye</p>
            <p class="text-sm">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($record->level === 'success') bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100
                    @elseif($record->level === 'info') bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100
                    @elseif($record->level === 'warning') bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100
                    @elseif($record->level === 'error') bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100
                    @endif">
                    {{ ucfirst($record->level) }}
                </span>
            </p>
        </div>

        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tarih</p>
            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $record->created_at->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>

    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Mesaj</p>
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <pre class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $record->message }}</pre>
        </div>
    </div>

    @if($record->context)
    <div>
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Context</p>
        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
            <pre class="text-sm text-gray-900 dark:text-gray-100">{{ json_encode($record->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif
</div>

