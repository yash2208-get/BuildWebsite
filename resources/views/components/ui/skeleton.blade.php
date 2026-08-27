@props(['rows' => 3, 'variant' => 'list'])
@if($variant === 'cards')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @for($i = 0; $i < $rows; $i++)
            <div class="card p-5 space-y-3">
                <div class="skeleton h-32 w-full"></div>
                <div class="skeleton h-4 w-2/3"></div>
                <div class="skeleton h-3 w-1/2"></div>
            </div>
        @endfor
    </div>
@elseif($variant === 'stats')
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @for($i = 0; $i < $rows; $i++)
            <div class="card p-5 space-y-3">
                <div class="skeleton h-3 w-20"></div>
                <div class="skeleton h-7 w-24"></div>
            </div>
        @endfor
    </div>
@else
    <div class="space-y-3">
        @for($i = 0; $i < $rows; $i++)
            <div class="card p-4 flex items-center gap-4">
                <div class="skeleton w-10 h-10 rounded-xl shrink-0"></div>
                <div class="flex-1 space-y-2">
                    <div class="skeleton h-3.5 w-1/3"></div>
                    <div class="skeleton h-3 w-1/2"></div>
                </div>
            </div>
        @endfor
    </div>
@endif
