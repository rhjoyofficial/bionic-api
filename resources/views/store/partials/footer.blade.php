<footer class="bg-primary text-white pt-16 pb-8 rounded-t-[40px] md:rounded-t-[80px] font-sans">
    <div class="max-w-8xl mx-auto px-4 md:px-8">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">

            <div class="space-y-6">
                <img src="{{ asset('assets/images/bionic-white-logo.png') }}" alt="Bionic Logo"
                    class="w-20 md:w-28 object-contain h-auto">
                <p class="text-gray-200 text-sm leading-relaxed max-w-xs">
                    Shop the freshest organic produce and sustainable lifestyle goods from local sources.
                </p>
                <div>
                    <h4 class="font-bold mb-4">Follow Us</h4>
                    <div class="flex gap-3">
                        @foreach (['facebook', 'pinterest', 'youtube', 'tiktok'] as $social)
                            <a href="#"
                                class="w-10 h-10 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 transition-colors group">
                                <span class="sr-only">{{ $social }}</span>

                                @if ($social == 'facebook')
                                    <svg class="w-5 h-5 fill-current text-white/80 group-hover:text-white"
                                        viewBox="0 0 320 512">
                                        <path
                                            d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" />
                                    </svg>
                                @elseif($social == 'pinterest')
                                    <svg class="w-5 h-5 fill-current text-white/80 group-hover:text-white"
                                        viewBox="0 0 496 512">
                                        <path
                                            d="M496 256c0 137-111 248-248 248-25.6 0-50.2-3.9-73.4-11.1 10.1-16.5 25.2-43.5 30.8-65 3-11.6 15.4-59 15.4-59 8.1 15.4 31.7 28.5 56.8 28.5 74.8 0 128.7-68.8 128.7-154.3 0-81.9-66.9-143.2-152.9-143.2-107 0-163.9 71.8-163.9 150.1 0 36.4 19.4 81.7 50.3 96.1 4.7 2.2 7.2 1.2 8.3-3.3.8-3.4 5-20.3 6.9-28.1.6-2.5.3-4.7-1.7-7.1-10.1-12.5-18.3-35.3-18.3-56.6 0-54.7 41.4-107.6 112-107.6 60.9 0 103.6 41.5 103.6 100.9 0 67.1-33.9 113.6-78 113.6-24.3 0-42.6-20.1-36.7-44.8 7-29.5 20.5-61.3 20.5-82.6 0-19-10.2-34.9-31.4-34.9-24.9 0-44.9 25.7-44.9 60.2 0 22 7.4 36.8 7.4 36.8s-24.5 103.8-29 123.2c-5 21.4-3 51.6-.9 71.2C65.4 450.9 0 361.1 0 256 0 119 111 8 248 8s248 111 248 248z" />
                                    </svg>
                                @elseif($social == 'youtube')
                                    <svg class="w-5 h-5 fill-current text-white/80 group-hover:text-white"
                                        viewBox="0 0 576 512">
                                        <path
                                            d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z" />
                                    </svg>
                                @elseif($social == 'tiktok')
                                    <svg class="w-5 h-5 fill-current text-white/80 group-hover:text-white"
                                        viewBox="0 0 448 512">
                                        <path
                                            d="M448,209.91a210.06,210.06,0,0,1-122.77-39.25V349.38A162.55,162.55,0,1,1,185,188.31V278.2a74.62,74.62,0,1,0,52.23,71.18V0l88,0a121.18,121.18,0,0,0,1.86,22.17h0A122.18,122.18,0,0,0,381,102.39a121.43,121.43,0,0,0,67,20.14Z" />
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div>
                <h4 class="font-heading font-bold text-lg mb-6">Quick Links</h4>
                <ul class="space-y-4 text-gray-200 text-sm">
                    <li>
                        <a href="#"
                            class="flex items-center gap-2 group hover:text-white transition-all duration-300">
                            <svg class="w-4 h-4 fill-current text-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                viewBox="0 0 320 512">
                                <path
                                    d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" />
                            </svg>
                            Home
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="flex items-center gap-2 group hover:text-white transition-all duration-300">
                            <svg class="w-4 h-4 fill-current text-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                viewBox="0 0 320 512">
                                <path
                                    d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" />
                            </svg>
                            About
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="flex items-center gap-2 group hover:text-white transition-all duration-300">
                            <svg class="w-4 h-4 fill-current text-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                viewBox="0 0 320 512">
                                <path
                                    d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" />
                            </svg>
                            Blog
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="flex items-center gap-2 group hover:text-white transition-all duration-300">
                            <svg class="w-4 h-4 fill-current text-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                                viewBox="0 0 320 512">
                                <path
                                    d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" />
                            </svg>
                            Contact
                        </a>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-heading font-bold text-lg mb-6">Our Services</h4>
                <ul class="space-y-4 text-gray-200 text-sm">
                    <li class="flex items-center gap-3 group hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5 fill-current text-yellow-500 group-hover:scale-110 transition-transform duration-300"
                            viewBox="0 0 640 512">
                            <path
                                d="M624 352h-16V243.9c0-12.7-5.1-24.9-14.1-33.9L494 110.1c-9-9-21.2-14.1-33.9-14.1H416V48c0-26.5-21.5-48-48-48H112C85.5 0 64 21.5 64 48v48H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h272c4.4 0 8 3.6 8 8v16c0 4.4-3.6 8-8 8H40c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h208c4.4 0 8 3.6 8 8v16c0 4.4-3.6 8-8 8H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h208c4.4 0 8 3.6 8 8v16c0 4.4-3.6 8-8 8H64v128c0 53 43 96 96 96s96-43 96-96h128c0 53 43 96 96 96s96-43 96-96h48c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zM160 464c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm320 0c-26.5 0-48-21.5-48-48s21.5-48 48-48 48 21.5 48 48-21.5 48-48 48zm80-208H416V144h44.1l99.9 99.9V256z" />
                        </svg>
                        <span>Free Shipping</span>
                    </li>
                    <li class="flex items-center gap-3 group hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5 fill-current text-yellow-500 group-hover:scale-110 transition-transform duration-300"
                            viewBox="0 0 448 512">
                            <path
                                d="M438.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L160 338.7 393.4 105.4c12.5-12.5 32.8-12.5 45.3 0z" />
                        </svg>
                        <span>Organic Products</span>
                    </li>
                    <li class="flex items-center gap-3 group hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5 fill-current text-yellow-500 group-hover:scale-110 transition-transform duration-300"
                            viewBox="0 0 512 512">
                            <path
                                d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm92.5 313l-20 20c-4.7 4.7-12.3 4.7-17 0L256 285.5l-55.5 55.5c-4.7 4.7-12.3 4.7-17 0l-20-20c-4.7-4.7-4.7-12.3 0-17l55.5-55.5L163.5 194c-4.7-4.7-4.7-12.3 0-17l20-20c4.7-4.7 12.3-4.7 17 0l55.5 55.5 55.5-55.5c4.7-4.7 12.3-4.7 17 0l20 20c4.7 4.7 4.7 12.3 0 17L285.5 256l55.5 55.5c4.7 4.7 4.7 12.3 0 17z" />
                        </svg>
                        <span>24/7 Support</span>
                    </li>
                    <li class="flex items-center gap-3 group hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5 fill-current text-yellow-500 group-hover:scale-110 transition-transform duration-300"
                            viewBox="0 0 576 512">
                            <path
                                d="M0 112.5v309.8c0 18 10.1 35 27 41.3 87 32.5 174 10.3 261-11.9 0 0 0 0 0 0-23.2-6.4-46.2-13.9-69-22.2-28.2-10.3-46.6-37.4-46.6-67.1V140.6c0-29.7 18.5-56.9 46.6-67.1 47.8-17.4 95-27.9 144-31.5V64c0 35.3 28.7 64 64 64 35.3 0 64-28.7 64-64V0 0C386 0 314.2 8 247.2 29.5c-44.8 14.4-80.8 47.6-98.8 83.9-19.8 40.1-19.8 86.3 0 126.5 18 36.4 54 69.6 98.8 83.9 56.8 18.3 118 22.8 176 22.8 0 0 0 0 0 0 58 0 119.2-4.5 176-22.8 44.8-14.4 80.8-47.6 98.8-83.9 19.8-40.2 19.8-86.4 0-126.5-18-36.4-54-69.6-98.8-83.9C386 8 314.2 0 247.2 0 220 0 192.6 1.1 166.3 3.4 124.3 6.8 83.6 14.5 48 30.8v420.6c27.9-12.4 56.8-23 86.5-31.5 50-14.4 101.6-22.3 153.5-23.5 0 0 0 0 0 0 0 0 0 0 0 0 27.9-.5 55.8.3 83.5 2.7-39.9 13.4-80.7 23-122.1 27.5-74.9 8.2-150.3-4.5-222.5-28.2-18.4-6.1-35.5-13.4-52.5-21.5v109.2z" />
                        </svg>
                        <span>Money-back Guarantee</span>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="font-heading font-bold text-lg mb-6 text-white">Contact Us</h4>
                <ul class="space-y-4 text-gray-200 text-sm">
                    <li class="flex gap-3 group hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5 fill-current text-yellow-500 shrink-0 group-hover:scale-110 transition-transform duration-300"
                            viewBox="0 0 384 512">
                            <path
                                d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" />
                        </svg>
                        <span>65, Feroza Garden, Shahid Smriti Sarak, Barguna-8700</span>
                    </li>
                    <li class="flex gap-3 group hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5 fill-current text-yellow-500 shrink-0 group-hover:scale-110 transition-transform duration-300"
                            viewBox="0 0 512 512">
                            <path
                                d="M497.39 361.8l-112-48a16 16 0 0 0-15.64 2.22l-42.95 32.73c-42.34-23.27-76.58-57.5-99.85-99.85l32.73-42.95a16 16 0 0 0 2.22-15.64l-48-112A16 16 0 0 0 196.7 64h-112A16 16 0 0 0 68 80a376.17 376.17 0 0 0 376 376 16 16 0 0 0 16-16v-112a16 16 0 0 0-10.61-15.2z" />
                        </svg>
                        <span>+8801733358158</span>
                    </li>
                    <li class="flex gap-3 group hover:text-white transition-all duration-300">
                        <svg class="w-5 h-5 fill-current text-yellow-500 shrink-0 group-hover:scale-110 transition-transform duration-300"
                            viewBox="0 0 512 512">
                            <path
                                d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z" />
                        </svg>
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
