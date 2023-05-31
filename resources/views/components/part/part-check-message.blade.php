@if ($show)
<div class="ui compact icon warning message">
    <i class="ui exclamation icon"></i>
    <div class="content">
        <div class="header">
            This part is not releaseable
        </div>
        <ul class="ui list">
            @if($part->vote_summary['S'] != 0)
                <li>Uncertified subfiles</li>
            @elseif(!$part->releasable())
                <li>No certified parents</li>
            @endif    
            @foreach($errors as $error)
                <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
</div>  
@endif