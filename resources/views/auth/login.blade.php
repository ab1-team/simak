<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="description" content="Sistem Informasi Manajemen Keuangan">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="simak, manajemen keuangan, keuangan, aplikasi">
    <meta name="author" content="Enfii">

    <link rel="apple-touch-icon" sizes="76x76" href="{{ $logo }}">
    <link rel="icon" type="image/png" href="{{ $logo }}">
    <title>
        AKUBUMDES &mdash; {{ $usaha->nama_usaha }}
    </title>
    <link rel="apple-touch-icon" href="{{ $logo }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ $logo }}">
    <link
        href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i|Comfortaa:300,400,500,700"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/forms/icheck/icheck.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/forms/icheck/custom.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/core/menu/menu-types/vertical-compact-menu.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/cryptocoins/cryptocoins.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/pages/account-login.css">
    @if (($appTheme ?? 'default') === 'merah-putih')
        <link rel="stylesheet" type="text/css" href="/assets/css/theme-merah-putih.css">
    @endif
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
                        <div class="col-sm-6 col-12 p-0">
                            <div class="card m-0 card-account-right height-400">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-lg-6 col-12 p-0 text-center d-none d-lg-block">
                                            <div class="d-flex justify-content-center align-items-center h-100">
                                                <img src="{{ $logo }}"
                                                    class="card-account-img width-200 rounded-circle"
                                                    alt="card-account-img">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-12 p-0">
                                            <p class="text-center h5 text-capitalize">
                                                {!! $usaha->nama_usaha !!}
                                            </p>
                                            <div class="mb-3 text-center">
                                                {{ $usaha->d->sebutan_desa->sebutan_desa }} {{ $usaha->d->nama_desa }},
                                                {{ $usaha->d->kec->nama_kec }}
                                            </div>
                                            <form class="form-horizontal form-signin" action="/login" method="post">
                                                @csrf

                                                <div class="form-label-group">
                                                    <input type="text" class="form-control" id="username"
                                                        name="username" placeholder="Your Username" required=""
                                                        value="{{ $usaha->id == '1' ? 'demo' : '' }}">
                                                    <label for="username" class="cursor-text">Username</label>
                                                </div>
                                                <div class="form-label-group">
                                                    <input type="password" class="form-control" id="password"
                                                        name="password" placeholder="Enter Password" required=""
                                                        value="{{ $usaha->id == '1' ? 'demo123' : '' }}">
                                                    <label for="password" class="cursor-text">Password</label>
                                                </div>
                                                <button type="submit" class="btn-gradient-primary btn-block my-1 mb-2">
                                                    Log In
                                                </button>
                                                <div class="text-center">
                                                    &copy; {{ date('Y') }} PT. Asta Brata Teknologi
                                                </div>
                                                <p class="text-center">
                                                    {{ str_pad($usaha->id, 4, '0', STR_PAD_LEFT) }}
                                                </p>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>

    <script src="/assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <script src="/assets/vendors/js/forms/icheck/icheck.min.js" type="text/javascript"></script>
    <script src="/assets/js/core/app-menu.js" type="text/javascript"></script>
    <script src="/assets/js/core/app.js" type="text/javascript"></script>
    <script src="/assets/js/scripts/forms/form-login-register.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        if (localStorage.getItem('devops') !== 'true') {
            $(document).bind("contextmenu", function(e) {
                return false;
            });

            $(document).keydown(function(event) {
                if (event.keyCode == 123) { // Prevent F12
                    return false;
                }
                if (event.ctrlKey && event.shiftKey && event.keyCode == 73) { // Prevent Ctrl+Shift+I        
                    return false;
                }
                if (event.ctrlKey && event.shiftKey && event.keyCode == 67) { // Prevent Ctrl+Shift+C  
                    return false;
                }
                if (event.ctrlKey && event.shiftKey && event.keyCode == 74) { // Prevent Ctrl+Shift+J
                    return false;
                }
            });
        }
    </script>

    @if (session('pesan'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            function Toastr(icon, text) {
                font = "1.6rem Nimrod MT";

                canvas = document.createElement("canvas");
                context = canvas.getContext("2d");
                context.font = font;
                width = context.measureText(text).width;
                formattedWidth = Math.ceil(width) + 100;

                Toast.fire({
                    icon: icon,
                    title: text,
                    width: formattedWidth
                })
            }

            Toastr('success', "{{ session('pesan') }}")
        </script>
    @endif
</body>

</html>
