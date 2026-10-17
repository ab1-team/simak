@extends('admin.layouts.base')

@section('content')
    <div class="card">
        <div class="card-header">
            <h5>Register Usaha Baru</h5>
        </div>
        <div class="card-body">
            <form action="/db/app" method="post" id="FormRegisterUsaha">
                @csrf

                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="form-label" for="provinsi">Provinsi</label>
                            <select class="form-control select2" name="provinsi" id="provinsi">
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach ($wilayah as $w)
                                    <option value="{{ $w->kode }}">
                                        {{ $w->nama }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-danger" id="msg_provinsi"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="form-label" for="kabupaten">Kabupaten</label>
                            <select class="form-control select2" name="kabupaten" id="kabupaten">
                                <option value="">-- Pilih kabupaten --</option>
                            </select>
                            <small class="text-danger" id="msg_kabupaten"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="form-label" for="kecamatan">Kecamatan</label>
                            <select class="form-control select2" name="kecamatan" id="kecamatan">
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                            <small class="text-danger" id="msg_kecamatan"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="form-label" for="desa">Desa</label>
                            <select class="form-control select2" name="desa" id="desa">
                                <option value="">-- Pilih Desa --</option>
                            </select>
                            <small class="text-danger" id="msg_desa"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="nama_usaha">Nama Usaha</label>
                            <input autocomplete="off" type="text" name="nama_usaha" id="nama_usaha"
                                class="form-control form-control-sm" value="">
                            <small class="text-danger" id="msg_nama_usaha"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="domain">Domain</label>
                            <input autocomplete="off" type="url" name="domain" id="domain"
                                class="form-control form-control-sm" value="">
                            <small class="text-danger" id="msg_domain"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="domain_alternatif">Domain Alternatif</label>
                            <input autocomplete="off" type="url" name="domain_alternatif" id="domain_alternatif"
                                class="form-control form-control-sm" value="">
                            <small class="text-danger" id="msg_domain_alternatif"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="form-label" for="tagihan_invoice">Tagihan Invoice</label>
                            <select class="form-control select2" name="tagihan_invoice" id="tagihan_invoice">
                                <option value="">-- Tagihan Invoice --</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>
                                        {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                    </option>
                                @endfor
                            </select>
                            <small class="text-danger" id="msg_tagihan_invoice"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="biaya_maintenance">Biaya Maintenance</label>
                            <input autocomplete="off" type="text" name="biaya_maintenance" id="biaya_maintenance"
                                class="form-control form-control-sm" value="{{ number_format(0, 2) }}">
                            <small class="text-danger" id="msg_biaya_maintenance"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="tgl_register">Tgl Register</label>
                            <input autocomplete="off" type="text" name="tgl_register" id="tgl_register"
                                class="form-control form-control-sm date" value="{{ Tanggal::tglIndo(date('Y-m-d')) }}">
                            <small class="text-danger" id="msg_tgl_register"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="tgl_pakai">Tgl Pakai</label>
                            <input autocomplete="off" type="text" name="tgl_pakai" id="tgl_pakai"
                                class="form-control form-control-sm date" value="{{ Tanggal::tglIndo(date('Y-m-d')) }}">
                            <small class="text-danger" id="msg_tgl_pakai"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="masa_aktif">Masa Aktif</label>
                            <input autocomplete="off" type="text" name="masa_aktif" id="masa_aktif"
                                class="form-control form-control-sm date"
                                value="{{ Tanggal::tglIndo(date('Y-m-d', strtotime('+1 month', strtotime(date('Y-m-d'))))) }}">
                            <small class="text-danger" id="msg_masa_aktif"></small>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label class="form-label" for="jenis_akun">Jenis Akun</label>
                            <select class="form-control select2" name="jenis_akun" id="jenis_akun">
                                <option value="">-- Pilih Jenis Akun --</option>
                                <option value="5" selected>Bumdes Biasa (Rekening)</option>
                                <option value="7">Perdagangan</option>
                                <option value="8">Koperasi</option>
                            </select>
                            <small class="text-danger" id="msg_jenis_akun"></small>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" id="SimpanUsaha" class="btn btn-sm btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(".date").flatpickr({
            dateFormat: "d/m/Y"
        })

        $("#biaya_maintenance").maskMoney({
            allowNegative: true
        });

        function prov() {
            var prov = $('#provinsi').val()

            $.ajax({
                type: 'GET',
                url: '/db/app/kabupaten/' + prov,
                data: {
                    prov
                },
                success: function(result) {
                    selectOption(result.data, '#kabupaten', 'Pilih Kabupaten')
                }
            })
        }

        $(document).on('change', '#provinsi', function(e) {
            prov()
        })

        $(document).on('change', '#kabupaten', function(e) {
            var kab = $(this).val()

            $.ajax({
                type: 'GET',
                url: '/db/app/kecamatan/' + kab,
                data: {
                    kab
                },
                success: function(result) {
                    selectOption(result.data, '#kecamatan', 'Pilih Kecamatan')
                }
            })
        })

        $(document).on('click', '#SimpanUsaha', function(e) {
            e.preventDefault();

            $('small').html('')
            var form = $('#FormRegisterUsaha')
            $.ajax({
                type: 'POST',
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Swal.fire('Berhasil', result.msg, 'success').then(() => {
                            window.location.href = '/db/app/'
                        })
                    }
                },
                error: function(result) {
                    const respons = result.responseJSON;

                    Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                    $.map(respons, function(res, key) {
                        $('#' + key).parent('.input-group.input-group-static').addClass('is-invalid')
                        $('#msg_' + key).html(res)
                    })
                }
            })
        })

        $(document).on('change', '#kecamatan', function(e) {
            var kab = $(this).val()

            $.ajax({
                type: 'GET',
                url: '/db/app/desa/' + kab,
                data: {
                    kab
                },
                success: function(result) {
                    selectOption(result.data, '#desa', 'Pilih Desa')
                }
            })
        })

        function selectOption(data, target, title) {
            var select, option;
            select = $(target)

            $(target).html('')
            option = $('<option></option>').attr('value', '').text('-- ' + title + ' --')
            select.append(option)

            data.map((item) => {
                option = $('<option></option>').attr('value', item.kode).text(item.nama)
                select.append(option)
            })
        }

        prov()
    </script>
@endsection
