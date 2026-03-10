<section class="w-full bg-white py-16">
    <div class="max-w-5xl mx-auto px-4 md:px-8">
        <div id="videoContainer"
            class="relative w-full aspect-video overflow-hidden rounded-3xl bg-gray-900 shadow-[0_32px_64px_-15px_rgba(0,0,0,0.3)] group cursor-pointer">

            <div id="thumbnail" class="absolute inset-0 w-full h-full transition-all duration-700 z-10">
                <img src="{{ asset('assets/video/video-thumbnail.png') }}" alt="Bionic Showcase"
                    class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105 opacity-80 filter brightness-90 grayscale-[20%] group-hover:grayscale-0">

                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-black/20"></div>
            </div>

            <div class="yt-mask"></div>
            <div id="player" class="absolute inset-0 w-full h-full"></div>

            <div id="buttonWrapper" class="absolute inset-0 z-20 flex items-center justify-center pointer-events-none">
                <button id="playButton"
                    class="pointer-events-auto relative flex items-center justify-center w-24 h-24 md:w-32 md:h-32 rounded-full bg-white/10 backdrop-blur-xl border border-white/40 text-white transition-all duration-500 hover:bg-primary hover:border-primary hover:scale-110 shadow-2xl group/btn">

                    <span
                        class="absolute inset-0 rounded-full border border-white/50 animate-[ping_3s_linear_infinite] opacity-40"></span>
                    <span
                        class="absolute inset-[-10px] rounded-full border border-white/20 animate-[ping_3s_linear_infinite_1s] opacity-20"></span>

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="w-12 h-12 md:w-16 md:h-16 ml-1.5 transition-transform duration-500 group-hover/btn:translate-x-1">
                        <path fill-rule="evenodd"
                            d="M4.5 5.653c0-1.427 1.529-2.33 2.779-1.643l11.54 6.347c1.295.712 1.295 2.573 0 3.286L7.28 19.99c-1.25.687-2.779-.217-2.779-1.643V5.653Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <div
                class="absolute bottom-8 left-8 md:bottom-12 md:left-12 z-20 text-white pointer-events-none transition-all duration-500 group-hover:translate-x-2">
                <span
                    class="inline-block px-3 py-1 mb-3 text-[10px] uppercase tracking-[0.2em] bg-white/20 backdrop-blur-md rounded-full border border-white/10">Brand
                    Film</span>
                <h3 class="font-heading text-xl md:text-3xl font-bold tracking-tight drop-shadow-2xl">
                    Experience the Bionic Garden
                </h3>
                <p class="text-sm md:text-base opacity-70 font-sans mt-1">100% Organic & Naturally Sourced</p>
            </div>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        const VIDEO_ID = '543FzXHt8es';
        let player;
        let apiLoaded = false;

        // 1. Performance: Only load YT API when user is near the section
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && !apiLoaded) {
                const tag = document.createElement('script');
                tag.src = "https://www.youtube.com/iframe_api";
                document.head.appendChild(tag);
                apiLoaded = true;
            }
        }, {
            threshold: 0.1
        });

        observer.observe(document.getElementById('videoContainer'));

        // 2. Initialize Player
        window.onYouTubeIframeAPIReady = function() {
            player = new YT.Player('player', {
                videoId: VIDEO_ID,
                playerVars: {
                    autoplay: 0,
                    controls: 1,
                    rel: 0,
                    modestbranding: 1,
                    iv_load_policy: 3,
                    disablekb: 1,
                    showinfo: 0,
                    fs: 0,
                    origin: window.location.origin
                },
                events: {
                    'onStateChange': onPlayerStateChange
                }
            });
        };

        // 3. Handle UI Transitions
        function onPlayerStateChange(event) {
            const thumbnail = document.getElementById('thumbnail');
            const buttonWrapper = document.getElementById('buttonWrapper');

            if (event.data === YT.PlayerState.PLAYING) {
                thumbnail.style.opacity = '0';
                thumbnail.style.pointerEvents = 'none';
                buttonWrapper.style.opacity = '0';
                buttonWrapper.style.pointerEvents = 'none';
            }
        }

        // 4. Play Trigger with Fallback
        document.getElementById('playButton').addEventListener('click', function() {
            if (player && typeof player.playVideo === 'function') {
                player.playVideo();
            } else {
                // Smooth feedback if API is still loading
                this.innerHTML =
                    `<svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;
            }
        });
    </script>
@endpush
