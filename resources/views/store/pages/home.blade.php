@extends('layouts.app')

@section('title', 'Healthy Foods for a Better Life')

@section('content')

    @include('store.partials.hero', ['heroCertifications' => $certifications->flatten()])
    @include('store.partials.trending-products', ['products' => $trendingProducts])
    @include('store.partials.ad-promotions')
    @include('store.partials.product-categories', [
        'categories' => $categories,
        'categoryProducts' => $categoryProducts,
    ])
    @include('store.partials.combo-products', ['combos' => $combos])
    @include('store.partials.certifications', ['certifications' => $certifications])
    @include('store.partials.video-promotion')
    @include('store.partials.testimonial-showcase')

@endsection
