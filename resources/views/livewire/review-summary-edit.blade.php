<div>
    <form class="ui form" id="manual" wire:submit="processManualEntry">
        <div class="ui field">
            <label>Parts List</label>
            <div class="ui blue segment">
                Each line should be the folder and part number of an unofficial part<br>
                Only unofficial parts should be listed, official parts will be automatically removed<br>
                Use "/" for a dividing line, optional text may be added for a heading.
            </div>
            <textarea rows="30" wire:model="manualEntry">{{$summary->toString()}}</textarea>
        </div>    
        <div class="ui field">
            <button form="manual" class="ui button">Edit</button>
        </div>
    </form>
</div>
