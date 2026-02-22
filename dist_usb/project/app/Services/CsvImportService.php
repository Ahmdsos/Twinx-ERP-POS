<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

/**
 * CsvImportService
 * Handles CSV file parsing and validation for bulk imports
 */
class CsvImportService
{
    protected array $errors = [];
    protected int $successCount = 0;
    protected int $errorCount = 0;

    /**
     * Parse CSV file and return collection of rows
     */
    public function parseFile(UploadedFile $file, bool $hasHeader = true): Collection
    {
        $rows = collect();
        $handle = fopen($file->getPathname(), 'r');

        if ($handle === false) {
            throw new \Exception('فشل في فتح الملف');
        }

        // Detect delimiter
        $firstLine = fgets($handle);
        rewind($handle);
        $delimiter = $this->detectDelimiter($firstLine);

        $lineNumber = 0;
        $headers = [];

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $lineNumber++;

            // Skip empty lines
            if (count($data) === 1 && empty($data[0])) {
                continue;
            }

            // First line is header
            if ($hasHeader && $lineNumber === 1) {
                $headers = array_map('trim', $data);
                // Clean BOM from first header
                if (!empty($headers[0])) {
                    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
                }
                continue;
            }

            // Convert to associative array if we have headers
            if ($hasHeader && !empty($headers)) {
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = isset($data[$index]) ? trim($data[$index]) : '';
                }
                $row['_line'] = $lineNumber;
                $rows->push($row);
            } else {
                $rows->push(array_merge($data, ['_line' => $lineNumber]));
            }
        }

        fclose($handle);
        return $rows;
    }

    /**
     * Detect CSV delimiter (comma, semicolon, tab)
     */
    protected function detectDelimiter(string $line): string
    {
        $delimiters = [
            ',' => substr_count($line, ','),
            ';' => substr_count($line, ';'),
            "\t" => substr_count($line, "\t"),
        ];

        return array_search(max($delimiters), $delimiters);
    }

    /**
     * Validate row data against rules
     */
    public function validateRow(array $row, array $rules, int $lineNumber): ?array
    {
        $validator = Validator::make($row, $rules);

        if ($validator->fails()) {
            $this->errors[] = [
                'line' => $lineNumber,
                'errors' => $validator->errors()->all(),
            ];
            $this->errorCount++;
            return null;
        }

        $this->successCount++;
        return $validator->validated();
    }

    /**
     * Get import results
     */
    public function getResults(): array
    {
        return [
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
        ];
    }

    /**
     * Reset counters for new import
     */
    public function reset(): void
    {
        $this->errors = [];
        $this->successCount = 0;
        $this->errorCount = 0;
    }

    /**
     * Generate sample CSV content for download
     */
    public static function generateSampleCsv(array $headers, array $sampleRow = []): string
    {
        $output = implode(',', $headers) . "\n";
        if (!empty($sampleRow)) {
            $output .= implode(',', $sampleRow) . "\n";
        }
        return "\xEF\xBB\xBF" . $output; // Add BOM for UTF-8
    }
}
