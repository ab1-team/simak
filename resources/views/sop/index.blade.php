@php
    $identitas_lembaga = 'active show';
    $pengelola_bumdes = '';
    $personalia_bumdes = '';
    $kop_dokumen = '';
    $upload_logo = '';

    if (!in_array('personalisasi_sop.identitas_lembaga', Session::get('tombol'))) {
        $identitas_lembaga = '';
    }

    if (
        in_array('personalisasi_sop.sebutan_pengelola', Session::get('tombol')) &&
        ($identitas_lembaga == '' && $personalia_bumdes == '' && $kop_dokumen == '' && $upload_logo == '')
    ) {
        $pengelola_bumdes = 'active show';
    }

    if (
        in_array('personalisasi_sop.personalia', Session::get('tombol')) &&
        ($identitas_lembaga == '' && $pengelola_bumdes == '' && $kop_dokumen == '' && $upload_logo == '')
    ) {
        $personalia_bumdes = 'active show';
    }

    if (
        in_array('personalisasi_sop.kop_dokumen', Session::get('tombol')) &&
        ($identitas_lembaga == '' && $pengelola_bumdes == '' && $personalia_bumdes == '' && $upload_logo == '')
    ) {
        $kop_dokumen = 'active show';
    }

    if (
        in_array('personalisasi_sop.logo', Session::get('tombol')) &&
        ($identitas_lembaga == '' && $pengelola_bumdes == '' && $personalia_bumdes == '' && $kop_dokumen == '')
    ) {
        $upload_logo = 'active show';
    }
@endphp

@extends('layouts.base')

