@extends('layouts.app')
@section('content')
    <div class="container">
        <h2>Export Templates</h2>
        <div class="row">
            <div class="col-12 col-sm-9">
                <table class="table table-striped">
                    <tr>
                        <th>Filename</th><th>Uploaded</th><th class="w-5"></th>
                    </tr>
                    @if ($templates->count() < 1)
                        <tr>
                            <td colspan="3">No templates found</td>
                        </tr>
                    @endif
                    @foreach ($templates as $template)
                    <tr>
                        <td>{{ $template->getBasename() }}</td>
                        <td>{{ \Carbon\Carbon::parse($template->getCTime())->format('d/m/y H:i') }}</td>
                        <td>
                            <confirm-navigation-modal
                                    delete-url="{{ route('settings.export-templates.destroy', ['template' => $template->getBasename()]) }}"
                                    confirm-label="Confirm"
                                    title="Delete Template File"
                                    message="Please confirm that you want to delete this template file. This cannot be undone"
                            >
                                <template slot-scope="{confirm}">
                                    <b-button size="sm" variant="outline-danger" @click="confirm"><b-icon-x></b-icon-x></b-button>
                                </template>
                            </confirm-navigation-modal>
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
            <div class="col-12 col-sm-3">
                <div class="alert alert-info">
                    When you export a sales order, you will be asked to choose a template.
                    You can add to that list of templates here.
                </div>
                {!! Form::open(['files' => true, 'url' => route('settings.export-templates.store')]) !!}
                <div class="form-group">
                <div class="custom-file">
                    <label class="custom-file-label" for="template">Upload new template</label>
                    {!! Form::file('template', ['class' => 'custom-file-input']) !!}
                </div>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Upload</button>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection
