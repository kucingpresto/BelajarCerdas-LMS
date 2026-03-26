<?php

namespace App\Imports\SubjectPassingGradeCriteria;

use App\Events\LmsSubjectPassingGradeCriteria;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\Mapel;
use App\Models\SchoolClass;
use App\Models\SubjectPassingGradeCriteria;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\WithTitle;

class SubjectPassingGradeCriteriaImport implements ToCollection, WithHeadingRow, WithStartRow, WithTitle
{
    /**
    * @param Collection $collection
    */

    protected $userId;
    protected $schoolId;
    protected $sheetTitle = '';

    public function __construct($userId, $schoolId, $sheetTitle = '')
    {
        $this->userId = $userId;
        $this->schoolId = $schoolId;
        $this->sheetTitle = $sheetTitle;
    }

    public function title(): string
    {
        return $this->sheetTitle; // set sheet title untuk indetifikasi error pada sheet mana
    }

    public function headingRow(): int
    {
        return 2; // <-- kalo pake WithHeadingRow header row diambil dari kolom pertama, jadi kalo header row tidak di kolom pertama harus di return seperti ini
    }
    public function startRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        // Jika sheet kosong -> langsung lempar error
        if ($rows->isEmpty() || $rows->every(fn($r) => $r->filter()->isEmpty())) {
            throw ValidationException::withMessages([
                'import' => ["File Excel kosong atau tidak memiliki data valid"]
            ]);
        }

        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 3;

            $schoolId = $this->schoolId;

            // Validasi awal jika ingin diaktifkan kembali
            $validator = Validator::make($row->toArray(), [
                'kurikulum' => 'required',
                'kelas' => 'required',
                'mata_pelajaran' => 'required',
                'tahun_ajaran' => 'required',
                'nilai_kkm' => 'required',
            ], [
                "kurikulum.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Kurikulum wajib diisi.",
                "kelas.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Kelas wajib diisi.",
                "mata_pelajaran.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Mata Pelajaran wajib diisi.",
                "tahun_ajaran.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Tahun Ajaran wajib diisi.",
                "nilai_kkm.required" => "Sheet {$this->sheetTitle} - Baris $rowNumber: Kolom Nilai KKM wajib diisi.",
            ]);

            if ($validator->fails()) {
                $errors = array_merge($errors, $validator->errors()->all());
                continue;
            }

            $curriculum = Kurikulum::where('nama_kurikulum', $row['kurikulum'])->first();

            if (!$curriculum) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris $rowNumber: Kurikulum tidak ditemukan.";
                continue;
            }

            $class = Kelas::where('kurikulum_id', $curriculum->id)->where('kelas', $row['kelas'])->first();

            if (!$class) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris $rowNumber: Kelas tidak ditemukan.";
                continue;
            }

            $subject = Mapel::where(function ($query) use ($schoolId) {

                $query->whereHas('SchoolMapel', function ($q1) use ($schoolId) {
                    $q1->where('school_partner_id', $schoolId)
                    ->where('is_active', 1);
                })

                ->orWhere(function ($q2) use ($schoolId) {
                    $q2->whereNull('school_partner_id')
                    ->where('status_mata_pelajaran', 'active')
                    ->whereDoesntHave('SchoolMapel', function ($sq) use ($schoolId) {
                        $sq->where('school_partner_id', $schoolId);
                    });
                });

            })
            ->where('kelas_id', $class->id)
            ->where('mata_pelajaran', $row['mata_pelajaran'])
            ->first();

            if (!$subject) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris $rowNumber: Mata Pelajaran tidak ditemukan.";
                continue;
            }

            $schoolClass = SchoolClass::where('school_partner_id', $schoolId)->where('kelas_id', $class->id)->where('tahun_ajaran', $row['tahun_ajaran'])->first();

            if (!$schoolClass) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris $rowNumber: Tahun ajaran di kelas tersebut tidak terdaftar.";
                continue;
            }

        try {
            $subjectPassingGradeCriteria = SubjectPassingGradeCriteria::create([
                'user_id' => $this->userId,
                'school_partner_id' => $schoolId,
                'kelas_id' => $class->id,
                'mapel_id' => $subject->id,
                'school_year' => $row['tahun_ajaran'],
                'kkm_value' => $row['nilai_kkm'],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = "Sheet {$this->sheetTitle} - Baris $rowNumber: Nilai KKM pada mata pelajaran di tahun ajaran ini telah terdaftar.";
                continue;
            }

            throw $e;
        }

            // Broadcast event
            if (isset($subjectPassingGradeCriteria)) {
                broadcast(new LmsSubjectPassingGradeCriteria('SubjectPassingGradeCriteria', 'import', [$subjectPassingGradeCriteria]))->toOthers();
            }
        }

        // Handle error
        if (!empty($errors)) {
            throw ValidationException::withMessages(['import' => $errors]);
        }
    }
}