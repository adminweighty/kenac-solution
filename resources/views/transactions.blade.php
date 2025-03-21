@extends('layouts.app')

@section('content')

@php
    $title = "Transactions";

@endphp

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Transactions') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <transactions></transactions>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
