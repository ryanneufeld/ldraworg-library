@props(['small' => false])
@foreach (\App\Helpers\PartsLibrary::unofficialStatusSummary() as $code => $count)
@if ($small)
<x-part.status.image code="{{$code}}" /> {{$count}}@if(!$loop->last) / @endif
@else
<x-part.status.image code="{{$code}}" /> {{$count}} <x-part.status.summary-text code="{{$code}}" /><br>
@endif
@endforeach