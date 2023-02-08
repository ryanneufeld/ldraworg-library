<x-layout.main>
  <x-slot name="title">Unofficial Part List</x-slot>
  <form class="ui form" action="" method="GET">
    <div class="four fields">
      <div class="field">
        <label>Status</label>        
        <x-form.select name="subset" placeholder="Status" :options="$subset" selected="{{request()->input('subset')}}" class="ui clearable dropdown"/>
      </div>
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
  <x-part.table title="Unofficial Part List" unofficial=1 :parts="$parts" />
</x-layout.main>