@extends('layouts.app')

@section('content')
    <div class="container">
        <edit-user
        :is-creating="true"
        :current-user='{!! json_encode(['id' => \Auth::id()]) !!}'
        >
            <template #title>
                <h4>Create New User</h4>
            </template>
        </edit-user>
    </div>
@endsection
