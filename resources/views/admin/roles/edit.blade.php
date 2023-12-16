<x-layout.base>
    <x-slot name="title">
        Edit Role
    </x-slot>
    <x-message.session-error />


    <form method="POST" action="{{route('admin.roles.update', $role)}}" class="ui form">
        @csrf
        @method('PATCH')
        <div class="field">
           <label for="name">Name:</label>
           <input name="name" type="text" value="{{$role->name}}" placeholder="Name">
        </div>
        @foreach($permissions as $p)
            <div class="inline field">
                <div class="ui checkbox">
                    <input type="checkbox" name="permissions[]" value="{{$p->name}}" tabindex="0" class="hidden" @checked($role->hasPermissionTo($p->name))>
                    <label>{{$p->name}}</label>
                </div>
            </div>
        @endforeach
        <button type="submit" class="ui button">Submit</button>
    </form>
</x-layout.base>