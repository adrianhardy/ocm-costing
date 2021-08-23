<ul class="nav navbar-nav mr-auto">
    @auth
        <li class="nav-item active">
            <a class="nav-link" href="{{ route('home') }}">
                <b-icon-house scale="0.8"></b-icon-house>
                Home
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('products.index') }}">
                <b-icon-box-seam scale="0.8"></b-icon-box-seam>
                Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('sales-orders.index') }}">
                <b-icon-box-seam scale="0.8"></b-icon-box-seam>
                Sales Orders
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('users.index') }}">
                <b-icon-box-seam scale="0.8"></b-icon-box-seam>
                Users
            </a>
        </li>
    @endauth
</ul>
