@props([
    'error' => false, 
    'warning' => false, 
    'header' => null, 
    'message' => null,
    'centered' => false,
    'compact' => false,
])
<div {{$attributes->class([
    'ui icon',
    'center aligned' => $centered !== false,
    'compact' => $compact !== false,
    'error' => $error !== false,
    'warning' => $warning !== false && !$error,
    'info' => !$warning && !$error,
    'message'
])}}>
    <i @class([
        'exclamation circle' => $error !== false,
        'exclamation triangle' => $warning !== false && !$error,
        'info' => !$warning && !$error,
        'icon'
       ])></i>
    <div class="content">
        @isset($header)
            <div class="header">{{$header}}</div>
        @endisset
        {{ $slot ?? $message}}    
    </div>
</div>
