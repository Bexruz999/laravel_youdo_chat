@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form id="register_form" method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button id="register_btn" type="submit" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Register
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('special-js')
        <script>
            $("#register_btn").click(function(){
                Messenger.button().addLoader({id : '#register_btn'});
                $("#register_form").submit();
            });
        </script>
    @endpush


@endsection
