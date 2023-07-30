<x-layout.tracker>
    <x-slot:title>Part Header Edit</x-slot>
    <x-slot:breadcrumbs>
      <x-breadcrumb-item class="active" item="Part File Comparision" />
    </x-slot>    
  <h3 class="ui header">Part File Comparision</h3>
  <form class="ui form" action="">
    <x-form.select class="clearable search" name="oldpart" id="oldpart" label="Original Part" placeholder="Original Part" :options="$parts" />
    <x-form.select class="clearable search" name="newpart" id="newpart" label="Newer Part" placeholder="Newer Part" :options="$parts" />
    <button class="ui button">Download</button>
  </form>
</x-layout.tracker>  