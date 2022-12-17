@aware(['vote'])
@if ($vote['H'] != 0)
<img class="status-image" src="{{asset('images/tracker/red1x1.gif')}}">
@elseif ($vote['S'] != 0)
<img class="status-image" src="{{asset('images/tracker/yellow1x1.gif')}}">
@elseif ((($vote['A'] > 0) && (($vote['C'] + $vote['A']) >= 2)) || ($vote['T'] > 0))
<img class="status-image" src="{{asset('images/tracker/brtgrn1x1.gif')}}">
@elseif (($vote['C'] + $vote['A']) >= 2)
<img class="status-image" src="{{asset('images/tracker/blue1x1.gif')}}">
@else
<img class="status-image" src="{{asset('images/tracker/gray1x1.gif')}}">
@endif
