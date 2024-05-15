<div>
    <x-fas-square @class([
        'inline w-5',
        'fill-lime-400' => $getRecord()->vote_type_code == 'A' || $getRecord()->vote_type_code == 'T',
        'fill-green-500' => $getRecord()->vote_type_code == 'C',
        'fill-red-600' => $getRecord()->vote_type_code == 'H',

    ]) />
    <span>{{$getRecord()->type->name}}</span>
</div>