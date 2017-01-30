@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="login-panel panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Central Authentication Service</h3>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-warning">
                            @lang('message.cas_redirect_warn', ['url' => $service])
                        </div>
                        <div>
                            <button class="btn btn-danger pull-left" id="btn_abort">@lang('common.abort')</button>
                            <a class="btn btn-primary pull-right" id="btn_ok" href="{{ $url }}">@lang('common.ok')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $('#btn_abort').click(function () {
            location.href = 'about:blank';
        });
    </script>
@endsection