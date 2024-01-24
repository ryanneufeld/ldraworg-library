@props(['type', 'header', 'message' => ''])
<div
    x-data="{ toast : false }" 
    x-init="$nextTick( () => {toast = true; setTimeout(() => { toast = false; }, 5000)})"
    class="relative w-full"
>
    <button
        type="button"
        @@click="toast = false" 
        x-show="toast"
        x-cloak 
        x-transition.duration.300ms 
        class="absolute right-4 top-4 z-50 transition"
    >
        <x-message type="{{$type}}" header="{{$header}}" message="{{$message}}" compact />
    </button>
</div>