<x-layout.edit>
  <form class="ui form" action="{{route('fileedit')}}" method="get">
  @csrf
  <div class="inline fields">
    <div class="field">
      <div class="ui action input">
        <input id="editfile" name="editfile" type="text" placeholder="File">
        <button class="ui button" >Load</button>
      </div>
    </div>
    <div class="field">
      <button id="editSaveButton" class="ui button" type="button">Save</button>
    </div>
  </div>
  </form>
  <div class="ace-wrapper">
    <div id="ace-editor">{{$filetext}}</div>
  </div>
</x-layout.edit>
