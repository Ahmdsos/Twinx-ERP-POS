<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ImportExportService
{
    /**
     * Export data to Excel/CSV
     *
     * @param object $exportClass The export class instance (implementing Maatwebsite\Excel\Concerns\FromCollection or similar)
     * @param string $fileName The desired filename (e.g., 'products.xlsx')
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export($exportClass, string $fileName)
    {
        return Excel::download($exportClass, $fileName);
    }

    /**
     * Import data from Excel/CSV
     *
     * @param object $importClass The import class instance (implementing Maatwebsite\Excel\Concerns\ToModel or similar)
     * @param UploadedFile $file The uploaded file logic
     * @return void
     */
    public function import($importClass, UploadedFile $file)
    {
        Excel::import($importClass, $file);
    }
}
