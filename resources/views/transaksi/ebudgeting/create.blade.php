<form action="/transaksi/simpan_anggaran" method="post" id="formRencanaAnggaran">
    @csrf

    <input type="hidden" name="bulan" id="bulan" value="{{ $bulan }}">
    <input type="hidden" name="tahun" id="tahun" value="{{ $tahun }}">
    <table class="table table-striped">
        <thead class="bg-dark text-white">
            <tr>
                <th>Kode Akun</th>
                <th>Rencana</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($akun1 as $lev1)
                @php
                    $total = 0;
                @endphp
                <tr class="bg-secondary">
                    <td colspan="2" class="text-white fw-bold">
                        <b>{{ $lev1->kode_akun }}. {{ $lev1->nama_akun }}</b>
                    </td>
                </tr>
                @foreach ($lev1->akun2 as $lev2)
                    @foreach ($lev2->rek as $rek)
                            @php
                                $nominal = 0;
                                if ($jumlah > 0) {
                                    $nominal = $rek->eb->jumlah;
                                }

                                $jenis_mutasi = strtolower((string) $rek->jenis_mutasi);
                                $is_beban = in_array($jenis_mutasi, ['debet', 'debit'], true);
                                if ($jenis_mutasi === '') {
                                    $is_beban = in_array((string) $rek->lev1, ['5', '6'], true);
                                }
                                $group = $is_beban ? 'beban' : 'pendapatan';

                                $total += $nominal;
                            @endphp
                            <tr>
                                <td style="vertical-align: middle;">{{ $rek->kode_akun }}. {{ $rek->nama_akun }}</td>
                                <td style="vertical-align: middle;">
                                    <div class="input-group input-group-dynamic mb-0">
                                        <input data-group="{{ $group }}" data-lev1="{{ $lev1->kode_akun }}"
                                            autocomplete="off" type="text"
                                            name="jumlah[{{ $rek->kode_akun }}]" id="{{ $rek->kode_akun }}"
                                            class="form-control nominal" value="{{ number_format($nominal, 2) }}">
                                    </div>
                                </td>
                            </tr>
                    @endforeach
                @endforeach

                <tr class="bg-dark text-white">
                    <td>
                        <b>Total Rencana {{ $lev1->nama_akun }}</b>
                    </td>
                    <td>
                        <div class="total-rencana" data-lev1="{{ $lev1->kode_akun }}">
                            {{ number_format($total, 2) }}</div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</form>

<div class="d-flex justify-content-end">
    <button id="SimpanAnggaran" class="btn btn-sm btn-github mb-0">Simpan Rencana Anggaran</button>
</div>

<script>
    var formatter = new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })

    $(".nominal").maskMoney({
        allowNegative: true
    });

    function hitungTotalRencana() {
        $('.total-rencana').each(function() {
            var kode = $(this).data('lev1')
            var total = 0

            $('input.nominal[data-lev1="' + kode + '"]').each(function() {
                if ($(this).val() == '') $(this).val('0.00')
                var nominal = $(this).val().split(',').join('')

                total += parseInt(nominal) || 0
            })

            $(this).html(formatter.format(total))
        })
    }

    $(document).on('change', '.nominal', hitungTotalRencana)
</script>
