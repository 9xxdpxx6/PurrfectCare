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
            return $this->getEmptyTemplate();
        }

        $tableHeaders = $this->getHeaders($collection->first(), $headers);
        $rows = $this->formatRows($collection, $tableHeaders);

        return $this->getTableTemplate($tableHeaders, $rows);
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
     * Get table template HTML
     *
     * @param array $headers
     * @param array $rows
     * @return string
     */
    protected function getTableTemplate(array $headers, array $rows): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Отчет</h2>
        <p>Дата создания: ' . now()->format('d.m.Y H:i') . '</p>
    </div>
    
    <table>
        <thead>
            <tr>';

        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }

        $html .= '</tr>
        </thead>
        <tbody>';

        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody>
    </table>
    
    <div class="footer">
        <p>Страница 1 из 1</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Get empty template HTML
     *
     * @return string
     */
    protected function getEmptyTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Export Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; text-align: center; }
        .empty { margin-top: 50px; color: #666; }
    </style>
</head>
<body>
    <div class="empty">
        <h3>Нет данных для экспорта</h3>
        <p>Дата создания: ' . now()->format('d.m.Y H:i') . '</p>
    </div>
</body>
</html>';
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
        // This can be extended to use Blade templates
        return $this->generateHtml($data, $headers, $template);
    }
}
