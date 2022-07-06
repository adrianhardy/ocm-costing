<ul class="nav navbar-nav mr-auto">
    @auth
        @auth
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home') }}">
                <b-icon-house scale="0.8"></b-icon-house>
                Home
            </a>
        </li>
        @endauth
        @role('super|admin|supplier')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('products.index') }}">
                <b-icon-flower-3 scale="0.8"></b-icon-flower-3>
                Products
            </a>
        </li>
        @endrole
        @role('super|admin')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('shipments.index') }}">
                <b-icon-box-seam scale="0.8"></b-icon-box-seam>
                Shipments
            </a>
        </li>
        @endrole
        @role('super|admin|supplier')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('sales-orders.index') }}">
                <b-icon-file-ruled scale="0.8"></b-icon-file-ruled>
                Sales Orders
            </a>
        </li>
        @endrole
        @role('super|pricing|admin|pricing_approver')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('price-list.index') }}">
                <b-icon-file-ruled scale="0.8"></b-icon-file-ruled>
                Price List
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
        @role('super')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('users.index') }}">
                <b-icon-person scale="0.8"></b-icon-person>
                Users
            </a>
        </li>
        @endrole
    @endauth
</ul>
