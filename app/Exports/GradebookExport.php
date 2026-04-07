<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class GradebookExport implements FromArray
{
    protected $data;
    protected $assessmentTypes;

    public function __construct($data, $assessmentTypes)
    {
        $this->data = $data;
        $this->assessmentTypes = $assessmentTypes;
    }

    public function array(): array
    {
        $rows = [];

        // HEADER
        $header = ['Nama Siswa'];

        foreach ($this->assessmentTypes as $type) {
            $header[] = $type['name'];
        }

        $header[] = 'Nilai Akhir';
        $header[] = 'Kontribusi Raport';

        $rows[] = $header;

        // DATA
        foreach ($this->data as $item) {
            $row = [$item['name']];

            foreach ($item['types'] as $type) {
                $row[] = $type['avg'];
            }

            $row[] = $item['final_normalized'];
            $row[] = $item['final_absolute'];

            $rows[] = $row;
        }

        return $rows;
    }
}
