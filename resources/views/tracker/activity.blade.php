<x-layout.tracker>
  <x-slot:title>Recent Activity</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Activity" />
  </x-slot>    
  <div class="ui right floated right aligned basic segment">
    Server Time: {{date('Y-m-d H:i:s')}}<br/>
    <x-part.unofficial-part-count />
  </div>

  <h2 class="ui header">Parts Tracker Activity Log</h2>
  <livewire:part-events-show />
</x-layout.tracker>