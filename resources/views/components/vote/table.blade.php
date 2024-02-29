@props(['votes'])
@if ($votes->count() ?? false)
    <table class="w-fit rounded border">
        <thead class="border-b-2 border-b-black">
            <tr class="*:bg-gray-200 *:p-2">
                <th>User</th>
                <th>Vote</th>
            </tr>
        </thead>
        <tbody class="divide-y">
        @foreach ($votes as $vote)
            <tr wire:key="{{$vote->user_id}}-{{$vote->vote_type_code}}">
                <td class="p-2">{{ $vote->user->name }}</td>
                <td @class([
                    'p-2',
                    'bg-green-200' => $vote->vote_type_code == 'C',
                    'bg-red-200' => $vote->vote_type_code == 'H',
                    'bg-lime-200' => $vote->vote_type_code == 'A' || $vote->vote_type_code == 'T',
                    ])>{{ $vote->type->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
<p>
    None
</p>
@endif
