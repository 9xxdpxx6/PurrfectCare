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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
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
     * Export multiple sheets to Excel format
     *
     * @param array $sheetsData Array of sheet data with structure:
     * [
     *   'sheet_name' => [
     *     'data' => [...],
     *     'headers' => [...]
     *   ]
     * ]
     * @param string $filename
     * @return BinaryFileResponse
     */
    public function exportMultipleSheets(array $sheetsData, string $filename): BinaryFileResponse
    {
        $export = new class($sheetsData) implements WithMultipleSheets {
            protected $sheetsData;

            public function __construct($sheetsData)
            {
                $this->sheetsData = $sheetsData;
            }

            public function sheets(): array
            {
                $sheets = [];
                
                foreach ($this->sheetsData as $sheetName => $sheetData) {
                    $sheets[] = new class($sheetName, $sheetData) implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle {
                        protected $sheetName;
                        protected $data;
                        protected $headers;

                        public function __construct($sheetName, $sheetData)
                        {
                            $this->sheetName = $sheetName;
                            $this->data = $sheetData['data'] ?? [];
                            $this->headers = $sheetData['headers'] ?? [];
                        }

                        public function collection()
                        {
                            return collect($this->data);
                        }

                        public function headings(): array
                        {
                            if (!empty($this->headers)) {
                                return array_values($this->headers);
                            }
                            
                            if (!empty($this->data)) {
                                return array_keys($this->data[0] ?? []);
                            }
                            
                            return [];
                        }

                        public function map($row): array
                        {
                            return array_values($row);
                        }

                        public function title(): string
                        {
                            return $this->sheetName;
                        }

                        public function styles(Worksheet $sheet)
                        {
                            return [
                                1 => ['font' => ['bold' => true]],
                            ];
                        }

                        public function columnWidths(): array
                        {
                            $widths = [];
                            $headers = $this->headings();
                            
                            foreach ($headers as $index => $header) {
                                $widths[chr(65 + $index)] = 15;
                            }
                            
                            return $widths;
                        }
                    };
                }
                
                return $sheets;
            }
        };

        return Excel::download($export, $filename);
    }
}
