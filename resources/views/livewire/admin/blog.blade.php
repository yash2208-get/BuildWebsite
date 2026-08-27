<div class="space-y-6">
    <x-ui.page-header title="Blog Management" description="Review, publish and moderate blog content.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat label="Total posts" :value="$totals['all']" icon="book" color="brand" />
        <x-ui.stat label="Published" :value="$totals['published']" icon="check-circle" color="emerald" />
        <x-ui.stat label="Drafts" :value="$totals['draft']" icon="edit" color="amber" />
        <x-ui.stat label="AI generated" :value="$totals['ai']" icon="sparkles" color="violet" />
    </div>

    <x-ui.toolbar placeholder="Search posts…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No posts found" emptyIcon="book" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="title" :sortIcon="$this->sortIcon('title')">Post</x-ui.th>
            <x-ui.th>Author</x-ui.th>
            <x-ui.th sort="views_count" :sortIcon="$this->sortIcon('views_count')">Views</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Date</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->title }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->excerpt, 60) }}</p>
                        </div></td>
                    <td><x-ui.user-cell :user="$row->user" /></td>
                    <td><span class="tabular-nums">{{ number_format($row->views_count ?? 0) }}</span></td>
                    <td><x-ui.badge :color="$row->status === 'published' ? 'emerald' : 'amber'" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            @if($row->status !== 'published')
                                <button wire:click="publishPost({{ $row->id }})" class="btn btn-ghost btn-icon text-emerald-500" title="Publish"><x-icon name="rocket" class="w-4 h-4" /></button>
                            @else
                                <button wire:click="unpublishPost({{ $row->id }})" class="btn btn-ghost btn-icon" title="Unpublish"><x-icon name="eye-off" class="w-4 h-4" /></button>
                            @endif
                            <button wire:click="deletePost({{ $row->id }})" wire:confirm="Delete this post?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
