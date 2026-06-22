@php
    function active($path, ...$paths)
    {
        $page = explode('/', Request::path());
        $page = '/' . end($page);
        if ($page == $path) {
            return 'active';
        }

        if (in_array($page, $paths)) {
            return 'active';
        }

        if (str_contains(Request::path(), $path) && $path != '') {
            return 'active';
        }
    }

@endphp

<div class="main-menu menu-fixed menu-dark menu-bg-default rounded menu-accordion menu-shadow">
    <div class="main-menu-content">
        <a class="navigation-brand d-none d-md-flex d-lg-flex d-xl-flex justify-content-center" href="/dashboard">
            <div class="avatar avatar-online avatar-md">
                <img class="brand-logo" alt="Logo" src="{{ asset('storage/logo/' . Session::get('logo')) }}" />
            </div>
        </a>
        <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
            <li class="{{ active('dashboard') }} nav-item">
                <a href="/db/dashboard">
                    <i class="icon-grid"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
            <li class="{{ active('/user') }} nav-item">
                <a href="/db/user">
                    <i class="icon-users"></i>
                    <span class="menu-title">User</span>
                </a>
            </li>
            <li class="{{ active('/app') }} nav-item">
                <a href="/db/app">
                    <i class="icon-briefcase"></i>
                    <span class="menu-title">App</span>
                </a>
            </li>
            <li class="{{ active('', '/invoice', '/paid', '/unpaid') }} nav-item">
                <a href="#">
                    <i class="icon-note"></i>
                    <span class="menu-title">Invoice</span>
                </a>
                <ul class="menu-content">
                    <li>
                        <a class="menu-item" href="/db/invoice">Tambah</a>
                    </li>
                    <li>
                        <a class="menu-item" href="/db/paid">Paid</a>
                    </li>
                    <li>
                        <a class="menu-item" href="/db/unpaid">Unpaid</a>
                    </li>
                </ul>
            </li>
            <li class="{{ active('/license') }} nav-item">
                <a href="/db/license">
                    <i class="icon-key"></i>
                    <span class="menu-title">License</span>
                </a>
            </li>
        </ul>
    </div>
</div>
