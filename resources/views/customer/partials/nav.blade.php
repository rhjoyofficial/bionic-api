<aside class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm h-fit">
    <h2 class="text-lg font-bold text-gray-900 mb-4">My Account</h2>
    <nav class="space-y-1">
        <a href="{{ route('customer.dashboard') }}"
            class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('customer.dashboard') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
            Dashboard
        </a>
        <a href="{{ route('customer.orders') }}"
            class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('customer.orders', 'customer.order-details') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
            Orders
        </a>
        <a href="{{ route('customer.profile') }}"
            class="block px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('customer.profile') ? 'bg-green-50 text-green-700' : 'text-gray-700 hover:bg-gray-50' }}">
            Profile
        </a>
    </nav>
</aside>
