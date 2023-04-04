<ul class="navbar-nav ml-auto">
    <!-- Authentication Links -->
    @guest
        {{--        <li class="nav-item">--}}
        {{--            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>--}}
        {{--        </li>--}}
    @else
        <li class="nav-item dropdown">
            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false" v-pre>
                <b-icon-person-circle></b-icon-person-circle>
                {{ Auth::user()->name }} <span class="caret"></span>
            </a>

            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                @role('super')
                <div class="dropdown-header">Super Administrator Tools</div>
                <a class="dropdown-item" href="{{ route('countries.index') }}">Countries</a>
                <a class="dropdown-item" href="{{ route('currencies.index') }}">Currencies</a>
                <a class="dropdown-item" href="{{ route('factories.index') }}">Factories</a>
                <a class="dropdown-item" href="{{ route('suppliers.index') }}">Suppliers</a>
                <a class="dropdown-item" href="{{ route('users.index') }}">Users</a>
                <a class="dropdown-item" href="{{ route('roles.index') }}">Manage Roles</a>
                <div class="dropdown-divider"></div>
                <div class="dropdown-header">Pricing Admin</div>
                <a class="dropdown-item" href="#" @click="$refs['send-all-products-to-pricing'].send()">
                    <send-all-products-to-pricing ref="send-all-products-to-pricing"></send-all-products-to-pricing>
                    Re-price all products
                </a>
                <div class="dropdown-divider"></div>
                @endrole
                @role('super')
                <div class="dropdown-header">Settings</div>
                <a class="dropdown-item" href="{{ route('settings.index') }}">Settings</a>
                <a class="dropdown-item" href="{{ route('settings.export-templates.index') }}">Templates</a>
                <div class="dropdown-divider"></div>
                @endrole
                @role('super|admin|pricing|pricing_admin')
                <a class="dropdown-item" @click="$refs['quick-cost-modal'].show()">Quick Cost</a>
                @endrole
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
    @endguest
</ul>
<quick-cost-modal ref="quick-cost-modal"></quick-cost-modal>
