@props(['part', 'showobsolete' => false])
<div @class(['ui',
'obsolete' => stripos($part->description, "obsolete") !== false && !$showobsolete,      
'official' => !$part->isUnofficial() && (stripos($part->description, "obsolete") === false || $showobsolete), 
'unofficial' => $part->isUnofficial() && (stripos($part->description, "obsolete") === false || $showobsolete), 
'pattern center aligned segment'])>
@if(stripos($part->description, "obsolete") !== false && !$showobsolete)
Obsolete file<br/><br/>
{{basename($part->filename, '.dat')}}
@elseif($part->isUnofficial())
<a class="ui image" href="{{route('tracker.show', $part)}}">
  <img src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '.png')}}" title="{{$part->description}}" alt="{{$part->description}}" />
</a><br />
<a href="{{route('tracker.show', $part)}}">{{basename($part->filename, '.dat')}}</a><br/>
<x-part.status :vote="$part->vote_summary" status_text="0" />
@else
<a class="ui image" href="{{route('official.show', $part)}}">
  <img src="{{asset('images/library/official/' . substr($part->filename, 0, -4) . '.png')}}" title="{{$part->description}}" alt="{{$part->description}}" />
</a><br />
<a href="{{route('official.show', $part)}}">{{basename($part->filename, '.dat')}}</a>
@endif  
</div>
