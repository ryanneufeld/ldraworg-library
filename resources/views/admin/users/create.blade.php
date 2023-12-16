<x-layout.base>
  <x-slot name="title">
    Edit User
  </x-slot>
  <x-message.session-error />

<form method="POST" action="{{route('admin.users.store')}}" class="ui form">
  @csrf
  <input type="hidden" name="forum_user_id" value="{{$user->uid}}">
  <div class="field">
    <label for="realname">Real Name:</label>
    <input name="realname" type="text" value="{{$user->username}}" placeholder="Real Name">
  </div>
  <div class="field">
    <label for="name">User Name:</label>
    <input name="name" type="text" value="{{$user->loginname}}" placeholder="User Name">
  </div>
  <div class="field">
    <label for="email">Email:</label>
    <input name="email" type="text" value="{{$user->email}}" placeholder="Email">
  </div>
  <div class="field">
    <label for="roles">Roles:</label>
    <select name="roles[]" multiple="" class="ui dropdown">
    @foreach ($roles as $role)
      <option value="{{ $role }}" @selected($role == 'Part Author'))>{{ $role }}</option>
    @endforeach
    </select>
  </div>
  <x-form.select label="License:" name="part_license_id" :options="\App\Models\PartLicense::pluck('name', 'id')" selected="{{\App\Models\PartLicense::default()->id}}" />
  <button type="submit" class="ui button">Submit</button>
</form>

</x-layout.base>