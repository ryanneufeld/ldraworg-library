@if (!$check['can_release'])
<div class="ui compact icon warning message">
    <i class="ui exclamation icon"></i>
    <div class="content">
        <div class="header">
            This part is not releaseable
        </div>
        <ul class="ui list">
            @foreach($check['errors'] as $error)
                <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
</div>  
@endif