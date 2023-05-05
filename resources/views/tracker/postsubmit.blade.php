<x-layout.tracker>
  <x-slot:title>Parts Tracker File Submit Form</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Submit" />
  </x-slot>    
  <h4 class="ui header'">The following files passed validation checks and have been submitted to the Parts Tracker</h4>
  Note: part image creation is a queued process and may lag the display of this table.<br>
  <x-part.table :parts="$parts" unofficial=1 />
  <a href="{{route('tracker.submit')}}">Submit more parts</a>
</x-layout.tracker>
