@props(['id' => ''])
<div class="ui fullscreen ldbi modal">
  <i class="close icon"></i>
  <div class="header">
    3D View
  </div>
  <div class="content">
    <div class="ui icon buttons">
      <button class="ui button default-mode" title="Normal mode"><i class="undo icon"></i></button>
      <button class="ui button harlequin" title="Harlequin (random color) mode"><i class="paint brush icon"></i></button>
      <button class="ui button bfc" title="BFC mode"><i class="green leaf icon"></i></button>
      <button class="ui toggle button stud-logos" title="Toggle stud logos"><i class="dot circle icon"></i></button>
      <button class="ui toggle button origin" title="Toggle origin"><i class="arrows alternate icon"></i></button>
      <button class="ui toggle button physical" title="Toggle photo render"><i class="eye icon"></i></button>
    </div>
    <div id="model-container"><canvas id="model-canvas"></canvas></div>
  </div> 
  <div class="actions">
    <div class="ui cancel button">Done</div>
  </div>
</div>
<script>var part_id = {{$id}}</script>