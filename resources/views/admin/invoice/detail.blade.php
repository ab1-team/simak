@extends('admin.layouts.base')

@section('content')
    <div class="alert alert-info text-white fw-bold">
        <div class="text-white font-weight-bold">
            #Invoice - {{ str_pad($invoice->lokasi, 3, '0', STR_PAD_LEFT) }} {{ $invoice->usaha->nama_usaha }}
            - {{ $invoice->tgl_invoice }} Rp. {{ number_format($invoice->jumlah, 2) }}
        </div>
    </div>

    @if ($invoice->status == 'UNPAID')
        <div class="card" id="FormPembayaran">
            <div class="card-body">
                <form action="/db/invoice/{{ $invoice->idv }}/edit" method="post" id="FormPembayaranInvoice">
                    @method('PUT')
                    @csrf

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="tgl_bayar">Tanggal Bayar</label>
                                <input autocomplete="off" type="text" name="tgl_bayar" id="tgl_bayar"
                                    class="form-control form-control-sm date" value="{{ date('d/m/Y') }}">
                                <small class="text-danger" id="msg_tgl_bayar"></small>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <label for="nominal">Nominal</label>
                                <input autocomplete="off" type="text" name="nominal" id="nominal"
                                    class="form-control form-control-sm"
                                    value="{{ number_format($invoice->jumlah - $jumlah_trx, 2) }}">
                                <small class="text-danger" id="msg_nominal"></small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label for="keterangan">Keterangan</label>
                                <input autocomplete="off" type="text" name="keterangan" id="keterangan"
                                    class="form-control form-control-sm" value="{{ $invoice->jp->nama_jp }}">
                                <small class="text-danger" id="msg_keterangan"></small>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label class="form-label" for="metode_pembayaran">Metode Pembayaran</label>
                                <select class="form-control select2" name="metode_pembayaran" id="metode_pembayaran">
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    @foreach ($rekening as $rk)
                                        <option value="{{ $rk->kd_rekening }}">
                                            {{ $rk->nama_rekening }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-danger" id="msg_metode_pembayaran"></small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="/db/unpaid" class="btn btn-sm btn-warning mr-2">Kembali</a>
                        <button type="submit" id="SimpanPembayaran" class="btn btn-sm btn-primary">
                            Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body p-2">
                    <embed src="/pelaporan/invoice/{{ $invoice->idv }}" type="application/pdf" width="100%"
                        height="600px" id="Invoice" />

                    <div class="d-flex justify-content-end" {!! $invoice->status == 'PAID' ? '' : 'style="display: none"' !!} id="BtnKembali">
                        <a href="/db/paid" class="btn btn-sm btn-warning mr-2">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body p-1">
                    @php
                        $kec = optional(optional($invoice->usaha->d)->kec)->nama_kec ?? '';
                        $nominal_bayar = $trx_terakhir ? $trx_terakhir->jumlah : $invoice->jumlah;
                        $tgl_bayar = $trx_terakhir ? $trx_terakhir->tgl_transaksi : $invoice->tgl_lunas;
                    @endphp
                    <textarea class="form-control bg-white border-0"
                        style="padding:8px 10px; font-size:13px; line-height:1.45; resize:none; height:200px;" readonly>{{ $invoice->jp->nama_jp }}{{ $kec ? ' Kec. ' . $kec : '' }}

            *Yth. {{ $invoice->usaha->nama_usaha }}{{ $kec ? ' ' . $kec : '' }}*
            Terima kasih telah melakukan pembayaran *{{ $invoice->jp->nama_jp }} Kec. {{ $kec }}* sebesar *Rp. {{ number_format($nominal_bayar, 2, '.', ',') }}* pada tanggal *{{ date('d/m/Y', strtotime($tgl_bayar)) }}*. Silakan cetak Invoice Paid di menu Pengaturan - Invoice.

            Salam,
            PT. Asta Brata Teknologi</textarea>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(".date").flatpickr({
            dateFormat: "d/m/Y"
        })

        $("#nominal").maskMoney({
            allowNegative: true
        });

        $('.select2').select2({
            theme: 'bootstrap-5'
        })

        $(document).on('click', '#SimpanPembayaran', function(e) {
            e.preventDefault();

            $('small.text-danger').html('')
            var form = $('#FormPembayaranInvoice')
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function(result) {
                    if (result.success) {
                        var id = result.id
                        Swal.fire('Berhasil', result.msg, 'success')
                        if (result.lunas) {
                            $('#FormPembayaran').hide()
                            $('#BtnKembali').show()
                        }

                        var invoice = document.getElementById('Invoice');
                        var src = invoice.src;
                        invoice.src = src.split('?')[0] + "?t=" + new Date().getTime();
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
    </script>
@endsection
