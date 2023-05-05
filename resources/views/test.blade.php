<x-layout.tracker>
@foreach($users as $user)
@if($user->parts_count > 0)
{{$user->name}} - {{$user->parts_count}}<br>
@endif
@endforeach
</x-layout.tracker>
