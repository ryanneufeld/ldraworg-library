<x-layout.main>
  <h3 class="ui header">LDraw.org Library Search</h3>
@if ($errors->any())
  <div class="ui error message">
    <ul class="ui list">
    @foreach($errors->all() as $errorfield)
      <li>{{$errorfield}}</li>
    @endforeach
    </ul>    
  </div>
@endif
  <p>
  Enter separate words to search for files containing all the words (e.g. <em>blue shirt</em> will find all files containing <em>blue</em> and <em>shirt</em>).<br/>
  Surround a phrase with double-quotes to search for that phrase (e.g. <em>"blue shirt"</em> will find all files containing <em>blue shirt</em>).<br/>
  Quoted and unquoted search words may be combined (e.g. <em>"blue shirt" jacket</em> will find files containing <em>blue shirt</em> and <em>jacket</em>).
  </p>
  <form class="ui form" name="search" action="{{route('tracker.search')}}" method="get">
    <div class="field">
      <label>Search terms:</label>
      <div class="ui action input">
        <input type="text" name="s" value="{{request()->input('s') ?? ''}}" tabindex="1" size="20" maxlength="50">
        <button class="ui button" type="submit">Go</button>
      </div>
    </div> 
    <div class="four wide field">
      <label>Search Scope:</label>
      <select name="scope" >
        <option value="filename" @selected(request()->input('scope') == 'filename')>Filename only</option>
        <option value="description" @selected(request()->input('scope') == 'description')>Filename and description</option>
        <option value="header" @selected(!request()->has('scope') || request()->input('scope') == 'header')>Filename and file header</option>
        <option value="file" @selected(request()->input('scope') == 'file')>Filename and entire file</option>
      </select>
    </div>  
  </form>
  @isset($results)
      <x-part.table title="Matched {{$results['uparts']->count()}} Unofficial Parts" unofficial=1 :parts="$results['uparts']" none=""/>
      <x-part.table title="Matched {{$results['oparts']->count()}} Official Parts" unofficial=0 :parts="$results['oparts']" none=""/>
  @endisset  
</x-layout.main>