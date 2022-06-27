@extends('layouts.app')
@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-sm-6">
                <settings-list></settings-list>
            </div>
            <div class="col-12 col-sm-6">
                <h4>Please Note</h4>
                <b-card>
                    <p>
                        Changing the following settings will have an immediate, knock-on
                        effect on <strong>all</strong> current estimated landed costs:
                    </p>
                    <ul>
                        <li>Shipping Cost Per Cubic Foot</li>
                        <li>Default Commission Rate</li>
                        <li>Default Import Duty</li>
                    </ul>
                </b-card>
            </div>
        </div>
        <amazon-guidance-upload></amazon-guidance-upload>
    </div>
@endsection
