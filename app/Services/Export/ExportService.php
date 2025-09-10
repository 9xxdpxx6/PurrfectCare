<?php

namespace App\Services\Export;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ExportService
{
    protected $excelService;
    protected $csvService;
    protected $pdfService;

    public function __construct(
        ExcelExportService $excelService,
        CsvExportService $csvService,
        PdfExportService $pdfService
    ) {
        $this->excelService = $excelService;
        $this->csvService = $csvService;
        $this->pdfService = $pdfService;
    }

    /**
     * Export data to Excel format
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @return BinaryFileResponse
     */
    public function toExcel($data, string $filename, array $headers = []): BinaryFileResponse
    {
        return $this->excelService->export($data, $filename, $headers);
    }

    /**
     * Export data to CSV format
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @return Response
     */
    public function toCsv($data, string $filename, array $headers = []): Response
    {
        return $this->csvService->export($data, $filename, $headers);
    }

    /**
     * Export data to PDF format
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @param string $template
     * @return Response
     */
    public function toPdf($data, string $filename, array $headers = [], string $template = 'default'): Response
    {
        return $this->pdfService->export($data, $filename, $headers, $template);
    }

    /**
     * Export data to Excel with multiple sheets
     *
     * @param array $sheetsData
     * @param string $filename
     * @return BinaryFileResponse
     */
    public function toExcelMultipleSheets(array $sheetsData, string $filename): BinaryFileResponse
    {
        return $this->excelService->exportMultipleSheets($sheetsData, $filename);
    }

    /**
     * Format data from query builder or collection
     *
     * @param mixed $query
     * @param array $columns
     * @return Collection
     */
    public function formatData($query, array $columns = []): Collection
    {
        if ($query instanceof Builder) {
            $data = $query->get();
        } elseif ($query instanceof Collection) {
            $data = $query;
        } else {
            $data = collect($query);
        }

        if (empty($columns)) {
            return $data;
        }

        return $data->map(function ($item) use ($columns) {
            $formatted = [];
            foreach ($columns as $key => $column) {
                if (is_string($column)) {
                    $formatted[$column] = $this->getNestedValue($item, $key);
                } else {
                    $formatted[$key] = $this->getNestedValue($item, $column);
                }
            }
            return $formatted;
        });
    }

    /**
     * Get nested value from object/array using dot notation
     *
     * @param mixed $item
     * @param string $key
     * @return mixed
     */
    protected function getNestedValue($item, string $key)
    {
        if (is_object($item)) {
            return data_get($item, $key);
        }
        
        if (is_array($item)) {
            return data_get($item, $key);
        }
        
        return $item;
    }

    /**
     * Generate filename with timestamp
     *
     * @param string $baseName
     * @param string $extension
     * @return string
     */
    public function generateFilename(string $baseName, string $extension): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        return "{$baseName}_{$timestamp}.{$extension}";
    }

    /**
     * Get appropriate content type for format
     *
     * @param string $format
     * @return string
     */
    public function getContentType(string $format): string
    {
        return match($format) {
            'excel', 'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream'
        };
    }
}
