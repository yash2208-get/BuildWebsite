@props(['rows' => null, 'cols' => 5, 'empty' => 'No records found', 'emptyIcon' => 'inbox', 'emptyDescription' => null])

<div class="card overflow-hidden">
    <div wire:loading.delay.longer class="p-6"><x-ui.skeleton variant="list" :rows="6" /></div>

    <div wire:loading.remove.delay.longer>
        @if($rows !== null && $rows->isEmpty())
            <x-ui.empty-state :icon="$emptyIcon" :title="$empty" :description="$emptyDescription">
                {{ $emptyActions ?? '' }}
            </x-ui.empty-state>
        @else
            <div class="table-wrap">
                <table class="tbl">
                    <thead><tr>{{ $head }}</tr></thead>
                    <tbody>{{ $body }}</tbody>
                </table>
            </div>
        @endif
    </div>

    @if($rows !== null && $rows->hasPages())
        <div class="px-5 py-3.5 border-t border-subtle">{{ $rows->links() }}</div>
    @endif
</div>
