<x-layout.main>
  <form class="ui form" action="{{route('admin.users.create')}}" method="GET">
    <div class="field">
      <label>Add Forum User:</label>
      <div class="ui action input">
        <input type="text" name="forum_user_id" placeholder="Forum User ID">
        <button class="ui button" type="submit">Go</button>
      </div>
    </div> 
  </form>
  <table class="ui sortable table">
    <thead>
      <tr>
        <th>Real Name</th>
        <th>User Name</th>
        <th>Roles</th>
        <th>License</th>
        <th>Parts<th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach($users as $user)
      <tr>
        <td>{{$user->realname}}</td>
        <td>{{$user->name}}</td>
        <td>{{implode(', ', $user->getRoleNames()->all())}}</td>
        <td>{{$user->license->name}}</td>
        <td>{{\App\Models\Part::where(function ($q) use ($user) { $q->whereRelation('history', 'user_id', $user->id)->orWhere('user_id', $user->id);})->count()}}</td>
        <td><a class="ui button" href="{{route('admin.users.edit', $user)}}">Edit</a></td>
      </tr>
    @endforeach
    </tbody>
  </table>
</x-layout.main>