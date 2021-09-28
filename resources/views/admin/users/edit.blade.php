@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-user
        user-id="{{$user->id}}"
        :current-user='{!! json_encode(['id' => \Auth::id()]) !!}'

        >
            <template #title>
                <h2>Edit User</h2>
            </template>
        </edit-user>
    </div>
@endsection