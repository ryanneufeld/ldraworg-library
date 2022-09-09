
import 'ace-builds';
import 'ace-builds/webpack-resolver';
window.$ = window.jQuery = require('jquery');

require('fomantic-ui/dist/semantic');;

let editorElement = document.getElementById('ace-editor');

if(editorElement){
  let editor = ace.edit(document.getElementById('ace-editor'), {
    mode: "ace/mode/php",
    theme: "ace/theme/monokai",
  })
}

//if(filebox) {
//  fetch('/edit/files', {method: 'GET', credentials: "same-origin"})
//    .then((response) => response.json())
//    .then((json) => {
//    });
//}
