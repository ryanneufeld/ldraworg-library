<x-layout.tracker>
  <x-slot:title>New Parts Tracker Submissions by Week</x-slot>
  <h3 class="ui header">New Parts Tracker Submissions by Week</h3>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Weekly Parts" />
  </x-slot> 
  <form class="ui form" name="weekly" action="{{route('tracker.weekly')}}" metohd="POST">
    <div class="ui inline field">
      <label for="order">Display Order:</label>
      <div class="ui action input">
        <select name="order">
          <option value="desc" @selected(!request()->has('order') || request()->input('order') == 'desc')>Newest First</option>
          <option value="asc" @selected(request()->has('order') && request()->input('order') == 'asc')>Oldest First</option>
        </select>
        <button class="ui button" type="submit">Go</button>
      </div>
    </div>
  </form>
  @foreach ($parts as $date => $pts)
    <h3 class="ui block header">New parts of the week of {{date("F j, Y", strtotime($date))}}</h3>
    <x-part.table :parts="$pts" />
  @endforeach  
</x-layout.tracker>