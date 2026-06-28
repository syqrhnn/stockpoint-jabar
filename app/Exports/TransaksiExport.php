<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class TransaksiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Barang',
            'Gudang',
            'Jenis',
            'Jumlah',
            'Saldo Sebelum',
            'Saldo Sesudah',
            'Supplier',
            'User',
            'Catatan'
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            Carbon::parse($row->tanggal)->format('d/m/Y'),
            $row->barang_nama,
            $row->gudang_nama,
            strtoupper($row->jenis),
            $row->jumlah,
            $row->saldo_sebelum,
            $row->saldo_sesudah,
            $row->supplier_nama ?? '-',
            $row->user_nama,
            $row->catatan ?? '-'
        ];
    }
}
