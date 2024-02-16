<x-card 
    title="Parts Update {{$update->name}}"
    link="{{route('part-update.index', ['latest'])}}"
    image="{{asset('/images/cards/updates.png')}}"
>
{{$blurb}}
</x-card>