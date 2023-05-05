<x-layout.tracker>
<x-slot name="title">Part Review Form</x-slot>
<x-slot:breadcrumbs>
  <x-breadcrumb-item class="active" item="Vote" />
</x-slot>    

@if ($errors->any())
<div class="ui error message">
  <div class="header">
    Your vote was not submitted due to these errors:
  </div>
  <ul class="list">
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif
<h3 class="ui header">Part {{ basename($vote->part->filename ?? $part->filename) }} Voting Form</h3>
<form class="ui form" method="post" ACTION="{{$vote != null ? route('tracker.vote.update', $vote->id) : route('tracker.vote.store', $part->id)}}" name="reviewform">
@csrf
@if($vote != null)
@method('PUT')
@endif
  <div class="ui horizontally fitted basic segment">
    <div>
      <strong>File:</strong> {{ $vote->part->filename ?? $part->filename }}
    </div>
    @canany('part.vote.certify', 'part.vote.hold', 'part.vote.admincertify', 'part.vote.fastrack')
    <div><strong>Your Current Review:</strong> <span class="{{$vote->type->short ?? 'none'}}-text">{{ $vote->type->name ?? 'None' }}</span></div>
    @endcan
  </div>
  <div class="grouped fields">
    <label for="vote_type">Vote</label>
    @if((Auth::user()->id == $part->user_id && Auth::user()->can('part.own.comment')) || (Auth::user()->can('part.comment')))
    <div class="field">
      <div class="ui radio checkbox">
        <input type="radio" name="vote_type" tabindex="1" value="M">
        <label>Comment.  Comment on this part without voting or changing your vote.</label>
      </div>
    </div>
    @endif
    @if($vote != null)
    <div class="field">
      <div class="ui radio checkbox">
        <input type="radio" name="vote_type" tabindex="1" value="N">
        <label>Cancel Vote.  This will clear your vote on this part.</label>
      </div>
    </div>
    @endif
    @foreach (\App\Models\VoteType::all() as $vt)
    @if((Auth::user()->id == $part->user_id && Auth::user()->can('part.own.vote.' . $vt->short)) || (Auth::user()->can('part.vote.' . $vt->short)))
    <div class="field">
      <div class="ui radio checkbox">
        <input type="radio" name="vote_type" tabindex="1" value="{{$vt->code}}" @checked($vote != null && $vote->vote_type_code == $vt->code)>
        <label>{{$vt->phrase}}</label>
      </div>
    </div>
    @endif
    @endforeach
  </div>
  <div class="field">
    <label for="comment">Comments</label>
    <textarea name="comment" rows="4" tabindex="19"></textarea>
  </div>
  <button class="ui button" type="submit" tabindex="20">Submit</button><button class="ui button" onclick="window.history.go(-1); return false;" tabindex="21">Back</button>
</form>
</x-layout.tracker>
