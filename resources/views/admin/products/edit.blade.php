@extends('admin.layouts.layout')

@section('title', 'Edit Product | BeastWear.pk Admin')
@section('page', 'product-edit')

@section('content')
    @include('admin.products._form')
@endsection
