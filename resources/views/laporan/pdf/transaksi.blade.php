<!DOCTYPE html>
<html>
<head>
    <title>Laporan Riwayat Transaksi</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN RIWAYAT TRANSAKSI</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th>Tanggal</th>
                <th>Barang</th>
                <th>Gudang</th>
                <th>Jenis</th>
                <th class="text-center">Jumlah</th>
                <th class="text-center">Sld Sblm</th>
                <th class="text-center">Sld Ssdh</th>
                <th>Supplier</th>
                <th>User</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                <td>{{ $row->barang_nama }}</td>
                <td>{{ $row->gudang_nama }}</td>
                <td>{{ strtoupper($row->jenis) }}</td>
                <td class="text-center">{{ $row->jumlah }}</td>
                <td class="text-center">{{ $row->saldo_sebelum }}</td>
                <td class="text-center">{{ $row->saldo_sesudah }}</td>
                <td>{{ $row->supplier_nama ?? '-' }}</td>
                <td>{{ $row->user_nama }}</td>
            </tr>
            @empty
            <tr><td colspan="10" class="text-center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
