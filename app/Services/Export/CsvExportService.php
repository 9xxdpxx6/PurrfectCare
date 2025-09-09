<?php

namespace App\Services\Export;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CsvExportService
{
    /**
     * Export data to CSV format
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @return Response
     */
    public function export($data, string $filename, array $headers = []): Response
    {
        $csvData = $this->formatToCsv($data, $headers);
        
        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Format data to CSV string
     *
     * @param mixed $data
     * @param array $headers
     * @return string
     */
    protected function formatToCsv($data, array $headers = []): string
    {
        if (empty($data)) {
            return '';
        }

        $collection = $data instanceof Collection ? $data : collect($data);
        
        if ($collection->isEmpty()) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8 to ensure proper encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Get headers
        $csvHeaders = $this->getHeaders($collection->first(), $headers);
        
        // Write headers
        fputcsv($output, $csvHeaders, ';');

        // Write data rows
        foreach ($collection as $row) {
            $csvRow = $this->formatRow($row, $csvHeaders);
            fputcsv($output, $csvRow, ';');
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }

    /**
     * Get headers for CSV
     *
     * @param mixed $firstItem
     * @param array $customHeaders
     * @return array
     */
    protected function getHeaders($firstItem, array $customHeaders = []): array
    {
        if (!empty($customHeaders)) {
            return array_values($customHeaders);
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
     * Format single row for CSV
     *
     * @param mixed $row
     * @param array $headers
     * @return array
     */
    protected function formatRow($row, array $headers): array
    {
        $formattedRow = [];

        foreach ($headers as $header) {
            $value = $this->getNestedValue($row, $header);
            $formattedRow[] = $this->formatValue($value);
        }

        return $formattedRow;
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
     * Format value for CSV output
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

        if (is_numeric($value)) {
            return (string) $value;
        }

        // Convert to string and clean up
        $stringValue = (string) $value;
        
        // Replace problematic characters
        $stringValue = str_replace(["\r\n", "\r", "\n"], ' ', $stringValue);
        $stringValue = trim($stringValue);

        return $stringValue;
    }

    /**
     * Export with custom delimiter
     *
     * @param mixed $data
     * @param string $filename
     * @param array $headers
     * @param string $delimiter
     * @return Response
     */
    public function exportWithDelimiter($data, string $filename, array $headers = [], string $delimiter = ';'): Response
    {
        $csvData = $this->formatToCsvWithDelimiter($data, $headers, $delimiter);
        
        return response($csvData)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Format data to CSV string with custom delimiter
     *
     * @param mixed $data
     * @param array $headers
     * @param string $delimiter
     * @return string
     */
    protected function formatToCsvWithDelimiter($data, array $headers = [], string $delimiter = ';'): string
    {
        if (empty($data)) {
            return '';
        }

        $collection = $data instanceof Collection ? $data : collect($data);
        
        if ($collection->isEmpty()) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Add BOM for UTF-8 to ensure proper encoding in Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Get headers
        $csvHeaders = $this->getHeaders($collection->first(), $headers);
        
        // Write headers
        fputcsv($output, $csvHeaders, $delimiter);

        // Write data rows
        foreach ($collection as $row) {
            $csvRow = $this->formatRow($row, $csvHeaders);
            fputcsv($output, $csvRow, $delimiter);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
