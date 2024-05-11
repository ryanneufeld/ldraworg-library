<div @class([
    'p-2 border rounded-md flex items-center',
    'justify-center text-center' => $centered,
    'w-full' => !$compact,
    'w-fit' => $compact,
    'bg-red-200 text-red-800' => $type === 'error',
    'bg-yellow-200 text-yellow-800' => $type === 'warning',
    'bg-blue-200 text-blue-800' => $type === 'info',
])>
    @if($icon)
        @switch($type)
            @case('error')
                <x-fas-exclamation-circle class="w-14 h-14 text-red-800" />
                @break
            @case('warning')
                <x-fas-exclamation-triangle class="w-14 h-14 text-yellow-800" />
                @break
            @case('info')
                <x-fas-info class="w-14 h-14 text-blue-800" />
                @break
        @endswitch
    @endif
    <div class="mx-2">
        <div class="font-bold">{{$header}}</div>
        <div>{{$slot ?? $message}}</div>
    </div>
</div>
