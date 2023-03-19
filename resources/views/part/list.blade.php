<x-layout.main>
  <x-slot name="title">{{$unofficial ? 'Unofficial' : 'Official'}} Part List</x-slot>
  @if($unofficial)
  <div class="ui right floated right aligned basic segment">
    Server Time: {{date('Y-m-d H:i:s')}}<br/>
    <x-part.unofficial-part-count />
  </div>
  @endif
  <form class="ui form" action="" method="GET">
    <div class="equal width fields">
      @if($unofficial)
      <div class="field">
        <label>Status</label>        
        <x-form.select name="subset" placeholder="Status" :options="$subset" selected="{{request()->input('subset')}}" class="ui clearable dropdown"/>
      </div>
      @endif
      <div class="field">
        <label>Type</label>        
        <x-form.select name="part_type_id" placeholder="Type" :options="$part_types" selected="{{request()->input('part_type_id')}}" class="ui clearable dropdown"/>
      </div>
      <div class="field">
        <label>Author</label>        
        <x-form.select name="user_id" placeholder="Author" :options="$users" selected="{{request()->input('user_id')}}" class="ui clearable search dropdown" />
      </div>
      <div class="field">
        <label>&nbsp;</label>
        <button class="ui button">Go</button>
      </div>
    </div>
  </form>
  <x-part.table title="{{$unofficial ? 'Unofficial' : 'Official'}} Part List" :parts="$parts" />
</x-layout.main>