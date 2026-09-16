@php
    use App\Utils\Tanggal;

    $kuitansi = false;
    $files = 'bm';
    if (
        $keuangan->startWith($trx->rekening_debit, '1.1.01') &&
        !$keuangan->startWith($trx->rekening_kredit, '1.1.01')
    ) {
        $files = 'bkm';
        $kuitansi = true;
    }
    if (
        !$keuangan->startWith($trx->rekening_debit, '1.1.01') &&
        $keuangan->startWith($trx->rekening_kredit, '1.1.01')
    ) {
        $files = 'bkk';
        $kuitansi = true;
    }
    if ($keuangan->startWith($trx->rekening_debit, '1.1.01') && $keuangan->startWith($trx->rekening_kredit, '1.1.01')) {
        $files = 'bm';
        $kuitansi = false;
    }
    if (
        $keuangan->startWith($trx->rekening_debit, '1.1.02') &&
        !(
            $keuangan->startWith($trx->rekening_kredit, '1.1.01') ||
            $keuangan->startWith($trx->rekening_kredit, '1.1.02')
        )
    ) {
        $files = 'bkm';
        $kuitansi = true;
    }
    if ($keuangan->startWith($trx->rekening_debit, '1.1.02') && $keuangan->startWith($trx->rekening_kredit, '1.1.02')) {
        $files = 'bm';
        $kuitansi = false;
    }
    if ($keuangan->startWith($trx->rekening_debit, '1.1.02') && $keuangan->startWith($trx->rekening_kredit, '1.1.01')) {
        $files = 'bm';
        $kuitansi = false;
    }
    if ($keuangan->startWith($trx->rekening_debit, '1.1.01') && $keuangan->startWith($trx->rekening_kredit, '1.1.02')) {
        $files = 'bm';
        $kuitansi = false;
    }
    if (
        $keuangan->startWith($trx->rekening_debit, '5.') &&
        !(
            $keuangan->startWith($trx->rekening_kredit, '1.1.01') ||
            $keuangan->startWith($trx->rekening_kredit, '1.1.02')
        )
    ) {
        $files = 'bm';
        $kuitansi = false;
    }
    if (
        !(
            $keuangan->startWith($trx->rekening_debit, '1.1.01') || $keuangan->startWith($trx->rekening_debit, '1.1.02')
        ) &&
        $keuangan->startWith($trx->rekening_kredit, '1.1.02')
    ) {
        $files = 'bm';
        $kuitansi = false;
    }
    if (
        !(
            $keuangan->startWith($trx->rekening_debit, '1.1.01') || $keuangan->startWith($trx->rekening_debit, '1.1.02')
        ) &&
        $keuangan->startWith($trx->rekening_kredit, '4.')
    ) {
        $files = 'bm';
        $kuitansi = false;
    }
@endphp

<div class="row">
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-body">
                <h6 class="text-uppercase text-body font-small-1 font-weight-bolder">
                    {{ $trx->keterangan_transaksi }}
                </h6>
                <ul class="list-group">
                    <li class="list-group-item border-0 d-flex justify-content-between border-radius-lg">
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <h6 class="mb-0 text-dark font-small-3">
                                    {{ $trx->rek_kredit?->kode_akun }} - {{ $trx->rek_kredit?->nama_akun }}
                                </h6>
                                <span class="font-small-1">{{ Tanggal::tglLatin($trx->tgl_transaksi) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center text-danger text-gradient font-small-3 font-weight-bold">
                            - Rp. {{ number_format($trx->jumlah, 2) }}
                        </div>
                    </li>
                    <li class="list-group-item border-0 d-flex justify-content-between border-radius-lg">
                        <div class="d-flex align-items-center">
                            <div class="d-flex flex-column">
                                <h6 class="mb-0 text-dark font-small-3">
                                    {{ $trx->rek_debit?->kode_akun }} - {{ $trx->rek_debit?->nama_akun }}
                                </h6>
                                <span class="font-small-1">{{ Tanggal::tglLatin($trx->tgl_transaksi) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center text-success text-gradient font-small-3 font-weight-bold">
                            + Rp. {{ number_format($trx->jumlah, 2) }}
                        </div>
                    </li>
                </ul>

                <div class="d-flex justify-content-end" style="gap: 1rem">
                    <div class="d-flex justify-content-end" style="gap: 1rem">
                        @if ($kuitansi && in_array('jurnal_umum.cetak_kuitansi', Session::get('tombol')))
                            <button type="button" data-action="/transaksi/dokumen/kuitansi/{{ $trx->idt }}"
                                class="btn btn-sm btn-info btn-link">Kuitansi</button>
                        @endif
                        @if (in_array('jurnal_umum.cetak_voucher', Session::get('tombol')))
                            <button type="button"
                                data-action="/transaksi/dokumen/{{ $files }}/{{ $trx->idt }}"
                                class="btn btn-sm btn-info btn-link">{{ $files }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
