<?php

namespace App\Services\Export;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelExportService
{
    /**
     * Export data to Excel format
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @return BinaryFileResponse
     */
    public function export($data, string $filename, array $headers = []): BinaryFileResponse
    {
        $export = new class($data, $headers) implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths {
            protected $data;
            protected $headers;

            public function __construct($data, $headers)
            {
                $this->data = $data;
                $this->headers = $headers;
            }

            public function collection()
            {
                if ($this->data instanceof Collection) {
                    return $this->data;
                }
                return collect($this->data);
            }

            public function headings(): array
            {
                if (!empty($this->headers)) {
                    return array_values($this->headers);
                }
                
                $firstItem = $this->collection()->first();
                if (!$firstItem) {
                    return [];
                }

                if (is_object($firstItem)) {
                    return array_keys($firstItem->toArray());
                }

                if (is_array($firstItem)) {
                    return array_keys($firstItem);
                }

                return [];
            }

            public function map($row): array
            {
                if (is_object($row)) {
                    return $row->toArray();
                }
                return (array) $row;
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => [
                            'bold' => true,
                            'size' => 12
                        ],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'E3F2FD'
                            ]
                        ]
                    ]
                ];
            }

            public function columnWidths(): array
            {
                $headings = $this->headings();
                $widths = [];
                
                foreach ($headings as $index => $heading) {
                    $widths[chr(65 + $index)] = 15; // A, B, C, etc.
                }
                
                return $widths;
            }
        };

        return Excel::download($export, $filename);
    }

    /**
     * Export multiple sheets to Excel
     *
     * @param array $sheetsData Array of ['name' => 'Sheet Name', 'data' => $data, 'headers' => $headers]
     * @param string $filename
     * @return BinaryFileResponse
     */
    public function exportMultipleSheets(array $sheetsData, string $filename): BinaryFileResponse
    {
        $export = new class($sheetsData) implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths {
            protected $sheetsData;

            public function __construct($sheetsData)
            {
                $this->sheetsData = $sheetsData;
            }

            public function collection()
            {
                return collect($this->sheetsData);
            }

            public function headings(): array
            {
                return ['Sheet Name', 'Data Count'];
            }

            public function map($row): array
            {
                return [
                    $row['name'],
                    is_array($row['data']) ? count($row['data']) : $row['data']->count()
                ];
            }

            public function styles(Worksheet $sheet)
            {
                return [
                    1 => [
                        'font' => ['bold' => true]
                    ]
                ];
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 20,
                    'B' => 15
                ];
            }
        };

        return Excel::download($export, $filename);
    }
}
