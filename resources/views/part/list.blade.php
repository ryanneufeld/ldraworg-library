<x-layout.tracker>
  <x-slot:title>{{$unofficial ? 'Unofficial' : 'Official'}} Part List</x-slot>
  <x-slot name="title">{{$unofficial ? 'Unofficial' : 'Official'}} Part List</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="{{$unofficial ? 'Unofficial' : 'Official'}} Part List" />
  </x-slot>    
  @if($unofficial)
  <div class="ui right floated right aligned basic segment">
    Server Time: {{date('Y-m-d H:i:s')}}<br/>
    <x-part.unofficial-part-count />
  </div>
  @endif
  <h2 class="ui header">{{$unofficial ? 'Unofficial' : 'Official'}} Part List</h2>
  <div class="ui hidden clearing basic divider"></div>
  <livewire:part.part-list unofficial="{{$unofficial}}"/>
</x-layout.tracker>