@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-supplier
            :factory-id="{{$factory->id}}"
            :is-factory="true"

        >
            <template #title>
                <h2>Edit Factory</h2>
            </template>
        </edit-supplier>
    </div>
@endsection
