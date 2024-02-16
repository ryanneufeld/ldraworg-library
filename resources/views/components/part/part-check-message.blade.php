@if (!$check['can_release'])
<x-message compact type="warning">
    <x-slot:header>
        This part is not releaseable
    </x-slot:header>
    <ul class="ui list">
        @foreach($check['errors'] as $error)
            <li>{{$error}}</li>
        @endforeach
    </ul>
</x-message>        
@endif