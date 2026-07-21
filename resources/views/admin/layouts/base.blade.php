<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="description" content="Sistem Informasi Manajemen Keuangan">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="simak, manajemen keuangan, keuangan, aplikasi">
    <meta name="author" content="Enfii">
    <title>
        {{ $title }} &mdash; Sistem Informasi Manajemen Keuangan
    </title>
    <link rel="apple-touch-icon" href="{{ asset('storage/logo/' . Session::get('logo')) }}">
    <link rel="shortcut icon" href="{{ asset('storage/logo/' . Session::get('logo')) }}">
    <link rel="icon" href="{{ asset('storage/logo/' . Session::get('logo')) }}">
    <link
        href="https://fonts.googleapis.com/css?family=Muli:300,300i,400,400i,600,600i,700,700i|Comfortaa:300,400,500,700"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.5/css/dataTables.dataTables.min.css">

    <link rel="stylesheet" type="text/css" href="/assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/forms/toggle/switchery.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/core/menu/menu-types/vertical-compact-menu.css">
    <link rel="stylesheet" type="text/css" href="/assets/vendors/css/cryptocoins/cryptocoins.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/pages/timeline.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/pages/dashboard-ico.css">
    @if (($appTheme ?? 'default') === 'merah-putih')
        <link rel="stylesheet" type="text/css" href="/assets/css/theme-merah-putih.css">
    @endif

    <style>
        .select2-container .select2-selection--single {
            padding: 0.5rem 0.75rem !important;
            height: unset !important;
        }

        .modal-fullscreen {
            width: 100vw;
            max-width: none;
            height: 100%;
            margin: 0;
        }

        .main-menu {
            z-index: 1050 !important;
        }

        .modal-dialog-scrollable .modal-body {
            overflow-y: auto;
        }

        .modal-dialog-scrollable .modal-content {
            max-height: 100%;
            overflow: hidden;
        }

        .transactions-table-tbody .card .card-body {
            padding: 0.5rem;
        }

        .tox-promotion {
            display: none;
        }

        .dropdown-submenu {
            position: relative;
        }

        .dropdown-submenu .dropdown-menu {
            top: 0;
            left: 100%;
            margin-top: -1px;
        }
    </style>
</head>

<body class="vertical-layout vertical-compact-menu 2-columns   menu-expanded fixed-navbar" data-open="click"
    data-menu="vertical-compact-menu" data-col="2-columns">

    @include('admin.layouts.navbar')
    @include('admin.layouts.sidebar')

    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>

            @yield('content')
        </div>
    </div>

    <footer class="footer footer-static footer-transparent">
        <p class="clearfix blue-grey lighten-2 text-sm-center mb-0 px-2">
            <span class="float-md-left d-block d-md-inline-block">
                &copy;
                <script>
                    document.write(new Date().getFullYear())
                </script>
                <a href="https://abt.co.id" class="font-weight-bold" target="_blank">
                    PT. Asta Brata Teknologi.
                </a>
            </span>
            <span class="float-md-right d-block d-md-inline-blockd-none d-lg-block">
                Made with <i class="fa fa-heart pink"></i>
            </span>
        </p>
    </footer>

    <form action="/db/logout" method="post" id="formLogout">
        @csrf
    </form>

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
    <script src="/assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script src="/assets/vendors/js/timeline/horizontal-timeline.js" type="text/javascript"></script>
    <script src="/assets/vendors/js/forms/toggle/switchery.min.js" type="text/javascript"></script>
    <script src="/assets/js/core/app-menu.js" type="text/javascript"></script>
    <script src="/assets/js/core/app.js" type="text/javascript"></script>

    <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="/assets/js/plugins/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-maskmoney/3.0.2/jquery.maskMoney.min.js"
        integrity="sha512-Rdk63VC+1UYzGSgd3u2iadi0joUrcwX0IWp2rTh6KXFoAmgOjRS99Vynz1lJPT8dLjvo6JZOqpAHJyfCEZ5KoA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"
        integrity="sha512-fD9DI5bZwQxOi7MhYWnnNPlvXdp/2Pj3XSTRrFs5FQa4mizyGLnJcN6tuvUS6LbmgN1ut+XGSABKvjN0H6Aoow=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
    <script src="https://cdn.datatables.net/2.1.5/js/dataTables.min.js"></script>

    <script src="/assets/js/scripts/forms/account-profile.js" type="text/javascript"></script>
    @yield('script')
    <script>
        function open_window(link) {
            return window.open(link)
        }

        $('.select2').select2({
            theme: 'bootstrap-5'
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

        $(document).on('click', '#logout', function(e) {
            e.preventDefault()

            Swal.fire({
                title: 'Logout',
                text: 'Dengan klik tombol logout maka anda tidak bisa membuka halaman ini lagi sebelum melakukan login ulang, Logout?',
                showCancelButton: true,
                confirmButtonText: 'Logout',
                cancelButtonText: 'Batal',
                icon: 'info'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#formLogout').submit()
                }
            })
        })

        tinymce.init({
            selector: '.tiny-mce-editor',
            plugins: 'table visualblocks fullscreen',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | align | table fullscreen | removeformat',
            font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier,monospace;',
            tinycomments_mode: 'embedded',
            tinycomments_author: 'ARAFII'
        });
    </script>
</body>

</html>
