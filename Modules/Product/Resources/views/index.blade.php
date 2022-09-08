@extends('layouts.admin_default')

@section('content')
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: {!! config('product.name') !!}
    </p>
@stop
