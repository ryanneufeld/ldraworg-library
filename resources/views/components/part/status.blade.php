@props(['part', 'showStatus' => false])
<div>
    <x-fas-square @class([
        'inline w-5',
        'fill-lime-400' => $part->vote_sort == 1,
        'fill-blue-700' => $part->vote_sort == 2,
        'fill-gray-400' => $part->vote_sort == 3,
        'fill-red-600' => $part->vote_sort == 5,

    ])/> {{$showStatus ? $part->statusText() : ''}} {{$part->statusCode()}}
</div>