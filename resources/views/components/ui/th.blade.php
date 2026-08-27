@props(['sort' => null, 'align' => 'left'])

@if($sort)
    <th class="text-{{ $align }}">
        <button wire:click="sortBy('{{ $sort }}')" class="inline-flex items-center gap-1.5 hover:text-primary transition-colors group">
            {{ $slot }}
            <x-icon :name="$sortIcon ?? 'chevron-down'" class="w-3 h-3 opacity-40 group-hover:opacity-100 transition-opacity" />
        </button>
    </th>
@else
    <th class="text-{{ $align }}">{{ $slot }}</th>
@endif
