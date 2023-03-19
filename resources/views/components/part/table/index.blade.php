@props(['parts', 'title' => '', 'hsize' => 'medium', 'none' => 'None'])
<div class="ui {{$hsize}} header">{{$title}}</div>
@if ($parts->count())
<table class="ui celled table">
  <thead>
    <tr>
      <th class="one wide">Image</th>
      <th class="three wide">Part</th>
      <th class="nine wide">Description</th>
      <th class="one wide">DAT</th>
      <th class="two wide">Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($parts as $part)
    <x-part.table.row :part="$part" />
    @endforeach  
  </tbody>
</table>
@else
{{$none}}
@endif
