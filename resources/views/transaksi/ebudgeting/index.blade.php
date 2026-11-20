@extends('layouts.base')

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <form action="/transaksi/anggaran" method="post" id="formAnggaran">
                @csrf

                @php
                    $th_pakai = $usaha && $usaha->tgl_pakai ? (int) explode('-', $usaha->tgl_pakai)[0] : (int) date('Y');
                @endphp

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="tahun">Tahun</label>
                            <select class="form-control select2" name="tahun" id="tahun">
                                @for ($i = $th_pakai; $i <= (int) date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ $i == (int) date('Y') ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                            <small class="text-danger" id="msg_tahun"></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label" for="bulan">Bulan</label>
                            <select class="form-control select2" name="bulan" id="bulan">
                                @for ($i = 1; $i <= 12; $i++)
                                    @php $bln = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                    <option value="{{ $i }}" {{ $i == (int) date('m') ? 'selected' : '' }}>
                                        {{ $bln }}. {{ strtoupper(\App\Utils\Tanggal::namaBulan(date('Y') . '-' . $bln . '-01')) }}
                                    </option>
                                @endfor
                            </select>
                            <small class="text-danger" id="msg_bulan"></small>
                        </div>
                    </div>
                </div>
            </form>

            <div class="d-flex justify-content-end">
                <button type="button" id="anggaran" class="btn btn-sm btn-github mb-0">Tentukan Rencana Anggaran</button>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="formEbudgeting"></div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).on('click', '#anggaran', function(e) {
            e.preventDefault()

            var form = $('#formAnggaran')
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        $('#formEbudgeting').html(result.view)
                    }
                }
            })
        })

        $(document).on('click', '#SimpanAnggaran', function(e) {
            e.preventDefault()


            var form = $('#formRencanaAnggaran')
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        Toastr('success', result.msg)

                        location.reload();
                    }
                }
            })
        })

        $("#nominal").maskMoney({
            allowNegative: true
        });
    </script>
@endsection
