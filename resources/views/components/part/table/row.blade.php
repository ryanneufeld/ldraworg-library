@props(['part'])
<tr @class(['red'=> $part->description == 'Missing'])>
  @if($part->description == 'Missing')
    <td></td> 
  @elseif($part->isUnofficial())
    <td><img class="ui image" src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb" ></td>
  @else
    <td><img class="ui image" src="{{asset('images/library/official/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb" ></td>
  @endif
  <td>{{ $part->filename }}</td>
  <td>
  @if($part->description == 'Missing')
    &nbsp; 
  @elseif($part->isUnofficial())
    <a href="{{ route('tracker.show',$part->id) }}">
  @else
    <a href="{{ route('official.show',$part->id) }}">
  @endif
      {{ $part->description }}
    </a>
  </td>
  <td>
    @if($part->description == 'Missing')
     &nbsp;
    @elseif($part->isUnofficial())
    <a href="{{route('unofficial.download', $part->filename)}}">[DAT]</a>
    @else
    <a href="{{route('official.download', $part->filename)}}">[DAT]</a>
    @endif
  </td>
  <td>
  @if($part->description == 'Missing')
    &nbsp;
  @elseif($part->isUnofficial())
  <x-part.status :vote="$part->vote_summary" status_text="1" />
  @else
    @isset ($part->unofficial_part_id)
      <a href="{{ route('tracker.show', $part->unofficial_part_id) }}">Updated part on tracker</a>
    @endisset
  @endif    
  </td>  
</tr>
