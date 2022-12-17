@props(['parts', 'title', 'unofficial', 'none' => 'None'])
<div class="ui medium header">{{$title}}</div>
@if ($parts->count())
<table class="ui collapsing celled striped sortable table">
  <thead>
    <tr>
      <th>Image</th>
      <th>Part</th>
      <th>Description</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($parts as $part)
    <x-part.table.row :part="$part" unofficial="{{$unofficial}}"/>
    @endforeach  
  </tbody>
</table>
@else
{{$none}}
@endif
