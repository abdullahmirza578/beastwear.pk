@extends('admin.layouts.layout')

@section('title', 'Add Product | BeastWear.pk Admin')
@section('page', 'product-create')

@section('content')
    @include('admin.products._form')
@endsection
