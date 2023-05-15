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
        @can('__uk')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('order-acknowledgements.index') }}">
                    <b-icon-list-task scale="0.8"></b-icon-list-task>
                    Order Acknowledgements
                </a>
            </li>
        @else
            @role('super|admin')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('sales-orders.index', ['stage' => 'quote']) }}">
                    <b-icon-list-task scale="0.8"></b-icon-list-task>
                    Quotes
                </a>
            </li>
            @else
            <li class="nav-item">
                <a class="nav-link disabled" href="#" disabled>
                    <b-icon-list-task scale="0.8"></b-icon-list-task>
                    Quotes
                </a>
            </li>
            @endrole
        @endcan

        @can('__china')
                @role('super|admin|supplier')
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('sales-orders.index', ['stage' => 'order']) }}">
                            <b-icon-list-check scale="0.8"></b-icon-list-check>
                            Orders
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a class="nav-link disabled" href="#" disabled>
                            <b-icon-list-check scale="0.8"></b-icon-list-check>
                            Orders
                        </a>
                    </li>
                @endrole
        @endcan
        @can('__uk')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('invoices.index') }}">
                    <b-icon-box-seam scale="0.8"></b-icon-box-seam>
                    Invoices
                </a>
            </li>
        @else
            @role('super|admin')
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('shipments.index') }}">
                        <b-icon-box-seam scale="0.8"></b-icon-box-seam>
                        Shipments
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link disabled" href="#" disabled>
                        <b-icon-box-seam scale="0.8" disabled></b-icon-box-seam>
                        Shipments
                    </a>
                </li>
            @endrole
        @endcan

        @role('super|pricing|pricing_approver|admin|supplier')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('products.index') }}">
                <b-icon-flower-3 scale="0.8"></b-icon-flower-3>
                Products
            </a>
        </li>
        @else
        <li class="nav-item">
            <a class="nav-link disabled" href="#" disabled>
                <b-icon-flower-3 scale="0.8" disabled></b-icon-flower-3>
                Products
            </a>
        </li>
        @endrole
        @role('super|pricing|pricing_approver')
        <li class="nav-item">
            <a class="nav-link" href="{{ route('price-list.index') }}">
                <b-icon-file-ruled scale="0.8"></b-icon-file-ruled>
                Pricing
            </a>
        </li>
        @else
        <li class="nav-item">
            <a class="nav-link disabled" href="#" disabled>
                <b-icon-file-ruled scale="0.8" disabled></b-icon-file-ruled>
                Pricing
            </a>
        </li>
        @endrole
        @can('__uk')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('daily-pricing.index') }}">
                    <b-icon-file-ruled scale="0.8"></b-icon-file-ruled>
                    Daily Pricing
                </a>
            </li>
        @endcan



        @can('__china')
            @role('super|admin|pricing|pricing_approver')
            <li class="nav-item">
                <a class="nav-link" href="{{ route('documents.index') }}">
                    <b-icon-file-earmark-text scale="0.8"></b-icon-file-earmark-text>
                    Documents
                </a>
            </li>
            @endrole
        @endcan
        @role('super|admin|pricing|pricing_approver')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
                <b-icon-upload scale="0.8"></b-icon-upload>
                Importers
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                <a class="dropdown-item" href="{{ route('product-asin.index') }}">Import ASIN/SKUs</a>
                <a class="dropdown-item" href="{{ route('imports.create') }}">Import Products</a>
                <a class="dropdown-item" href="{{ route('unit-dimensions.index') }}">Import Product Dimensions</a>
                @can('__china')
                    <a class="dropdown-item" href="{{ route('shipments.index') }}">Import Shipment Details</a>
                @endcan
            </div>
        </li>
        @endrole
    @endauth
</ul>
