@props(['changes'])
<div class="ui accordion">
    <div class="title">
        <i class="dropdown icon"></i>
        Header Edits
    </div>
    <div class="content">
        @foreach($changes['old'] as $field => $value)
            {{$field}}:<br> 
            <code>{!! nl2br($value) !!}</code><br>
            to<br>
            <code>{!! nl2br($changes['new'][$field]) !!}</code><br>
        @endforeach
    </div>
</div>
