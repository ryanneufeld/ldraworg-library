@aware(['part'])
@switch($part->vote_sort)
@case (0)
<img class="status-image" src="{{asset('images/tracker/brtgrn1x1.gif')}}">
@break
@case (1)
<img class="status-image" src="{{asset('images/tracker/blue1x1.gif')}}">
@break
@case (2)
<img class="status-image" src="{{asset('images/tracker/gray1x1.gif')}}">
@break
@case (3)
<img class="status-image" src="{{asset('images/tracker/yellow1x1.gif')}}">
@break
@case (4)
<img class="status-image" src="{{asset('images/tracker/red1x1.gif')}}">
@break
@endswitch