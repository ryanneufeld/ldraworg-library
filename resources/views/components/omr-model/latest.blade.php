@forelse($models as $model)
    <a class="ui tiny horizontal card" href="{{route('omr.sets.show', $model->set)}}">
        <div class="image">
            <img src="{{$model->set->rb_url}}">
        </div>
        <div class="content">
            <div class="header">{{$model->alt_model_name ?? 'Main Model'}}</div>
            <div class="meta">
                <span class="category">{{$model->set->number}} - {{$model->set->name}}</span>
            </div>
            <div class="description">
                <p>
                    By {{$model->user->authorString()}}
                </p>
            </div>
        </div>
    </a>
@empty
    None
@endforelse       