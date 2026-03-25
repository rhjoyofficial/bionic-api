@extends('layouts.app')

@section('title', '{{ $productSlug }}')

@section('content')
    <div id="product-app" data-slug="{{ $productSlug }}" data-product-id="{{ $productId }}">
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", async () => {

            const root = document.getElementById('product-app')
            const slug = root.dataset.slug

            showLoader()

            try {

                const res = await fetch(`/api/v1/products/${slug}`)
                const json = await res.json()

                renderProduct(json.data)

                loadRecommendations(root.dataset.productId)

            } catch (e) {

                showFlash("Product load failed", "error")

            }

            hideLoader()

        })
    </script>
@endpush
