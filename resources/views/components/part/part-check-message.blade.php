@if ($show)
<div class="ui compact icon warning message">
    <i class="ui exclamation icon"></i>
    <div class="content">
        <div class="header">
            This part is not releaseable
        </div>
        <ul class="ui list">
            @if($part->hasUncertifiedSubfiles())
                <li>Uncertified subfiles</li>
            @elseif(!$part->hasCertifiedParent() && $part->vote_sort == 1 && $part->type->folder != "parts/" && !is_null($part->official_part_id))
                <li>No certified parents</li>
            @endif    
            @foreach($errors as $error)
                <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
</div>  
@endif