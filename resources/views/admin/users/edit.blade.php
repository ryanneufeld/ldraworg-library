<x-layout.base>
  <x-slot name="title">
    Edit User
  </x-slot>
  <x-message.session-error />


<form method="POST" action="{{route('admin.users.update', $user)}}" class="ui form">
  @csrf
  @method('PATCH')
  <div class="field">
    <label for="realname">Real Name:</label>
    <input name="realname" type="text" value="{{$user->realname}}" placeholder="Real Name">
  </div>
  <div class="field">
    <label for="name">User Name:</label>
    <input name="name" type="text" value="{{$user->name}}" placeholder="User Name">
  </div>
  <div class="field">
    <label for="email">Email:</label>
    <input name="email" type="text" value="{{$user->email}}" placeholder="Email">
  </div>
  <div class="field">
    <label for="roles">Roles:</label>
    <select name="roles[]" multiple="" class="ui dropdown">
    @foreach ($roles as $role)
      <option value="{{$role}}" @selected($user->hasRole($role))>{{$role}}</option>
    @endforeach
    </select>
  </div>
  <x-form.select label="License:" name="part_license_id" :options="\App\Models\PartLicense::pluck('name', 'id')" selected="{{$user->part_license_id ?? ''}}" />
  <button type="submit" class="ui button">Submit</button>
</form>

</x-layout.base>