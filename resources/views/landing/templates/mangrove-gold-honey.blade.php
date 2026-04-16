@extends('layouts.app')

@section('title', $landing->meta_title ?? $product->name)
@section('meta_description', $landing->meta_description ?? $product->short_description)

@section('content')
    <!-- ================== HERO SECTION ================ -->
    <section class="relative bg-linear-to-br from-white to-orange-50/50 pt-20 pb-12 md:pt-32 md:pb-24 overflow-hidden">
        <div class="absolute top-0 right-0 -translate-y-1/4 translate-x-1/4 w-96 h-96 bg-primary/5 rounded-full blur-3xl">
        </div>

        <div class="max-w-8xl mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="md:w-1/2 text-center md:text-left">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-sm font-bold mb-6 animate-bounce">
                        <i class="fas fa-award"></i> ১০০% খাঁটি ও প্রাকৃতিক
                    </div>

                    <h1 class="text-4xl md:text-6xl font-black text-gray-900 leading-tight mb-6 font-hind">
                        সুন্দরবনের সেরা উপহার <br />
                        <span class="text-primary italic">Mangrove Gold Honey</span>
                    </h1>

                    <p class="text-lg md:text-xl text-gray-600 mb-8 font-hind leading-relaxed max-w-xl">
                        "সুস্থ থাকুন. সুস্থ জীবন উপভোগ করুন।" সরাসরি সুন্দরবনের গহীন অরণ্য
                        থেকে সংগৃহীত প্রাকৃতিক পুষ্টিগুণে ভরপুর প্রিমিয়াম মধু।
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-4 justify-center md:justify-start mb-8">
                        <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-primary/20">
                            <span class="block text-xs text-gray-500 font-bold uppercase tracking-wider">পরিমাণ: ৫০৫~
                                গ্রাম</span>
                            <span class="text-2xl font-black text-gray-900">৳৯৯০
                                <span class="text-sm font-normal text-gray-400 line-through font-noto">৳১২৫০</span></span>
                        </div>

                        <a href="#order-form"
                            class="w-full sm:w-auto px-10 py-5 bg-primary hover:bg-emerald-800 text-white font-bold text-lg rounded-2xl shadow-xl shadow-primary/20 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3">
                            <i class="fas fa-shopping-cart"></i> এখনই অর্ডার করুন
                        </a>
                    </div>

                    <div class="flex items-center gap-6 justify-center md:justify-start text-sm text-gray-500 font-hind">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-truck text-primary"></i> দ্রুত ডেলিভারি
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-undo text-primary"></i> রিফান্ড গ্যারান্টি
                        </div>
                    </div>
                </div>

                <div class="md:w-1/2 relative">
                    <div
                        class="relative z-10 w-full max-w-md mx-auto aspect-square rounded-[3rem] overflow-hidden shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-500 border-8 border-white">
                        <img src="img/mangrove-honey.jpeg" alt="Mangrove Gold Honey" class="w-full h-full object-cover" />
                    </div>

                    <div
                        class="absolute -bottom-6 -left-6 md:left-0 z-20 bg-accent text-white p-5 rounded-3xl shadow-xl animate-pulse text-center font-inter">
                        <span class="block text-2xl font-black font-inter">Premium</span>
                        <span class="text-xs uppercase tracking-tighter font-inter">Quality Honey</span>
                    </div>

                    <i class="fas fa-leaf absolute -top-10 right-10 text-primary/10 text-6xl rotate-45 hidden md:block"></i>
                </div>
            </div>
        </div>
    </section>
    <!-- ================= PRODUCT INTRODUCTION SECTION ================= -->
    <section class="py-16 md:py-24 bg-white overflow-hidden font-hind">
        <div class="max-w-8xl mx-auto px-6">
            <div class="flex flex-col md:flex-row items-center gap-12 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="relative overflow-hidden rounded-[2.5rem] shadow-2xl border-b-8 border-primary/20">
                        <img src="img/mangrove-forest.jpeg" alt="Sundarban Mangrove Forest"
                            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 aspect-4/5 md:aspect-auto" />
                        <div
                            class="absolute inset-0 bg-linear-to-t from-black/60 via-transparent to-transparent opacity-60">
                        </div>

                        <div class="absolute bottom-6 left-6 text-white">
                            <span class="text-xs uppercase tracking-widest font-bold opacity-80">উৎস:</span>
                            <p class="text-xl font-bold">সুন্দরবনের গহীন অরণ্য</p>
                        </div>
                    </div>

                    <div
                        class="hidden md:block absolute -top-8 -right-8 w-32 h-32 bg-primary/10 rounded-full blur-2xl animate-pulse">
                    </div>
                </div>

                <div class="md:w-1/2 space-y-6">
                    <div class="flex items-center gap-3 text-primary font-bold tracking-wide uppercase text-sm">
                        <span class="w-10 h-0.5 bg-primary"></span>
                        বিশুদ্ধতার গল্প
                    </div>

                    <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">
                        প্রকৃতির শ্রেষ্ঠ দান: <br />
                        <span class="text-primary">ম্যানগ্রোভ গোল্ড হানি</span>
                    </h2>

                    <div class="space-y-5 text-gray-600 text-lg leading-relaxed text-justify md:text-left">
                        <p>
                            সুন্দরবন বিশ্বের বৃহত্তম ম্যানগ্রোভ বন, যা প্রায় ১০,০০০ বর্গ
                            কিলোমিটার এলাকা জুড়ে বিস্তৃত। এখানকার বুনো ফুল থেকে মৌমাছিরা
                            প্রাকৃতিকভাবে যে মধু তৈরি করে, তা এর অকৃত্রিম বিশুদ্ধতা ও উচ্চ
                            পুষ্টিগুণের জন্য বিশ্বজুড়ে বিখ্যাত।
                        </p>
                        <p class="bg-primary/5 p-5 border-l-4 border-primary rounded-r-2xl italic italic-none">
                            "Mangrove Gold Honey-তে রয়েছে অ্যান্টিঅক্সিডেন্ট ও প্রাকৃতিক
                            এনজাইম। এছাড়াও রয়েছে স্বাস্থ্যের জন্য উপকারী অসংখ্য
                            পুষ্টিগুণ।"
                        </p>
                        <p>
                            আমরা কোনো প্রকার কৃত্রিম ফ্লেভার বা প্রিজারভেটিভ ব্যবহার করি না।
                            সরাসরি মৌয়ালদের কাছ থেকে সংগৃহীত এই মধু আপনার শরীরের জন্য
                            সম্পূর্ণ নিরাপদ এবং অত্যন্ত কার্যকর।
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <span class="font-bold text-gray-800">১০০% অর্গানিক</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                                <i class="fas fa-vial"></i>
                            </div>
                            <span class="font-bold text-gray-800">নো অ্যাডেড সুগার</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ================= CERTIFICATIONS SECTION ================= -->
    <section id="certifications" class="pb-6 md:pb-10 bg-white">
        <div class="max-w-8xl mx-auto px-6">
            <div class="text-center mb-6">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4 font-hind">
                    আন্তর্জাতিক মানের নিশ্চয়তা
                </h2>
                <div class="w-24 h-1.5 bg-primary mx-auto rounded-full mb-6"></div>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto font-hind">
                    ম্যানগ্রোভ গোল্ড হানি আমরা সরাসরি আপনার কাছে পৌঁছে দিচ্ছি। আমাদের
                    বিশ্বাসযোগ্যতার সনদসমূহ:
                </p>
            </div>

            <div class="flex flex-wrap justify-center items-center gap-6 md:gap-10 lg:gap-20">
                <!-- ISO -->
                <div
                    class="group flex flex-col items-center text-center transition duration-300 hover:scale-105 hover:-translate-y-1">
                    <img src="img/cert-iso-22000.png" alt="ISO 22000 Certified"
                        class="h-16 md:h-20 object-contain transition-transform duration-300 group-hover:scale-110"
                        loading="lazy" />
                    <span class="mt-3 text-sm font-medium text-gray-800 font-inter">ISO 22000</span>
                </div>

                <!-- Halal -->
                <div
                    class="group flex flex-col items-center text-center transition duration-300 hover:scale-105 hover:-translate-y-1">
                    <img src="img/cert-halal.png" alt="Halal Certified"
                        class="h-16 md:h-20 object-contain transition-transform duration-300 group-hover:scale-110"
                        loading="lazy" />
                    <span class="mt-3 text-sm font-medium text-gray-800 font-inter">Halal Certified</span>
                </div>

                <!-- HACCP -->
                <div
                    class="group flex flex-col items-center text-center transition duration-300 hover:scale-105 hover:-translate-y-1">
                    <img src="img/cert-haccp.png" alt="HACCP Certified"
                        class="h-16 md:h-20 object-contain transition-transform duration-300 group-hover:scale-110"
                        loading="lazy" />
                    <span class="mt-3 text-sm font-medium text-gray-800 font-inter">HACCP</span>
                </div>

                <!-- GMP Quality -->
                <div
                    class="group flex flex-col items-center text-center transition duration-300 hover:scale-105 hover:-translate-y-1">
                    <img src="img/gmp-quality.png" alt="GMP Quality"
                        class="h-16 md:h-20 object-contain transition-transform duration-300 group-hover:scale-110"
                        loading="lazy" />
                    <span class="mt-3 text-sm font-medium text-gray-800 font-inter">GMP Quality</span>
                </div>

            </div>
        </div>
    </section>
    <!-- ================= KEY BENEFITS SECTION ================= -->
    <section class="t-16 md:pt-24 bg-gray-50/50 font-hind">
        <div class="max-w-8xl mx-auto px-6 text-center mb-16">
            <span class="text-primary font-bold uppercase tracking-widest text-sm mb-3 block">হেলথ বেনিফিটস</span>
            <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight">
                প্রতিদিন মধু খাওয়ার
                <span class="text-primary">অবিশ্বাস্য উপকারিতা</span>
            </h2>
            <div class="w-20 h-1.5 bg-primary mx-auto mt-6 rounded-full"></div>
        </div>

        <div class="max-w-8xl mx-auto px-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-3xl mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    রোগ প্রতিরোধ ক্ষমতা
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    প্রতিদিন সকালে মধু সেবন করলে শরীরে অ্যান্টি-অক্সিডেন্ট বৃদ্ধি পায়,
                    যা ঠান্ডা-কাশি ও ভাইরাল ইনফেকশন প্রতিরোধে দারুণ কাজ করে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-3xl mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all">
                    <i class="fas fa-weight"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    ওজন ও মেদ নিয়ন্ত্রণ
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    হালকা গরম পানিতে লেবু ও মধু মিশিয়ে নিয়মিত খেলে শরীরের বাড়তি মেদ
                    দ্রুত ঝরে যায় এবং লিভারের টক্সিন দূর করে শরীর সুস্থ রাখে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-3xl mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    হৃদরোগের ঝুঁকি হ্রাস
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    মধু রক্তনালী পরিষ্কার রাখতে সাহায্য করে এবং ক্ষতিকর কোলেস্টেরল কমিয়ে
                    হৃদপিণ্ডের কার্যকারিতা ও রক্ত সঞ্চালন স্বাভাবিক রাখে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-3xl mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all">
                    <i class="fa-solid fa-face-grin-stars"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    উজ্জ্বল ও সুন্দর ত্বক
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    ত্বকে মধুর ব্যবহারে মুখের কালো দাগ দূর হয়। এটি ত্বকের আর্দ্রতা ধরে
                    রেখে প্রাকৃতিক লাবণ্য ফিরিয়ে আনে এবং বুড়িয়ে যাওয়া রোধ করে।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-3xl mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">এনার্জি বুস্ট</h3>
                <p class="text-gray-600 leading-relaxed">
                    দুধ ও মধুর মিশ্রণ টেস্টোস্টেরন (Testosterone) বৃদ্ধিতে সাহায্য করে,
                    যা পুরুষের পেশি গঠন, প্রজনন ক্ষমতা ও স্ট্যামিনা বাড়াতে সহায়ক।
                </p>
            </div>

            <div
                class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 hover:shadow-xl hover:border-primary/20 transition-all duration-300 group">
                <div
                    class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary text-3xl mb-6 group-hover:scale-110 group-hover:bg-primary group-hover:text-white transition-all">
                    <i class="fas fa-lungs-virus"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    হজম ও অনিদ্রা দূরীকরণ
                </h3>
                <p class="text-gray-600 leading-relaxed">
                    মধু কোষ্ঠকাঠিন্য দূর করে হজমশক্তি বাড়াতে কার্যকর। ঘুমানোর আগে মধু
                    খেলে তা স্নায়ু শিথিল করে এবং গভীর ও আরামদায়ক ঘুম নিশ্চিত করে।
                </p>
            </div>
        </div>
    </section>

    <!-- ================= VERSATILE USES SECTION ================= -->
    <section class="py-16 md:py-24 bg-white font-hind overflow-hidden">
        <div class="max-w-8xl mx-auto px-6 mb-16 text-center">
            <h2 class="text-3xl md:text-5xl font-black text-gray-900 leading-tight mb-4">
                মধুর
                <span class="text-primary italic underline underline-offset-8 decoration-primary/30">
                    বহুমুখী ব্যবহার</span>
            </h2>
            <p class="text-gray-500 max-w-2xl mx-auto text-lg mt-6">
                দৈনন্দিন জীবনে আমাদের ম্যানগ্রোভ গোল্ড মধু আপনার সুস্বাস্থ্য ও
                সৌন্দর্যের সঙ্গী হতে পারে।
            </p>
        </div>

        <div class="max-w-6xl mx-auto px-6 space-y-20 font-hind">
            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-2/1">
                        <img src="img/honey-tea.jpeg" alt="Honey in Tea"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    </div>
                    <div class="absolute -bottom-4 -right-4 bg-primary text-white p-4 rounded-2xl hidden md:block">
                        <i class="fas fa-mug-hot text-2xl"></i>
                    </div>
                </div>
                <div class="md:w-1/2 space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900">
                        চিনির স্বাস্থ্যকর বিকল্প
                    </h3>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        চা, দুধ বা যেকোনো ডেজার্টে সাদা চিনি বাদ দিয়ে আমাদের মধু ব্যবহার
                        করুন। এটি কেবল স্বাস্থ্যকরই নয়, আপনার খাবারের স্বাদকেও করবে
                        বহুগুণ সুস্বাদু।
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row-reverse items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-2/1">
                        <img src="img/honey-mask.jpeg" alt="Honey Skin Mask"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    </div>
                    <div class="absolute -bottom-4 -left-4 bg-accent text-white p-4 rounded-2xl hidden md:block">
                        <i class="fas fa-smile-beam text-2xl"></i>
                    </div>
                </div>
                <div class="md:w-1/2 space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900">
                        প্রাকৃতিক রূপচর্চা
                    </h3>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        মধুকে আপনি প্রাকৃতিক **Moisturizing Mask** হিসেবে ব্যবহার করতে
                        পারেন। এটি ত্বকের আর্দ্রতা ধরে রাখে এবং রুক্ষতা দূর করে ত্বককে
                        কোমল ও উজ্জ্বল করে তোলে।
                    </p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-20">
                <div class="md:w-1/2 relative group">
                    <div class="rounded-3xl overflow-hidden shadow-xl aspect-2/1">
                        <img src="img/honey-remedy.jpeg" alt="Honey Remedy"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700" />
                    </div>
                    <div class="absolute -bottom-4 -right-4 bg-primary text-white p-4 rounded-2xl hidden md:block">
                        <i class="fas fa-briefcase-medical text-2xl"></i>
                    </div>
                </div>
                <div class="md:w-1/2 space-y-4 text-center md:text-left">
                    <h3 class="text-2xl font-black text-gray-900">
                        প্রাথমিক সুরক্ষা ও চিকিৎসা
                    </h3>
                    <p class="text-gray-600 text-lg leading-relaxed">
                        ঠান্ডা এবং ফ্লু প্রতিরোধে মধুর কার্যকারিতা সর্বজনবিদিত। এছাড়াও
                        দুর্বল ও ভঙ্গুর নখের চিকিৎসায় মধুর ব্যবহার নখকে শক্ত ও
                        স্বাস্থ্যোজ্জ্বল করতে সাহায্য করে।
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
