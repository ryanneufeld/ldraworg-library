<x-layout.main>
  <x-slot name="title">Recent Activity</x-slot>
  <div class="ui right floated right aligned basic segment">
    Server Time: {{date('Y-m-d H:i:s')}}<br/>
    <x-part.unofficial-part-count />
  </div>

  <h3 class="ui header">Parts Tracker Activity Log</h3>
  <livewire:part-events-show />
</x-layout.main>