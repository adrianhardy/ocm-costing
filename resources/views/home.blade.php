@extends('layouts.app')
@section('layout.breadcrumbs', Breadcrumbs::render('home') )
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Global Notes</div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-sm-8">
                    <table class="table striped">
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
                            <label>Add new comment</label>
                            {!! Form::textarea('comment', null, ['class' => 'form-control']) !!}
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
