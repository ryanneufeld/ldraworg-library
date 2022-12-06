@foreach (\App\Models\Part::where('unofficial', true)->get()->pluck('vote_sort')->countBy()->sortKeys()->all() as $code => $count)
@if ($small)
<x-part.status.image code="{{$code}}" /> {{$count}}@if(!$loop->last) / @endif
@else
<x-part.status.image code="{{$code}}" /> {{$count}} <x-part.status.summary-text code="{{$code}}" /><br>
@endif
@endforeach

