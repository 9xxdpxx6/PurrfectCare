<?php

namespace App\Services\Export;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    /**
     * Export data to PDF format
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @param string $template
     * @return Response
     */
    public function export($data, string $filename, array $headers = [], string $template = 'default'): Response
    {
        $html = $this->generateHtml($data, $headers, $template);
        
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultMediaType' => 'print',
            'isFontSubsettingEnabled' => true,
        ]);
        
        return $pdf->download($filename);
    }

    /**
     * Generate HTML content for PDF
     *
     * @param mixed $data
     * @param array $headers
     * @param string $template
     * @return string
     */
    protected function generateHtml($data, array $headers = [], string $template = 'default'): string
    {
        $collection = $data instanceof Collection ? $data : collect($data);
        
        if ($collection->isEmpty()) {
            return view('admin.exports.pdf-empty')->render();
        }

        $tableHeaders = $this->getHeaders($collection->first(), $headers);
        $rows = $this->formatRows($collection, $tableHeaders);

        return view('admin.exports.pdf-table', compact('tableHeaders', 'rows'))->render();
    }

    /**
     * Get headers for PDF table
     *
     * @param mixed $firstItem
     * @param array $customHeaders
     * @return array
     */
    protected function getHeaders($firstItem, array $customHeaders = []): array
    {
        if (!empty($customHeaders)) {
            return $customHeaders;
        }

        if (is_object($firstItem)) {
            return array_keys($firstItem->toArray());
        }

        if (is_array($firstItem)) {
            return array_keys($firstItem);
        }

        return [];
    }

    /**
     * Format rows for PDF table
     *
     * @param Collection $collection
     * @param array $headers
     * @return array
     */
    protected function formatRows(Collection $collection, array $headers): array
    {
        $rows = [];

        foreach ($collection as $row) {
            $formattedRow = [];
            foreach ($headers as $header) {
                $value = $this->getNestedValue($row, $header);
                $formattedRow[] = $this->formatValue($value);
            }
            $rows[] = $formattedRow;
        }

        return $rows;
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
     * Format value for PDF output
     *
     * @param mixed $value
     * @return string
     */
    protected function formatValue($value): string
    {
        if (is_null($value)) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }


    /**
     * Export with custom template
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @param string $template
     * @param array $options
     * @return Response
     */
    public function exportWithTemplate($data, string $filename, array $headers = [], string $template = 'default', array $options = []): Response
    {
        $html = $this->generateCustomHtml($data, $headers, $template, $options);
        
        $pdf = Pdf::loadHTML($html);
        
        $paper = $options['paper'] ?? 'A4';
        $orientation = $options['orientation'] ?? 'landscape';
        
        $pdf->setPaper($paper, $orientation);
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultMediaType' => 'print',
            'isFontSubsettingEnabled' => true,
        ]);
        
        return $pdf->download($filename);
    }

    /**
     * Generate custom HTML content
     *
     * @param mixed $data
     * @param array $headers
     * @param string $template
     * @param array $options
     * @return string
     */
    protected function generateCustomHtml($data, array $headers = [], string $template = 'default', array $options = []): string
    {
        // Use the same logic as generateHtml for now
        // Can be extended to use different templates based on $template parameter
        return $this->generateHtml($data, $headers, $template);
    }
}
