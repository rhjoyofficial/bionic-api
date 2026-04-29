@extends('layouts.app')

@section('title', $data['title'])

@section('content')
    <!-- Elegant, Light-Themed Cinematic Design -->
    <div
        class="bg-[#f0f5f1] min-h-screen text-slate-600 font-light overflow-hidden selection:bg-primary/30 shadow-2xl bg-white border-x border-slate-200">

        <!-- Cinematic Hero -->
        <section
            class="relative h-100 flex items-center justify-center border-b border-slate-200 bg-primary overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1596524430615-b46475ddff6e?auto=format&fit=crop&q=80&w=2000"
                    class="w-full h-full object-cover opacity-20 scale-110 group-hover:scale-100 transition-transform duration-[10s]"
                    alt="Contact Us">
                <div class="absolute inset-0 bg-linear-to-t from-primary via-primary/40 to-transparent"></div>
                <div class="absolute inset-0 bg-linear-to-b from-primary/20 via-transparent to-transparent"></div>
            </div>

            <div class="absolute top-10 left-10 w-20 h-20 border-t border-l border-white/10 hidden md:block"></div>
            <div class="absolute bottom-10 right-10 w-20 h-20 border-b border-r border-white/10 hidden md:block"></div>

            <div class="relative z-10 text-center px-4 max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-3 mb-6">
                    <span class="w-8 h-px bg-yellow-400/50"></span>
                    <p class="text-yellow-400 uppercase tracking-[0.5em] text-[10px] font-bold">
                        Get In Touch
                    </p>
                    <span class="w-8 h-px bg-yellow-400/50"></span>
                </div>

                <h1
                    class="text-4xl md:text-5xl lg:text-6xl font-playfair text-white mb-6 leading-[1.1] tracking-tight drop-shadow-2xl">
                    {{ $data['title'] }}
                </h1>

                <p class="text-sm md:text-base text-green-100/80 max-w-xl mx-auto leading-relaxed font-light line-clamp-2">
                    {{ $data['subtitle'] }}
                </p>

                <div class="mt-8 flex justify-center">
                    <div class="w-1.5 h-1.5 rounded-full bg-yellow-400 animate-pulse"></div>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <div class="max-w-8xl mx-auto px-4 py-24">
            <div class="grid lg:grid-cols-12 gap-16 lg:gap-24">

                <!-- Contact Details -->
                <div class="lg:col-span-5 space-y-16">
                    <div>
                        <h2 class="text-3xl font-playfair text-slate-900 mb-4 tracking-wide">Direct Lines</h2>
                        <div class="w-12 h-px bg-primary mb-10"></div>
                        <p class="text-sm leading-relaxed text-slate-500 font-light mb-12">
                            Whether you have a question about our products, sourcing, or anything else, our team is ready to
                            answer all your questions.
                        </p>
                    </div>

                    <div class="space-y-10">
                        <!-- Phone -->
                        <div class="group flex items-start gap-6">
                            <div
                                class="w-12 h-12 flex items-center justify-center border border-slate-200 bg-white text-primary rounded-full group-hover:bg-primary group-hover:text-white transition-colors duration-500 shrink-0">
                                <i class="fa-solid fa-phone group-hover:text-white"></i>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-1">Call Us</p>
                                <p class="text-lg font-playfair text-slate-900 tracking-wide">{{ $data['phone'] }}</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="group flex items-start gap-6">
                            <div
                                class="w-12 h-12 flex items-center justify-center border border-slate-200 bg-white text-primary rounded-full group-hover:bg-primary group-hover:text-white transition-colors duration-500 shrink-0">
                                <i class="fa-solid fa-envelope group-hover:text-white"></i>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-1">Email Us</p>
                                <p class="text-lg font-playfair text-slate-900 tracking-wide">{{ $data['email'] }}</p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="group flex items-start gap-6">
                            <div
                                class="w-12 h-12 flex items-center justify-center border border-slate-200 bg-white text-primary rounded-full group-hover:bg-primary group-hover:text-white transition-colors duration-500 shrink-0">
                                <i class="fa-solid fa-location-dot group-hover:text-white"></i>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-[0.2em] text-slate-400 mb-1">Visit Us</p>
                                <p class="text-sm text-slate-600 leading-relaxed font-light max-w-[200px]">
                                    {{ $data['address'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Support Card -->
                    <div class="bg-primary p-10 relative overflow-hidden group shadow-2xl">
                        <div
                            class="absolute -right-8 -bottom-8 opacity-10 transform group-hover:scale-110 transition-transform duration-[3s]">
                            <i class="fa-brands fa-whatsapp text-9xl text-white"></i>
                        </div>
                        <div class="relative z-10">
                            <h3 class="text-xl font-playfair text-white mb-3 tracking-wide">Customer Support</h3>
                            <p class="text-xs text-green-50/80 mb-8 font-light leading-relaxed">Available from 09:00 AM to
                                6:00 PM every day for prompt assistance.</p>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $data['phone']) }}"
                                class="inline-flex items-center gap-3 bg-white text-primary px-6 py-3 text-[10px] uppercase tracking-[0.2em] font-medium hover:bg-slate-900 hover:text-white transition-colors duration-500">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                Chat on WhatsApp
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Contact Form & Map -->
                <div class="lg:col-span-7 space-y-16">

                    <!-- Elegant Form -->
                    <div class="bg-white border border-slate-200 p-10 md:p-16 shadow-xl relative">
                        <div
                            class="absolute top-0 right-0 w-32 h-32 bg-[#f0f5f1]/50 rounded-full -mr-16 -mt-16 pointer-events-none">
                        </div>
                        <h2 class="text-3xl font-playfair text-slate-900 mb-2 tracking-wide">Send a Message</h2>
                        <p class="text-xs text-slate-500 font-light mb-10">We will get back to you as soon as possible.</p>

                        <form action="#" class="grid md:grid-cols-2 gap-x-8 gap-y-10 relative z-10">
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase tracking-[0.2em] text-slate-400">Full Name</label>
                                <input type="text"
                                    class="w-full bg-transparent border-b border-slate-200 py-3 text-sm text-slate-900 placeholder:text-slate-300 focus:outline-none focus:border-primary transition-colors duration-300"
                                    placeholder="CM Moin">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] uppercase tracking-[0.2em] text-slate-400">Email Address</label>
                                <input type="email"
                                    class="w-full bg-transparent border-b border-slate-200 py-3 text-sm text-slate-900 placeholder:text-slate-300 focus:outline-none focus:border-primary transition-colors duration-300"
                                    placeholder="cmmoin@gmail.com">
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[9px] uppercase tracking-[0.2em] text-slate-400">Subject</label>
                                <input type="text"
                                    class="w-full bg-transparent border-b border-slate-200 py-3 text-sm text-slate-900 placeholder:text-slate-300 focus:outline-none focus:border-primary transition-colors duration-300"
                                    placeholder="How can we help?">
                            </div>
                            <div class="md:col-span-2 space-y-2">
                                <label class="text-[9px] uppercase tracking-[0.2em] text-slate-400">Message</label>
                                <textarea rows="4"
                                    class="w-full bg-transparent border-b border-slate-200 py-3 text-sm text-slate-900 placeholder:text-slate-300 focus:outline-none focus:border-primary transition-colors duration-300 resize-none"
                                    placeholder="Your message here..."></textarea>
                            </div>
                            <div class="md:col-span-2 mt-4">
                                <button type="button"
                                    class="w-full md:w-auto px-12 py-4 bg-slate-900 text-white text-[10px] uppercase tracking-[0.3em] hover:bg-primary transition-colors duration-500 shadow-xl shadow-slate-900/10">
                                    Send Message
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Google Map -->
                    <div>
                        <h2 class="text-[10px] uppercase tracking-[0.4em] text-primary mb-6">Our Location</h2>
                        <div
                            class="aspect-video w-full bg-slate-200 border border-slate-200 shadow-lg overflow-hidden group">
                            <iframe src="{{ $data['map_embed'] }}" width="100%" height="100%" style="border:0;"
                                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                class="grayscale contrast-125 opacity-80 group-hover:grayscale-0 group-hover:opacity-100 transition-all duration-[2s]">
                            </iframe>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
