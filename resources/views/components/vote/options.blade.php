@foreach (\App\Models\VoteType::all() as $vt)
@if((Auth::user()->id == $part->user_id && Auth::user()->can('part.own.vote.' . $vt->short)) || (Auth::user()->can('part.vote.' . $vt->short)))
<div class="field">
  <div class="ui radio checkbox">
    <input type="radio" name="vote_type" tabindex="1" value="{{$vt->code}}" @if (isset($vote) && $vote->vote_type_code == $vt->code) selected @endif>
    <label>{{$vt->phrase}}</label>
  </div>
</div>
@endif
@endforeach
