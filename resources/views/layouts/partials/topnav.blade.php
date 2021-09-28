<ul class="nav navbar-nav mr-auto">
    @auth
        <li class="nav-item active">
            <a class="nav-link" href="{{ route('home') }}">
                <b-icon-house scale="0.8"></b-icon-house>
                Home
            </a>
        </li>
        @role('super|admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('products.index') }}">
                <b-icon-box-seam scale="0.8"></b-icon-box-seam>
                Products
            </a>
        </li>
        @endrole
        @role('super|admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('sales-orders.index') }}">
                <b-icon-file-ruled scale="0.8"></b-icon-file-ruled>
                Sales Orders
            </a>
        </li>
        @endrole
        @role('super|pricing|admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('price-list.index') }}">
                <b-icon-file-ruled scale="0.8"></b-icon-file-ruled>
                Price List
            </a>
        </li>
        @endrole
        @role('super|admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('users.index') }}">
                <b-icon-person scale="0.8"></b-icon-person>
                Users
            </a>
        </li>
        @endrole
        @role('super')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('suppliers.index') }}">
                <b-icon-truck scale="0.8"></b-icon-truck>
                Suppliers
            </a>
        </li>
        @endrole
        @role('super|admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('factories.index') }}">
                <b-icon-building scale="0.8"></b-icon-building>
                Factories
            </a>
        </li>
        @endrole
    @endauth
</ul>
