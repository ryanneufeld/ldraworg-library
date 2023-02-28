@props(['parts', 'title' => '', 'hsize' => 'medium', 'none' => 'None'])
<div class="ui {{$hsize}} header">{{$title}}</div>
@if ($parts->count())
<table class="ui collapsing celled striped sortable table">
  <thead>
    <tr>
      <th>Image</th>
      <th>Part</th>
      <th>Description</th>
      <th>DAT</th>
      <th>Status</th>
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
