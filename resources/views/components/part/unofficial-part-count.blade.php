<div @class([
    'flex',
    'flex-row space-x-4 place-content-center' => $small,
    'flex-col space-y-2' => !$small,
    'w-fit'
])>
    <div @class([
        'flex',
        'flex-col place-items-center' => $small,
        'flex-row space-x-2 items-center justify-items-start' => !$small
    ])>
        <x-fas-square class="w-5 fill-lime-400" />
        <div>{{$summary['1']}}{{$small ? '' : " certified files"}}</div>
    </div>
    <div @class([
        'flex',
        'flex-col place-items-center' => $small,
        'flex-row space-x-2 items-center justify-items-start' => !$small
    ])>
        <x-fas-square class="w-5 fill-blue-700" />
        <div>{{$summary['2']}}{{$small ? '' : " files need admin review"}}</div>
    </div>
    <div @class([
        'flex',
        'flex-col place-items-center' => $small,
        'flex-row space-x-2 items-center justify-items-start' => !$small
    ])>
        <x-fas-square class="w-5 fill-gray-400" />
        <div>{{$summary['3']}}{{$small ? '' : " files need more votes"}}</div>
    </div>
    <div @class([
        'flex',
        'flex-col place-items-center' => $small,
        'flex-row space-x-2 items-center justify-items-start' => !$small
    ])>
        <x-fas-square class="w-5 fill-red-600" />
        <div>{{$summary['5']}}{{$small ? '' : " files are held for errors"}}</div>
    </div>
</div>