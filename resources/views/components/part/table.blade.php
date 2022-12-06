<div class="ui medium header">{{$title}}</div>
@if ($parts->count())
<table class="ui collapsing compact celled striped small table">
  <thead>
    <tr>
      <th>Part</th>
      <th>Description</th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($parts as $part)
    <tr>
      <td>{{ $part->filename }}</td>
      <td>
      @if($unofficial)
        <a href="{{ route('tracker.show',$part->id) }}">
      @else
        <a href="{{ route('official.show',$part->id) }}">
      @endif
          {{ $part->description }}
        </a>
      </td>
      <td>
      @if($unofficial)
      <x-part.status :part="$part" type="full" />
      @else
        @isset ($part->unofficial_part_id)
          <a href="{{ route('tracker.show', $part->unofficial_part_id) }}">Updated part on tracker</a>
        @endisset
      @endif    
      </td>  
    </tr>
    @endforeach  
  </tbody>
</table>
@else
None
@endif
