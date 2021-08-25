@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-supplier
        :is-factory="true"
        >
            <template #title>
                <h2>Create New Factory</h2>
            </template>
        </edit-supplier>
    </div>
@endsection