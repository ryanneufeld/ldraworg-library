@props(['part', 'unofficial'])
<tr>
  @if($unofficial)
    <td><img class="ui image" src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb" ></td>
  @else
    <td><img class="ui image" src="{{asset('images/library/official/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb" ></td>
  @endif
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
  <x-part.status :vote="unserialize($part->vote_summary)" status_text="1" />
  @else
    @isset ($part->unofficial_part_id)
      <a href="{{ route('tracker.show', $part->unofficial_part_id) }}">Updated part on tracker</a>
    @endisset
  @endif    
  </td>  
</tr>
