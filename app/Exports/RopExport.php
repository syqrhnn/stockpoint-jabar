<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RopExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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
            'Barang',
            'Gudang',
            'ADU',
            'Lead Time (Hari)',
            'Safety Stock',
            'ROP',
            'Stok Aktual',
            'Status'
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;
        return [
            $no,
            $row->barang_nama,
            $row->gudang_nama,
            $row->adu,
            $row->lead_time,
            $row->safety_stock,
            $row->rop,
            $row->stok_aktual,
            strtoupper($row->status)
        ];
    }
}
