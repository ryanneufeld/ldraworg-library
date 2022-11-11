import ace from 'ace-builds/src-noconflict/ace';

//Setup static workers for ace
ace.config.setModuleUrl('ace/mode/php', new URL('ace-builds/src-noconflict/mode-php.js', import.meta.url));
ace.config.setModuleUrl('ace/mode/php_worker', new URL('ace-builds/src-noconflict/worker-php.js', import.meta.url));
ace.config.setModuleUrl('ace/theme/monokai', new URL('ace-builds/src-noconflict/theme-monokai.js', import.meta.url));

let editorElement = document.getElementById('ace-editor');

if(editorElement){
  let editor = ace.edit(document.getElementById('ace-editor'), {
    mode: "ace/mode/php",
    theme: "ace/theme/monokai",
  })
}

let saveButton = document.getElementById('editSaveButton');

if (saveButton) {
  saveButton.onclick = function (e) {
    e.preventDefault;
    let edtext = ace.edit("ace-editor").getValue();
    let file = document.getElementById('editfile').value;
    let msg = document.getElementById('message');
    let token = document.querySelector('meta[name="csrf-token"]').content;

    let formData = new FormData();

//    formData.append('_token', token);
    formData.append('text', edtext);
    formData.append('file', file);

    fetch("/fileedit/save",
    {
      headers: {
        'X-CSRF-TOKEN': $token
      },
      body: formData,
      method: 'post',
      credentials: 'same-origin'
    });
  }
}
