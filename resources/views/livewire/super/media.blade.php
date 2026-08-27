<div class="space-y-6">
    <x-ui.page-header title="All Media" description="Every file stored on the platform.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-2">
        <x-ui.stat label="Total files" :value="number_format($totals['all'])" icon="image" color="brand" />
        <x-ui.stat label="Storage" :value="$totals['size'].' MB'" icon="database" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search files…">
        <select wire:model.live="filters.type" class="field lg:w-44 shrink-0">
            <option value="">All types</option>
                <option value="image">Images</option>
                <option value="video">Video</option>
                <option value="document">Documents</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No media found" emptyIcon="image" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="original_name" :sortIcon="$this->sortIcon('original_name')">File</x-ui.th>
            <x-ui.th>Owner</x-ui.th>
            <x-ui.th sort="type" :sortIcon="$this->sortIcon('type')">Type</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Uploaded</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="flex items-center gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-lg bg-surface-muted grid place-items-center overflow-hidden shrink-0">
                                @if($row->type === 'image')<img src="{{ $row->url }}" class="w-full h-full object-cover" alt="" loading="lazy">
                                @else<x-icon name="file" class="w-4 h-4 text-tertiary" />@endif
                            </span>
                            <div class="min-w-0">
                                <p class="font-medium truncate">{{ $row->original_name }}</p>
                                <p class="text-xs text-tertiary">{{ round($row->size / 1024) }} KB</p>
                            </div>
                        </div></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><x-ui.badge color="slate">{{ ucfirst($row->type) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><button wire:click="deleteMedia({{ $row->id }})" wire:confirm="Delete this file?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
