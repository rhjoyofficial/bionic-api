<footer class="bg-primary text-white pt-16 pb-8 rounded-t-[40px] md:rounded-t-[80px] font-sans">
    <div class="max-w-8xl mx-auto px-4 md:px-8">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">

            <div class="space-y-6">
                <img src="{{ asset('assets/logo-white.png') }}" alt="Bionic Logo" class="h-12 w-auto">
                <p class="text-gray-200 text-sm leading-relaxed max-w-xs">
                    Shop the freshest organic produce and sustainable lifestyle goods from local sources.
                </p>
                <div>
                    <h4 class="font-bold mb-4">Follow Us</h4>
                    <div class="flex gap-3">
                        @foreach (['facebook', 'pinterest', 'youtube', 'tiktok'] as $social)
                            <a href="#"
                                class="w-10 h-10 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                                <span class="sr-only">{{ $social }}</span>
                                <div class="w-5 h-5 bg-white/80 rounded-sm"></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <h4 class="font-heading font-bold text-lg mb-6">Quick Links</h4>
                <ul class="space-y-4 text-gray-200 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors">Home</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">About</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Contact</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-heading font-bold text-lg mb-6">Our Services</h4>
                <ul class="space-y-4 text-gray-200 text-sm">
                    <li class="flex items-center gap-3">
                        <span class="text-yellow-500">🚚</span> Free Shipping
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="text-yellow-500">💬</span> Organic Products
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="text-yellow-500">📞</span> 24/7 Support
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="text-yellow-500">🔄</span> Money-back Guarantee
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-heading font-bold text-lg mb-6 text-white">Contact Us</h4>
                <ul class="space-y-4 text-gray-200 text-sm">
                    <li class="flex gap-3">
                        <span class="text-yellow-500 shrink-0">📍</span>
                        <span>65, Feroza Garden, Shahid Smriti Sarak, Barguna-8700</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="text-yellow-500 shrink-0">📞</span>
                        <span>+8801733358158</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="text-yellow-500 shrink-0">📧</span>
                        <span>care@bionic.garden</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-sm text-gray-300">
                2024 Bidrush. All rights reserved
            </p>

            <div class="flex items-center gap-8 text-sm font-medium">
                <a href="#" class="hover:text-yellow-500 transition-colors">Terms</a>
                <a href="#" class="hover:text-yellow-500 transition-colors">Privacy Policy</a>
                <a href="#" class="hover:text-yellow-500 transition-colors">Legal Disclaimer</a>
            </div>

            <div class="flex gap-4">
                @foreach (['fb', 'ig', 'tw', 'yt'] as $bottomSocial)
                    <a href="#"
                        class="w-8 h-8 flex items-center justify-center rounded-full bg-white text-primary hover:bg-yellow-500 transition-colors">
                        <div class="w-4 h-4 bg-primary rounded-full"></div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</footer>
