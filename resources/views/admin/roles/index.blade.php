<x-layout.base>
  <x-slot name="title">
    Role List
  </x-slot>
  <form class="ui form" action="{{route('admin.roles.create')}}" method="GET">
    <div class="field">
      <button class="ui button" type="submit">Create Role</button>
    </div> 
  </form>
  <table class="ui celled table">
    <thead>
      <tr>
        <th>Name</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($roles as $role)
      <tr>
        <td>{{$role->name}}</td>
        <td><a class="ui button" href="{{route('admin.roles.edit', $role)}}">Edit</a>
      </tr>
    @endforeach
    </tbody>
  </table>
</x-layout.base>