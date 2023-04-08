<x-layout.main>
  <x-slot name="title">
    Edit User
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


<form method="POST" action="{{route('admin.users.store')}}" class="ui form">
  @csrf
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

</x-layout.main>