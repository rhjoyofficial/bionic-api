@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    <section class="max-w-7xl mx-auto px-4 md:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            @include('customer.partials.nav')

            <div class="lg:col-span-3 bg-white border border-gray-100 rounded-2xl p-6 shadow-sm">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">My Profile</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-xs uppercase text-gray-500">Name</p>
                        <p class="text-base font-semibold text-gray-900 mt-1">{{ $user->name }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-xs uppercase text-gray-500">Email</p>
                        <p class="text-base font-semibold text-gray-900 mt-1">{{ $user->email ?: 'Not provided' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-xs uppercase text-gray-500">Phone</p>
                        <p class="text-base font-semibold text-gray-900 mt-1">{{ $user->phone ?: 'Not provided' }}</p>
                    </div>
                    <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                        <p class="text-xs uppercase text-gray-500">Referral Code</p>
                        <p class="text-base font-semibold text-gray-900 mt-1">{{ $user->referral_code ?: 'Not generated' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
