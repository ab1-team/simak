<!-- - var bodyCustom = 'bg-blue bg-lighten-2' // Use any color palette class-->
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="description" content="Sistem Informasi Manajemen Keuangan">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="simak, manajemen keuangan, keuangan, aplikasi">
    <meta name="author" content="Enfii">

    <title>Login Admin</title>

    <link
        href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i|Comfortaa:300,400,500,700"
        rel="stylesheet">
    <!-- BEGIN VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/forms/icheck/icheck.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/forms/icheck/custom.css">
    <!-- END VENDOR CSS-->
    <!-- BEGIN MODERN CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css">
    <!-- END MODERN CSS-->
    <!-- BEGIN Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="/assets/css/core/menu/menu-types/vertical-compact-menu.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/cryptocoins/cryptocoins.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/pages/account-login.css">
    @if (($appTheme ?? 'default') === 'merah-putih')
        <link rel="stylesheet" type="text/css" href="/assets/css/theme-merah-putih.css">
    @endif
    <!-- END Page Level CSS-->
</head>

<body class="vertical-layout vertical-compact-menu 1-column  bg-full-screen-image menu-expanded blank-page blank-page"
    data-open="click" data-menu="vertical-compact-menu" data-col="1-column">
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <section id="account-login" class="flexbox-container">
                    <div class="col-12 d-flex align-items-center justify-content-center">

                        <!-- login form -->
                        <div class="col-xl-3 col-lg-4 col-md-5 col-sm-5 col-12 p-0">
                            <div
                                class="card border-grey border-lighten-3 m-0 box-shadow-0 card-account-right height-400">
                                <div class="card-content">
                                    <div class="card-body p-3">
                                        <p class="text-center h5 text-capitalize">Welcome!</p>
                                        <p class="mb-3 text-center">Masukkan username dan password anda</p>
                                        <form class="form-horizontal form-signin" action="/db/auth" method="post">
                                            @csrf

                                            <fieldset class="form-label-group">
                                                <input type="text" class="form-control" name="username"
                                                    id="username" placeholder="Your Username" required=""
                                                    autofocus="">
                                                <label for="username">Username</label>
                                            </fieldset>
                                            <fieldset class="form-label-group">
                                                <input type="password" class="form-control" name="password"
                                                    id="password" placeholder="Password" required="">
                                                <label for="password">Password</label>
                                            </fieldset>
                                            <div class="form-group row">
                                                <div class="col-md-6 col-12 text-center text-sm-left">
                                                    <fieldset>
                                                        <input type="checkbox" id="remember-me" class="chk-remember">
                                                        <label for="remember-me"> Remember</label>
                                                    </fieldset>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn-gradient-primary btn-block my-1">
                                                Log In
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
    <!-- ////////////////////////////////////////////////////////////////////////////-->

    <!-- BEGIN VENDOR JS-->
    <script src="/assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <!-- BEGIN VENDOR JS-->
    <!-- BEGIN PAGE VENDOR JS-->
    <script src="/assets/vendors/js/forms/icheck/icheck.min.js" type="text/javascript"></script>
    <!-- END PAGE VENDOR JS-->
    <!-- BEGIN MODERN JS-->
    <script src="/assets/js/core/app-menu.js" type="text/javascript"></script>
    <script src="/assets/js/core/app.js" type="text/javascript"></script>
    <!-- END MODERN JS-->
    <!-- BEGIN PAGE LEVEL JS-->
    <script src="/assets/js/scripts/forms/form-login-register.js" type="text/javascript"></script>
    <!-- END PAGE LEVEL JS-->
</body>

</html>
