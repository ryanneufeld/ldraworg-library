@props(['vote' => null, 'status_text' => 0])
@if(!empty($vote))
<x-part.status.image />@if(!empty($statusText))<x-part.status.text />@endif<x-part.status.letter-code />
@endif
