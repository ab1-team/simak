@extends('admin.layouts.base')

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h4 class="card-title">Daftar Lisensi</h4>
                <button class="btn btn-primary btn-sm" id="btnTambah">
                    <i class="fa fa-plus"></i> Tambah Lisensi
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped" id="tabelLisensi">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Usaha</th>
                            <th>API Secret</th>
                            <th>Status</th>
                            <th>Tanggal Berakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Tambah / Edit --}}
    <div class="modal fade" id="modalLisensi" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Lisensi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formLisensi">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <div class="modal-body">
                        <div class="form-group" id="wrapUsaha">
                            <label class="form-label">Usaha <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="usaha_id" id="usaha_id" style="width: 100%;">
                                <option value="">-- Pilih Usaha --</option>
                                @foreach ($usaha as $u)
                                    <option value="{{ $u->id }}">
                                        {{ $u->id }} - {{ $u->nama_usaha }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-danger" id="err_usaha_id"></small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">API Secret</label>
                            <input type="text" class="form-control" name="api_secret" id="api_secret"
                                placeholder="Masukkan API Secret">
                            <small class="text-danger" id="err_api_secret"></small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status License</label>
                            <div class="d-flex align-items-center">
                                <label class="switch mr-1">
                                    <input type="checkbox" name="is_active" id="is_active" checked>
                                    <span class="slider round"></span>
                                </label>
                                <span id="statusText" class="ml-1">Aktif</span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tanggal Berakhir</label>
                            <input type="datetime-local" class="form-control" name="expired_at" id="expired_at">
                            <small class="text-muted">Kosongkan jika lisensi aktif selamanya.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSimpan">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #28a745;
        }

        input:checked+.slider:before {
            transform: translateX(26px);
        }
    </style>

    <script>
        var table = $('#tabelLisensi').DataTable({
            language: {
                paginate: {
                    previous: "&laquo;",
                    next: "&raquo;"
                }
            },
            processing: true,
            serverSide: true,
            ajax: '/db/license',
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'usaha',
                    name: 'usaha'
                },
                {
                    data: 'api_secret',
                    name: 'api_secret'
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'expired_at',
                    name: 'expired_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // Tambah
        $('#btnTambah').on('click', function() {
            $('#formLisensi')[0].reset();
            $('#formMethod').val('POST');
            $('#modalTitle').text('Tambah Lisensi');
            $('#wrapUsaha').show();
            $('#usaha_id').prop('disabled', false).val(null).trigger('change');
            $('#is_active').prop('checked', true);
            $('#statusText').text('Aktif');
            $('#expired_at').val('');
            clearErrors();
            $('#modalLisensi').modal('show');
        });

        // Edit
        $('#tabelLisensi').on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            $.get('/db/license/' + id, function(res) {
                if (res.success) {
                    var d = res.data;
                    $('#formMethod').val('PUT');
                    $('#modalTitle').text('Edit Lisensi');
                    $('#wrapUsaha').hide();
                    $('#usaha_id').val(d.usaha_id).trigger('change');
                    $('#api_secret').val(d.api_secret);
                    $('#is_active').prop('checked', d.is_active);
                    $('#statusText').text(d.is_active ? 'Aktif' : 'Nonaktif');
                    $('#expired_at').val(d.expired_at || '');
                    clearErrors();
                    $('#modalLisensi').data('id', id).modal('show');
                }
            });
        });

        // Hapus
        $('#tabelLisensi').on('click', '.btn-delete', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Hapus Lisensi?',
                text: 'Tindakan ini tidak dapat dibatalkan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/db/license/' + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Toastr('success', res.msg);
                            table.ajax.reload();
                        },
                        error: function() {
                            Toastr('error', 'Gagal menghapus lisensi.');
                        }
                    });
                }
            });
        });

        // Submit form (tambah / edit)
        $('#formLisensi').on('submit', function(e) {
            e.preventDefault();
            clearErrors();

            var id = $('#modalLisensi').data('id');
            var method = $('#formMethod').val();
            var url = method === 'POST' ? '/db/license' : '/db/license/' + id;

            var formData = $(this).serializeArray();
            // boolean checkbox
            formData.push({
                name: 'is_active',
                value: $('#is_active').is(':checked') ? 1 : 0
            });
            if (method === 'PUT') {
                formData.push({
                    name: '_method',
                    value: 'PUT'
                });
            }

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                success: function(res) {
                    $('#modalLisensi').modal('hide');
                    Toastr('success', res.msg);
                    table.ajax.reload();
                },
                error: function(xhr) {
                    if (xhr.status === 301) {
                        var errs = xhr.responseJSON;
                        $.each(errs, function(key, val) {
                            $('#err_' + key).text(val[0]);
                        });
                    } else {
                        Toastr('error', 'Terjadi kesalahan.');
                    }
                }
            });
        });

        // Switch label sync
        $('#is_active').on('change', function() {
            $('#statusText').text(this.checked ? 'Aktif' : 'Nonaktif');
        });

        function clearErrors() {
            $('[id^="err_"]').text('');
        }
    </script>
@endsection
