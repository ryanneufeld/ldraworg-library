<x-layout.base>
    <x-slot name="title">
        Create Role
    </x-slot>
    <x-message.session-error />

    <form method="POST" action="{{route('admin.roles.store')}}" class="ui form">
        @csrf
        <div class="field">
            <label for="name">Name:</label>
            <input name="name" type="text" value="{{old('name') ?? ''}}" placeholder="Name">
        </div>
        @foreach($permissions as $p)
            <div class="inline field">
                <div class="ui checkbox">
                <input type="checkbox" name="permissions[]" value="{{$p->name}}" tabindex="0" class="hidden">
                <label>{{$p->name}}</label>
                </div>
            </div>
        @endforeach
        <button type="submit" class="ui button">Submit</button>
    </form>
</x-layout.base>