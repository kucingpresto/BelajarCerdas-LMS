<?php

namespace App\Imports\SubjectPassingGradeCriteria;

use App\Imports\SubjectPassingGradeCriteria\SubjectPassingGradeCriteriaImport;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SubjectPassingGradeCriteriaSheetImport implements WithMultipleSheets
{
    protected $userId;
    protected $schoolId;
    protected $file;

    public function __construct($userId, $schoolId, $file)
    {
        $this->userId = $userId;
        $this->schoolId = $schoolId;
        $this->file = $file;
    }

    public function sheets(): array
    {
        // Inisialisasi array kosong untuk menyimpan semua sheet yang akan diimpor
        $sheets = [];

        // Load file Excel (.xlsx) ke dalam objek Spreadsheet
        // $this->file adalah file yang dikirim dari form upload
        // getRealPath() memberikan path file sementara yang bisa dibaca oleh PhpSpreadsheet
        $spreadsheet = IOFactory::load($this->file->getPathName());

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            // Buat instance SyllabusImport untuk tiap sheet. contoh:
            // Sheet dengan nama 'Bulk_Upload_Math' akan di-handle oleh SubjectPassingGradeCriteriaImport($userId, $schoolId, 'Bulk_Upload_Math')
            $sheets[$sheetName] = new SubjectPassingGradeCriteriaImport($this->userId, $this->schoolId, $sheetName);
        }

        return $sheets;
    }
}