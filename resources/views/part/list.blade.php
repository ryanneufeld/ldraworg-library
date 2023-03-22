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
      <x-form.select name="part_type_id" class="clearable" label="Type" placeholder="Type" :options="$part_types" selected="{{request()->input('part_type_id')}}" />
      <x-form.select name="user_id" class="clearable search " label="Author" placeholder="Author" :options="$users" selected="{{request()->input('user_id')}}"  />
      @if($unofficial)
      <x-form.select name="subset" class="clearable" label="Status" placeholder="Status" :options="$subset" selected="{{request()->input('subset')}}" />
      @endif
      <div class="field">
        <label>&nbsp;</label>
        <button class="ui button">Go</button>
      </div>
    </div>
  </form>
  <x-part.table title="{{$unofficial ? 'Unofficial' : 'Official'}} Part List" :parts="$parts" />
</x-layout.main>