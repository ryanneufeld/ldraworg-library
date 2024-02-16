<div @class([
    'flex',
    'flex-row divide-x divide-solid divide-black space-x-2' => $small,
    'flex-col space-y-2' => !$small,
])>
    <div>
        <x-fas-square class="inline w-5 fill-lime-400" />
        <span class="inline">{{$summary['1']}}{{$small ? '' : " certified files"}}</span>
    </div>
    <div>
        <x-fas-square class="ml-2 inline w-5 fill-blue-700" />
        <span class="inline">{{$summary['2']}}{{$small ? '' : " files need admin review"}}</span>
    </div>
    <div>
        <x-fas-square class="ml-2 inline w-5 fill-gray-400" />
        <span class="inline">{{$summary['3']}}{{$small ? '' : " files need more votes"}}</span>
    </div>
    <div>
        <x-fas-square class="ml-2 inline w-5 fill-red-600" />
        <span>{{$summary['5']}}{{$small ? '' : " files are held for errors"}}</span>
    </div>
</div>