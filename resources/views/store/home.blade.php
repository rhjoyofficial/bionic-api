@extends('layouts.app')

@section('title', 'Healthy Foods for a Better Life')

@section('content')

    @include('store.partials.hero')
    @include('store.partials.trending-products')
    @include('store.partials.ad-promotions')
    @include('store.partials.product-categories')
    @include('store.partials.combo-products')
    @include('store.partials.video-promotion')
    @include('store.partials.testimonial-showcase')

@endsection
