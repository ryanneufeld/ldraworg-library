<x-layout.omr>
    <x-slot:title>LDraw.org Official Model Repository - {{$set->name}}</x-slot>
    @push('css')
    <link rel="stylesheet" type="text/css" href="{{ mix('assets/css/ldbi.css') }}">
    @endpush
      <x-slot:breadcrumbs>
      <x-breadcrumb-item class="active" item="Set Detail" />
    </x-slot>    
    <h2 class="ui block header">{{$set->number}} - {{$set->name}}</h2>
    <div class="ui grid">
        <div class="eleven wide column">
            <img class="ui image" src="{{$set->rb_url}}">
{{--
            <div id="model-container">
                <canvas id="model-canvas"></canvas>
            </div>
--}}
        </div>
        <div class="five wide column">
            <h3 class="ui block header">Models</h3>
            @foreach($set->models->sortBy('alt_model') as $model)
                <table class="ui celled striped stackable table">
                    <thead>
                        <tr>
                            <th colspan="3">
                                {{$model->alt_model_name ?? 'Main Model'}}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                Author
                            </td>
                            <td colspan="2">
                                {{$model->user->authorString()}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Missing Parts
                            </td>
                            <td>
                                Missing Patterns
                            </td>
                             <td>
                                Missing Stickers
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{$model->missing_parts ? 'Yes' : 'No'}}
                            </td>
                            <td>
                                {{$model->missing_patterns ? 'Yes' : 'No'}}
                            </td>
                            <td>
                                {{$model->missing_stickers ? 'Yes' : 'No'}}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <a class="ui primary button" href="{{asset('library/omr/' . $model->filename())}}">Download</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endforeach    
        </div>
    </div>
{{--    
    @push('scripts')
        <script src="/assets/ldbi/js/three.min.js" type="text/javascript" ></script>
        <script src="/assets/js/WebGL.js" type="text/javascript"> </script>
        <script src="/assets/ldbi/js/OrbitControls.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/CopyShader.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/Pass.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/OutlinePass.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/ShaderPass.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/RenderPass.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/MaskPass.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/EffectComposer.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/colors.js" type="text/javascript" ></script>
        <script src="/assets/js/ldraworgscene.js" type="text/javascript" ></script>    
        <script src="/assets/ldbi/js/ClientStorage.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/LDROptions.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/LDRShaders.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/LDRColorMaterials.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/LDRGeometries.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/LDRBFCGeometries.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/LDRMeasurer.js" type="text/javascript" ></script>
        <script src="/assets/ldbi/js/LDRLoader.js" type="text/javascript"></script>
        <script src="/assets/ldbi/js/LDRGenerator.js" type="text/javascript"></script>
        <script src="/assets/ldbi/js/LDRStuds.js" type="text/javascript"></script>
        <script src="/assets/ldbi/js/VertexNormalsHelper.js"></script>
        <script type="text/javascript">
var part_paths;

let canvas = document.getElementById('model-canvas');
var scene;

LDR.Options.bgColor = 0xFFFFFF;

LDR.Colors.envMapPrefix = '/assets/ldbi/textures/cube/';    
LDR.Colors.textureMaterialPrefix = '/assets/ldbi/textures/materials/';

var idToUrl = function(id) {
  if (part_paths[id]) {
    return [part_paths[id]];
  }
  else {
    return [id];
  }
};

var idToTextureUrl = function(id) {
  if (part_paths[id]) {
    return part_paths[id];
  }
  else {
    return id;
  }
};

$( function() {
    return false;
  partID ='';
  if ( !WEBGL.isWebGLAvailable() ) { return; }
  if (!scene) {
    // pre-fetch the paths to the subfiles used to speed up loading
    var posting = $.get( '')
      .done(function( response ) {
        part_paths = response;
        scene = new LDrawOrg.Model(canvas, partID, {idToUrl: idToUrl, idToTextureUrl: idToTextureUrl});
        window.addEventListener('resize', () => scene.onChange(), false);
    });
  }

});
        </script>
    @endpush
--}}    
</x-layout.omr>