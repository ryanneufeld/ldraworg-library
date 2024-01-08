@props([
    'icon',
    'size',
    'class' => 'w-5',
    'top_left' => null, 
    'top_right' => null,
    'bottom_left' => null,
    'bottom_right' => null,
])
<div {{$attributes->merge(['class' =>"relative {$class}"])}}>
        <x-dynamic-component :component="$icon" />
        @isset($top_left)
            <x-dynamic-component :component="$top_left" class='absolute w-1/2 top-0 left-0 z-10 fill-blue-500' />
        @endif    
        @isset($top_right)
            <x-dynamic-component :component="$top_right" class='absolute w-1/2 top-0 right-0 z-10 text-blue-500' />
        @endif    
        @isset($bottom_left)
            <x-dynamic-component :component="$bottom_left" class='absolute w-1/2 bottom-0 left-0 z-10 text-blue-500' />
        @endif    
        @isset($bottom_right)
            <x-dynamic-component :component="$bottom_right" class='absolute w-1/2 bottom-0 right-0 z-10 text-blue-500' />
        @endif    
</div>

