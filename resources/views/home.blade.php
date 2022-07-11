@extends('layouts.app')
@section('layout.breadcrumbs', Breadcrumbs::render('home') )
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Sales Order Progress Dashboard</div>
        <div class="card-body">
            <table class="table">
                <tr class="text-center">
                    <th class="w-15 ">
                        Draft<br />
                    </th>
                    <th class="w-15">
                        Quote<br />
                    </th>
                    <th class="w-15">
                        Sales Order<br />
                    </th>
                    <th class="w-15">
                        Confirmed<br />
                    </th>
                    <th class="w-15">
                        On Water<br />
                    </th>
                    <th class="w-15">
                        Recently Docked<br />
                    </th>
                    <th></th>
                </tr>
                <tr>
                    <td>
                        @foreach ($orders['draft'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['quote'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['sales_order'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['confirmed_sales_order'] as $order)
                            <a class="btn btn-primary btn-block mb-1" href="{{ route('sales-orders.edit', $order) }}">{{ $order->reference }}</a>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['receiving'] as $order)
                            <div class="dropdown">
                            <button class="btn btn-primary btn-block mb-1 dropdown-toggle" data-toggle="dropdown">
                                <span class="badge badge-light">{{ $order->consignments->count() }}</span>
                                {{ $order->reference }}
                            </button>
                            <div class="dropdown-menu">
                                @foreach ($order->consignments as $consignment)
                                <a class="dropdown-item" href="{{ route('shipments.show', $consignment) }}">
                                    {{ $consignment->vessel_name }}<br />
                                    <small class="text-muted">
                                        @if ($consignment->docked_at)
                                            Docked: {{ $consignment->docked_at->format('d/m/Y') }}
                                        @else
                                            Not yet docked
                                        @endif
                                    </small>
                                </a>
                                @endforeach
                            </div>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @foreach ($orders['recently_docked'] as $order)
                            <div class="dropdown">
                                <button class="btn btn-primary btn-block mb-1 dropdown-toggle" data-toggle="dropdown">
                                    <span class="badge badge-light">{{ $order->consignments->count() }}</span>
                                    {{ $order->reference }}
                                </button>
                                <div class="dropdown-menu">
                                    @foreach ($order->consignments as $consignment)
                                        <a class="dropdown-item" href="{{ route('shipments.show', $consignment) }}">
                                            {{ $consignment->vessel_name }}<br />
                                            <small class="text-muted">
                                                @if ($consignment->docked_at)
                                                    Docked: {{ $consignment->docked_at->format('d/m/Y') }}
                                                @else
                                                    Not yet docked
                                                @endif
                                            </small>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Global Notes</div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-sm-8">
                    <table class="table table-striped table-sm">
                        <tr>
                            <th>Author</th>
                            <th>Comment</th>
                        </tr>
                        @forelse ($comments as $comment)
                            <tr>
                                <td class="w-15 text-nowrap">
                                    {{ $comment->commentator->name }}<br />
                                    <small>{{ $comment->created_at->format('d/M/Y H:i') }}</small>
                                </td>
                                <td>
                                    {{ $comment->comment }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">No comments</td>
                            </tr>
                        @endforelse
                    </table>
                    {!! $comments->links() !!}
                </div>
                <div class="col-12 col-sm-4">
                    {!! Form::open(['url' => route('global-notes.store')]) !!}
                        <div class="form-group mt-1">
                            {!! Form::textarea('comment', null, ['class' => 'form-control', 'rows' => 4, 'placeholder' => 'Add a new comment']) !!}
                        </div>
                        <div>
                            <button type="submit" class="btn btn-block btn-primary btn-lg">Save</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
