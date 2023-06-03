@props(['event'])
<tr @class(['green' => isset($event->part) && !$event->part->isUnofficial()])>
  <td class='center aligned'>
    <x-event.icon :event="$event" type="table"/>
  </td>
  <td>{{$event->user->name ?? ''}}</td>
  <td>{{$event->created_at}}</td>
  <td>
    @isset($event->part)
      @if($event->part->isUnofficial())
      <img class="ui image" src="{{asset('images/library/unofficial/' . substr($event->part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
      @else
      <img class="ui image" src="{{asset('images/library/official/' . substr($event->part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
      @endif
    @endisset  
  </td>      
  <td>
    @empty($event->part)
    {{$event->deleted_filename}}
    @else
    {{$event->part->filename}}
    @endempty
  </td>
  <td>
    @empty($event->part)
    {{$event->deleted_description}}
    @else
    <a href="{{ $event->part->isUnofficial() ? route('tracker.show',$event->part->id) : route('official.show',$event->part->id)}}">{{$event->part->description}}</a>
    @endempty
    </td>
  <td>
    @isset($event->part)
      @if($event->part->isUnofficial())
      <x-part.status :part="$event->part"/>
      @else
      {{$event->release->name}} Release
      @endif
    @else
      Removed  
    @endisset  
  </td>
</tr>