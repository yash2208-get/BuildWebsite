<div class="space-y-6">
    <x-ui.page-header title="Contact Messages" description="Enquiries submitted through the public contact form.">
    </x-ui.page-header>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-ui.stat label="Total" :value="$totals['all']" icon="mail" color="brand" />
        <x-ui.stat label="Unread" :value="$totals['unread']" icon="bell" color="amber" />
        <x-ui.stat label="Replied" :value="$totals['replied']" icon="check-circle" color="emerald" />
    </div>

    <x-ui.toolbar placeholder="Search messages…">
        <select wire:model.live="filters.status" class="field lg:w-44 shrink-0">
            <option value="">All statuses</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
                <option value="replied">Replied</option>
        </select>
    </x-ui.toolbar>

    <x-ui.table :rows="$rows" empty="No messages found" emptyIcon="mail" emptyDescription="Try adjusting your search or filters.">
        <x-slot:head>
            <x-ui.th sort="name" :sortIcon="$this->sortIcon('name')">From</x-ui.th>
            <x-ui.th>Subject</x-ui.th>
            <x-ui.th sort="status" :sortIcon="$this->sortIcon('status')">Status</x-ui.th>
            <x-ui.th sort="created_at" :sortIcon="$this->sortIcon('created_at')">Received</x-ui.th>
            <x-ui.th align="right"></x-ui.th>
        </x-slot:head>
        <x-slot:body>
            @foreach($rows as $row)
                <tr wire:key="r-{{ $row->id }}">
                    <td><div class="min-w-0">
                            <p class="font-medium truncate">{{ $row->name }}</p>
                            <p class="text-xs text-tertiary truncate">{{ $row->email }}</p>
                        </div></td>
                    <td><div class="min-w-0">
                            <p class="text-sm truncate">{{ $row->subject ?: '(no subject)' }}</p>
                            <p class="text-xs text-tertiary truncate">{{ Str::limit($row->message, 60) }}</p>
                        </div></td>
                    <td><x-ui.badge :color="match($row->status) { 'unread' => 'amber', 'replied' => 'emerald', default => 'slate' }" dot>{{ ucfirst($row->status) }}</x-ui.badge></td>
                    <td><span class="text-xs text-tertiary whitespace-nowrap">{{ $row->created_at->diffForHumans(short: true) }}</span></td>
                    <td class="text-right"><div class="flex items-center justify-end gap-1">
                            <button wire:click="markRead({{ $row->id }})" class="btn btn-ghost btn-icon" title="Mark read"><x-icon name="eye" class="w-4 h-4" /></button>
                            <button wire:click="markReplied({{ $row->id }})" class="btn btn-ghost btn-icon text-emerald-500" title="Mark replied"><x-icon name="check" class="w-4 h-4" /></button>
                            <button wire:click="deleteMessage({{ $row->id }})" wire:confirm="Delete this message?" class="btn btn-ghost btn-icon text-rose-500"><x-icon name="trash" class="w-4 h-4" /></button>
                        </div></td>
                </tr>
            @endforeach
        </x-slot:body>
    </x-ui.table>

</div>
