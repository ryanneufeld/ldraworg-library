<x-layout.main>
<x-slot name="title">Part Review Form</x-slot>

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

<form class="ui form" method="post" ACTION="{{$vote != null ? route('tracker.vote.update', $vote->id) : route('tracker.vote.store', $part->id)}}" name="reviewform">
@csrf
@if($vote != null)
@method('PUT')
@endif
  <div class="inline field">
    <label>Reviewer</label>
    <div class="ui transparent input">
      <input type="text" value="{{ Auth::user()->name }}" readonly>
    </div>
    <a href="http://www.ldraw.org/library/tracker/ref/reviewinfo">
	  <i class="circular help small blue inverted link icon"></i>
	  Help!  What is this page?
	</a>  
  </div>
  <div class="inline field">
    <label for="filename">File</label>
    <div class="ui transparent input">
      <input type="text" name="part_filename" value="{{ $part->filename }}" readonly>
    </div>
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
        <input type="radio" name="vote_type" tabindex="1" value="{{$vt->code}}" @if ($vote != null && $vote->vote_type_code == $vt->code) checked="checked" @endif>
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
  <button class="ui button" type="submit" tabindex="20">Submit</button>
</form>
</x-layout.main>
