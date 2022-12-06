@props(['part', 'statusText' => false])
<x-part.status.image />@isset($statusText)<x-part.status.text />@endisset<x-part.status.letter-code />