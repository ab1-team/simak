<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="description" content="Sistem Informasi Dana Bergulir Masyarakat &mdash; Siap Audit Kapanpun Siapapun">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords"
        content="dbm, sidbm, sidbm.net, demo.sidbm.net, app.sidbm.net, asta brata teknologi, abt, dbm, kepmendesa 136, kepmendesa nomor 136 tahun 2022">
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <style>
        .select2-container .select2-selection--single {
            padding: 0.5rem 0.75rem !important;
            height: unset !important;
        }
    </style>
</head>

<body class="vertical-layout vertical-compact-menu 1-column  bg-full-screen-image menu-expanded blank-page blank-page"
    data-open="click" data-menu="vertical-compact-menu" data-col="1-column">
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
            </div>
            <div class="content-body">
                <section id="account-register" class="flexbox-container">
                    <div class="col-12 d-flex align-items-center justify-content-center">
                        <div class="col-md-8 col-12 p-0">
                            <div class="card border-grey border-lighten-3 m-0 box-shadow-0 card-account-right">
                                <div class="card-content">
                                    <div class="card-body p-2">
                                        <p class="text-center h5 text-capitalize">Register</p>
                                        <p class="mb-2 text-center">Buat Akun Baru</p>
                                        <form class="form-horizontal form-signin" method="post" action="/register">
                                            @csrf

                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label class="form-label" for="provinsi">
                                                            Pilih Provinsi
                                                        </label>
                                                        <select class="form-control select2" name="provinsi"
                                                            id="provinsi">
                                                            <option value="">-- Pilih Provinsi --</option>
                                                        </select>
                                                        <small class="text-danger" id="msg_provinsi"></small>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label class="form-label" for="kabupaten">
                                                            Pilih Kabupaten
                                                        </label>
                                                        <select class="form-control select2" name="kabupaten"
                                                            id="kabupaten">
                                                            <option value="">-- Pilih Kabupaten --</option>
                                                        </select>
                                                        <small class="text-danger" id="msg_kabupaten"></small>
                                                    </div>
                                                </div>
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label class="form-label" for="kecamatan">
                                                            Pilih Kecamatan
                                                        </label>
                                                        <select class="form-control select2" name="kecamatan"
                                                            id="kecamatan">
                                                            <option value="">-- Pilih Kecamatan --</option>
                                                        </select>
                                                        <small class="text-danger" id="msg_kecamatan"></small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <div class="form-group">
                                                        <label class="form-label" for="desa">
                                                            Pilih Desa
                                                        </label>
                                                        <select class="form-control select2" name="desa"
                                                            id="desa">
                                                            <option value="">-- Pilih Desa --</option>
                                                        </select>
                                                        <small class="text-danger" id="msg_desa"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="nama_usaha">Nama Usaha</label>
                                                        <input autocomplete="off" type="text" name="nama_usaha"
                                                            id="nama_usaha" class="form-control form-control-sm">
                                                        <small class="text-danger" id="msg_nama_usaha"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="tgl_register">Tgl Register</label>
                                                        <input autocomplete="off" type="text" name="tgl_register"
                                                            id="tgl_register"
                                                            class="form-control form-control-sm date"
                                                            value="{{ date('d/m/Y') }}">
                                                        <small class="text-danger" id="msg_tgl_register"></small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="alamat">Alamat</label>
                                                        <input autocomplete="off" type="text" name="alamat"
                                                            id="alamat" class="form-control form-control-sm">
                                                        <small class="text-danger" id="msg_alamat"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="email">Email</label>
                                                        <input autocomplete="off" type="text" name="email"
                                                            id="email" class="form-control form-control-sm">
                                                        <small class="text-danger" id="msg_email"></small>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="telpon">Telpon</label>
                                                        <input autocomplete="off" type="text" name="telpon"
                                                            id="telpon" class="form-control form-control-sm">
                                                        <small class="text-danger" id="msg_telpon"></small>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn-gradient-primary btn-block my-1">
                                                Daftar
                                            </button>
                                            <p class="text-center">
                                                <a href="/" class="card-link">Login</a>
                                            </p>
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

    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="/assets/vendors/js/vendors.min.js" type="text/javascript"></script>
    <script src="/assets/vendors/js/forms/icheck/icheck.min.js" type="text/javascript"></script>
    <script src="/assets/js/core/app-menu.js" type="text/javascript"></script>
    <script src="/assets/js/core/app.js" type="text/javascript"></script>
    <script src="/assets/js/scripts/forms/form-login-register.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="/assets/js/plugins/flatpickr.min.js"></script>

    <script>
        $(".date").flatpickr({
            dateFormat: "d/m/Y"
        })

        $('.select2').select2({
            theme: 'bootstrap-5'
        })

        $.get('/ambil_prov', function(result) {
            if (result.success) {
                setSelectValue('provinsi', result.data)
            }
        })

        $(document).on('change', '#provinsi', function() {
            var kode = $(this).val()
            $.get('/ambil_kab/' + kode, function(result) {
                if (result.success) {
                    setSelectValue('kabupaten', result.data)
                }
            })
        })

        $(document).on('change', '#kabupaten', function() {
            var kode = $(this).val()
            $.get('/ambil_kec/' + kode, function(result) {
                if (result.success) {
                    setSelectValue('kecamatan', result.data)
                }
            })
        })

        $(document).on('change', '#kecamatan', function() {
            var kode = $(this).val()
            $.get('/ambil_des/' + kode, function(result) {
                if (result.success) {
                    setSelectValue('desa', result.data)
                }
            })
        })

        function setSelectValue(id, data) {
            var label = ucwords(id)

            $('#' + id).empty()
            $('#' + id).append('<option>-- Pilih ' + label + ' --</option>')
            data.forEach((val, index) => {
                $('#' + id).append('<option value="' + val.kode + '">' + val.nama + '</option>')
            })
        }

        function ucwords(str) {
            return str.replace(/\b\w/g, function(char) {
                return char.toUpperCase();
            });
        }

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
