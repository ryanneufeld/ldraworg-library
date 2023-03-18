<x-layout.main>
<h3 class="ui header">{{$part->name()}} Header Edit</h3>
<p> Fields in blue are shown for information only and are not editable.</p>
@if ($errors->any())
  <div class="ui error message">
    <ul class="ui list">
    @foreach($errors->all() as $errorfield)
      <li>{{$errorfield}}</li>
    @endforeach
    </ul>    
  </div>
@endif
<form class="ui form" name="headeredit" action="{{route('tracker.doeditheader', $part->id)}}" method="POST">
  @method('PUT')
  @csrf
  <div class="field">
    <label for="description">Description</label>
    <input type="text" name="description" value="{{old('description') ?? $part->description}}">
  </div>
  <div class="field info">
    <label for="name">0 Name:</label>
    <input class="text" name="name" readonly value="{{$part->name()}}">
  </div>
  <div class="field info">
    <label for="user">0 Author:</label>
    <input type="text" name="user" readonly value="{{$part->user->authorString()}}">
  </div>
  <div class="fields">
    <div class="ten wide field info">
      <label for="type">0 !LDRAW_ORG</label>
      <input type="text" name="type" readonly value="{{$part->type->type}}">
    </div>  
    <div class="six wide field">
      <label for="qual_type">&nbsp;</label>
      <x-form.select class="ui clearable dropdown" name="part_type_qualifier_id" :options="\App\Models\PartTypeQualifier::pluck('type', 'id')"  placeholder="Qualifier" selected="{{old('part_type_qualifier_id') ?? $part->part_type_qualifier_id}}" />
    </div>  
  </div>
  <div class="field info">
    <label for="license">0 !LICENSE</label>
    <input type="text" name="license" readonly value="{{$part->license->text}}">
  </div>
  <div class="field">
    <label for="help">0 !HELP (Note: Do not include 0 !HELP; each line will be a separate help line)</label>
    <textarea>{{old('help') ?? $part->help()->orderBy('order')->get()->implode('text', "\n")}}</textarea>
  </div>
  <div class="field info">
    <label for="bfc">0 BFC CERTIFY</label>
    <input type="text" name="bfc" readonly value="{{$part->bfc ?? ''}}">
  </div>
  <div class="field">
    <label for="part_category_id">0 !CATEGORY (Note: A !CATEGORY meta will be added only if this differs from the first word in the description)</label>
    <x-form.select class="ui clearable dropdown" name="part_category_id" :options="\App\Models\PartCategory::pluck('category', 'id')"  placeholder="Category" selected="{{old('part_category_id') ?? $part->part_category_id}}" />
  </div>
  <div class="field">
    <label for="keywords">0 !KEYWORDS (Note: Do not include 0 !KEYWORDS; the number of keyword lines and keyword order will not be preserved)</label>
    <textarea name="keywords">{{old('keywords') ?? $part->keywords()->orderBy('keyword')->get()->implode('keyword', ", ")}}</textarea>
  </div>
  <div class="field">
    <label for="cmdline">0 !CMDLINE</label>
    <input type="text" name="cmdline" value="{{old('cmdline') ?? $part->cmdline}}">
  </div>
  <div class="field">
    <label for="history">0 !HISTORY (Note: Must include 0 !HISTORY; ALL changes to existing history must be documented with a comment)</label>
<textarea name="history">
@if(old('history') !== null)
{{old('history')}}
@else
@foreach($part->history()->oldest()->get() as $hist)
{{$hist->toString()}}
@endforeach
@endisset
</textarea>
  </div>
  <div class="field">
    <label for="editcomment">Comment on the edit</label>
    <textarea name="editcomment">{{old('editcomment')}}</textarea>
  </div>
<button class="ui button" type="submit" tabindex="20">Submit</button>
</form>
</x-layout.main>