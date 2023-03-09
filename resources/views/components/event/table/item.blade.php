@props(['event'])
<tr @class(['green' => !$event->part->isUnofficial()])>
  <td class='center aligned'>
    <x-event.icon :event="$event" type="table"/>
  </td>
  <td>{{$event->user->name ?? ''}}</td>
  <td>{{$event->created_at}}</td>
  <td>
    @if($event->part->isUnofficial())
    <img class="ui image" src="{{asset('images/library/unofficial/' . substr($event->part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
    @else
    <img class="ui image" src="{{asset('images/library/official/' . substr($event->part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
    @endif
  </td>      
  <td>{{$event->part->filename}}</td>
  <td><a href="{{ $event->part->isUnofficial() ? route('tracker.show',$event->part->id) : route('official.show',$event->part->id)}}">{{$event->part->description}}</a></td>
  <td>
    @if($event->part->isUnofficial())
    <x-part.status :vote="$event->part->vote_summary" />
    @else
    {{$event->release->name}} Release
    @endif
  </td>
</tr>