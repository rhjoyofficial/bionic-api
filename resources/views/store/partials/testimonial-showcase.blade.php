<section class="py-12 px-4 md:px-8 bg-white overflow-hidden">
    <div class="max-w-8xl mx-auto">

        <div class="flex items-center justify-between gap-6 mb-8 pb-4">
            <div class="max-w-2xl md:shrink-0">
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-brand mb-4">
                    What Our Community Says
                </h2>
                <p class="text-gray-600 font-sans">
                    Real stories from real customers who have experienced the Bionic difference.
                </p>
            </div>
            <span class="h-0.5 w-full bg-gray-200 hidden md:block"></span>
            <div class="flex gap-2 md:shrink-0">
                <button
                    class="testi-prev p-2 rounded-md border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button
                    class="testi-next p-2 rounded-md border border-gray-200 hover:bg-gray-50 transition-colors cursor-pointer focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-primary" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="swiper testimonial-swiper overflow-visible">
            <div class="swiper-wrapper items-stretch pb-3">

                @foreach ([1, 2, 3, 4, 5] as $i)
                    <div class="swiper-slide flex h-auto">

                        <div
                            class="flex flex-col w-full h-full bg-gray-50 rounded-3xl p-6 transition-all duration-300 hover:shadow-xl border border-transparent hover:border-primary/10">

                            {{-- MEDIA AREA --}}
                            <div class="media-container mb-6">

                                {{-- VIDEO --}}
                                @if ($i == 2)
                                    <div class="relative media-frame group" data-video>

                                        <video class="w-full h-full object-cover rounded-xl" preload="metadata">
                                            <source src="{{ asset('assets/video/video-file.mp4') }}" type="video/mp4">
                                        </video>

                                        <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/10 transition cursor-pointer"
                                            data-video-toggle>

                                            <div
                                                class="w-14 h-14 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white border border-white/30 shadow-lg group-hover:scale-110 transition">

                                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                                                    viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="m7 3 14 9-14 9z" />
                                                </svg>

                                            </div>

                                        </div>

                                        <input type="range" data-video-progress value="0" step="0.1"
                                            class="absolute bottom-0 left-0 w-full h-1 bg-white/20 accent-primary cursor-pointer opacity-0 group-hover:opacity-100 transition">

                                    </div>

                                    {{-- IMAGE --}}
                                @elseif ($i == 1)
                                    <div class="media-frame image-preview-trigger cursor-zoom-in"
                                        data-image="{{ asset('assets/review/review-3.jpeg') }}">

                                        <img src="{{ asset('assets/review/review-3.jpeg') }}"
                                            loading="lazy"
                                            class="w-full h-full object-contain" alt="Customer chat review">

                                    </div>

                                    {{-- TEXT REVIEW --}}
                                @else
                                    <div class="flex text-amber-400 text-xs mb-4">★★★★★</div>

                                    <p class="text-gray-700 font-sans italic text-sm md:text-base leading-relaxed">
                                        "The quality of the Mariyam dates is exceptional.
                                        I've never had anything so fresh and naturally sweet.
                                        Highly recommended for Ramadan!"
                                    </p>
                                @endif

                            </div>


                            {{-- USER --}}
                            <div class="flex items-center gap-4 pt-4 border-t border-gray-200/50 mt-auto">

                                <div
                                    class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center font-bold text-primary uppercase">
                                    {{ substr('Ariful Islam', 0, 1) }}
                                </div>

                                <div>
                                    <h4 class="font-heading font-bold text-gray-900 text-sm">
                                        Ariful Islam
                                    </h4>
                                    <p class="text-xs text-gray-500">Verified Buyer</p>
                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>
        </div>
    </div>
</section>


{{-- IMAGE MODAL --}}
<div id="image-preview-modal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50 p-6">

    <img id="preview-image" class="max-h-[90vh] max-w-[90vw] rounded-xl shadow-xl">

</div>



@push('scripts')
    <script>
        new Swiper('.testimonial-swiper', {

            slidesPerView: 1.2,
            spaceBetween: 20,
            loop: true,

            navigation: {
                nextEl: '.testi-next',
                prevEl: '.testi-prev',
            },

            breakpoints: {
                640: {
                    slidesPerView: 2.2
                },
                1024: {
                    slidesPerView: 3.2
                },
                1280: {
                    slidesPerView: 4
                },
            }

        });


        const certModal = document.getElementById('certModal');
        const certModalImg = document.getElementById('certModalImg');
        const imageModal = document.getElementById('image-preview-modal');
        const previewImage = document.getElementById('preview-image');

        // Certificate cards modal
        document.querySelectorAll('.cert-card').forEach(card => {
            card.onclick = () => {
                certModalImg.src = card.dataset.img;
                certModal.classList.remove('opacity-0', 'pointer-events-none');
                setTimeout(() => certModalImg.classList.remove('scale-95'), 10);
            };
        });

        certModal.onclick = () => {
            certModal.classList.add('opacity-0', 'pointer-events-none');
            certModalImg.classList.add('scale-95');
        };

        // Image preview modal
        document.querySelectorAll('.image-preview-trigger').forEach(el => {
            el.addEventListener('click', () => {
                previewImage.src = el.dataset.image;
                imageModal.classList.remove('hidden');
                imageModal.classList.add('flex');
            });
        });

        imageModal.addEventListener('click', () => {
            imageModal.classList.add('hidden');
            imageModal.classList.remove('flex');
        });

        // Escape key for both modals
        document.addEventListener('keydown', e => {
            if (e.key === "Escape") {
                certModal.click();
                if (!imageModal.classList.contains('hidden')) {
                    imageModal.click();
                }
            }
        });
    </script>
@endpush
