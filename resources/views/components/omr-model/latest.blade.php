@forelse($models as $model)
    <x-card
        title="{{$model->set->number}} - {{$model->set->name}}"
        link="{{route('omr.sets.show', $model->set)}}"
        image="{{$model->set->rb_url}}"
    >
        <div class="text-sm text-grey-500">
            {{$model->alt_model_name ?? 'Main Model'}}
        </div>
        <p>
            By {{$model->user->author_string}}
        </p>
    </x-card>
@empty
    None
@endforelse       