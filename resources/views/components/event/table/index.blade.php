@props(['events'])
<table class="ui striped celled table">
  <thead>
    <tr>
      <th class="one wide">Event</th>
      <th>User</th>
      <th>Date/Time</th>
      <th>Image</th>
      <th>Part</th>
      <th>Description</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach($events as $event)
      <x-event.table.item :event="$event" />
    @endforeach
  </tbody>
</table>
