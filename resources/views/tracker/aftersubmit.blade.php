<x-layout.main>
  @foreach($files as $file)
    <div class="ui message">
      <div class="header">{{$file['filename']}}</div>
      <div class="content">
<code><pre>{{$file['text']}}</pre></code>
      </div>
    </div>
  @endforeach    
</x-layout.main>
