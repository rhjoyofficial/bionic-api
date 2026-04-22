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
                        <i class="fas fa-mountain"></i> সরাসরি হিমালয় থেকে সংগৃহীত
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
                        class="relative z-10 w-full max-w-md mx-auto aspect-[4/5] rounded-[2.5rem] overflow-hidden shadow-2xl transition-transform duration-500 border-[10px] border-white bg-white">
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
                        <span class="w-10 h-[2px] bg-pink-600"></span>
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
                            কেন সাধারণ লবণের চেয়ে সেরা?
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
                                <span class="text-gray-700 font-bold text-sm md:text-base">প্রাকৃতিক উপায়ে আহরিত</span>
                                <div class="flex gap-16 md:gap-20 items-center">
                                    <i class="fas fa-times-circle text-red-400"></i>
                                    <i class="fas fa-check-circle text-pink-600"></i>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-8 bg-white p-4 rounded-2xl text-sm text-gray-500 italic text-center border border-pink-100">
                            "সাধারণ লবণ রিফাইন করার সময় এর প্রাকৃতিক গুণাগুণ নষ্ট হয়ে যায়,
                            কিন্তু পিঙ্ক সল্ট থাকে অপরিবর্তিত।"
                        </div>
                    </div>
                </div>
            </div>
            <!-- ############# START CTA BUTTON SECTION ############# -->
            <div class="flex justify-center mt-6">
                <button
                    onclick="
              document
                .getElementById('order-form')
                .scrollIntoView({ behavior: 'smooth' })
            "
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
                    আন্তর্জাতিক মানের নিশ্চয়তা
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
                <span class="text-pink-600">আপনার প্রয়োজন?</span>
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
                    pH ব্যালান্স নিয়ন্ত্রণ
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    শরীরের অম্লতা বা এসিডিটি কমিয়ে pH লেভেলের সঠিক ভারসাম্য বজায় রাখতে
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
                    ইলেকট্রোলাইট ভারসাম্য বজায় রেখে শরীরকে ভেতর থেকে হাইড্রেট রাখতে
                    সহায়তা করে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-pink-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-pink-100 rounded-2xl flex items-center justify-center text-pink-600 text-3xl mb-6 group-hover:bg-pink-600 group-hover:text-white transition-all">
                    <i class="fas fa-digestive-tract"></i>
                    <i class="fas fa-utensils"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">হজমে সহায়তা</h3>
                <p class="text-gray-600 leading-relaxed">
                    খাবারের পুষ্টি শোষণ বৃদ্ধি করে এবং প্রাকৃতিকভাবে হজম প্রক্রিয়াকে
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
                    রক্তচাপ নিয়ন্ত্রণ
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    সাধারণ লবণের তুলনায় সোডিয়াম কম থাকায় এটি রক্তচাপ নিয়ন্ত্রণে রাখতে
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
                    বাথ সল্ট হিসেবে ব্যবহারে ত্বকের উজ্জ্বলতা বাড়ে এবং মানসিক ক্লান্তি
                    দূর করে প্রশান্তি আনে।
                </p>
            </div>
        </div>

        <!-- ############# START CTA BUTTON SECTION ############# -->
        <div class="flex justify-center mt-6">
            <button
                onclick="
            document
              .getElementById('order-form')
              .scrollIntoView({ behavior: 'smooth' })
          "
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
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-[2/1]">
                        <img src="img/pink-salt-cooking.jpg" alt="Cooking with Pink Salt"
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
                        হিমালয়ান পিঙ্ক সল্ট। এটি কেবল খাবারের স্বাদই বাড়ায় না, বরং ৮৪টি
                        খনিজ উপাদান সরাসরি শরীরের পুষ্টি জোগায়।
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row-reverse items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-[2/1]">
                        <img src="img/pink-salt-detox.jpg" alt="Pink Salt Detox Drink"
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
                        সকালে হালকা কুসুম গরম পানিতে এক চিমটি পিঙ্ক সল্ট মিশিয়ে পান করুন।
                        এটি শরীরের বিষাক্ত পদার্থ বের করে দিয়ে আপনাকে ভেতর থেকে সতেজ ও
                        প্রাণবন্ত রাখতে সাহায্য করে।
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-[2/1]">
                        <img src="img/pink-salt-skin.jpg" alt="Pink Salt Skin Care"
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
                        নারিকেল তেলের সাথে পিঙ্ক সল্ট মিশিয়ে ব্যবহার করুন বডি স্ক্রাব
                        হিসেবে। এটি ত্বকের মৃত কোষ দূর করে ত্বককে কোমল রাখে এবং বাথ সল্ট
                        হিসেবে ক্লান্ত পেশীর ব্যথা দূর করতে কার্যকর।
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= ORDER FROM SECTION START ================= -->
    <!-- ================== CHECKOUT SECTION TEMPLATE 1 ================== -->
    <section class="py-16 md:py-24 bg-gray-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex flex-col lg:row gap-12 items-start lg:flex-row">
                <!-- LEFT: Product Info + Rules -->
                <div class="lg:w-1/2 w-full space-y-8">
                    <!-- Product Card -->
                    <div class="bg-red-50 p-8 rounded-[2.5rem] border border-red-100 relative overflow-hidden">
                        <div class="relative z-10">
                            <h3 class="text-2xl font-black text-gray-900 mb-4 font-hind">
                                বৈশাখের বিশেষ অফার!
                            </h3>
                            <p class="text-gray-600 mb-6 font-hind">
                                আমাদের প্রিমিয়াম ইলিশ আচার টেস্ট করে দেখতে আজই অর্ডার করুন।
                                ২টি কিনলে সারা বাংলাদেশে ডেলিভারি একদম ফ্রি!
                            </p>
                            <div class="flex items-center gap-6 bg-white p-4 rounded-2xl shadow-sm border border-red-100">
                                <div class="w-20 h-20 bg-red-50 rounded-xl overflow-hidden flex-shrink-0 group">
                                    <img src="img/hilsa-pickle.png" alt="Hilsa Pickle"
                                        class="w-full h-full object-cover group-hover:scale-105 duration-300 rounded-2xl" />
                                </div>
                                <div class="font-hind">
                                    <span class="block text-sm text-gray-400 font-bold uppercase">ইলিশ মাছের আচার (৪০০~
                                        গ্রাম)</span>
                                    <div class="flex items-baseline gap-2 font-noto">
                                        <span class="text-2xl font-black text-brand-600">৳৯৯৯</span>
                                        <span class="text-sm text-gray-400 line-through">৳১৩৫০</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <i class="fas fa-utensils absolute -bottom-6 -right-6 text-red-200/30 text-9xl"></i>
                    </div>

                    <!-- Rules -->
                    <div class="space-y-4 font-noto">
                        <h4 class="text-xl font-bold text-gray-900">
                            অর্ডারের নিয়মাবলী:
                        </h4>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-start gap-3">
                                <i class="fas fa-truck text-brand-600 mt-1"></i>
                                <span><strong>২টি বা তার বেশি</strong> অর্ডার করলে সারা বাংলাদেশে
                                    <strong>ডেলিভারি ফ্রি!</strong></span>
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-brand-600 mt-1"></i> ঢাকা
                                সিটিতে ডেলিভারি চার্জ ৬০ টাকা।
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-brand-600 mt-1"></i> ঢাকার
                                আশেপাশে (সাভার, কেরানীগঞ্জ, গাজীপুর) চার্জ ৯০ টাকা।
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-check-circle text-brand-600 mt-1"></i> ঢাকার
                                বাইরে কুরিয়ার চার্জ ১২০ টাকা।
                            </li>
                            <li class="flex items-start gap-3">
                                <i class="fas fa-hand-holding-usd text-brand-600 mt-1"></i>
                                ক্যাশ অন ডেলিভারি — পণ্য হাতে পেয়ে টাকা পরিশোধ করুন।
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- end left -->

                <!-- RIGHT: Order Form -->
                <div class="lg:w-1/2 w-full font-noto" id="checkout">
                    <div class="bg-white border border-red-100 rounded-3xl p-6 md:p-10 shadow-xl shadow-red-100/50">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-900">
                                অর্ডার কনফার্ম করুন
                            </h2>
                            <p class="text-brand-600 text-sm italic mt-2">
                                সঠিক তথ্য দিয়ে নিচের ফর্মটি পূরণ করুন।
                            </p>
                        </div>

                        <div class="space-y-4">
                            <!-- Name + Phone -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" id="custName" placeholder="আপনার নাম *"
                                    class="w-full px-5 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-1 focus:ring-red-200 outline-none text-sm transition-all" />
                                <input type="tel" id="custPhone" placeholder="মোবাইল নম্বর *"
                                    class="w-full px-5 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-1 focus:ring-red-200 outline-none text-sm transition-all" />
                            </div>

                            <!-- Address -->
                            <input type="text" id="custAddress"
                                placeholder="পূর্ণ ঠিকানা (বাসা নম্বর, রোড, এলাকা, জেলা) *"
                                class="w-full px-5 py-3 rounded-xl border border-gray-200 focus:border-brand-500 focus:ring-1 focus:ring-red-200 outline-none text-sm transition-all" />

                            <!-- Quantity Picker -->
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                <label
                                    class="qty-label flex items-center p-3 sm:p-4 border rounded-xl cursor-pointer border-brand-500 bg-red-50/50 transition-all">
                                    <input type="radio" name="qty" value="1" checked onchange="calculate()"
                                        class="w-4 h-4 text-brand-600 shrink-0" />
                                    <div class="ml-2 sm:ml-3 text-[11px] xs:text-xs sm:text-sm">
                                        <span class="block font-bold">১টি জার</span>
                                        <span class="text-gray-500">৳৯৯৯ + ডেলিভারি</span>
                                    </div>
                                </label>

                                <label
                                    class="qty-label flex items-center p-3 sm:p-4 border rounded-xl cursor-pointer border-gray-200 hover:bg-red-50 transition-all">
                                    <input type="radio" name="qty" value="2" onchange="calculate()"
                                        class="w-4 h-4 text-brand-600 shrink-0" />
                                    <div class="ml-2 sm:ml-3 text-[11px] xs:text-xs sm:text-sm">
                                        <span class="block font-bold">২টি জার (অফার)</span>
                                        <span class="text-green-600 font-bold">৳১৯৯৮ (ফ্রি)</span>
                                    </div>
                                </label>

                                <div
                                    class="col-span-2 p-4 bg-orange-50 border border-dashed border-orange-300 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-3 w-3 relative shrink-0">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                        </span>
                                        <p class="text-xs md:text-sm text-amber-900 text-center sm:text-left">
                                            আরও বেশি বা পাইকারি নিতে চান?
                                        </p>
                                    </div>
                                    <a href="tel:01334943783"
                                        class="text-sm md:text-base font-black text-pink-700 hover:underline">
                                        কল করুন: 01334 943783
                                    </a>
                                </div>
                            </div>

                            <!-- Delivery Zone — NO default selected -->
                            <div id="deliveryZoneSection"
                                class="bg-red-50/50 p-4 rounded-xl border border-red-100 transition-all">
                                <p class="text-sm font-bold text-gray-700 mb-1">
                                    ডেলিভারি এলাকা নির্বাচন করুন
                                    <span class="text-red-500">*</span>
                                </p>
                                <p id="zoneError" class="hidden text-xs text-red-600 mb-2 font-medium">
                                    ⚠ অনুগ্রহ করে একটি ডেলিভারি এলাকা নির্বাচন করুন।
                                </p>
                                <div class="grid grid-cols-3 gap-2">
                                    <label
                                        class="area-label flex flex-col items-center p-2 border rounded-lg cursor-pointer text-center bg-white hover:border-brand-500 transition-all border-gray-200">
                                        <input type="radio" name="area" value="60" class="hidden"
                                            onchange="selectArea(60, this)" />
                                        <span class="text-xs font-bold">ঢাকা সিটি</span>
                                        <span class="text-xs text-brand-600">৳৬০</span>
                                    </label>
                                    <label
                                        class="area-label flex flex-col items-center p-2 border rounded-lg cursor-pointer text-center bg-white hover:border-brand-500 transition-all border-gray-200">
                                        <input type="radio" name="area" value="90" class="hidden"
                                            onchange="selectArea(90, this)" />
                                        <span class="text-xs font-bold">ঢাকার আশেপাশে</span>
                                        <span class="text-xs text-brand-600">৳৯০</span>
                                    </label>
                                    <label
                                        class="area-label flex flex-col items-center p-2 border rounded-lg cursor-pointer text-center bg-white hover:border-brand-500 transition-all border-gray-200">
                                        <input type="radio" name="area" value="120" class="hidden"
                                            onchange="selectArea(120, this)" />
                                        <span class="text-xs font-bold">ঢাকার বাইরে</span>
                                        <span class="text-xs text-brand-600">৳১২০</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div class="p-4 bg-gray-50 rounded-xl border border-dashed border-gray-300 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>পণ্যের মূল্য:</span>
                                    <span>৳ <span id="subtotal">৯৯৯</span></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>ডেলিভারি চার্জ:</span>
                                    <span id="delivery-display" class="text-brand-600 font-bold">—</span>
                                </div>
                                <div class="flex justify-between border-t pt-2 mt-2 font-black text-lg text-brand-600">
                                    <span>সর্বমোট:</span>
                                    <span>৳ <span id="total">৯৯৯</span></span>
                                </div>
                            </div>

                            <!-- Submit -->
                            <button id="orderBtn" onclick="handleOrder()"
                                class="w-full py-5 bg-brand-600 hover:bg-brand-700 text-white font-bold text-xl rounded-2xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-3 font-hind">
                                অর্ডার কনফার্ম করুন <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- end right -->
            </div>
        </div>
    </section>
    <!-- ================== CHECKOUT SECTION TEMPLATE 2 ================== -->
    <section id="checkout" class="py-16 bg-gray-50 font-noto relative min-h-screen">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-10">
                <div class="space-y-6">
                    <div>
                        <h2 class="text-3xl font-black text-gray-900 mb-2">
                            পণ্য নির্বাচন করুন
                        </h2>
                        <p class="text-gray-500">
                            আপনার পছন্দের পণ্যটি টিক দিন, ওজন ও পরিমাণ নির্বাচন করুন
                        </p>
                    </div>

                    <div class="space-y-4" id="productList">
                        <!-- LOITTYA -->
                        <div onclick="autoCheckProduct('loittya', event)"
                            class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 transition hover:shadow-md hover:border-red-100 cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" id="check-loittya" onchange="toggleProduct('loittya')"
                                    onclick="event.stopPropagation()"
                                    class="w-5 h-5 accent-red-600 cursor-pointer flex-shrink-0" />
                                <img src="img/loittya.png"
                                    class="w-16 h-16 rounded-xl object-cover flex-shrink-0 hover:scale-105 transition-transform duration-300"
                                    alt="লুইট্টা" />
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-lg leading-tight">
                                        লুইট্টা শুঁটকি মাছ
                                    </h3>
                                    <p id="price-loittya" class="text-red-600 font-bold mt-0.5">
                                        ৳২৬০
                                    </p>
                                    <div class="flex gap-2 mt-2 flex-wrap" id="weights-loittya">
                                        <button
                                            onclick="
                          selectWeight('loittya', 0, this);
                          event.stopPropagation();
                        "
                                            data-active="true"
                                            class="weight-btn active-weight px-2 py-1 text-xs rounded border border-red-400 bg-red-50 text-red-700 font-semibold transition">
                                            ১২৫ গ্রাম
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('loittya', 1, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ০.৫ কেজি
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('loittya', 2, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ১.০ কেজি
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-red-50 p-2 rounded flex-shrink-0">
                                    <button
                                        onclick="
                        updateQty('loittya', -1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-gray-100 hover:bg-red-50 hover:text-red-600 rounded-xl font-bold transition">
                                        −
                                    </button>
                                    <span id="qty-loittya" class="w-6 text-center font-bold text-gray-700">১</span>
                                    <button
                                        onclick="
                        updateQty('loittya', 1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-red-600 hover:bg-red-700 text-white rounded font-bold transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- CHURI -->
                        <div onclick="autoCheckProduct('churi', event)"
                            class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 transition hover:shadow-md hover:border-red-100 cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" id="check-churi" onchange="toggleProduct('churi')"
                                    onclick="event.stopPropagation()"
                                    class="w-5 h-5 accent-red-600 cursor-pointer flex-shrink-0" />
                                <img src="img/churi.png"
                                    class="w-16 h-16 rounded-xl object-cover flex-shrink-0 hover:scale-105 transition-transform duration-300"
                                    alt="ছুরি" />
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-lg leading-tight">
                                        ছুরি শুঁটকি মাছ
                                    </h3>
                                    <p id="price-churi" class="text-red-600 font-bold mt-0.5">
                                        ৳৩৬০
                                    </p>
                                    <div class="flex gap-2 mt-2 flex-wrap" id="weights-churi">
                                        <button
                                            onclick="
                          selectWeight('churi', 0, this);
                          event.stopPropagation();
                        "
                                            data-active="true"
                                            class="weight-btn active-weight px-2 py-1 text-xs rounded border border-red-400 bg-red-50 text-red-700 font-semibold transition">
                                            ১২৫ গ্রাম
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('churi', 1, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ০.৫ কেজি
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('churi', 2, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ১.০ কেজি
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-red-50 p-2 rounded flex-shrink-0">
                                    <button
                                        onclick="
                        updateQty('churi', -1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-gray-100 hover:bg-red-50 hover:text-red-600 rounded-xl font-bold transition">
                                        −
                                    </button>
                                    <span id="qty-churi" class="w-6 text-center font-bold text-gray-700">১</span>
                                    <button
                                        onclick="
                        updateQty('churi', 1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-red-600 hover:bg-red-700 text-white rounded font-bold transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- MODHU -->
                        <div onclick="autoCheckProduct('modhu', event)"
                            class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 transition hover:shadow-md hover:border-red-100 cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" id="check-modhu" onchange="toggleProduct('modhu')"
                                    onclick="event.stopPropagation()"
                                    class="w-5 h-5 accent-red-600 cursor-pointer flex-shrink-0" />
                                <img src="img/modhu.png"
                                    class="w-16 h-16 rounded-xl object-cover flex-shrink-0 hover:scale-105 transition-transform duration-300"
                                    alt="মধু ফাইশ্যা" />
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-lg leading-tight">
                                        মধু ফাইশ্যা শুঁটকি মাছ
                                    </h3>
                                    <p id="price-modhu" class="text-red-600 font-bold mt-0.5">
                                        ৳২৪০
                                    </p>
                                    <div class="flex gap-2 mt-2 flex-wrap" id="weights-modhu">
                                        <button
                                            onclick="
                          selectWeight('modhu', 0, this);
                          event.stopPropagation();
                        "
                                            data-active="true"
                                            class="weight-btn active-weight px-2 py-1 text-xs rounded border border-red-400 bg-red-50 text-red-700 font-semibold transition">
                                            ১২৫ গ্রাম
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('modhu', 1, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ০.৫ কেজি
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('modhu', 2, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ১.০ কেজি
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-red-50 p-2 rounded flex-shrink-0">
                                    <button
                                        onclick="
                        updateQty('modhu', -1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-gray-100 hover:bg-red-50 hover:text-red-600 rounded-xl font-bold transition">
                                        −
                                    </button>
                                    <span id="qty-modhu" class="w-6 text-center font-bold text-gray-700">১</span>
                                    <button
                                        onclick="
                        updateQty('modhu', 1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-red-600 hover:bg-red-700 text-white rounded font-bold transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- KACHKI -->
                        <div onclick="autoCheckProduct('kachki', event)"
                            class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 transition hover:shadow-md hover:border-red-100 cursor-pointer">
                            <div class="flex items-center gap-4">
                                <input type="checkbox" id="check-kachki" onchange="toggleProduct('kachki')"
                                    onclick="event.stopPropagation()"
                                    class="w-5 h-5 accent-red-600 cursor-pointer flex-shrink-0" />
                                <img src="img/kachki.png"
                                    class="w-16 h-16 rounded-xl object-cover flex-shrink-0 hover:scale-105 transition-transform duration-300"
                                    alt="মৌরালা কাচকি" />
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-gray-900 text-lg leading-tight">
                                        মৌরালা কাচকি শুঁটকি
                                    </h3>
                                    <p id="price-kachki" class="text-red-600 font-bold mt-0.5">
                                        ৳২৮০
                                    </p>
                                    <div class="flex gap-2 mt-2 flex-wrap" id="weights-kachki">
                                        <button
                                            onclick="
                          selectWeight('kachki', 0, this);
                          event.stopPropagation();
                        "
                                            data-active="true"
                                            class="weight-btn active-weight px-2 py-1 text-xs rounded border border-red-400 bg-red-50 text-red-700 font-semibold transition">
                                            ১২৫ গ্রাম
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('kachki', 1, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ০.৫ কেজি
                                        </button>
                                        <button
                                            onclick="
                          selectWeight('kachki', 2, this);
                          event.stopPropagation();
                        "
                                            class="weight-btn px-2 py-1 text-xs rounded border border-gray-200 bg-white text-gray-600 hover:border-red-300 font-semibold transition">
                                            ১.০ কেজি
                                        </button>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 bg-red-50 p-2 rounded flex-shrink-0">
                                    <button
                                        onclick="
                        updateQty('kachki', -1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-gray-100 hover:bg-red-50 hover:text-red-600 rounded-xl font-bold transition">
                                        −
                                    </button>
                                    <span id="qty-kachki" class="w-6 text-center font-bold text-gray-700">১</span>
                                    <button
                                        onclick="
                        updateQty('kachki', 1);
                        event.stopPropagation();
                      "
                                        class="w-6 h-6 bg-red-600 hover:bg-red-700 text-white rounded font-bold transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 font-noto md:block hidden mt-6">
                        <h4 class="text-xl font-bold text-gray-900">
                            অর্ডারের নিয়মাবলী:
                        </h4>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-start gap-3 border-brand-500 bg-red-50/50">
                                <i class="fas fa-truck text-brand-600 mt-1"></i>
                                <span><strong>(২০০০ টাকা অর্ডার)</strong> করলে সারা বাংলাদেশে
                                    <strong>ডেলিভারি ফ্রি!</strong></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="w-full">
                    <div class="bg-white border border-red-100 rounded-3xl p-6 md:p-10 shadow-xl shadow-red-100/50">
                        <div class="text-center mb-8">
                            <h2 class="text-3xl font-bold text-gray-900">
                                অর্ডার কনফার্ম করুন
                            </h2>
                            <p class="text-red-600 text-sm italic mt-2">
                                সঠিক তথ্য দিয়ে নিচের ফর্মটি পূরণ করুন।
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <input type="text" id="custName" placeholder="আপনার নাম *"
                                    class="w-full px-5 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-1 focus:ring-red-200 outline-none text-sm transition-all" />
                                <input type="tel" id="custPhone" placeholder="মোবাইল নম্বর *"
                                    class="w-full px-5 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-1 focus:ring-red-200 outline-none text-sm transition-all" />
                            </div>

                            <input type="text" id="custAddress"
                                placeholder="পূর্ণ ঠিকানা (বাসা নম্বর, রোড, এলাকা, জেলা) *"
                                class="w-full px-5 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-1 focus:ring-red-200 outline-none text-sm transition-all" />

                            <div
                                class="p-4 bg-orange-50 border border-dashed border-orange-300 rounded-xl flex items-center justify-between mt-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-3 w-3 relative">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                                    </span>
                                    <p class="text-xs md:text-sm text-amber-900">
                                        আরও বেশি বা পাইকারি নিতে চান?
                                    </p>
                                </div>
                                <a href="tel:01334943783"
                                    class="text-sm md:text-base font-black text-pink-700 hover:underline">কল করুন: 01334
                                    943783</a>
                            </div>

                            <div id="deliveryZoneSection"
                                class="bg-red-50/50 p-4 rounded-xl border border-red-100 mt-4 transition-all">
                                <p class="text-sm font-bold text-gray-700 mb-1">
                                    ডেলিভারি এলাকা নির্বাচন করুন
                                    <span class="text-red-500">*</span>
                                    <span class="font-normal text-gray-400">(২০০০ টাকার উপর ফ্রি)</span>
                                </p>
                                <p id="zoneError" class="hidden text-xs text-red-600 mb-2 font-medium">
                                    ⚠ অনুগ্রহ করে একটি ডেলিভারি এলাকা নির্বাচন করুন।
                                </p>
                                <div class="grid grid-cols-3 gap-2">
                                    <label
                                        class="flex flex-col items-center p-2 border rounded-lg cursor-pointer text-center transition-all area-label border-gray-200 bg-white hover:border-red-400">
                                        <input type="radio" name="area" value="60" class="hidden"
                                            onchange="selectArea(60, this)" />
                                        <span class="text-xs font-bold">ঢাকা সিটি</span>
                                        <span class="text-xs text-red-600">৳৬০</span>
                                    </label>
                                    <label
                                        class="flex flex-col items-center p-2 border rounded-lg cursor-pointer text-center transition-all area-label border-gray-200 bg-white hover:border-red-400">
                                        <input type="radio" name="area" value="90" class="hidden"
                                            onchange="selectArea(90, this)" />
                                        <span class="text-xs font-bold">ঢাকার আশেপাশে</span>
                                        <span class="text-xs text-red-600">৳৯০</span>
                                    </label>
                                    <label
                                        class="flex flex-col items-center p-2 border rounded-lg cursor-pointer text-center transition-all area-label border-gray-200 bg-white hover:border-red-400">
                                        <input type="radio" name="area" value="120" class="hidden"
                                            onchange="selectArea(120, this)" />
                                        <span class="text-xs font-bold">সারাদেশ</span>
                                        <span class="text-xs text-red-600">৳১২০</span>
                                    </label>
                                </div>
                            </div>

                            <!-- ORDER SUMMARY BOX -->
                            <div
                                class="p-4 bg-gray-50 rounded-xl border border-dashed border-gray-300 space-y-2 text-sm mt-4">
                                <!-- Per-product breakdown — populated by calculate() -->
                                <div id="cart-breakdown" class="space-y-1 pb-2 border-b border-gray-200 empty:hidden">
                                </div>

                                <div class="flex justify-between">
                                    <span class="text-gray-500">পণ্যের মূল্য:</span>
                                    <span class="font-semibold">৳ <span id="subtotal">০</span></span>
                                </div>
                                <div class="flex justify-between">
                                    <span>ডেলিভারি চার্জ:</span>
                                    <span id="shipping-display" class="text-red-600 font-bold">—</span>
                                </div>
                                <div class="flex justify-between border-t pt-2 mt-2 font-black text-lg text-red-600">
                                    <span>সর্বমোট:</span>
                                    <span>৳ <span id="total">০</span></span>
                                </div>
                            </div>

                            <button id="orderBtn" onclick="handleOrder()"
                                class="w-full mt-4 py-5 bg-red-600 hover:bg-red-700 text-white font-bold text-xl rounded-2xl shadow-lg transition-all active:scale-95 flex items-center justify-center gap-3">
                                অর্ডার কনফার্ম করুন
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-4 font-noto block md:hidden mt-6">
                        <h4 class="text-xl font-bold text-gray-900">
                            অর্ডারের নিয়মাবলী:
                        </h4>
                        <ul class="space-y-3 text-gray-600">
                            <li class="flex items-start gap-3 border-brand-500 bg-red-50/50">
                                <i class="fas fa-truck text-brand-600 mt-1"></i>
                                <span><strong>(২০০০ টাকা অর্ডার)</strong> করলে সারা বাংলাদেশে
                                    <strong>ডেলিভারি ফ্রি!</strong></span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- SUCCESS MODAL -->
        <div id="successModal"
            class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 px-4">
            <div
                class="bg-white p-8 rounded-3xl max-w-sm w-full text-center shadow-2xl transform transition-all border border-red-100">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-black text-gray-900 mb-2">
                    অর্ডার সফল হয়েছে!
                </h2>
                <p class="text-gray-500 mb-6">
                    আমাদের প্রতিনিধি দ্রুতই আপনার সাথে যোগাযোগ করবেন।
                </p>
                <button onclick="window.location.reload()"
                    class="w-full bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition">
                    ঠিক আছে
                </button>
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
                        লবণের চেয়ে এটার স্বাদ অনেক ন্যাচারাল লাগে। আর প্যাকেজিং ছিল জাস্ট
                        প্রিমিয়াম!"
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
                        "ডাক্তারের পরামর্শে সাধারণ লবণ বাদ দিয়ে এটা শুরু করেছি। এখন
                        অ্যাসিডিটির সমস্যা অনেক কম। লবণের রঙ আর ফ্রেশনেস দেখলেই বোঝা যায়
                        এটা খাঁটি।"
                    </p>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-full bg-pink-100 flex items-center justify-center text-pink-600 font-bold">
                            স
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900 leading-none">
                                সুমাইয়া আক্তার
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
                        "আমি এটা শুধু রান্নায় না, স্ক্রাব হিসেবেও ইউজ করি। ত্বকের জন্য
                        দারুণ কাজ করে। ডেলিভারিও খুব দ্রুত পেয়েছি, ধন্যবাদ।"
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
                    আপনিও কি হিমালয়ান পিঙ্ক সল্টের স্বাদ নিতে চান?
                </p>
                <a href="#order-form"
                    class="inline-flex items-center gap-2 text-pink-600 font-bold border-b-2 border-pink-700 pb-1 hover:gap-4 transition-all">
                    আজই আপনার অর্ডারটি বুক করুন <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>
@endsection
