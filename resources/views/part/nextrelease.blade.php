<x-layout.tracker>
  <x-slot:title>Parts In Next Release</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Next Release" />
  </x-slot>    
  <p>
    These are the parts that currently qualify for the next update. While the
    parts on this list will generally be released in the next update, some of them 
    may be manually held back by the the Library Admin for various other reasons.
  </p>  
  <x-part.table :parts="$parts" title="Unofficial Parts" />
</x-layout.tracker>