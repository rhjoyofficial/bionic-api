<section class="w-full bg-white py-16">
    <div class="max-w-5xl mx-auto px-4 md:px-8">

        {{--
            VideoManager auto-init: add data-video to the container,
            then configure with data-video-* attributes.

            Supported types (data-video-type):
              youtube  → data-video-src is the YouTube video ID
              vimeo    → data-video-src is the Vimeo video ID
              html5    → data-video-src is a direct video file URL
              iframe   → data-video-src is any embed URL
        --}}
        <div data-video data-video-type="youtube" data-video-src="543FzXHt8es"
            data-video-thumbnail="{{ asset('assets/video/video-thumbnail.jpg') }}" data-video-badge="Brand Film"
            data-video-title="Experience the Bionic Garden" data-video-subtitle="100% Organic &amp; Naturally Sourced"
            data-video-lazy="true" style="border-radius:1.5rem;">
        </div>

    </div>
</section>

{{-- No @push('scripts') needed — VideoManager is bundled inside app.js via Vite --}}
