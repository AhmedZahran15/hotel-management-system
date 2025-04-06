<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ClientsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Client::with('approvedBy:id,name')->select(
            'id', 
            'name', 
            'country', 
            'gender', 
            'user_id', 
            'approved_by', 
            'created_at', 
            'updated_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Country',
            'Gender',
            'User ID',
            'Approved By',
            'Created At',
            'Updated At',
        ];
    }
    public function map($client): array
    {
        return [
            $client->id,
            $client->name,
            $client->country,
            $client->gender,
            $client->user_id,
            optional($client->approvedBy)->name ?? '"Not approved yet"',
            $client->created_at->format('Y-m-d H:i:s'),
            $client->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the entire first row (header row) as bold
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
            ],
        ];
    }
}
