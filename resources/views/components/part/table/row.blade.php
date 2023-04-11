@props(['part'])
<tr>
  <td class="center aligned">
    @if($part->isUnofficial())
    <img class="ui centered image" src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
    @else
    <img class="ui centered image" src="{{asset('images/library/official/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
    @endif
  </td>
  <td>{{ $part->filename }}</td>
  <td>
    @if($part->isUnofficial())
    <a href="{{ route('tracker.show',$part->id) }}">{{ $part->description }}</a>
    @else
    <a href="{{ route('official.show',$part->id) }}">{{ $part->description }}</a>
    @endif
  </td>
  <td class="center aligned">
    @if($part->isUnofficial())
    <a href="{{route('unofficial.download', $part->filename)}}">[DAT]</a>
    @else
    <a href="{{route('official.download', $part->filename)}}">[DAT]</a>
    @endif
  </td>
  <td>
    @if($part->isUnofficial())
    <x-part.status :vote="$part->vote_summary" status_text="1" />
    @else
      @isset ($part->unofficial_part_id)
        <a href="{{ route('tracker.show', $part->unofficial_part_id) }}">Updated part on tracker</a>
      @endisset
    @endif    
  </td>  
</tr>
