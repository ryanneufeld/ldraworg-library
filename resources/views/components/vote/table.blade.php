@props(['votes'])
@if ($votes->count() ?? false)
    <table class="ui collapsing compact celled striped small table">
        <thead>
            <tr>
                <th>User</th>
                <th>Vote</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($votes as $vote)
            <tr>
                <td>{{ $vote->user->name }}</td>
                <td @class([
                    'green' => $vote->vote_type_code == 'C',
                    'red' => $vote->vote_type_code == 'H',
                    'olive' => $vote->vote_type_code == 'A' || $vote->vote_type_code == 'T',
                    ])>{{ $vote->type->name }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
None
@endif
