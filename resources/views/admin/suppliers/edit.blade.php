@extends('layouts.app')
@section('content')
    <div class="container">
        <edit-supplier
        supplier-id="{{$supplier->id}}"
        >
            <template #title>
                <h2>Edit Supplier</h2>
            </template>
        </edit-supplier>
    </div>
@endsection