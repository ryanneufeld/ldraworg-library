<x-layout.main>
  <x-slot name="title">{{$unofficial ? 'Unofficial' : 'Official'}} Part List</x-slot>
  @if($unofficial)
  <div class="ui right floated right aligned basic segment">
    Server Time: {{date('Y-m-d H:i:s')}}<br/>
    <x-part.unofficial-part-count />
  </div>
  @endif
  <h2 class="ui header">{{$unofficial ? 'Unofficial' : 'Official'}} Part List</h2>
  <div class="ui hidden clearing basic divider"></div>
  <livewire:parts-show unofficial="{{$unofficial}}"/>
</x-layout.main>