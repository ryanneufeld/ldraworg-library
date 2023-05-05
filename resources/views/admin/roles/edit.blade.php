<x-layout.base>
  <x-slot name="title">
    Edit Role
  </x-slot>
@if (count($errors) > 0)
  <div class="ui icon negative message">
    <i class="exclamation icon"></i>
    <div class="header">There were some problems with your input:</div>
    <ul class="list">
      @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>  
@endif


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