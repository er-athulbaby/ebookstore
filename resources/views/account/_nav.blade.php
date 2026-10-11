<div class="list-group mb-4">
    <a href="{{ route('account.dashboard') }}" class="list-group-item list-group-item-action @if(request()->routeIs('account.dashboard')) active @endif">Dashboard</a>
    <a href="{{ route('account.orders') }}" class="list-group-item list-group-item-action @if(request()->routeIs('account.orders*')) active @endif">My Orders</a>
    <a href="{{ route('account.downloads') }}" class="list-group-item list-group-item-action @if(request()->routeIs('account.downloads')) active @endif">My Downloads</a>
    <a href="{{ route('account.wallet') }}" class="list-group-item list-group-item-action @if(request()->routeIs('account.wallet')) active @endif">Wallet</a>
    <a href="{{ route('wishlist.index') }}" class="list-group-item list-group-item-action @if(request()->routeIs('wishlist.*')) active @endif">Wishlist</a>
    <a href="{{ route('account.addresses') }}" class="list-group-item list-group-item-action @if(request()->routeIs('account.addresses')) active @endif">Addresses</a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="list-group-item list-group-item-action w-100 text-start text-danger">Logout</button>
    </form>
</div>
