<x-layout.main>
  <p>
    These are the parts that currently qualify for the next update. While the
    parts on this list will generally be released in the next update, some of them 
    may be manually held back by the the Library Admin for various other reasons.
  </p>  
  <x-part.table :parts="$parts" title="Unofficial Parts" />
  <x-part.table :parts="$minor_edits" title="Official Parts With Header Edits" />
</x-layout.main>