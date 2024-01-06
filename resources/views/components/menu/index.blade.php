@props(['menuname', 'level' => 0])
@if($level == 0)
    <ul class="flex flex-row divide-x bg-white border rounded-md w-max">
@else
    <ul 
        @class([
            'flex flex-col bg-white absolute divide-y border rounded-md w-max z-10',
            'mt-2 left-0 end-0' => $level == 1,
            'left-3/4 end-0' => $level > 1])
        x-show="{{$menuname}}" 
        x-transition:enter="transition ease-out duration-100" 
        x-transition:enter-start="transform opacity-0"
    >
@endempty
  {{$slot}}
</ul>  