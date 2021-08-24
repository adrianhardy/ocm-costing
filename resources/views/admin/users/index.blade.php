@extends('layouts.app')
@section('content')
<div class="container">
    <users-list>
        <template #title>
            <h2>Users</h2>
        </template>
    </users-list>
</div>
@endsection