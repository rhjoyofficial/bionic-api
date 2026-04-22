@extends('layouts.app')

@php
    $certifications = $product->certifications;
@endphp

@section('title', $landing->meta_title ?? $product->name)
@section('meta_description', $landing->meta_description ?? $product->short_description)

@section('content')
    <!-- ================== HERO SECTION ================ -->
    <section class="relative bg-[#FFF5F5] pt-16 pb-12 md:pt-28 md:pb-20 overflow-hidden font-bengali">
        <div class="absolute top-0 left-0 w-72 h-72 bg-pink-200/30 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2">
        </div>
        <div
            class="absolute bottom-0 right-0 w-96 h-96 bg-orange-100/40 rounded-full blur-3xl translate-x-1/3 translate-y-1/3">
        </div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2 text-center md:text-left">
                    <div
                        class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-pink-100 text-pink-700 text-sm font-bold mb-6 border border-pink-200 animate-bounce">
                        <i class="fas fa-mountain"></i> সরাসরি হিমালয় থেকে সংগৃহীত
                    </div>

                    <h1 class="text-4xl md:text-6xl font-black text-gray-900 leading-[1.2] mb-6">
                        প্রকৃতির বিশুদ্ধ খনিজ <br />
                        <span class="text-pink-600">হিমালয়ান পিঙ্ক সল্ট</span>
                    </h1>

                    <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed max-w-xl">
                        ৮৪টিরও বেশি প্রাকৃতিক খনিজ সমৃদ্ধ এক অলৌকিক লবণ। আপনার রান্নাকে
                        করুন স্বাস্থ্যসম্মত আর শরীরকে রাখুন বিষমুক্ত ও প্রাণবন্ত।
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-5 justify-center md:justify-start mb-8">
                        <div
                            class="bg-white px-8 py-3 rounded-2xl shadow-sm border border-pink-100 flex flex-col items-center sm:items-start">
                            <span
                                class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1 font-bengali">পরিমাণ:
                                ১০০০~ গ্রাম</span>
                            <div class="flex items-baseline gap-2 font-bengali">
                                <span class="text-3xl font-black font-bengali text-gray-900">৳৮৯০</span>
                                <span class="text-sm font-medium text-gray-400 font-bengali line-through">৳১০৯০</span>
                            </div>
                        </div>

                        <a href="#order-form"
                            class="w-full sm:w-auto px-10 py-5 bg-pink-600 hover:bg-pink-700 text-white font-bold text-lg rounded-2xl shadow-xl shadow-pink-200 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3">
                            <i class="fas fa-shopping-basket"></i> এখনই অর্ডার করুন
                        </a>
                    </div>

                    <div class="flex items-center gap-6 justify-center md:justify-start text-sm text-gray-500 font-medium">
                        <div class="flex items-center gap-2 font-bengali">
                            <i class="fas fa-check-circle text-pink-500"></i> ১০০% প্রাকৃতিক
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-certificate text-pink-500"></i> খনিজ সমৃদ্ধ
                        </div>
                    </div>
                </div>

                <div class="md:w-1/2 relative group">
                    <div
                        class="relative z-10 w-full max-w-md mx-auto aspect-4/5 rounded-[2.5rem] overflow-hidden shadow-2xl transition-transform duration-500 border-10 border-white bg-white">
                        <img src="{{ asset($product->image_url) }}" alt="Himalayan Pink Salt Premium Pack"
                            class="w-full h-full object-cover group-hover:scale-110 duration-300 transform-all" />
                    </div>

                    <div
                        class="absolute -top-6 -right-4 md:right-0 z-20 bg-white border border-pink-100 p-4 rounded-2xl shadow-lg transform rotate-6 hover:rotate-0 transition-all">
                        <p class="text-[10px] font-bold text-gray-400 uppercase leading-none">
                            ছোট প্যাকও আছে
                        </p>
                        <span class="text-lg font-black text-pink-600 leading-none font-bengali">১৪০~ গ্রাম মাত্র
                            ৳১৯০</span>
                        <a href="{{ route('product.show', $product->slug) }}" target="_blank"
                            class="block text-[10px] text-blue-500 underline mt-1">বিস্তারিত দেখুন</a>
                    </div>

                    <div class="absolute -bottom-8 -left-8 w-24 h-24 bg-pink-100 rounded-full -z-10 animate-pulse"></div>
                </div>
            </div>
        </div>
    </section>
    <!-- ================= PRODUCT INTRODUCTION SECTION ================= -->
    <section class="py-16 md:py-24 bg-white font-bengali overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-12 md:gap-20">
                <div class="lg:w-1/2 space-y-6">
                    <div class="flex items-center gap-3 text-pink-600 font-bold tracking-wide uppercase text-sm">
                        <span class="w-10 h-0.5 bg-pink-600"></span>
                        বিশুদ্ধতার উৎস
                    </div>

                    <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">
                        হিমালয় পর্বত থেকে <br />
                        <span class="text-pink-600">আসা প্রাকৃতিক বিস্ময়</span>
                    </h2>

                    <p class="text-gray-600 text-lg leading-relaxed text-justify md:text-left">
                        হিমালয়ান পিঙ্ক সল্ট হলো প্রাকৃতিকভাবে খনিজসমৃদ্ধ এক ধরনের লবণ, যা
                        হিমালয়ের প্রাচীন পাহাড় থেকে সংগ্রহ করা হয়। এর মনোরম গোলাপি রঙ
                        আসে এতে থাকা ৮৪টিরও বেশি প্রাকৃতিক খনিজ থেকে। এটি কোনো রাসায়নিক
                        প্রক্রিয়ার মধ্য দিয়ে যায় না, তাই এর প্রতিটি কণা থাকে সম্পূর্ণ
                        বিশুদ্ধ।
                    </p>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-2 pt-4">
                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">Ca</span>
                            <span class="font-bold text-gray-800">ক্যালসিয়াম</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">Mg</span>
                            <span class="font-bold text-gray-800">ম্যাগনেসিয়াম</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">K</span>
                            <span class="font-bold text-gray-800">পটাশিয়াম</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">Fe</span>
                            <span class="font-bold text-gray-800">আয়রন</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">I</span>
                            <span class="font-bold text-gray-800">আয়োডিন</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">Zn</span>
                            <span class="font-bold text-gray-800">জিংক</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">Cu</span>
                            <span class="font-bold text-gray-800">কপার</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">NaCl</span>
                            <span class="font-bold text-gray-800">সোডিয়াম</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">Mn</span>
                            <span class="font-bold text-gray-800">ম্যাঙ্গানিজ</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-8 h-8 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">P</span>
                            <span class="font-bold text-gray-800">ফসফরাস</span>
                        </div>

                        <div class="bg-pink-50 p-4 rounded-2xl flex items-center gap-3 border border-pink-100 shadow-sm">
                            <span
                                class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-xs shadow-inner">Se</span>
                            <span class="font-bold text-gray-800">সেলেনিয়াম</span>
                        </div>

                        <div class="bg-pink-600 p-4 rounded-2xl flex items-center gap-3 border border-pink-700 shadow-md">
                            <span
                                class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-pink-600 font-bold text-sm shadow-inner">84+</span>
                            <span class="font-bold text-white">অন্যান্য খনিজ</span>
                        </div>
                    </div>
                </div>

                <div class="lg:w-1/2 w-full">
                    <div class="bg-gray-50 rounded-[2.5rem] p-8 md:p-10 border border-gray-100 shadow-sm relative">
                        <h3 class="text-2xl font-black text-gray-900 mb-8 text-center">
                            কেন সাধারণ লবণের চেয়ে সেরা?
                        </h3>

                        <div class="space-y-6">
                            <div class="flex items-center justify-between pb-4 border-b border-gray-200">
                                <span class="text-gray-500 font-medium text-sm md:text-base">তুলনামূলক পার্থক্য</span>
                                <div class="flex gap-8 text-sm font-black">
                                    <span class="text-red-400">সাদা লবণ</span>
                                    <span class="text-pink-600">পিঙ্ক সল্ট</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-bold text-sm md:text-base">প্রাকৃতিক খনিজ</span>
                                <div class="flex gap-16 md:gap-20 items-center">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                    <i class="fas fa-check-circle text-pink-600"></i>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-bold text-sm md:text-base">কেমিক্যাল মুক্ত</span>
                                <div class="flex gap-16 md:gap-20 items-center">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                    <i class="fas fa-check-circle text-pink-600"></i>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-bold text-sm md:text-base">pH ব্যালান্স</span>
                                <div class="flex gap-16 md:gap-20 items-center">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                    <i class="fas fa-check-circle text-pink-600"></i>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-bold text-sm md:text-base">অ্যান্টি-কেকিং মুক্ত</span>
                                <div class="flex gap-16 md:gap-20 items-center">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                    <i class="fas fa-check-circle text-pink-600"></i>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-bold text-sm md:text-base">ইলেক্ট্রোলাইট</span>
                                <div class="flex gap-16 md:gap-20 items-center">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                    <i class="fas fa-check-circle text-pink-600"></i>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-gray-700 font-bold text-sm md:text-base">প্রাকৃতিক উপায়ে আহরিত</span>
                                <div class="flex gap-16 md:gap-20 items-center">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                    <i class="fas fa-check-circle text-pink-600"></i>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-8 bg-white p-4 rounded-2xl text-sm text-gray-500 italic text-center border border-pink-100">
                            "সাধারণ লবণ রিফাইন করার সময় এর প্রাকৃতিক গুণাগুণ নষ্ট হয়ে যায়,
                            কিন্তু পিঙ্ক সল্ট থাকে অপরিবর্তিত।"
                        </div>
                    </div>
                </div>
            </div>
            <!-- ############# START CTA BUTTON SECTION ############# -->
            <div class="flex justify-center mt-6">
                <button onclick="document.getElementById('order-form').scrollIntoView({ behavior: 'smooth' })"
                    class="inline-flex items-center justify-center transition-all duration-300 active:scale-95 font-bold px-10 py-4 gap-3 rounded-full text-lg md:text-2xl bg-pink-50 text-pink-700 border-4 border-pink-500 animate-pulse shadow-[0_10px_25px_rgba(219,39,119,0.3)] hover:bg-pink-600 hover:text-white hover:border-pink-700 group">
                    <svg class="w-6 h-6 md:w-8 md:h-8 group-hover:rotate-12 transition-transform" fill="currentColor"
                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                            fill="currentColor" opacity="0.2" />
                        <path
                            d="M12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7C9.23858 7 7 9.23858 7 12C7 14.7614 9.23858 17 12 17Z"
                            fill="currentColor" />
                    </svg>

                    <span class="font-anekBangla tracking-wide">এখনি অর্ডার করুন</span>
                </button>
            </div>
            <!-- ############# END CTA BUTTON SECTION ############# -->
        </div>
    </section>
    <!-- ================= CERTIFICATIONS SECTION ================= -->
    <section id="certifications" class="pb-6 md:pb-10 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-6">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 font-bengali">
                    আন্তর্জাতিক মানের নিশ্চয়তা
                </h2>
                <div class="w-20 h-1 bg-pink-600 mx-auto rounded-full"></div>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto font-bengali">
                    আমাদের হিমালয়ান পিঙ্ক সল্ট কঠোর মাননিয়ন্ত্রণ ও স্বাস্থ্যসম্মত
                    প্রক্রিয়ায় আপনার কাছে পৌঁছায়। আমাদের বিশুদ্ধতা ও নির্ভরযোগ্যতার
                    সনদসমূহ:
                </p>
            </div>

            <div class="flex flex-wrap justify-center items-center gap-6 md:gap-10 lg:gap-20">
                @foreach ($certifications as $cert)
                    <x-certification-item :image="$cert->logo_url" :name="$cert->name" />
                @endforeach
            </div>
        </div>
    </section>
    <!-- ================= KEY BENEFITS SECTION ================= -->
    <section class="py-16 md:py-24 bg-pink-50/30 font-bengali">
        <div class="max-w-7xl mx-auto px-6 text-center mb-16">
            <span class="text-pink-600 font-bold uppercase tracking-widest text-sm mb-3 block">সুস্থতার চাবিকাঠি</span>
            <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">
                হিমালয়ান পিঙ্ক সল্ট কেন
                <span class="text-pink-600">আপনার প্রয়োজন?</span>
            </h2>
            <div class="w-20 h-1.5 bg-pink-600 mx-auto mt-6 rounded-full"></div>
        </div>

        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 text-3xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-all">
                    <i class="fas fa-scale-balanced"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    pH ব্যালান্স নিয়ন্ত্রণ
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    শরীরের অম্লতা বা এসিডিটি কমিয়ে pH লেভেলের সঠিক ভারসাম্য বজায় রাখতে
                    এটি অত্যন্ত কার্যকর।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 text-3xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-all">
                    <i class="fas fa-hand-holding-water"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">হাইড্রেট রাখে</h3>
                <p class="text-gray-600 leading-relaxed">
                    ইলেকট্রোলাইট ভারসাম্য বজায় রেখে শরীরকে ভেতর থেকে হাইড্রেট রাখতে
                    সহায়তা করে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 text-3xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-all">
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">হজমে সহায়তা</h3>
                <p class="text-gray-600 leading-relaxed">
                    খাবারের পুষ্টি শোষণ বৃদ্ধি করে এবং প্রাকৃতিকভাবে হজম প্রক্রিয়াকে
                    উন্নত করতে সাহায্য করে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 text-3xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-all">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    রক্তচাপ নিয়ন্ত্রণ
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    সাধারণ লবণের তুলনায় সোডিয়াম কম থাকায় এটি রক্তচাপ নিয়ন্ত্রণে রাখতে
                    ইতিবাচক ভূমিকা রাখে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 text-3xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-all">
                    <i class="fas fa-leaf"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">ডিটক্সিফিকেশন</h3>
                <p class="text-gray-600 leading-relaxed">
                    শরীরের বিষাক্ত পদার্থ বা টক্সিন বের করে শরীরকে প্রাকৃতিকভাবে
                    ডিটক্সিফাই করতে সাহায্য করে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 text-3xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-all">
                    <i class="fas fa-spa"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">মানসিক প্রশান্তি</h3>
                <p class="text-gray-600 leading-relaxed">
                    বাথ সল্ট হিসেবে ব্যবহারে ত্বকের উজ্জ্বলতা বাড়ে এবং মানসিক ক্লান্তি
                    দূর করে প্রশান্তি আনে।
                </p>
            </div>
        </div>

        <!-- ############# START CTA BUTTON SECTION ############# -->
        <div class="flex justify-center mt-6">
            <button onclick="document.getElementById('order-form').scrollIntoView({ behavior: 'smooth' })"
                class="inline-flex items-center justify-center transition-all duration-300 active:scale-95 font-bold px-10 py-4 gap-3 rounded-full text-lg md:text-2xl bg-pink-50 text-pink-700 border-4 border-pink-500 animate-pulse shadow-[0_10px_25px_rgba(219,39,119,0.3)] hover:bg-pink-600 hover:text-white hover:border-pink-700 group">
                <svg class="w-6 h-6 md:w-8 md:h-8 group-hover:rotate-12 transition-transform" fill="currentColor"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                        fill="currentColor" opacity="0.2" />
                    <path
                        d="M12 17C14.7614 17 17 14.7614 17 12C17 9.23858 14.7614 7 12 7C9.23858 7 7 9.23858 7 12C7 14.7614 9.23858 17 12 17Z"
                        fill="currentColor" />
                </svg>

                <span class="font-anekBangla tracking-wide">এখনি অর্ডার করুন</span>
            </button>
        </div>
        <!-- ############# END CTA BUTTON SECTION ############# -->
    </section>
    <!-- ================= VERSATILE USES SECTION ================= -->
    <section class="py-16 md:py-24 bg-white font-bengali overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 mb-16 text-center">
            <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight mb-4">
                পিঙ্ক সল্টের
                <span class="text-pink-600 italic underline underline-offset-8 decoration-pink-200">
                    বহুমুখী ব্যবহার</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-lg mt-6">
                সুস্থতা থেকে সৌন্দর্য—প্রতিটি ক্ষেত্রে হিমালয়ান পিঙ্ক সল্ট আপনার সেরা
                সঙ্গী।
            </p>
        </div>

        <div class="max-w-6xl mx-auto px-6 space-y-20">
            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-2/1">
                        <img src="{{ asset('assets/images/pink-salt-cooking.jpg') }}" alt="Cooking with Pink Salt"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    </div>
                    <div class="absolute -bottom-4 -right-4 bg-pink-600 text-white p-4 rounded-2xl hidden md:block">
                        <i class="fas fa-utensils text-2xl"></i>
                    </div>
                </div>
                <div class="md:w-1/2 space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900">
                        রান্নার স্বাদ ও স্বাস্থ্য
                    </h3>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        প্রতিদিনের তরকারি, সালাদ বা স্যুপে সাধারণ লবণের বদলে ব্যবহার করুন
                        হিমালয়ান পিঙ্ক সল্ট। এটি কেবল খাবারের স্বাদই বাড়ায় না, বরং ৮৪টি
                        খনিজ উপাদান সরাসরি শরীরের পুষ্টি জোগায়।
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row-reverse items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-2/1">
                        <img src="{{ asset('assets/images/pink-salt-detox.jpg') }}" alt="Pink Salt Detox Drink"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    </div>
                    <div class="absolute -bottom-4 -left-4 bg-pink-400 text-white p-4 rounded-2xl hidden md:block">
                        <i class="fas fa-glass-water text-2xl"></i>
                    </div>
                </div>
                <div class="md:w-1/2 space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900">
                        শরীরের ডিটক্সিফিকেশন
                    </h3>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        সকালে হালকা কুসুম গরম পানিতে এক চিমটি পিঙ্ক সল্ট মিশিয়ে পান করুন।
                        এটি শরীরের বিষাক্ত পদার্থ বের করে দিয়ে আপনাকে ভেতর থেকে সতেজ ও
                        প্রাণবন্ত রাখতে সাহায্য করে।
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-2/1">
                        <img src="{{ asset('assets/images/pink-salt-skin.jpg') }}" alt="Pink Salt Skin Care"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    </div>
                    <div class="absolute -bottom-4 -right-4 bg-pink-600 text-white p-4 rounded-2xl hidden md:block">
                        <i class="fas fa-spa text-2xl"></i>
                    </div>
                </div>
                <div class="md:w-1/2 space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900">
                        প্রাকৃতিক রূপচর্চা ও স্ক্রাব
                    </h3>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        নারিকেল তেলের সাথে পিঙ্ক সল্ট মিশিয়ে ব্যবহার করুন বডি স্ক্রাব
                        হিসেবে। এটি ত্বকের মৃত কোষ দূর করে ত্বককে কোমল রাখে এবং বাথ সল্ট
                        হিসেবে ক্লান্ত পেশীর ব্যথা দূর করতে কার্যকর।
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= ORDER FROM SECTION ================= -->
    <section id="order-form" class="py-10 md:py-14 bg-[#FFF5F5] overflow-hidden font-bengali">
        <div class="max-w-7xl mx-auto px-4 sm:px-6" data-lp-checkout data-lp-slug="{{ $landing->slug }}"
            data-lp-cart-mode="1">

            <div class="text-center mb-8 md:mb-10">
                <span class="text-pink-600 font-bold uppercase tracking-widest text-sm mb-2 block">সহজ অর্ডার
                    প্রক্রিয়া</span>
                <h2 class="text-2xl md:text-3xl font-black text-gray-900">আপনার অর্ডার দিন</h2>
                <div class="w-16 h-1 bg-pink-600 mx-auto mt-3 rounded-full"></div>
            </div>

            <div class="flex flex-col lg:flex-row gap-6 lg:gap-16 items-start">

                {{-- LEFT: Multi-Variant Checkout Cards (cart mode) --}}
                <div class="lg:w-1/2 w-full space-y-4">

                    {{-- Section Header --}}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-black text-gray-900">প্যাক সাইজ নির্বাচন করুন</h3>
                            <p class="text-sm text-gray-500 mt-0.5">একাধিক ভ্যারিয়েন্ট একসাথে নিতে পারবেন</p>
                        </div>
                        <div
                            class="shrink-0 bg-pink-100 text-pink-700 text-[10px] font-bold px-2 py-1 rounded-full flex items-center gap-1 mt-0.5">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            মাল্টি-সিলেক্ট
                        </div>
                    </div>

                    {{-- Variant Cards --}}
                    <div class="space-y-2">
                        @foreach ($product->variants as $variant)
                            @php
                                $price = (float) $variant->price;
                                $tierPrices = $variant->tierPrices ?? collect();
                                $tierData = $tierPrices
                                    ->sortBy('min_quantity')
                                    ->map(
                                        fn($t) => [
                                            'min_qty' => $t->min_quantity,
                                            'price' =>
                                                $t->discount_type === 'percentage'
                                                    ? round($price * (1 - $t->discount_value / 100), 2)
                                                    : round($price - $t->discount_value, 2),
                                        ],
                                    )
                                    ->values()
                                    ->toArray();
                                $itemKey = 'v_' . $variant->id;
                            @endphp

                            <div data-lp-item-card data-item-key="{{ $itemKey }}"
                                data-variant-id="{{ $variant->id }}" data-price="{{ $price }}"
                                data-tier-prices="{{ json_encode($tierData) }}" data-preselected="0"
                                data-item-label="{{ addslashes($variant->title) }}"
                                data-active-class="border-pink-500 ring-1 ring-pink-100"
                                class="relative bg-white rounded-xl border border-gray-200 shadow-sm hover:border-pink-300 transition-all duration-200 cursor-pointer group overflow-hidden">

                                {{-- Subtle pink accent when selected --}}
                                <div
                                    class="absolute inset-0 bg-pink-50/0 group-[.border-pink-500]:bg-pink-50/40 transition-all duration-200 pointer-events-none rounded-xl">
                                </div>

                                <div class="relative flex items-center gap-3 p-3">

                                    {{-- Checkbox --}}
                                    <div data-lp-item-check data-active-class="bg-pink-600 border-pink-600"
                                        class="w-5 h-5 rounded border border-gray-300 bg-white flex items-center justify-center shrink-0 transition-all duration-200">
                                        <svg style="display:none" class="w-3 h-3 text-white" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div
                                        class="w-10 h-10 border border-gray-200 rounded bg-pink-50 overflow-hidden shrink-0">
                                        <img src="{{ $product->image_url }}" alt=""
                                            class="w-full h-full object-cover">
                                    </div>


                                    {{-- Variant Info --}}
                                    <div class="flex-1 items-stretch min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="font-bold text-gray-900 text-sm leading-tight truncate">{{ $variant->title }}</span>
                                            @if (isset($variant->compare_price) && $variant->compare_price > $price)
                                                <span
                                                    class="text-sm text-gray-400 line-through font-bengali">৳{{ number_format($variant->compare_price, 0) }}</span>
                                                <span
                                                    class="text-[9px] bg-green-100 text-green-700 font-bold px-1.5 py-0.5 rounded-full">সাশ্রয়!</span>
                                            @endif
                                        </div>
                                        <div class="flex items-baseline gap-1 mt-0.5">
                                            <span
                                                class="text-pink-600 font-black text-lg font-bengali leading-none">৳{{ number_format($price, 0) }}</span>
                                            <span class="text-sm text-gray-400">প্রতি প্যাক</span>
                                        </div>

                                        {{-- Tier Badges --}}
                                        @if ($tierPrices->isNotEmpty())
                                            <div class="flex flex-wrap gap-1 mt-1.5">
                                                @foreach ($tierPrices->sortBy('min_quantity') as $tier)
                                                    @php
                                                        $computedTierPrice =
                                                            $tier->discount_type === 'percentage'
                                                                ? round($price * (1 - $tier->discount_value / 100), 0)
                                                                : round($price - $tier->discount_value, 0);
                                                    @endphp
                                                    <span
                                                        class="inline-flex items-center gap-0.5 text-[11px] bg-pink-50 text-pink-700 border border-pink-200 rounded-md px-1.5 py-0.5 font-semibold">
                                                        <i class="fas fa-tag text-[12px]"></i>
                                                        Buy
                                                        {{ $tier->min_quantity }}+&nbsp;→&nbsp;Get
                                                        ৳{{ number_format($computedTierPrice, 0) }}/unit
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    {{-- NEW: Refined Qty Stepper --}}
                                    <div data-lp-qty-control
                                        class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm shrink-0">
                                        <button data-lp-qty-dec="{{ $itemKey }}" type="button"
                                            class="w-7 h-7 bg-gray-50 hover:bg-gray-100 active:bg-gray-200 text-gray-600 flex items-center justify-center transition-colors text-base leading-none cursor-pointer">
                                            &minus;
                                        </button>
                                        <span data-lp-qty-display="{{ $itemKey }}"
                                            class="w-7 text-center font-bold text-gray-800 text-sm border-x border-gray-100 py-0.5">
                                            0
                                        </span>
                                        <button data-lp-qty-inc="{{ $itemKey }}" type="button"
                                            class="w-7 h-7 bg-pink-50 hover:bg-pink-100 active:bg-pink-200 text-pink-700 flex items-center justify-center transition-colors text-base leading-none cursor-pointer">
                                            +
                                        </button>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Selected Items Mini-Summary --}}
                    <div data-lp-selected-container style="display:none"
                        class="bg-linear-to-br from-pink-50 to-white rounded-xl border border-pink-200 shadow-sm overflow-hidden">
                        <div class="px-3 pt-2 pb-2 border-b border-pink-100 flex items-center gap-1.5">
                            <div class="w-4 h-4 bg-pink-600 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-pink-700 uppercase tracking-wider">নির্বাচিত পণ্য</p>
                        </div>
                        <div data-lp-selected-list class="px-3 py-2 space-y-1"></div>
                    </div>

                    {{-- No Items Warning --}}
                    <div data-lp-no-items class="text-center text-sm text-gray-400 py-1">
                        পণ্যের পাশের + বাটন চেপে পরিমাণ নির্বাচন করুন
                    </div>

                    {{-- Rules --}}
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 space-y-2">
                        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                            <i class="fas fa-info-circle text-pink-500"></i> অর্ডারের নিয়মাবলী
                        </h4>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-truck text-pink-500 mt-0.5 shrink-0"></i>
                                <span>নির্দিষ্ট পরিমাণের বেশি অর্ডারে সারা বাংলাদেশে <strong>ডেলিভারি ফ্রি!</strong></span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-hand-holding-usd text-pink-500 mt-0.5 shrink-0"></i>
                                <span>ক্যাশ অন ডেলিভারি — পণ্য হাতে পেয়ে টাকা পরিশোধ করুন।</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-shield-alt text-pink-500 mt-0.5 shrink-0"></i>
                                <span>১০০% অরিজিনাল পণ্যের গ্যারান্টি।</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-headset text-pink-500 mt-0.5 shrink-0"></i>
                                <span>পাইকারি অর্ডারের জন্য কল করুন:
                                    <a href="tel:01334943783" class="font-black text-pink-700 hover:underline">01334
                                        943783</a>
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- RIGHT: Order Form --}}
                <div class="lg:w-1/2 w-full">
                    <div class="bg-white border border-pink-100 rounded-2xl p-5 md:p-7 shadow-lg shadow-pink-100/40">

                        <div class="text-center mb-6">
                            <h2 class="text-xl font-black text-gray-900">অর্ডার কনফার্ম করুন</h2>
                            <p class="text-pink-600 text-sm italic mt-1">সঠিক তথ্য দিয়ে নিচের ফর্মটি পূরণ করুন</p>
                        </div>

                        <div class="space-y-3">

                            {{-- Name + Phone --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <input type="text" name="customer_name" placeholder="আপনার নাম *"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-pink-400 focus:ring-1 focus:ring-pink-400 outline-none text-sm transition-all shadow-sm caret-pink-600">
                                <input type="tel" name="customer_phone" placeholder="মোবাইল নম্বর *"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-pink-400 focus:ring-1 focus:ring-pink-400 outline-none text-sm transition-all shadow-sm caret-pink-600">
                            </div>

                            {{-- Address --}}
                            <input type="text" name="address_line"
                                placeholder="পূর্ণ ঠিকানা (বাসা, রোড, এলাকা, জেলা) *"
                                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:border-pink-400 focus:ring-1 focus:ring-pink-400 outline-none text-sm transition-all shadow-sm caret-pink-600">

                            {{-- Bulk CTA --}}
                            <div
                                class="p-3 bg-amber-50 border border-dashed border-amber-300 rounded-lg flex flex-col sm:flex-row items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-2 w-2 relative shrink-0">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                                    </span>
                                    <p class="text-sm text-amber-900">পাইকারি বা বেশি পরিমাণে নিতে চান?</p>
                                </div>
                                <a href="tel:01334943783"
                                    class="text-sm font-black text-pink-700 hover:underline shrink-0">কল: 01334 943783</a>
                            </div>

                            {{-- Delivery Zone --}}
                            <div class="bg-pink-50/50 p-3 rounded-xl border border-pink-100">
                                <p class="text-base font-bold text-gray-700 mb-2">
                                    ডেলিভারি এলাকা <span class="text-red-500">*</span>
                                </p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @foreach ($zones as $zone)
                                        <label data-lp-zone-label
                                            data-active-class="border-pink-500 bg-pink-50 text-pink-700"
                                            title="{{ $zone->free_shipping_threshold ? 'Get Free Shipping, Buying more than' . $zone->free_shipping_threshold : $zone->name . ' ৳' . number_format($zone->base_charge, 0) }}"
                                            class="flex flex-col items-center p-2 border rounded-lg cursor-pointer text-center transition-all border-gray-200 bg-white hover:border-pink-200">
                                            <input type="radio" name="zone" value="{{ $zone->id }}"
                                                data-lp-zone class="hidden">
                                            <span
                                                class="text-[12px] font-bold text-gray-800 leading-tight">{{ $zone->name }}</span>
                                            <span
                                                class="text-[12px] text-pink-600 font-bold font-bengali mt-0.5">৳{{ number_format($zone->base_charge, 0) }}</span>
                                            @if ($zone->free_shipping_threshold)
                                                <span
                                                    class="text-[10px] text-pink-400 font-bold font-bengali mt-0.5">{{ 'Get Free Shipping, Buying more than' . $zone->free_shipping_threshold . '৳' }}</span>
                                            @endif

                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Order Summary --}}
                            <div
                                class="p-3 bg-gray-50 rounded-xl border border-dashed border-gray-200 space-y-1.5 text-sm">
                                <div class="flex justify-between text-gray-500">
                                    <span>পণ্যের মূল্য</span>
                                    <span data-lp-display="subtotal" class="font-semibold text-gray-800">—</span>
                                </div>
                                <div data-lp-display-row="tier-discount" style="display:none"
                                    class="flex justify-between text-green-600">
                                    <span>বাল্ক ডিসকাউন্ট</span>
                                    <span data-lp-display="tier-discount" class="font-semibold">—</span>
                                </div>
                                <div class="flex justify-between text-gray-500">
                                    <span>ডেলিভারি চার্জ</span>
                                    <span data-lp-zone-note class="text-gray-400 italic text-[10px]">এলাকা নির্বাচন
                                        করুন</span>
                                    <span data-lp-display="shipping" style="display:none"
                                        class="font-semibold text-gray-800 font-bengali"></span>
                                </div>
                                <div class="flex justify-between items-center border-t border-gray-200 pt-2 mt-1">
                                    <span class="font-black text-gray-800 text-sm">সর্বমোট</span>
                                    <span data-lp-display="total"
                                        class="text-xl font-black text-pink-600 font-bengali">—</span>
                                </div>
                            </div>

                            {{-- Error --}}
                            <div data-lp-error style="display:none"
                                class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-2 font-medium">
                            </div>

                            {{-- Submit --}}
                            <button data-lp-submit type="button"
                                class="w-full py-3 bg-pink-600 hover:bg-pink-700 text-white font-bold text-base rounded-xl shadow-md shadow-pink-200 transition-all active:scale-[.98] disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer">
                                <svg data-lp-submit-spinner style="display:none" class="animate-spin h-4 w-4 text-white"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <i class="fas fa-shopping-basket"></i>
                                <span data-lp-submit-label>অর্ডার কনফার্ম করুন</span>
                            </button>

                            <div class="flex justify-center gap-5 mt-1">
                                <span class="flex items-center gap-1.5 text-[10px] text-gray-400 uppercase tracking-wide">
                                    <i class="fas fa-lock text-pink-400"></i> নিরাপদ অর্ডার
                                </span>
                                <span class="flex items-center gap-1.5 text-[10px] text-gray-400 uppercase tracking-wide">
                                    <i class="fas fa-undo text-pink-400"></i> ক্যাশ অন ডেলিভারি
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Success Modal --}}
            <div data-lp-success-modal style="display:none"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4">
                <div class="bg-white p-6 md:p-8 rounded-2xl max-w-sm w-full text-center shadow-xl border border-pink-100">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-black text-gray-900 mb-2">অর্ডার সফল হয়েছে!</h2>
                    <p class="text-gray-500 text-sm mb-5">আমাদের প্রতিনিধি শীঘ্রই আপনার সাথে যোগাযোগ করবেন।</p>
                    <button onclick="window.location.href='/'"
                        class="w-full bg-pink-600 text-white py-2 rounded-lg font-bold hover:bg-pink-700 transition cursor-pointer text-sm">
                        ঠিক আছে
                    </button>
                </div>
            </div>
        </div>
    </section>
    <!-- ================= ORDER FROM SECTION END ================= -->

    <!-- ================= TESTIMONIALS SECTION ================= -->
    <section class="py-16 md:py-24 bg-white font-hind overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-pink-600 font-bold uppercase tracking-widest text-sm mb-3 block italic">রিভিউ ও
                    অভিমত</span>
                <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">
                    আমাদের ওপর <span class="text-pink-600">ক্রেতাদের আস্থা</span>
                </h2>
                <div class="w-20 h-1 bg-pink-600 mx-auto mt-6 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div
                    class="bg-pink-50/50 p-8 rounded-[2.5rem] relative group border border-transparent hover:border-pink-200 hover:bg-white hover:shadow-2xl transition-all duration-500">
                    <div class="flex text-amber-400 mb-4 text-sm">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-8 italic">
                        "আমি গত দুই মাস ধরে এই পিঙ্ক সল্ট ব্যবহার করছি। তরকারিতে সাধারণ
                        লবণের চেয়ে এটার স্বাদ অনেক ন্যাচারাল লাগে। আর প্যাকেজিং ছিল জাস্ট
                        প্রিমিয়াম!"
                    </p>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                            রা
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 leading-none">রাশেদ খান</h4>
                            <span class="text-xs text-gray-400 uppercase tracking-tighter">ভেরিফাইড কাস্টমার</span>
                        </div>
                    </div>
                    <i class="fas fa-quote-right absolute top-8 right-8 text-pink-200/20 text-5xl"></i>
                </div>

                <div
                    class="bg-pink-50/50 p-8 rounded-[2.5rem] relative group border border-transparent hover:border-pink-200 hover:bg-white hover:shadow-2xl transition-all duration-500">
                    <div class="flex text-amber-400 mb-4 text-sm">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-8 italic">
                        "ডাক্তারের পরামর্শে সাধারণ লবণ বাদ দিয়ে এটা শুরু করেছি। এখন
                        অ্যাসিডিটির সমস্যা অনেক কম। লবণের রঙ আর ফ্রেশনেস দেখলেই বোঝা যায়
                        এটা খাঁটি।"
                    </p>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                            স
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 leading-none">
                                সুমাইয়া আক্তার
                            </h4>
                            <span class="text-xs text-gray-400 uppercase tracking-tighter">ভেরিফাইড কাস্টমার</span>
                        </div>
                    </div>
                    <i class="fas fa-quote-right absolute top-8 right-8 text-pink-200/20 text-5xl"></i>
                </div>

                <div
                    class="bg-pink-50/50 p-8 rounded-[2.5rem] relative group border border-transparent hover:border-pink-200 hover:bg-white hover:shadow-2xl transition-all duration-500">
                    <div class="flex text-amber-400 mb-4 text-sm">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i
                            class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 leading-relaxed mb-8 italic">
                        "আমি এটা শুধু রান্নায় না, স্ক্রাব হিসেবেও ইউজ করি। ত্বকের জন্য
                        দারুণ কাজ করে। ডেলিভারিও খুব দ্রুত পেয়েছি, ধন্যবাদ।"
                    </p>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                            ন
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 leading-none">নাইম আহমেদ</h4>
                            <span class="text-xs text-gray-400 uppercase tracking-tighter">ভেরিফাইড কাস্টমার</span>
                        </div>
                    </div>
                    <i class="fas fa-quote-right absolute top-8 right-8 text-pink-200/20 text-5xl"></i>
                </div>
            </div>

            <div class="mt-16 text-center">
                <p class="text-gray-500 mb-6 font-hind">
                    আপনিও কি হিমালয়ান পিঙ্ক সল্টের স্বাদ নিতে চান?
                </p>
                <a href="#order-form"
                    class="inline-flex items-center gap-2 text-pink-600 font-bold border-b-2 border-pink-700 pb-1 hover:gap-4 transition-all">
                    আজই আপনার অর্ডারটি বুক করুন <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/landing-checkout.js') }}"></script>
@endpush
