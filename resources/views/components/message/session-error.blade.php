@if ($errors->any())
    <x-message error>
        <x-slot:header>
            There were some problems with your input:
        </x-slot:header>
        <ul class="list">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-message>        
@endif
