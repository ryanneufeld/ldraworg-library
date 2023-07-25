<div class="grouped fields">
    <label>Select destination folder:</label>
    @foreach($options as $option)
        <div class="field">
            <div @class(['ui radio checkbox', 'checked' => $option['folder'] == $value])>
                <input type="radio" name="part_type_id" value="{{$option['id']}}" tabindex="0" @checked($option['folder'] == $value) class="hidden">
                <label>{{$option['text']}}</label>
            </div>
        </div>
    @endforeach    
</div>