@section('content')
    <div class="row">
        <div class="col-md-10 col-12  order-2">
            <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade {{ $identitas_lembaga }}" id="lembaga" role="tabpanel"
                    aria-labelledby="lembaga-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Identitas Lembaga</h5>
                        </div>
                        <div class="card-body pt-0">
                            @include('sop.partials._lembaga')
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{ $pengelola_bumdes }}" id="pengelola" role="tabpanel"
                    aria-labelledby="pengelola-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Sebutan Pengelola Bumdes</h5>
                        </div>
                        <div class="card-body pt-0">
                            @include('sop.partials._pengelola')
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{ $personalia_bumdes }}" id="personalia" role="tabpanel"
                    aria-labelledby="personalia-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Sebutan Personalia</h5>
                        </div>
                        <div class="card-body pt-0">
                            @include('sop.partials._personalia')
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{ $kop_dokumen }}" id="kop" role="tabpanel" aria-labelledby="kop-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Kop Dokumen</h5>
                        </div>
                        <div class="card-body pt-0">
                            @include('sop.partials._kop')
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade {{ $upload_logo }}" id="upload-logo" role="tabpanel"
                    aria-labelledby="upload-logo-tab">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Upload Logo</h5>
                        </div>
                        <div class="card-body pt-0">
                            @include('sop.partials._logo')
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-12 order-1">
            <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                @if (in_array('personalisasi_sop.identitas_lembaga', Session::get('tombol')))
                    <a class="nav-link {{ $identitas_lembaga }}" id="lembaga-tab" data-toggle="pill" href="#lembaga"
                        role="tab" aria-controls="lembaga" aria-selected="true">Identitas Lembaga</a>
                @endif
                @if (in_array('personalisasi_sop.sebutan_pengelola', Session::get('tombol')))
                    <a class="nav-link {{ $pengelola_bumdes }}" id="pengelola-tab" data-toggle="pill" href="#pengelola"
                        role="tab" aria-controls="pengelola" aria-selected="false">Sebutan Pengelola</a>
                @endif
                @if (in_array('personalisasi_sop.personalia', Session::get('tombol')))
                    <a class="nav-link {{ $personalia_bumdes }}" id="personalia-tab" data-toggle="pill" href="#personalia"
                        role="tab" aria-controls="personalia" aria-selected="false">Sebutan Personalia</a>
                @endif
                <a class="nav-link {{ $kop_dokumen }}" id="kop-tab" data-toggle="pill" href="#kop" role="tab"
                    aria-controls="kop" aria-selected="false">Kop Dokumen</a>
                @if (in_array('personalisasi_sop.logo', Session::get('tombol')))
                    <a class="nav-link {{ $upload_logo }}" id="upload-logo-tab" data-toggle="pill" href="#upload-logo"
                        role="tab" aria-controls="logo" aria-selected="false">Logo</a>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.tiny.cloud/1/rhedf7n960hbva1kt51t8gz54xlm2ad7q87fp5p3l9w3nehg/tinymce/5/tinymce.min.js"
        referrerpolicy="origin" crossorigin="anonymous"></script>

    <script>
        tinymce.init({
            selector: '#kop-laporan',
            height: 500,
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsizeselect | bold italic underline strikethrough | link image media table customtableborder | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            fontsize_formats: "1pt 2pt 3pt 4pt 5pt 6pt 7pt 8pt 9pt 10pt 11px 12pt",
            file_picker_types: 'image',
            file_picker_callback: function(callback, value, meta) {
                if (meta.filetype === 'image') {
                    var input = document.createElement('input');
                    input.setAttribute('type', 'file');
                    input.setAttribute('accept', 'image/*');

                    input.onchange = function() {
                        var file = this.files[0];
                        var reader = new FileReader();

                        reader.onload = function() {
                            var base64 = reader.result;

                            callback(base64, {
                                alt: file.name,
                                title: file.name
                            });
                        };

                        reader.readAsDataURL(file);
                    };

                    input.click();
                }
            },
            paste_data_images: true,
            images_upload_handler: function(blobInfo, success, failure) {
                var reader = new FileReader();
                reader.onload = function() {
                    success(reader.result);
                };
                reader.onerror = function() {
                    failure('Gagal membaca gambar');
                };
                reader.readAsDataURL(blobInfo.blob());
            },
            setup: function(editor) {
                // Custom dialog untuk border individual
                editor.ui.registry.addButton('customtableborder', {
                    text: 'Border Detail',
                    onAction: function() {
                        const selectedTable = editor.dom.getParent(editor.selection.getNode(),
                            'table');
                        const selectedCell = editor.dom.getParent(editor.selection.getNode(),
                            'td,th');

                        if (!selectedTable && !selectedCell) {
                            alert('Pilih table atau cell terlebih dahulu');
                            return;
                        }

                        const element = selectedCell || selectedTable;
                        const currentStyle = element.style;

                        editor.windowManager.open({
                            title: 'Custom Border Properties',
                            body: {
                                type: 'panel',
                                items: [{
                                    type: 'grid',
                                    columns: 2,
                                    items: [{
                                            type: 'label',
                                            label: 'Border Top',
                                            items: []
                                        },
                                        {
                                            type: 'input',
                                            name: 'borderTop',
                                            placeholder: 'e.g. 1px solid black'
                                        },

                                        {
                                            type: 'label',
                                            label: 'Border Bottom',
                                            items: []
                                        },
                                        {
                                            type: 'input',
                                            name: 'borderBottom',
                                            placeholder: 'e.g. 1px solid black'
                                        },

                                        {
                                            type: 'label',
                                            label: 'Border Left',
                                            items: []
                                        },
                                        {
                                            type: 'input',
                                            name: 'borderLeft',
                                            placeholder: 'e.g. 1px solid black'
                                        },

                                        {
                                            type: 'label',
                                            label: 'Border Right',
                                            items: []
                                        },
                                        {
                                            type: 'input',
                                            name: 'borderRight',
                                            placeholder: 'e.g. 1px solid black'
                                        }
                                    ]
                                }]
                            },
                            initialData: {
                                borderTop: currentStyle.borderTop || '',
                                borderBottom: currentStyle.borderBottom || '',
                                borderLeft: currentStyle.borderLeft || '',
                                borderRight: currentStyle.borderRight || ''
                            },
                            buttons: [{
                                    type: 'cancel',
                                    text: 'Cancel'
                                },
                                {
                                    type: 'submit',
                                    text: 'Apply',
                                    primary: true
                                }
                            ],
                            onSubmit: function(api) {
                                const data = api.getData();

                                if (data.borderTop) element.style.borderTop = data
                                    .borderTop;
                                if (data.borderBottom) element.style.borderBottom = data
                                    .borderBottom;
                                if (data.borderLeft) element.style.borderLeft = data
                                    .borderLeft;
                                if (data.borderRight) element.style.borderRight = data
                                    .borderRight;

                                api.close();
                            }
                        });
                    }
                });
            },
            table_default_attributes: {
                border: '0'
            },
            table_default_styles: {
                width: '100%'
            },
            table_use_colgroups: true,
            table_responsive_width: true
        });
    </script>

    <script>
        $(".date").flatpickr({
            dateFormat: "d/m/Y"
        })

        var tahun = "{{ date('Y') }}"
        var bulan = "{{ date('m') }}"

        $(".money").maskMoney();

        $(document).on('click', '.btn-simpan', async function(e) {
            e.preventDefault()

            if ($(this).attr('id') == 'SimpanSPK') {
                await $('#spk').val(quill.container.firstChild.innerHTML)
            }

            if ($(this).attr('id') == 'SimpanBeritaAcara') {
                await $('#ba').val(quill1.container.firstChild.innerHTML)
            }

            if ($(this).attr('id') == 'SimpanKopLaporan') {
                await $('#kop_laporan').val(tinymce.get('kop-laporan').getContent())
            }

            var form = $($(this).attr('data-target'))
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Toastr('success', result.msg)
                    }
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static').addClass(
                            'is-invalid')
                        $('#msg_' + key).html(res)
                    })
                }
            })
        })

        $(document).on('click', '#EditLogo', function(e) {
            e.preventDefault()

            $('#logo').trigger('click')
        })

        $(document).on('change', '#logo', function(e) {
            e.preventDefault()

            var logo = $(this).get(0).files[0]
            if (logo) {
                var form = $('#FormLogo')
                var formData = new FormData(document.querySelector('#FormLogo'));
                $.ajax({
                    type: form.attr('method'),
                    url: form.attr('action'),
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    success: function(result) {
                        if (result.success) {
                            var reader = new FileReader();

                            reader.onload = function() {
                                $("#previewLogo").attr("src", reader.result);
                                $(".brand-logo").attr("src", reader.result);
                            }

                            reader.readAsDataURL(logo);
                            Toastr('success', result.msg)
                        } else {
                            Toastr('error', result.msg)
                        }
                    }
                })
            }
        })
    </script>
@endsection
