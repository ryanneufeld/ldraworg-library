<x-layout.omr>
    <x-slot:title>
        LDraw.org Official Model Repository
    </x-slot>
    <p>
        Welcome to the LDraw OMR, the 'Official Model Repository'. Here you can find LDraw files of official models released by LEGO.
    </p>
    <div class="ui grid">
        <div class="ten wide column">
            <h4 class="ui top attached block header">What is this?</h4>
            <div class="ui bottom attached segment">
                <p>
                    The model repository is a database of files in the 'LDraw File Format' describing models that are released as sets by LEGO. 
                    It also includes a specification of how the files should be named and structured (the OMR specification). If you want your 
                    models to be submitted to this website, you first need to make sure your files are according the OMR spec. You can read 
                    the specification here. And here is a tutorial on how to make your LDraw files OMR compliant.
                </p>
            </div>    
            <h4 class="ui top attached block header">How to use this website?</h4>
            <div class="ui bottom attached segment">
                <p>
                    This website is made purely for organizing and accessing all the OMR files. This site is not meant to be used as a database 
                    for LEGO sets, there are a lot of better websites for that. If you're looking for an LDraw file for a specific set, 
                    you can enter the set number at the top search bar. If you just want to go through all available files and filter 
                    through them, you can go to the all files page.            
                </p>
            </div>    
            <h4 class="ui top attached block header">How can I submit files?</h4>
            <div class="ui bottom attached segment">
                <p>
                    If you have LDraw files of official LEGO sets and you've made them OMR compliant, you can submit them at the LDraw forum. 
                    At the moment we don't have an onsite submit form.
                </p>
            </div>    
        </div>
        <div class="six wide column">
            <h4 class="ui block header">Latest Models</h4>
            <x-omr-model.latest />
            {{-- Place holder recent files --}}
        </div>
    </div>
</x-layout.omr>
  