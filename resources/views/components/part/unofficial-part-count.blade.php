<div>
  @if ($small)
  <img class="status-image" src="{{asset('images/tracker/brtgrn1x1.gif')}}"> {{$summary['1']}} /
  <img class="status-image" src="{{asset('images/tracker/blue1x1.gif')}}"> {{$summary['2']}} /
  <img class="status-image" src="{{asset('images/tracker/gray1x1.gif')}}"> {{$summary['3']}} /
  <img class="status-image" src="{{asset('images/tracker/yellow1x1.gif')}}"> {{$summary['4']}} /
  <img class="status-image" src="{{asset('images/tracker/red1x1.gif')}}"> {{$summary['5']}}
  @else
  <img class="status-image" src="{{asset('images/tracker/brtgrn1x1.gif')}}"> {{$summary['1']}} certified files<br>
  <img class="status-image" src="{{asset('images/tracker/blue1x1.gif')}}"> {{$summary['2']}} files need admin review<br>
  <img class="status-image" src="{{asset('images/tracker/gray1x1.gif')}}"> {{$summary['3']}} files need more votes<br>
  <img class="status-image" src="{{asset('images/tracker/yellow1x1.gif')}}"> {{$summary['4']}} have uncertified subfiles<br>
  <img class="status-image" src="{{asset('images/tracker/red1x1.gif')}}"> {{$summary['5']}} files are held for errors
  @endif
</div>