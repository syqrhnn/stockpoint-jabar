<!DOCTYPE html>
<html>
<head>
    <title>Laporan Status ROP</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN STATUS ROP</h2>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th>Barang</th>
                <th>Gudang</th>
                <th class="text-center">ADU</th>
                <th class="text-center">Lead Time</th>
                <th class="text-center">Safety Stock</th>
                <th class="text-center">ROP</th>
                <th class="text-center">Stok Aktual</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row->barang_nama }}</td>
                <td>{{ $row->gudang_nama }}</td>
                <td class="text-center">{{ $row->adu }}</td>
                <td class="text-center">{{ $row->lead_time }}</td>
                <td class="text-center">{{ $row->safety_stock }}</td>
                <td class="text-center">{{ $row->rop }}</td>
                <td class="text-center">{{ $row->stok_aktual }}</td>
                <td class="text-center">{{ strtoupper(str_replace('_', ' ', $row->status)) }}</td>
            </tr>
            @empty
            <tr><td colspan="9" class="text-center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
