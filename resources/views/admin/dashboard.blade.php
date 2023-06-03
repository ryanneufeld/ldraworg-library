<x-layout.base>
    <x-slot name="title">Admin Dashboard</x-slot>
    <h3 class="ui header">Admin Dashboard</h3>
    <div class="ui compact menu">
        <a class="item" href="{{route('admin.dashboard')}}">Admin Dashboard</a> 
        <a class="item" href="{{route('admin.users.index')}}">Add/Edit Users</a> 
        <a class="item" href="{{route('admin.review-summaries.index')}}">Add/Edit Review Summaries</a> 
        <a class="item" href="{{route('admin.roles.index')}}">Add/Edit Roles</a> 
    </div>

    <div class="ui top attached tabular dashboardmenu menu">
      <a class="item active" data-tab="delete-flagged">Parts Flagged for Deletion</a>
      <a class="item" data-tab="manual-hold">Parts Administrativly Held</a>
    </div>
    <div class="ui bottom attached tab segment active" data-tab="delete-flagged">
      <x-part.table :parts="$delete_flags" />
    </div>
    <div class="ui bottom attached tab segment" data-tab="manual-hold">
        <x-part.table :parts="$manual_hold_flags" />
    </div>
  </x-layout.base>