<aside v-show="$route.path === '/' || $route.path === '/register'|| $route.path === '/forgot'? false:true" style="display: none" class="main-sidebar sidebar-dark-primary elevation-4">

    <a href="index3.html" class="brand-link">
        {{-- <img src="https://adminlte.io/themes/v3/dist/img/AdminLTELogo.png" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8"> --}}
        <span class="brand-text font-weight-light">Inventory Management</span>
    </a>

    <div class="sidebar">

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">                
                    <img src="/storage/profile/user.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Sanjith</a>
            </div>
        </div>

        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                    aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">

                <li class="nav-item">
                    <router-link to="/dashboard" class="nav-link" active-class="active">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link to="/pos" class="nav-link" active-class="active">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            POS
                        </p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <router-link to="/order" class="nav-link" active-class="active">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Orders
                        </p>
                    </router-link>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" active-class="active">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Employee
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link to="/employee" active-class="active" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Employee List</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/employee/create" active-class="active" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add Employee</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" active-class="active">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Custoemr
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link to="/customer" active-class="active" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Customer List</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/customer/create" active-class="active" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add Customer</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link" active-class="active">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Product
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <router-link to="/product" active-class="active" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Product List</p>
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link to="/product/create" active-class="active" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Add Product</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <router-link to="/category" active-class="active" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Category</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-th"></i>
                        <p>
                            Simple Link
                            <span class="right badge badge-danger">New</span>
                        </p>
                    </a>
                </li>
            </ul>
        </nav>

    </div>

</aside>