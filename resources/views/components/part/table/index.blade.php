@props(['parts', 'title' => '', 'hsize' => 'medium', 'none' => 'None', 'missing' => []])
<div class="ui {{$hsize}} header">{{$title}}</div>
@if ($parts->count() || !empty($missing))
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
    @foreach ($missing as $m)
      <tr class="red"><td></td><td>{{$m}}</td><td>Missing</td><td></td><td></td></tr>
    @endforeach
  </tbody>
</table>
@else
{{$none}}
@endif
