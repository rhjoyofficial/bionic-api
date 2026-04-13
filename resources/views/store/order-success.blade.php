@extends('layouts.app')

@section('title', 'Order Confirmed - Invoice')

@section('content')
    <section class="bg-[#f0f5f1] min-h-screen py-10 px-4 font-bengali">
        <div class="w-full max-w-4xl mx-auto">

            {{-- Top Success Message (Hidden on Print/PDF) --}}
            <div class="text-center mb-8 no-print">
                <div
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary mb-4 shadow-lg shadow-green-900/20">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-primary font-bengali mb-2">অভিনন্দন!</h1>
                <p class="text-gray-700 font-bengali text-lg">আপনার অর্ডারটি সফলভাবে সম্পূর্ণ হয়েছে। ধন্যবাদ আমাদের সাথে
                    কেনাকাটা করার জন্য।</p>
            </div>

            {{-- Action Buttons (Hidden on Print/PDF) --}}
            <div class="flex justify-end mb-4 no-print gap-3">
                <button id="downloadBtn"
                    class="bg-primary hover:bg-secondary text-white px-5 py-2.5 rounded shadow-sm flex items-center gap-2 font-medium transition-all focus:ring-4 focus:ring-primary/30 cursor-not-allowed disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span>Download PDF</span>
                </button>
            </div>

            {{-- INVOICE CONTAINER (This is what gets converted to PDF & Printed) --}}
            <div id="invoice-content"
                class="bg-white p-8 md:p-12 rounded-xl shadow-lg border border-gray-100 print-container relative overflow-hidden">

                {{-- Decorative Top Border --}}
                <div class="absolute top-0 left-0 w-full h-2 bg-linear-to-r from-primary to-secondary"></div>

                {{-- Header Section --}}
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center border-b-2 border-gray-100 pb-6 mb-8 gap-6">
                    <div>
                        <a href="/" class="shrink-0 block mb-2">
                            <img src="{{ asset('assets/images/bionic-logo.png') }}" class="w-32 object-contain"
                                alt="Bionic Logo">
                        </a>
                        <div class="text-sm text-gray-600 space-y-0.5">
                            <p class="font-medium text-gray-800">Bionic Garden</p>
                            <p>65, Feroza Garden, Shahid Smriti Sarak</p>
                            <p>Barguna-8700</p>
                            <p>Phone: +8801334943785</p>
                            <p>Email: care@bionic.garden</p>
                        </div>
                    </div>
                    <div class="text-left md:text-right">
                        <h2 class="text-4xl font-black uppercase tracking-widest text-primary mb-1">Invoice</h2>
                        <p class="text-lg font-semibold text-gray-700">#{{ $order->order_number }}</p>
                        <p class="text-sm text-gray-500 mt-1">Date:
                            {{ $order->created_at ? $order->created_at->format('M d, Y') : date('M d, Y') }}</p>
                    </div>
                </div>

                {{-- Addresses Section --}}
                <div class="flex flex-col md:flex-row justify-between items-start mb-8 gap-8">
                    {{-- Billed/Shipped To --}}
                    <div class="w-full md:w-1/2 bg-gray-50 p-4 rounded-lg border border-gray-100">
                        <h4 class="text-xs font-bold text-secondary uppercase tracking-wider mb-2">Shipping Details</h4>
                        @if ($order->shippingAddress)
                            <div class="text-sm text-gray-800 space-y-1">
                                <p class="font-bold text-base text-primary">{{ $order->shippingAddress->customer_name }}
                                </p>
                                <p class="flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg> {{ $order->shippingAddress->customer_phone }}</p>
                                <p class="flex items-start gap-2 mt-1"><svg class="w-4 h-4 text-gray-400 shrink-0 mt-0.5"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    {{ collect([$order->shippingAddress->address_line, $order->shippingAddress->city])->filter()->join(', ') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Order Status Box --}}
                    <div class="w-full md:w-1/3 space-y-3">
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-sm text-gray-500 font-medium">Payment Method:</span>
                            <span
                                class="text-sm font-bold text-gray-800">{{ $order->payment_method === 'cod' ? 'Cash on Delivery' : ucfirst($order->payment_method) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-gray-100 pb-2">
                            <span class="text-sm text-gray-500 font-medium">Payment Status:</span>
                            <span
                                class="text-sm font-bold {{ $order->payment_status === 'paid' ? 'text-primary' : 'text-amber-600' }} uppercase">{{ $order->payment_status ?? 'Pending' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 font-medium">Order Status:</span>
                            <span
                                class="text-sm font-bold text-gray-800 capitalize">{{ str_replace('_', ' ', $order->status ?? 'order placed') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Items Table --}}
                <div class="overflow-hidden border border-gray-200 rounded-lg mb-6">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-secondary text-white">
                            <tr>
                                <th class="py-3 px-4 font-semibold text-center w-12">#</th>
                                <th class="py-3 px-4 font-semibold">Item Description</th>
                                <th class="py-3 px-4 font-semibold text-center w-24">Qty</th>
                                <th class="py-3 px-4 font-semibold text-right w-28">MRP</th>
                                <th class="py-3 px-4 font-semibold text-right w-28">Discount</th>
                                <th class="py-3 px-4 font-semibold text-right w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($order->items as $index => $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-3 px-4 text-center text-gray-600">{{ $index + 1 }}</td>
                                    <td class="py-3 px-4">
                                        <p class="font-bold text-gray-800 font-bengali">
                                            {{ $item->combo_name_snapshot ?: $item->product_name_snapshot }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">SKU: {{ $item->sku ?? 'N/A' }}</p>
                                    </td>
                                    <td class="py-3 px-4 text-center font-medium">{{ $item->quantity }}</td>
                                    <td class="py-3 px-4 text-right text-gray-600">
                                        {{ number_format(($item->total_price + ($item->discount ?? 0)) / $item->quantity, 2) }}
                                        ৳
                                    </td>
                                    <td class="py-3 px-4 text-right text-amber-600">
                                        {{ $item->discount > 0 ? '-' . number_format($item->discount, 2) . ' ৳' : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-gray-800">
                                        {{ number_format($item->total_price, 2) }} ৳
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-500 italic">No items found in this
                                        order.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Totals Section --}}
                <div class="flex flex-col md:flex-row justify-between items-end gap-6 mt-6">

                    {{-- Amount in Words --}}
                    <div class="w-full md:w-1/2 bg-gray-50/50 p-4 rounded-lg border border-gray-100">
                        <p class="text-xs font-bold text-secondary uppercase tracking-wide mb-1">Amount in Words:</p>
                        <p class="text-sm text-gray-700 italic font-medium">
                            {{ $order->amount_in_words ?? 'N/A' }}
                        </p>
                    </div>

                    {{-- Calculation Table --}}
                    <div class="w-full md:w-1/3">
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal</span>
                                <span class="font-medium">{{ number_format($order->subtotal, 2) }} ৳</span>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <span>Shipping Fee</span>
                                <span
                                    class="font-medium">{{ $order->shipping_cost == 0 ? 'Free' : number_format($order->shipping_cost, 2) . ' ৳' }}</span>
                            </div>
                            @if ($order->discount_total > 0)
                                <div class="flex justify-between text-primary">
                                    <span>Total Discount</span>
                                    <span class="font-medium">-{{ number_format($order->discount_total, 2) }} ৳</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center pt-3 border-t-2 border-gray-200">
                                <span class="font-bold text-gray-800 text-base">Grand Total</span>
                                <span class="font-black text-xl text-primary">{{ number_format($order->grand_total, 2) }}
                                    ৳</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Message --}}
                <div class="mt-12 pt-6 border-t border-gray-100 text-center">
                    <p class="text-sm font-medium text-secondary">Thank you for your business!</p>
                    <p class="text-xs text-gray-400 mt-1">If you have any questions about this invoice, please contact
                        care@bionic.garden</p>
                </div>

            </div>

            {{-- Bottom Call to Actions (Hidden on Print/PDF) --}}
            <div class="flex flex-col sm:flex-row justify-center gap-4 mt-8 no-print">
                <a href="{{ route('shop') }}"
                    class="flex items-center justify-center gap-2 bg-primary text-white px-8 py-3 rounded-full font-bold text-sm hover:bg-secondary transition-all shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    Continue Shopping
                </a>
                @auth
                    <a href="{{ route('customer.orders') }}"
                        class="flex items-center justify-center gap-2 border-2 border-primary text-primary px-8 py-3 rounded-full font-bold text-sm hover:bg-[#f0f5f1] transition-all">
                        My Orders
                    </a>
                @endauth
            </div>

        </div>
    </section>

    @push('styles')
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                #invoice-content,
                #invoice-content * {
                    visibility: visible;
                }

                #invoice-content {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    border: none !important;
                    box-shadow: none !important;
                    padding: 20px !important;
                    margin: 0 !important;
                }

                .no-print {
                    display: none !important;
                }

                @page {
                    margin: 0.5cm;
                    size: A4;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
            integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script>
            sessionStorage.removeItem('bionic_coupon');

            function downloadPDF() {
                const button = document.getElementById('downloadBtn');
                const originalHTML = button.innerHTML;

                button.innerHTML = `
        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Generating...</span>`;
                button.disabled = true;

                // Temporarily hide the logo to avoid CORS issues
                const logo = document.querySelector('#invoice-content img');
                const logoSrc = logo ? logo.src : null;
                if (logo) logo.style.visibility = 'hidden';

                const element = document.getElementById('invoice-content');

                const opt = {
                    margin: [0.4, 0.4, 0.4, 0.4],
                    filename: 'Invoice-{{ $order->order_number }}.pdf',
                    image: {
                        type: 'jpeg',
                        quality: 0.98
                    },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        logging: true, // Check browser console for errors
                        scrollY: -window.scrollY,
                        windowWidth: document.getElementById('invoice-content').scrollWidth,
                    },
                    jsPDF: {
                        unit: 'in',
                        format: 'a4',
                        orientation: 'portrait'
                    }
                };

                html2pdf().set(opt).from(element).save()
                    .then(() => {
                        if (logo) logo.style.visibility = 'visible';
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                    })
                    .catch((err) => {
                        console.error('PDF Error:', err);
                        if (logo) logo.style.visibility = 'visible';
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                        alert('PDF generation failed. Using print dialog instead.');
                        window.print();
                    });
            }
        </script>
    @endpush
@endsection
