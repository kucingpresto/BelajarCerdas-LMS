<?php

namespace App\Http\Controllers;

use App\Events\ActivateQuestionBankPG;
use App\Events\BankSoalLmsEditPG;
use App\Events\LmsAssessmentTypeManagement;
use App\Events\LmsAssessmentWeightManagement;
use App\Events\LmsContentManagement;
use App\Events\LmsSchoolSubscription;
use App\Events\LmsManagementAccount;
use App\Events\LmsManagementClass;
use App\Events\LmsManagementMajors;
use App\Events\LmsManagementStudentInClass;
use App\Models\AssessmentMode;
use App\Models\Fase;
use App\Models\Kelas;
use App\Models\Kurikulum;
use App\Models\LmsContent;
use App\Models\LmsQuestionBank;
use App\Models\LmsQuestionOption;
use App\Models\SchoolAssessmentType;
use App\Models\SchoolAssessmentTypeWeight;
use App\Models\SchoolClass;
use App\Models\SchoolLmsContent;
use App\Models\SchoolLmsSubscription;
use App\Models\SchoolMajor;
use App\Models\SchoolPartner;
use App\Models\SchoolQuestionBank;
use App\Models\SchoolStaffProfile;
use App\Models\ServiceRule;
use App\Models\StudentSchoolClass;
use App\Models\TeacherMapel;
use App\Models\UserAccount;
use App\Services\LMS\BankSoalWordImportService;
use App\Services\LMS\LmsContentService;
use App\Services\ReviewContent\LmsReviewContentService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LmsController extends Controller
{
    // CONSTRUCT LMS REVIEW CONTENT
    public function __construct(protected LmsReviewContentService $reviewContentService) 
    {}

    // HELPER NAMING CLASS
    private function extractClassLevel(string $className): int
    {
        $className = trim(strtoupper($className));

        // 1. Coba angka di depan (7, 10, 12, dst)
        if (preg_match('/^\d+/', $className, $match)) {
            return (int) $match[0];
        }

        // 2. Coba romawi di depan (I, II, III, IV, V, VI, VII, VIII, IX, X, XI, XII)
        if (preg_match('/^(XII|XI|X|IX|VIII|VII|VI|V|IV|III|II|I)\b/', $className, $match)) {
            return $this->romanToInt($match[0]);
        }

        return 0; // fallback aman
    }

    private function romanToInt(string $roman): int
    {
        $map = [
            'I' => 1,
            'II' => 2,
            'III' => 3,
            'IV' => 4,
            'V' => 5,
            'VI' => 6,
            'VII' => 7,
            'VIII' => 8,
            'IX' => 9,
            'X' => 10,
            'XI' => 11,
            'XII' => 12,
        ];

        return $map[$roman] ?? 0;
    }

    // function lms school subscription view
    public function lmsSchoolSubscriptionView()
    {
        return view('features.lms.administrator.lms-school-subscription');
    }

    // function pagiante lms school subscription
    public function paginateLmsSchoolSubscription(Request $request)
    {
        $today = now()->format('Y-m-d');

        $lmsSchoolSubscription = SchoolLmsSubscription::whereHas('Transaction', function ($query) {
            $query->where('transaction_status', 'Berhasil');
        })->where('end_date', '<', $today)->get();

        if ($lmsSchoolSubscription) {
            foreach ($lmsSchoolSubscription as $history) {
                $history->update([
                    'subscription_status' => 'tidak_aktif'
                ]);
            }
        }

        $lmsSchoolSubscription = SchoolPartner::with(['UserAccount.SchoolStaffProfile', 'SchoolLmsSubscription' => function ($query) {
            $query->whereHas('transaction', function ($q) {
                $q->where('transaction_status', 'Berhasil');
            })->orderByDesc('start_date')->limit(1); // ambil subscription terbaru
        }
        ])->orderBy('updated_at', 'desc');


        // Filter school
        if ($request->filled('search_school')) {
            $search = $request->search_school;
            $lmsSchoolSubscription->where('nama_sekolah', 'LIKE', "%{$search}%");
        }

        $paginated = $lmsSchoolSubscription->paginate(20);

        return response()->json([
            'data' => $paginated->values(), // flat array, bukan nested
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'lmsAcademicManagement' => '/lms/school-subscription/:schoolName/:schoolId/academic-management',
        ]);
    }

    // function activate lms school subscription
    public function lmsSchoolSubscriptionActivate(Request $request, $subscriptionId)
    {
        $subscription = SchoolLmsSubscription::findOrFail($subscriptionId);

        $subscription->update([
            'subscription_status' => $request->subscription_status,
        ]);

        broadcast(new LmsSchoolSubscription($subscription))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription status updated successfully',
            'subscription' => $subscription
        ]);
    }

    // function lms academic management
    public function lmsAcademicManagementView($schoolName, $schoolId)
    {
        return view('features.lms.administrator.academic-management.lms-academic-management', compact('schoolName', 'schoolId'));
    }

    // function paginate lms academic management
    public function paginateLmsAcademicManagement($schoolName, $schoolId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $groupedRoles = $users->groupBy('role');

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $groupedRoles->values(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'lmsRoleManagement' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/',
            'lmsQuestionBankManagement' => '/lms/school-subscription/:schoolName/:schoolId/question-bank-management/',
            'lmsCurriculumManagementBySchool' => '/lms/school-subscription/:schoolName/:schoolId/kurikulum',
            'lmsContentManagement' => '/lms/school-subscription/:schoolName/:schoolId/content-management',
            'lmsAssessmentTypeManagement' => '/lms/school-subscription/:schoolName/:schoolId/assessment-type-management',
            'lmsTeacherSubjectManagement' => '/lms/school-subscription/:schoolName/:schoolId/subject-teacher-management',
            'lmsAssessmentWeightManagement' => '/lms/school-subscription/:schoolName/:schoolId/assessment-weight-management',
        ]);
    }

    // function lms management roles view
    public function lmsManagementRolesView($schoolName, $schoolId)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-role-account', compact('schoolName', 'schoolId'));
    }

    // function paginate lms management roles
    public function paginateLmsSchoolSubscriptionRoleAccount(Request $request, $schoolName, $schoolId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $groupedRoles = $users->groupBy('role');

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $groupedRoles->values(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'lmsManagementAccounts' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-accounts',
            'lmsManagementMajors' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-majors',
            'lmsManagementClass' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-class',
        ]);
    }

    // function lms management account view
    public function lmsManagementAccountView($schoolName, $schoolId, $role)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-account', compact('schoolName', 'schoolId', 'role'));
    }

    // function paginate lms management account
    public function paginateLmsSchoolAccount(Request $request, $schoolName, $schoolId, $role)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->where('role', $role);

        // Filter school
        if ($request->filled('search_user')) {
            $search = $request->search_user;

            $users->where(function ($q) use ($search) {
                $q->whereHas('StudentProfile', function ($s) use ($search) {
                    $s->where('nama_lengkap', 'LIKE', "%{$search}%");
                })->orWhereHas('SchoolStaffProfile', function ($s) use ($search) {
                    $s->where('nama_lengkap', 'LIKE', "%{$search}%");
                });
            });
        }

        $countUsers = $users->count();

        $paginated = $users->paginate(10);

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        return response()->json([
            'data' => $paginated->items(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
        ]);
    }

    // function lms activate account
    public function lmsActivateAccount(Request $request, $schoolId, $id)
    {
        DB::beginTransaction();

        try {
            $user = UserAccount::findOrFail($id);

            // Pastikan ini kepsek
            if ($user->role !== 'Kepala Sekolah') {
                $user->update(['status_akun' => $request->status_akun]);
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil mengubah status akun',
                ]);
            }

            // Ambil semua kepsek di sekolah ini
            $kepsekQuery = UserAccount::where('role', 'Kepala Sekolah')
                ->whereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                    $q->where('school_partner_id', $schoolId);
                });

            $activeKepsek = (clone $kepsekQuery)->where('status_akun', 'aktif')->get();

            // ==========================
            // KASUS 1: AKTIFKAN KEPSEK
            // ==========================
            if ($request->status_akun === 'aktif') {

                // Nonaktifkan kepsek aktif lainnya
                $kepsekQuery
                    ->where('status_akun', 'aktif')
                    ->where('id', '!=', $user->id)
                    ->update(['status_akun' => 'non-aktif']);

                // Aktifkan kepsek ini
                $user->update(['status_akun' => 'aktif']);

                // Update kepsek_id di SchoolPartner
                SchoolPartner::where('id', $schoolId)->update(['kepsek_id' => $user->id]);

                broadcast(new LmsManagementAccount($user))->toOthers();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Kepala sekolah berhasil diaktifkan dan kepsek lain dinonaktifkan.',
                ]);
            }

            // ==========================
            // KASUS 2: NONAKTIFKAN KEPSEK
            // ==========================
            if ($request->status_akun === 'non-aktif') {

                // Jika hanya ada 1 kepsek aktif → TOLAK
                if ($activeKepsek->count() <= 1) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'cannotDeactivateLastKepsek' => true,
                        'message' => 'Minimal harus ada satu Kepala Sekolah yang aktif.',
                    ], 422);
                }

                $user->update(['status_akun' => 'non-aktif']);
                DB::commit();

                broadcast(new LmsManagementAccount($user))->toOthers();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Kepala sekolah berhasil dinonaktifkan',
                ]);
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // function lms management majors view
    public function lmsManagementMajorsView($schoolName, $schoolId, $role)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-majors', compact('schoolName', 'schoolId', 'role'));
    }

    // function paginate lms management majors
    public function paginateLmsSchoolSubscriptionMajors(Request $request, $schoolName, $schoolId, $role)
    {
        $majors = SchoolMajor::withCount([
            'schoolClass as school_class_count' => function ($q) {
                $q->where('status_major', 'active');
            }
        ])->where('school_partner_id', $schoolId)->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        return response()->json([
            'data' => $majors,
            'schoolIdentity' => $getSchool,
            'lmsManagementClass' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-majors/:majorId/management-class',
        ]);
    }

    // function lms management create majors
    public function lmsManagementCreateMajor(Request $request, $schoolName, $schoolId, $role)
    {
        $validator = Validator::make($request->all(), [
            'major_name' => [
                'required',
                Rule::unique('school_majors', 'major_name')->where('school_partner_id', $schoolId),
            ],
            'major_code' => [
                'required',
                Rule::unique('school_majors', 'major_code')->where('school_partner_id', $schoolId),
            ]
        ], [
            'major_name.required' => 'Nama jurusan harus diisi.',
            'major_name.unique' => 'Nama jurusan telah terdaftar pada sekolah ini.',
            'major_code.required' => 'Kode jurusan harus diisi.',
            'major_code.unique' => 'Kode jurusan telah terdaftar pada sekolah ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $major = SchoolMajor::create([
            'school_partner_id' => $schoolId,
            'major_name' => request()->major_name,
            'major_code' => request()->major_code,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'create', $major))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menambahkan jurusan',
        ]);
    }

    // function lms management edit major
    public function lmsManagementEditMajor(Request $request, $schoolName, $schoolId, $role, $majorId)
    {
        $validator = Validator::make($request->all(), [
            'major_name' => [
                'required',
                Rule::unique('school_majors', 'major_name')->where('school_partner_id', $schoolId)->ignore($majorId),
            ],
            'major_code' => [
                'required',
            ]
        ], [
            'major_name.required' => 'Nama jurusan harus diisi.',
            'major_name.unique' => 'Nama jurusan telah terdaftar pada sekolah ini.',
            'major_code.required' => 'Kode jurusan harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }
        $major = SchoolMajor::findOrFail($majorId);

        $major->update([
            'school_partner_id' => $schoolId,
            'major_name' => $request->major_name,
            'major_code' => $request->major_code,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'edit', $major));

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil edit jurusan',
        ]);
    }

    // function lms activate major
    public function lmsActivateMajor(Request $request, $id)
    {
        $major = SchoolMajor::findOrFail($id);

        $major->update([
            'status_major' => $request->status_major,
        ]);

        broadcast(new LmsManagementMajors('SchoolMajor', 'activate', $major))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status jurusan',
        ]);
    }

    // function lms management class view
    public function lmsManagementClassView($schoolName, $schoolId, $role, $majorId = null)
    {
        $getSchool = SchoolPartner::where('id', $schoolId)->first();

        $phaseMap = [
            'SD' => ['fase a', 'fase b', 'fase c'],
            'MI' => ['fase a', 'fase b', 'fase c'],
            'SMP' => ['fase d'],
            'MTS' => ['fase d'],
            'SMA' => ['fase e', 'fase f'],
            'SMK' => ['fase e', 'fase f'],
            'MA' => ['fase e', 'fase f'],
            'MAK' => ['fase e', 'fase f'],
        ];

        $allowedPhases = $phaseMap[$getSchool->jenjang_sekolah] ?? [];

        $phases = Fase::whereIn(DB::raw('LOWER(kode)'), $allowedPhases)->get();

        return view('Features.lms.administrator.lms-school-subscription-management-class', compact('schoolName', 'schoolId', 'role', 'majorId', 'phases'));
    }

    // function paginate lms management class
    public function paginateLmsSchoolSubscriptionClass(Request $request, $schoolName, $schoolId, $role, $majorId = null) 
    {
        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $startLevelMap = [
            'SD' => 1,
            'MI' => 1,
            'SMP' => 7,
            'MTS' => 7,
            'SMA' => 10,
            'SMK' => 10,
            'MA' => 10,
            'MAK' => 10
        ];

        $defaultLevel = $startLevelMap[$getSchool->jenjang_sekolah] ?? 1;

        // level dari dropdown (optional)
        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : $defaultLevel;
        $selectedYear = $request->filled('search_year') ? $request->search_year : SchoolClass::where('school_partner_id', $schoolId)
        ->orderBy('tahun_ajaran')->value('tahun_ajaran');

        $getClassQuery = SchoolClass::with(['UserAccount', 'UserAccount.SchoolStaffProfile', 'Kelas'])
            ->withCount([
                'StudentSchoolClass as student_school_class_count' => function ($q) {
                    $q->where('student_class_status', 'active')
                    ->where(function ($sub) {
                        $sub->whereNull('academic_action')
                            ->orWhere('academic_action', '');
                    });
                }
            ])
            ->where('school_partner_id', $schoolId)
            ->where('tahun_ajaran', $selectedYear);

        if ($majorId) {
            $getClassQuery->where('major_id', $majorId);
        }        

        $getClass = $getClassQuery->get()->filter(function ($class) use ($selectedClass) {
            return $this->extractClassLevel($class->class_name) === $selectedClass;
        })->values();


        $className = SchoolClass::where('school_partner_id', $schoolId)->pluck('class_name')->map(function ($className) {
            return $this->extractClassLevel($className);
        })->unique()->sort()->values();

        // ambil tahun ajaran berdasarkan tingkat kelas
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->when($majorId, function ($q) use ($majorId) {
            $q->where('major_id', $majorId);
        })->get()->filter(function ($class) use ($selectedClass) {
            if (!$selectedClass) return true;
                return $this->extractClassLevel($class->class_name) === $selectedClass;
            })->pluck('tahun_ajaran')->unique()->sort()->values();

        return response()->json([
            'data' => $getClass,
            'schoolIdentity' => $getSchool,
            'className' => $className,
            'tahunAjaran' => $tahunAjaran,
            'selectedYear' => $selectedYear,
            'selectedClass' => $selectedClass,
            'lmsManagementStudentsWithMajor' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-class/:classId/management-majors/:majorId/management-students',
            'lmsManagementStudentsNoMajor' => '/lms/school-subscription/:schoolName/:schoolId/management-role-account/:role/management-class/:classId/management-students',
        ]);
    }

    // function lms management create class
    public function lmsManagementCreateClass(Request $request, $schoolName, $schoolId, $role, $majorId = null)
    {
        // Rule dasar yang selalu berlaku
        $rules = [
            'fase_id' => 'required',
            'kelas_id' => 'required',
            'akun_wali_kelas' => 'required|email|regex:/^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/',
            'tahun_ajaran' => 'required',
        ];

        // Membuat rule unique untuk class_name
        $classNameRule = Rule::unique('school_classes', 'class_name')->where('tahun_ajaran', $request->tahun_ajaran)->where('school_partner_id', $schoolId);

        // Jika MAJOR ID ada (kelas berbasis jurusan),
        if ($majorId) {
            $classNameRule->where('major_id', $majorId);
        }

        // Menambahkan rule class_name ke dalam rules utama
        $rules['class_name'] = ['required', $classNameRule];

        // Tentukan pesan error unique berdasarkan ada/tidaknya jurusan
        $classNameUniqueMessage = $majorId ? 'Kelas telah terdaftar pada tahun ajaran dan jurusan ini.' : 'Kelas telah terdaftar pada tahun ajaran ini.';

        $validator = Validator::make(
            $request->all(),
            $rules,
            [
                'fase_id.required' => 'Fase harus diisi.',
                'kelas_id.required' => 'Kelas harus diisi.',
                'class_name.required' => 'Nama kelas harus diisi.',
                'class_name.unique'   => $classNameUniqueMessage,
                'akun_wali_kelas.required' => 'Akun wali kelas harus diisi',
                'akun_wali_kelas.email'    => 'Format email harus @belajarcerdas.id.',
                'akun_wali_kelas.regex'    => 'Format email harus @belajarcerdas.id.',
                'tahun_ajaran.required'    => 'Tahun ajaran harus diisi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getWaliKelas = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->akun_wali_kelas);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getWaliKelas) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'akun_wali_kelas' => ['Akun wali kelas tidak terdaftar.']
                ]
            ], 422);
        }

        $class = SchoolClass::create([
            'school_partner_id' => $schoolId,
            'class_name' => $request->class_name,
            'fase_id' => $request->fase_id ?? null,
            'kelas_id' => $request->kelas_id,
            'major_id' => $majorId ?? null,
            'wali_kelas_id' => $getWaliKelas->user_id,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        broadcast(new LmsManagementClass('SchoolClass', 'create', $class))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menambahkan kelas',
        ]);
    }

    // function lms management edit class
    public function lmsManagementEditClass(Request $request, $schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        // Rule dasar yang selalu berlaku
        $rules = [
            'fase_id' => 'required',
            'kelas_id' => 'required',
            'akun_wali_kelas' => 'required|email|regex:/^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/',
            'tahun_ajaran' => 'required',
        ];

        // Membuat rule unique untuk class_name
        $classNameRule = Rule::unique('school_classes', 'class_name')->where('tahun_ajaran', $request->tahun_ajaran)->where('school_partner_id', $schoolId)->ignore($classId);

        // Jika MAJOR ID ada (kelas berbasis jurusan),
        if ($majorId) {
            $classNameRule->where('major_id', $majorId);
        }

        // Menambahkan rule class_name ke dalam rules utama
        $rules['class_name'] = ['required', $classNameRule];

        // Tentukan pesan error unique berdasarkan ada/tidaknya jurusan
        $classNameUniqueMessage = $majorId ? 'Kelas telah terdaftar pada tahun ajaran dan jurusan ini.' : 'Kelas telah terdaftar pada tahun ajaran ini.';

        $validator = Validator::make(
            $request->all(),
            $rules,
            [
                'fase_id.required' => 'Fase harus diisi.',
                'kelas_id.required' => 'Kelas harus diisi.',
                'class_name.required' => 'Nama kelas harus diisi.',
                'class_name.unique'   => $classNameUniqueMessage,
                'akun_wali_kelas.required' => 'Akun wali kelas harus diisi',
                'akun_wali_kelas.email'    => 'Format email harus @belajarcerdas.id.',
                'akun_wali_kelas.regex'    => 'Format email harus @belajarcerdas.id.',
                'tahun_ajaran.required'    => 'Tahun ajaran harus diisi.',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getWaliKelas = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->akun_wali_kelas);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getWaliKelas) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'akun_wali_kelas' => ['Akun wali kelas tidak terdaftar.']
                ]
            ], 422);
        }

        $class = SchoolClass::findOrFail($classId);

        $class->update([
            'school_partner_id' => $schoolId,
            'class_name' => $request->class_name,
            'fase_id' => $request->fase_id ?? null,
            'kelas_id' => $request->kelas_id,
            'wali_kelas_id' => $getWaliKelas->user_id,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        broadcast(new LmsManagementClass('SchoolClass', 'edit', $class))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil edit kelas',
        ]);
    }

    // function lms activate class
    public function LmsActivateClass(Request $request, $id)
    {
        $class = SchoolClass::findOrFail($id);

        $class->update([
            'status_class' => $request->status_class,
        ]);
        
        broadcast(new LmsManagementClass('SchoolClass', 'activate', $class));

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status kelas',
        ]);
    }

    // function lms management students view
    public function lmsManagementStudentsView($schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        return view('Features.lms.administrator.lms-school-subscription-management-students', compact('schoolName', 'schoolId', 'role', 'classId', 'majorId'));
    }

    // function paginate lms management users
    public function paginateLmsSchoolSubscriptionUsers($schoolName, $schoolId, $role, $classId, $majorId = null)
    {
        $getUsersQuery = StudentSchoolClass::with(['UserAccount.StudentProfile', 'SchoolClass', 
        'SchoolClass.UserAccount.SchoolStaffProfile']);

        if ($majorId) {
            $getUsersQuery->with(['SchoolClass.SchoolMajor']);
        }

        $getUsers = $getUsersQuery->whereHas('SchoolClass', function ($query) use ($schoolId) {
            $query->where('school_partner_id', $schoolId);
        })->where('school_class_id', $classId)->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $academicActionCheck = $getUsers->map(function ($item) {
            $item->has_academic_action = !empty($item->academic_action);
            return $item;
        });;

        return response()->json([
            'data' => $getUsers,
            'schoolIdentity' => $getSchool,
            'academicActionCheck' => $academicActionCheck,
        ]);
    }

    // function activate student in class
    public function lmsActivateStudentInClass(Request $request, $id)
    {
        $studentSchoolClass = StudentSchoolClass::findOrFail($id);

        $studentSchoolClass->update([
            'student_class_status' => $request->student_class_status,
        ]);

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'activate', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status siswa di kelas',
        ]);
    }

    // function promote class lms management users
    public function promotionClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $targetLevel = $currentLevel + 1;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel, $targetLevel) {
            // tahun ajaran lebih besar
            if ($cls->tahun_ajaran <= $currentYear) {
                return false;
            }

            $level = $this->extractClassLevel($cls->class_name);

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $targetLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function repeat class lms management users
    public function repeatClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel) {
            // tahun ajaran lebih besar
            if ($cls->tahun_ajaran <= $currentYear) {
                return false;
            }

            $level = $this->extractClassLevel($cls->class_name);

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $currentLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function move class lms management users
    public function moveClassOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClassId = $request->class_id;

        $currentClass = SchoolClass::findOrFail($currentClassId);

        // ambil tingkat kelas (7 dari 7.1)
        $currentLevel = $this->extractClassLevel($currentClass->class_name);
        $currentYear  = $currentClass->tahun_ajaran;

        $classesQuery = SchoolClass::where('school_partner_id', $schoolId)->where('tahun_ajaran', $currentYear)
        ->where('id', '!=', $currentClassId)->orderBy('tahun_ajaran');

        if ($majorId) {
            $classesQuery->where('major_id', $majorId);
        }

        // ambil semua kelas sekolah
        $classes = $classesQuery->get()->filter(function ($cls) use ($currentYear, $currentLevel) {
            $level = $level = $this->extractClassLevel($cls->class_name);;

            // hanya memunculkan options 1 tingkat kelas dari kelas sebelumnya
            return $level === $currentLevel;
        })->values(); // reset index

        return response()->json($classes);
    }

    // function move major lms management users
    public function moveMajorOptions(Request $request, $schoolId, $majorId = null)
    {
        $currentClass = SchoolClass::findOrFail($request->class_id);
        $currentLevel = $this->extractClassLevel($currentClass->class_name);

        $classes = SchoolClass::with(['SchoolMajor'])->where('school_partner_id', $schoolId)
            ->where('tahun_ajaran', $currentClass->tahun_ajaran)
            ->where('id', '!=', $currentClass->id)
            ->when($majorId, fn ($q) => $q->where('major_id', '!=', $majorId))
            ->get()
            ->filter(fn ($cls) =>
                $this->extractClassLevel($cls->class_name) === $currentLevel
            )
            ->values();

        return response()->json($classes);
    }

    // function update lms management promote class
    public function lmsManagementPromoteClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId) ->where('school_class_id', $classId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'PROMOTED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'promote-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil menaikkan kelas',
        ]);
    }

    // function update lms management repeat class
    public function lmsManagementRepeatClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'REPEATED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'repeat-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengulang kelas',
        ]);
    }

    // function update lms management move class
    public function lmsManagementMoveClass(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'TRANSFERRED_CLASS',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'move-class', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memindahkan kelas',
        ]);
    }

    // function update lms management move major
    public function lmsManagementMoveMajor(Request $request, $schoolName, $schoolId, $role, $classId)
    {
        $validator = Validator::make($request->all(), [
            'tahun_ajaran' => 'required',
            'major_id' => 'required',
            'school_class_id' => 'required',
        ], [
            'tahun_ajaran.required' => 'Tahun ajaran harus diisi.',
            'major_id.required' => 'Jurusan harus diisi.',
            'school_class_id.required' => 'Kelas harus diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $explodeStudentIds = explode(',', $request->student_id);
        $majorId = $request->major_id;

        $studentSchoolClass = StudentSchoolClass::whereIn('student_id', $explodeStudentIds)->where('school_class_id', $classId)->whereNotNull('academic_action')->where('academic_action', '!=', '')
        ->exists();

        if ($studentSchoolClass) {
            return response()->json([
                'status' => 'error',
                'studentSchoolClassCheck' => true,
                'message' => 'tidak dapat menggunakan aksi akademik kembali pada siswa yang telah memiliki keterangan.',
            ], 422);
        } else {
            foreach ($explodeStudentIds as $studentId) {
                StudentSchoolClass::where('student_id', $studentId)->update([
                    'student_class_status' => 'inactive',
                    'academic_action' => 'TRANSFERRED_MAJOR',
                ]);
    
                StudentSchoolClass::create([
                    'student_id' => $studentId,
                    'school_class_id' => $request->school_class_id,
                    'major_id' => $majorId,
                    'student_class_status' => 'active',
                ]);
            }
        }

        broadcast(new LmsManagementStudentInClass('StudentSchoolClass', 'move-major', $studentSchoolClass))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil memindahkan jurusan',
        ]);
    }

    // function question bank management view
    public function lmsQuestionBankManagementView($schoolName = null, $schoolId = null)
    {
        $getCurriculum = Kurikulum::all();

        return view('features.lms.administrator.question-bank-management.lms-question-bank-management', compact('schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate bank soal
    public function paginateLmsQuestionBankManagement(Request $request, $schoolName = null, $schoolId = null)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        // jika ada schoolId maka ambil content dari sekolah tersebut dan dari global
        if ($schoolId) {
            $schoolPartner = SchoolPartner::findOrFail($schoolId);
    
            $mappingClasses = [
                'SD'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                'MI'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                'SMP' => ['kelas 7','kelas 8','kelas 9'],
                'MTS' => ['kelas 7','kelas 8','kelas 9'],
                'SMA' => ['kelas 10','kelas 11','kelas 12'],
                'SMK' => ['kelas 10','kelas 11','kelas 12'],
                'MA'  => ['kelas 10','kelas 11','kelas 12'],
                'MAK' => ['kelas 10','kelas 11','kelas 12'],
            ];
    
            $jenjang = strtoupper($schoolPartner->jenjang_sekolah);
    
            $allowedKelas = $mappingClasses[$jenjang] ?? [];
    
            // ambil kelas sesuai dengan jenjang sekolahnya, lalu ambil id nya saja
            $kelasIds = Kelas::whereIn(DB::raw('LOWER(kelas)'), $allowedKelas)->pluck('id');
        }

        $getQuestions = LmsQuestionBank::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile','Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab',
            'SchoolPartner',
            'SchoolQuestionBank' => function ($q) use ($schoolId) {

            if ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            }
            
        }])->orderBy('created_at', 'desc');

        if ($schoolId) {
            $getQuestions->where(function ($q1) use ($schoolId, $kelasIds) {
                $q1->where('school_partner_id', $schoolId)
                ->orWhere(function ($q2) use ($kelasIds) {
                    $q2->whereNull('school_partner_id')->whereIn('kelas_id', $kelasIds);
                });
            });
        } else {
            $getQuestions->whereNull('school_partner_id');
        }

        $rows = $getQuestions->get()->groupBy(fn ($q) => $q->sub_bab_id.'-'.$q->tipe_soal.'-'.$q->school_partner_id)->values();

        // Pagination manual
        $page = $request->get('page', 1);
        $perPage = 20;

        $paged = $rows->slice(
            ($page - 1) * $perPage,
            $perPage
        )->values();

        $paginated = new LengthAwarePaginator(
            $paged,
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $paginated->values(),
            'links' => (string) $paginated->links(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
            'source' => $source ?? null,
            'lmsReviewQuestion' => '/lms/question-bank-management/source/:source/review/question-type/:questionType/:subBabId',
            'lmsReviewQuestionBySchool' => '/lms/school-subscription/question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:schoolName/:schoolId',
        ]);
    }

    // function bank soal store UH, ASTS, ASAS
    public function lmsQuestionBankManagementStore(Request $request)
    {
        return app(BankSoalWordImportService::class)->bankSoalImportService($request);
    }

    // function activate bank soal
    public function lmsActivateQuestionBank(Request $request, $subBabId, $source, $questionType, $schoolName = null, $schoolId = null) 
    {
        $isEnable = $request->action === 'enable';

        // Ambil semua soal target (TANPA gate global)
        $questions = LmsQuestionBank::where('sub_bab_id', $subBabId)->where('question_source', $source)
        ->where('tipe_soal', $questionType)->get();

        if ($schoolId) {

            // MODE SEKOLAH (OVERRIDE)
            foreach ($questions as $question) {
                SchoolQuestionBank::updateOrCreate(
                    [
                        'question_id' => $question->id,
                        'school_partner_id' => $schoolId,
                    ],
                    [
                        'is_active' => $isEnable,
                    ]
                );
            }
        } else {
            // MODE GLOBAL
            $status = $isEnable ? 'Publish' : 'Unpublish';

            $affected = LmsQuestionBank::where('sub_bab_id', $subBabId)->where('question_source', $source)
            ->where('tipe_soal', $questionType)->update([
                'status_bank_soal' => $status,
            ]);
        }

        broadcast(new ActivateQuestionBankPG($subBabId,$source,$request->action,$questions->count()))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil mengubah status bank soal',
        ]);
    }


    // function bank soal detail view
    public function lmsQuestionBankManagementDetailView($source, $questionType, $subBabId, $schoolName = null, $schoolId = null)
    {
        return view('features.lms.administrator.question-bank-management.administrator-question-bank-management-detail', compact('source', 'questionType', 
        'subBabId', 'schoolName', 'schoolId'));
    }

    // function paginate bank soal detail
    public function paginateReviewQuestionBank($source, $questionType, $subBabId, $schoolName = null, $schoolId = null) 
    {
        $user = Auth::user();

        $questions = LmsQuestionBank::with('LmsQuestionOption')
            ->where('sub_bab_id', $subBabId)
            ->where('question_source', $source)
            ->where('tipe_soal', $questionType)
            ->get();

        $videoIds = $questions->map(function ($q) {
            if (preg_match(
                '/youtu\.be\/([a-zA-Z0-9_-]{11})|youtube\.com\/.*v=([a-zA-Z0-9_-]{11})/',
                $q->explanation,
                $matches
            )) {
                return $matches[1] ?? $matches[2];
            }
            return null;
        });

        $response = [
            'data' => $questions,
            'videoIds' => $videoIds,
        ];

        if ($user->role === 'Administrator') {
            $response['lmsEditQuestion'] = '/lms/question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:questionId/edit';
            $response['lmsEditQuestionBySchool'] = '/lms/school-subscription/question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:questionId/:schoolName/:schoolId/edit';
        } else if ($user->role === 'Guru') {
            $response['lmsEditQuestion'] = '/lms/:role/:schoolName/:schoolId/teacher-question-bank-management/source/:source/review/question-type/:questionType/:subBabId/:questionId/edit';
        }

        return response()->json($response);
    }

    // function edit question view
    public function lmsQuestionBankManagementEditView($source, $questionType, $subBabId, $questionId, $schoolName = null, $schoolId = null)
    {
        // Mengambil data soal berdasarkan ID
        $editQuestion = LmsQuestionBank::find($questionId);

        if (!$editQuestion) {
            if ($schoolId) {
                return redirect()->route('lms.questionBankManagementDetail.view.schoolPartner', [$source, $questionType, $subBabId, $schoolName, $schoolId]);
            } else {
                return redirect()->route('lms.questionBankManagementDetail.view.noSchoolPartner', [$source, $questionType, $subBabId]);
            }
        }

        // Mengambil data soal yang punya pertanyaan (questions) yang sama, lalu dikelompokkan berdasarkan isi questions-nya
        $dataSoal = LmsQuestionBank::where('questions', $editQuestion->questions)->get()->groupBy('questions');

        // Simpan hasil pengelompokan ke variabel baru
        $groupedSoal = $dataSoal;

        return view('features.lms.administrator.question-bank-management.administrator-question-bank-management-edit', compact('source', 'subBabId', 'questionId', 
        'schoolName', 'schoolId', 'questionType'));
    }

    // form edit question
    public function formEditQuestion($source, $questionType, $subBabId, $questionId, $schoolName = null, $schoolId = null)
    {
        $editQuestion = LmsQuestionBank::with('LmsQuestionOption')->findOrFail($questionId);

        if (!$editQuestion) {
            if ($schoolId) {
                return redirect()->route('lms.questionBankManagementDetail.view.schoolPartner', [$source, $questionType, $subBabId, $schoolName, $schoolId]);
            } else {
                return redirect()->route('lms.questionBankManagementDetail.view.noSchoolPartner', [$source, $questionType, $subBabId]);
            }
        }

        $options = $editQuestion->LmsQuestionOption;

        // Buat mapping LEFT -> RIGHT dari extra_data['pair_with']
        $matching = [];
        foreach ($options as $opt) {
            if (($opt->extra_data['side'] ?? null) === 'left') {
                $matching[$opt->options_key] = $opt->extra_data['pair_with'] ?? null;
            }
        }

        // Mengambil data soal yang punya pertanyaan (questions) yang sama, lalu dikelompokkan berdasarkan isi questions-nya
        $dataSoal = LmsQuestionBank::where('questions', $editQuestion->questions)->get()->groupBy('questions');

        // Simpan hasil pengelompokan ke variabel baru
        $groupedSoal = $dataSoal;

        // cek apakah soal suda dipake untuk assessment atau belum
        $isUsed = $editQuestion->SchoolAssessmentQuestion()->whereHas('StudentAssessmentAnswer')->exists();

        return response()->json([
            'status' => 'success',
            'data' => $groupedSoal,
            'editQuestion' => $editQuestion,
            'options' => $editQuestion->LmsQuestionOption,
            'matching' => $matching,
            'type' => strtoupper($editQuestion->tipe_soal),
            'isUsed' => $isUsed
        ]);
    }

    // function bankSoal edit question
    public function lmsQuestionBankManagementEdit(Request $request, $questionId)
    {
        $user = Auth::user();

        $question = LmsQuestionBank::findOrFail($questionId);
        $questionType = strtoupper($question->tipe_soal);

        // GENERAL VALIDATION
        $rules = [
            'questions'   => 'required|string',
            'difficulty'  => 'required|in:Mudah,Sedang,Sukar',
            'bloom'       => 'required',
            'explanation' => 'required|string',
        ];

        $messages = [
            'questions.required'   => 'Pertanyaan wajib diisi.',
            'difficulty.required'  => 'Difficulty wajib dipilih.',
            'difficulty.in'        => 'Difficulty tidak valid.',
            'bloom.required'       => 'Bloom wajib diisi.',
            'explanation.required' => 'Pembahasan wajib diisi.',
        ];

        if ($questionType === 'MCQ') {
            $rules += [
                'options'    => 'required',
                'answer_key' => 'required|string',
            ];

            $messages += [
                'options.*.required' => 'Harap isi jawaban soal.',
                'answer_key.required' => 'Pilih jawaban benar.',
            ];
        }

        if ($questionType === 'MCMA') {
            $rules += [
                'options.*'      => 'required',
                'answer_key'   => 'required|array|min:1',
                'answer_key.*' => 'string',
            ];

            $messages += [
                'options.*.required' => 'Harap isi jawaban soal.',
                'answer_key.required' => 'Pilih minimal satu jawaban benar.',
                'answer_key.min' => 'Pilih minimal satu jawaban benar.',
            ];
        }

        if ($questionType === 'MATCHING') {
            $rules += [
                'left.*'     => 'required',
                'right.*'    => 'required',
                'pair_with.*' => 'required',
            ];

            $messages += [
                'left.*.required' => 'Harap isi jawaban soal.',
                'right.*.required' => 'Harap isi jawaban soal.',
                'pair_with.*.required' => 'Harap pilih pasangan.',
            ];
        }

        if ($questionType === 'PG_KOMPLEKS') {
            $rules += [
                'header_item' => 'required',
                'item.*' => 'required',
                'category.*' => 'required',
                'answer.*' => 'required',
            ];

            $messages += [
                'header_item.required' => 'Harap isi header item soal.',
                'item.*.required' => 'Harap isi item soal.',
                'category.*.required' => 'Harap isi kategori soal.',
                'answer.*.required' => 'Harap pilih jawaban kategori soal.',
            ];
        }

        $isUsed = $question->SchoolAssessmentQuestion()->whereHas('StudentAssessmentAnswer')->exists();

        if ($questionType === 'MATCHING') {
            // Ambil semua pasangan lama (LEFT) dari DB
            $existingPairs = LmsQuestionOption::where('question_id', $question->id)->where('options_key', 'like', 'LEFT%')->pluck('extra_data', 'id');
    
            $isPairChanged = false;
    
            // Loop semua input pair dari request
            foreach ($request->pair_with as $id => $newPair) {
    
                // Ambil data lama berdasarkan id
                $oldExtra = $existingPairs[$id] ?? null;
    
                // Ambil value pair_with dari extra_data
                $oldPair = is_array($oldExtra) ? ($oldExtra['pair_with'] ?? null) : (json_decode($oldExtra, true)['pair_with'] ?? null);
    
                // Bandingkan pair lama vs baru
                if ($oldPair != $newPair) {
                    $isPairChanged = true;
                    break;
                }
            }
    
            if ($isUsed && $questionType === 'MATCHING' && $isPairChanged) {
                return response()->json([
                    'status' => 'error',
                    'isUsed' => true,
                    'message' => 'Soal sudah digunakan, pasangan tidak bisa diubah.'
                ], 422);
            }
        }

        if ($questionType === 'PG_KOMPLEKS') {
            // Ambil semua option yang merupakan ITEM (bukan category)
            $existingAnswers = LmsQuestionOption::where('question_id', $question->id)->get()->filter(fn($opt) => ($opt->extra_data['side'] ?? null) === 'item')->pluck('extra_data', 'id');
    
            $isAnswerChanged = false;
    
            // Loop semua jawaban (mapping item -> category)
            foreach ($request->input('answer', []) as $itemId => $newCategory) {
    
                // Ambil extra_data lama dari DB
                $oldExtra = $existingAnswers[$itemId] ?? [];
    
                // Ambil category lama (answer)
                $oldCategory = $oldExtra['answer'] ?? null;
    
                // Bandingkan category lama vs baru
                if ($oldCategory != $newCategory) {
                    $isAnswerChanged = true;
                    break;
                }
            }
    
            if ($isUsed && $isAnswerChanged) {
                return response()->json([
                    'status' => 'error',
                    'isUsed' => true,
                    'message' => 'Soal sudah digunakan, pasangan category tidak dapat diubah.'
                ], 422);
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $question->update([
            'user_id' => $user->id,
            'questions'   => $request->questions,
            'difficulty'  => $request->difficulty,
            'bloom'       => $request->bloom,
            'explanation' => $request->explanation,
        ]);

        switch ($questionType) {

            // MCQ
            case 'MCQ':

                $options = LmsQuestionOption::whereIn('id', array_keys($request->options))->get()->keyBy('id');

                foreach ($request->options as $optionId => $value) {
                    $isCorrect = $request->answer_key === $options[$optionId]->options_key;

                    LmsQuestionOption::where('id', $optionId)->update([
                        'options_value' => $value,
                        'is_correct'    => $isCorrect,
                    ]);
                }

            break;

            // MCMA
            case 'MCMA':

                $options = LmsQuestionOption::whereIn('id', array_keys($request->options))->get()->keyBy('id');

                foreach ($request->options as $optionId => $value) {
                    $option = $options[$optionId];

                    LmsQuestionOption::where('id', $optionId)->update([
                        'options_value' => $value,
                        'is_correct'    => in_array($option->options_key, $request->answer_key),
                    ]);
                }

            break;

            // MATCHING
            case 'MATCHING':

                // Update LEFT
                foreach ($request->left as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update RIGHT
                foreach ($request->right as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update PAIR WITH
                foreach ($request->pair_with as $id => $value) {
                    $option = LmsQuestionOption::find($id);

                    // ambil data lama
                    $extra = $option->extra_data ?? [];

                    // update hanya field answer
                    $extra['pair_with'] = $value;

                    $option->update([
                        'extra_data' => $extra
                    ]);
                }

            break;

            case 'PG_KOMPLEKS':

                lmsQuestionBank::where('id', $questionId)->update([
                    'header_item' => $request->header_item
                ]);

                // Update ITEM
                foreach ($request->item as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update CATEGORY
                foreach ($request->category as $id => $value) {
                    LmsQuestionOption::where('id', $id)->update([
                        'options_value' => $value,
                    ]);
                }

                // Update ANSWER katgori pada item
                foreach ($request->answer as $id => $value) {
                    $option = LmsQuestionOption::find($id);

                    // ambil data lama
                    $extra = $option->extra_data ?? [];

                    // update hanya field answer
                    $extra['answer'] = $value;

                    $option->update([
                        'extra_data' => $extra
                    ]);
                }

            break;
        }

        broadcast(new BankSoalLmsEditPG($question, $questionId))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => 'Soal berhasil diupdate',
        ]);
    }
    
    // function edit image bank soal (for ckeditor)
    public function editImageBankSoal(Request $request) {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathInfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('lms-docx-image'), $fileName);

            $url = "/lms-docx-image/$fileName";
            return response()->json(['fileName' => $fileName, 'uploaded' => 1, 'url' => $url]);
        }
    }

    // function delete image bank soal (for ckeditor)
    public function deleteImageBankSoal(Request $request) {
        $request->validate([
            'imageUrl' => 'required|url',
        ]);

        $imagePath = str_replace(asset(''), '', $request->imageUrl); // Hapus base URL
        $fullImagePath = public_path($imagePath);

        if (file_exists($fullImagePath)) {
            unlink($fullImagePath); // Hapus gambar
            return response()->json(['message' => 'Gambar berhasil dihapus']);
        }

        return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
    }

    // function content management view
    public function lmsContentManagementView($schoolName = null, $schoolId = null)
    {
        $getCurriculum = Kurikulum::all();

        return view('Features.lms.administrator.content-management.lms-content-management', compact('getCurriculum', 'schoolName', 'schoolId'));
    }

    // function paginate lms content
    public function paginateLmsContentManagement($schoolName = null, $schoolId = null)
    {
        // jika ada schoolId maka ambil content dari sekolah tersebut dan dari global
        if ($schoolId) {
            $schoolPartner = SchoolPartner::findOrFail($schoolId);

            $mappingClasses = [
                'SD'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                'MI'  => ['kelas 1','kelas 2','kelas 3','kelas 4','kelas 5','kelas 6'],
                'SMP' => ['kelas 7','kelas 8','kelas 9'],
                'MTS' => ['kelas 7','kelas 8','kelas 9'],
                'SMA' => ['kelas 10','kelas 11','kelas 12'],
                'SMK' => ['kelas 10','kelas 11','kelas 12'],
                'MA'  => ['kelas 10','kelas 11','kelas 12'],
                'MAK' => ['kelas 10','kelas 11','kelas 12'],
            ];

            $jenjang = strtoupper($schoolPartner->jenjang_sekolah);

            $allowedKelas = $mappingClasses[$jenjang] ?? [];

            // ambil kelas sesuai dengan jenjang sekolahnya, lalu ambil id nya saja
            $kelasIds = Kelas::whereIn(DB::raw('LOWER(kelas)'), $allowedKelas)->pluck('id');

            $getContent = LmsContent::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'Service',
                'SchoolPartner', 'SchoolLmsContent' => function ($query) use ($schoolId) {
                    $query->where('school_partner_id', $schoolId);
                },
            ])->where(function ($query) use ($schoolId, $kelasIds) {
                $query->where('school_partner_id', $schoolId)->orWhere(function ($q) use ($kelasIds) {
                    $q->whereNull('school_partner_id')->whereIn('kelas_id', $kelasIds);
                });
            })->orderBy('created_at', 'desc')->paginate(10);;
        } else {
            $getContent = LmsContent::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 
            'Service', 'SchoolLmsContent'])->whereNull('school_partner_id')->orderBy('created_at', 'desc')->paginate(10);
        }

        return response()->json([
            'data'   => $getContent->items(),
            'links'  => (string) $getContent->links(),
            'current_page' => $getContent->currentPage(),
            'per_page' => $getContent->perPage(),
            'reviewContent' => '/lms/content-management/:contentId/review',
            'reviewContentBySchool' => '/lms/school-subscription/content-management/:contentId/:schoolName/:schoolId/review',
            'editContent' => '/lms/content-management/:contentId/edit',
            'editContentBySchool' => '/lms/school-subscription/content-management/:contentId/:schoolName/:schoolId/edit',
        ]);
    }
    
    // function content management store
    public function lmsContentManagementStore(Request $request, LmsContentService $service, $schoolName = null, $schoolId = null)
    {
        // base validation
        $rules = [
            'service_id'   => 'required',
            'kurikulum_id' => 'required',
            'kelas_id'     => 'required',
            'mapel_id'     => 'required',
            'bab_id'       => 'required',
            'sub_bab_id'   => 'required',
        ];

        $messages = [
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required'     => 'Harap pilih kelas.',
            'mapel_id.required'     => 'Harap pilih mapel.',
            'bab_id.required'       => 'Harap pilih bab.',
            'sub_bab_id.required'   => 'Harap pilih sub bab.',
            'service_id.required'   => 'Harap pilih service.',
        ];

        // DYNAMIC VALIDATION (BERDASARKAN SERVICE RULE)
        $serviceRules = ServiceRule::where('service_id', $request->service_id)->get();

        foreach ($serviceRules as $rule) {

            // TEXT INPUT (ARRAY)
            if ($rule->upload_type === 'text') {
                $rules["text.{$rule->id}"] = 'required|array|min:1';
                $rules["text.{$rule->id}.*"] = 'required|string';

                $messages["text.{$rule->id}.required"] = "Text wajib diisi";
                $messages["text.{$rule->id}.array"]    = "Text wajib diisi";
                $messages["text.{$rule->id}.min"]      = "Text minimal 1 data";
                $messages["text.{$rule->id}.*.required"] = "Text tidak boleh kosong";
            }

            // FILE INPUT
            if ($rule->upload_type === 'file') {
                // default jika null
                $maxMb = $rule->max_size_mb ?? 100;
                $maxKb = $maxMb * 1024;

                $rules["files.{$rule->id}"] = "required|file|max:{$maxKb}";

                $messages["files.{$rule->id}.required"] = "File wajib diunggah.";
                $messages["files.{$rule->id}.file"]     = "Format file tidak valid.";
                $messages["files.{$rule->id}.max"]      = "File telah melebihi kapasitas yang ditentukan.";
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // store via service
        $content = $service->store(
            $validator->validated(),
            Auth::id(),
            $schoolId ?? null
        );

        broadcast(new LmsContentManagement($content))->toOthers();

        // success response
        return response()->json([
            'status'  => 'success',
            'message' => 'Content berhasil ditambahkan',
            'data'    => $content,
        ]);
    }

    // function activate lms content
    public function lmsContentManagementActivate(Request $request, $contentId, $schoolName = null, $schoolId = null)
    {
        $isEnable = $request->action === 'enable';

        LmsContent::findOrFail($contentId);

        if ($schoolId) {
            SchoolLmsContent::updateOrCreate(
                [
                    'lms_content_id' => $contentId,
                    'school_partner_id' => $schoolId,
                ],
                [
                    'is_active' => $isEnable,
                ]
            );
        } else {
            $status = $isEnable ? 1 : 0;

            $affected = LmsContent::where('id', $contentId)->update([
                'is_active' => $status,
            ]);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Status content berhasil diubah.',
        ]);
    }

    // function review content view
    public function lmsReviewContent($contentId, $schoolName = null, $schoolId = null)
    {
        $data = $this->reviewContentService->getByContentId($contentId);

        return view('Features.lms.administrator.content-management.administrator-review-content', compact('contentId', 'data', 'schoolName', 'schoolId'));
    }

    // function edit content view
    public function lmsContentManagementEditView($contentId, $schoolName = null, $schoolId = null)
    {
        $content = LmsContent::with(['Kurikulum', 'Kelas', 'Mapel', 'Bab', 'SubBab', 'Service'])->findOrFail($contentId);

        $getCurriculum = Kurikulum::all();

        return view('Features.lms.administrator.content-management.administrator-content-management-edit',compact('content', 'getCurriculum', 
            'schoolName', 'schoolId'));
    }

    // function form edit content
    public function lmsContentManagementFormEdit($contentId)
    {
        $data = $this->reviewContentService->getByContentId($contentId);

        return response()->json([
            'data' => $data
        ]);
    }

    // function form action edit content
    public function lmsContentManagementEdit(Request $request, $contentId)
    {
        // AMBIL RULE DARI SERVICE YANG DIPILIH USER
        $serviceRules = ServiceRule::where('service_id', $request->service_id)->get();

        // base validation
        $rulesValidation = [
            'kurikulum_id' => 'required',
            'kelas_id'     => 'required',
            'mapel_id'     => 'required',
            'bab_id'       => 'required',
            'sub_bab_id'   => 'required',
            'service_id'   => 'required',
        ];

        $messages = [
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required'     => 'Harap pilih kelas.',
            'mapel_id.required'     => 'Harap pilih mapel.',
            'bab_id.required'       => 'Harap pilih bab.',
            'sub_bab_id.required'   => 'Harap pilih sub bab.',
            'service_id.required'   => 'Harap pilih service.',
        ];

        // dynamic validation
        foreach ($serviceRules as $rule) {

            /* ================= TEXT ================= */
            if ($rule->upload_type === 'text') {

                $rulesValidation["text.{$rule->id}"] = [
                    $rule->is_required ? 'required' : 'nullable',
                    'array',
                    $rule->is_required ? 'min:1' : null,
                ];

                $rulesValidation["text.{$rule->id}.*"] = [
                    'required',
                    'string',
                ];

                if ($rule->is_required) {
                    $messages["text.{$rule->id}.required"] = 'Text tidak boleh kosong.';
                    $messages["text.{$rule->id}.min"]      = 'Minimal satu data harus diisi.';
                }

                $messages["text.{$rule->id}.*.required"] = "Text tidak boleh kosong.";
            }

            /* ================= FILE ================= */
            if ($rule->upload_type === 'file') {

                $hasExisting = $request->input("existing_files.{$rule->id}") == 1;

                // default max size (MB)
                $maxMb = $rule->max_size_mb ?? 100;
                $maxKb = $maxMb * 1024;

                $fileRules = [];

                $fileRules[] = $hasExisting ? 'nullable' : 'required';
                $fileRules[] = 'file';
                $fileRules[] = "max:{$maxKb}";

                $rulesValidation["files.{$rule->id}"] = $fileRules;

                $messages["files.{$rule->id}.required"] = "File wajib diunggah.";
                $messages["files.{$rule->id}.file"]     = "Format file tidak valid.";
                $messages["files.{$rule->id}.max"]      = "File telah melebihi kapasitas yang ditentukan.";
            }
        }

        $validator = Validator::make($request->all(), $rulesValidation, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $content = LmsContent::findOrFail($contentId);

        $lmsContentService = new LmsContentService();

        // update via service
        $updated = $lmsContentService->update(
            $content,
            $request->all(),
            Auth::id()
        );

        broadcast(new LmsContentManagement($updated))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'Content berhasil diperbarui',
        ]);
    }

    // LMS ASSESSMENT TYPE MANAGEMENT
    // function lms assessment type management view
    public function lmsAssessmentTypeManagementView($schoolName, $schoolId)
    {
        $getAssessmentMode = AssessmentMode::all();

        return view('features.lms.administrator.assessment-type-management.lms-assessment-type-management', compact('schoolName', 'schoolId', 'getAssessmentMode'));
    }

    // function paginate lms assessment type management
    public function paginateLmsAssessmentTypeManagement($schoolName, $schoolId) 
    {
        $assessmentTypes = SchoolAssessmentType::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'AssessmentMode'])
        ->where('school_partner_id', $schoolId)->paginate(10);

        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        return response()->json([
            'data' => $assessmentTypes->items(),
            'links' => (string) $assessmentTypes->links(),
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
        ]);
    }

    // function lms assessment type management store
    public function lmsAssessmentTypeManagementStore(Request $request, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('school_assessment_types', 'name')->where('school_partner_id', $schoolId),
            ],
            'assessment_mode_id' => 'required',
            'is_remedial_allowed' => 'required',
            'max_remedial_attempt' => 'required_if:is_remedial_allowed,1|integer|min:1',
        ], [
            'name.required' => 'Nama asesmen tidak boleh kosong.',
            'name.unique'   => 'Nama asesmen telah terdaftar pada sekolah ini.',
            'assessment_mode_id.required' => 'Mode asesmen tidak boleh kosong.',
            'is_remedial_allowed.required' => 'Kebijakan remedial tidak boleh kosong.',
            'max_remedial_attempt.required_if' => 'Jumlah remedial tidak boleh kosong.',
            'max_remedial_attempt.min' => 'Jumlah remedial harus lebih dari 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $isRemedialAllowed = (int) $request->is_remedial_allowed;

        $maxRemedialAttempt = null;

        if ($isRemedialAllowed === 1) {
            $maxRemedialAttempt = (int) $request->max_remedial_attempt;
        }

        $assessmentType = SchoolAssessmentType::create([
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'name' => $request->name,
            'assessment_mode_id' => $request->assessment_mode_id,
            'is_remedial_allowed' => $request->is_remedial_allowed ?? null,
            'max_remedial_attempt' => $maxRemedialAttempt,
        ]);

        broadcast(new LmsAssessmentTypeManagement('SchoolAssessmentType', 'create', $assessmentType))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'Nama asesmen berhasil ditambahkan.',
            'data'    => $assessmentType,
        ]);
    }

    // function lms assessment type management edit
    public function lmsAssessmentTypeManagementEdit(Request $request, $schoolName, $schoolId, $assessmentTypeId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => [
                'required',
                Rule::unique('school_assessment_types', 'name')->where('school_partner_id', $schoolId)->ignore($assessmentTypeId),
            ],
            'assessment_mode_id' => 'required',
            'is_remedial_allowed' => 'required',
            'max_remedial_attempt' => 'required_if:is_remedial_allowed,1|integer|min:1',
        ], [
            'name.required' => 'Nama asesmen tidak boleh kosong.',
            'name.unique'   => 'Nama asesmen telah terdaftar pada sekolah ini.',
            'assessment_mode_id.required' => 'Mode asesmen tidak boleh kosong.',
            'is_remedial_allowed.required' => 'Kebijakan remedial tidak boleh kosong.',
            'max_remedial_attempt.required_if' => 'Jumlah remedial tidak boleh kosong.',
            'max_remedial_attempt.min' => 'Jumlah remedial harus lebih dari 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $assessmentType = SchoolAssessmentType::findOrFail($assessmentTypeId);

        $isRemedialAllowed = (int) $request->is_remedial_allowed;

        $maxRemedialAttempt = null;

        if ($isRemedialAllowed === 1) {
            $maxRemedialAttempt = (int) $request->max_remedial_attempt;
        }

        $assessmentType->update([
            'user_id' => $user->id,
            'name' => $request->name,
            'assessment_mode_id' => $request->assessment_mode_id,
            'is_remedial_allowed' => $request->is_remedial_allowed ?? null,
            'max_remedial_attempt' => $maxRemedialAttempt,
        ]);

        broadcast(new LmsAssessmentTypeManagement('SchoolAssessmentType', 'edit', $assessmentType))->toOthers();

        return response()->json([
            'data' => $assessmentType,
            'message' => 'Data berhasil diperbarui.',
        ]);
    }

    // function lms assessment type management activate
    public function lmsAssessmentTypeManagementActivate(Request $request, $schoolName, $schoolId, $assessmentTypeId)
    {
        $assessmentType = SchoolAssessmentType::findOrFail($assessmentTypeId);

        $assessmentType->update([
            'is_active' => $request->is_active,
        ]);

        broadcast(new LmsAssessmentTypeManagement('SchoolAssessmentType', 'activate', $assessmentType))->toOthers();

        return response()->json([
            'data' => $assessmentType,
            'message' => 'Status asesmen berhasil diperbarui.',
        ]);
    }

    // TEACHER SUBJECT MANAGEMENT
    // function teacher suject management view
    public function lmsTeacherSubjectManagement($schoolName, $schoolId)
    {
        $getCurriculum = Kurikulum::all();

        return view('features.lms.administrator.subject-teacher-management.lms-subject-teacher-management', compact('schoolName', 'schoolId', 'getCurriculum'));
    }

    // function paginate teacher subject management
    public function paginateLmsTeacherSubjectManagement(Request $request, $schoolName, $schoolId)
    {
        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')
            ->where('id', $schoolId)
            ->first();

        $startLevelMap = [
            'SD'  => 1,
            'MI'  => 1,
            'SMP' => 7,
            'MTS' => 7,
            'SMA' => 10,
            'SMK' => 10,
            'MA'  => 10,
            'MAK' => 10
        ];

        $defaultLevel = $startLevelMap[$getSchool->jenjang_sekolah] ?? 1;

        $selectedClass = $request->filled('search_class') ? (int) $request->search_class : $defaultLevel;

        // dropdown data
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $className = SchoolClass::where('school_partner_id', $schoolId)->pluck('class_name')->map(function ($className) {
            return $this->extractClassLevel($className);
        })->unique()->sort()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        $searchTeacher = $request->search_teacher;

        // base query
        $query = TeacherMapel::with(['UserAccount.SchoolStaffProfile', 'Mapel', 'SchoolClass'])
        ->whereHas('SchoolClass', function ($q) use ($schoolId, $searchYear) {
            $q->where('school_partner_id', $schoolId);

            if ($searchYear) {
                $q->where('tahun_ajaran', $searchYear);
            }
        });

        if ($searchTeacher) {
            $query->whereHas('UserAccount.SchoolStaffProfile', function ($q) use ($searchTeacher) {
                $q->where('nama_lengkap', 'like', '%' . $searchTeacher . '%');
            });
        }

        $teacherSubjectCollection = $query->orderBy('created_at', 'desc')->get();

        // Filter berdasarkan level kelas (PHP side filtering)
        if ($selectedClass) {
            $teacherSubjectCollection = $teacherSubjectCollection->filter(function ($item) use ($selectedClass) {

                if (!$item->SchoolClass || !$item->SchoolClass->class_name) {
                    return false;
                }

                return $this->extractClassLevel($item->SchoolClass->class_name) == $selectedClass;
            });
        }

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $teacherSubject = new LengthAwarePaginator(
            $teacherSubjectCollection->forPage($currentPage, $perPage)->values(),
            $teacherSubjectCollection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return response()->json([
            'data'          => $teacherSubject->items(),
            'links'         => (string) $teacherSubject->links(),
            'current_page'  => $teacherSubject->currentPage(),
            'per_page'      => $teacherSubject->perPage(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'selectedClass' => $selectedClass,
            'className'     => $className
        ]);
    }

    // function teacher subject management store
    public function lmsTeacherSubjectManagementStore(Request $request, $schoolName, $schoolId)
    {
        $validator = Validator::make($request->all(), [
            'kurikulum_id' => 'required',
            'kelas_id' => 'required',
            'mapel_id' => 'required',
            'school_class_id' => 'required',
            'teacher' => [
                'required',
                'email',
                'regex:/^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/',
                Rule::unique('teacher_mapels', 'user_id')->where('school_class_id', $request->school_class_id),
            ],
        ], [
            'kurikulum_id.required' => 'Harap pilih kurikulum.',
            'kelas_id.required' => 'Harap pilih kelas.',
            'mapel_id.required' => 'Harap pilih mapel.',
            'school_class_id.required' => 'Harap pilih rombel kelas.',
            'teacher.required' => 'Harap isi nama guru.',
            'teacher.email'    => 'Format email tidak valid.',
            'teacher.regex'    => 'Format email harus @belajarcerdas.id.',
            'teacher.unique'    => 'Guru telah terdaftar pada rombel kelas di tahun ini.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getTeacher = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->teacher);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getTeacher) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Akun guru tidak terdaftar.']
                ]
            ], 422);
        }

        $exists = TeacherMapel::where('user_id', $getTeacher->user_id)
            ->where('mapel_id', $request->mapel_id)
            ->where('school_class_id', $request->school_class_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Guru telah terdaftar pada mapel dan rombel kelas ini.']
                ]
            ], 422);
        }

        $teacherSubject = TeacherMapel::create([
            'user_id' => $getTeacher->user_id,
            'mapel_id' => $request->mapel_id,
            'school_class_id' => $request->school_class_id,
        ]);

        return response()->json([
            'data' => $teacherSubject,
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    // function teacher subject management update
    public function lmsTeacherSubjectManagementEdit(Request $request, $schoolName, $schoolId, $teacherSubjectId)
    {
        $validator = Validator::make($request->all(), [
            'teacher' => 'required|email|regex:/^[A-Za-z0-9._%+-]+@belajarcerdas\.id$/',
        ], [
            'teacher.required' => 'Harap isi nama guru.',
            'teacher.email'    => 'Format email tidak valid.',
            'teacher.regex'    => 'Format email harus @belajarcerdas.id.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $getTeacher = SchoolStaffProfile::whereHas('UserAccount', function ($query) use ($request) {
            $query->where('email', $request->teacher);
        })->where('school_partner_id', $schoolId)->first();

        if (!$getTeacher) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Akun guru tidak terdaftar.']
                ]
            ], 422);
        }

        $exists = TeacherMapel::where('user_id', $getTeacher->user_id)
            ->where('mapel_id', $request->mapel_id)
            ->where('school_class_id', $request->school_class_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'teacher' => ['Guru telah terdaftar pada mapel dan rombel kelas ini.']
                ]
            ], 422);
        }
        
        $teacherSubject = TeacherMapel::findOrFail($teacherSubjectId);

        $teacherSubject->update([
            'user_id' => $getTeacher->user_id,
        ]);

        return response()->json([
            'data' => $teacherSubject,
            'message' => 'Data berhasil disimpan.',
        ]);
    }

    // function teacher subject management activate
    public function lmsTeacherSubjectManagementActivate(Request $request, $schoolName, $schoolId, $teacherSubjectId)
    {
        $teacherSubject = TeacherMapel::findOrFail($teacherSubjectId);

        $teacherSubject->update([
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'data' => $teacherSubject,
            'message' => 'Status berhasil diubah.',
        ]);
    }

    // function assessment weight management
    public function assessmentWeight($schoolName, $schoolId)
    {
        $assessmentType = SchoolAssessmentType::where('is_active', 1)->where('school_partner_id', $schoolId)->get();

        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        return view('features.lms.administrator.assessment-weight-management.lms-assessment-weight-management', compact('schoolName', 'schoolId', 'assessmentType', 'tahunAjaran'));
    }

    // function paginate assessment weight
    public function paginateAssessmentWeight(Request $request, $schoolName, $schoolId)
    {
        $users = UserAccount::with(['StudentProfile', 'SchoolStaffProfile'])->where(function ($query) use ($schoolId) {
            $query->whereHas('StudentProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            })->orWhereHas('SchoolStaffProfile', function ($q) use ($schoolId) {
                $q->where('school_partner_id', $schoolId);
            });
        })->get();

        $getSchool = SchoolPartner::with('UserAccount.SchoolStaffProfile')->where('id', $schoolId)->first();

        $countUsers = $users->count();

        // dropdown data
        $tahunAjaran = SchoolClass::where('school_partner_id', $schoolId)->pluck('tahun_ajaran')->unique()->sortDesc()->values();

        $searchYear = $request->filled('search_year') ? $request->search_year : ($tahunAjaran->first() ?? null);

        // base query
        $query = SchoolAssessmentTypeWeight::with(['UserAccount', 'UserAccount.OfficeProfile', 'UserAccount.SchoolStaffProfile', 'SchoolAssessmentType'])
        ->where('school_partner_id', $schoolId)->where('school_year', $searchYear);

        $assessmentTypes = $query->orderBy('created_at', 'desc')->get();

        // manual pagination karena sudah menjadi collection
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;

        $assessmentTypes = new LengthAwarePaginator(
            $assessmentTypes->forPage($currentPage, $perPage)->values(),
            $assessmentTypes->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url()]
        );

        return response()->json([
            'data' => $assessmentTypes->items(),
            'links' => (string) $assessmentTypes->links(),
            'tahunAjaran'   => $tahunAjaran,
            'selectedYear'  => $searchYear,
            'schoolIdentity' => $getSchool,
            'countUsers' => $countUsers,
        ]);
    }

    // function assessment weight store
    public function assessmentWeightStore(Request $request, $schoolName, $schoolId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'assessment_type_id' => 'required',
            'school_year' => 'required',
            'weight' => 'required|integer|min:1|max:100',
        ], [
            'assessment_type_id' => 'Harap pilih tipe asesmen.',
            'school_year' => 'Harap pilih tahun ajaran.',
            'weight.required' => 'Bobot asesmen tidak boleh kosong.',
            'weight.min' => 'Bobot asesmen harus lebih dari 0.',
            'weight.max' => 'Bobot asesmen tidak boleh lebih dari 100.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->where('school_year', $request->school_year)->where('assessment_type_id', $request->assessment_type_id)->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'assessment_type_id' => ['Bobot pada asesmen di tahun ajaran ini telah terdaftar.']
                ]
            ], 422);
        }

        $totalWeight = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->where('school_year', $request->school_year)->sum('weight');

        $newTotal = $totalWeight + $request->weight;

        if ($newTotal > 100) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'weight_limit_exceeded',
                'message' => 'Total bobot semua jenis asesmen tidak boleh melebihi 100%. Silakan sesuaikan bobot yang ada.',
            ], 422);
        }

        $assessmentTypeWeight = SchoolAssessmentTypeWeight::create([
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'assessment_type_id' => $request->assessment_type_id,
            'weight' => $request->weight,
            'school_year' => $request->school_year,
        ]);

        broadcast(new LmsAssessmentWeightManagement('SchoolAssessmentTypeWeight', 'create', $assessmentTypeWeight))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'Bobot berhasil ditambahkan.',
            'data'    => $assessmentTypeWeight,
        ]);
    }

    // function assessment weight edit
    public function assessmentWeightEdit(Request $request, $schoolName, $schoolId, $assessmentWeightId)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'assessment_type_id' => 'required',
            'school_year' => 'required',
            'weight' => 'required|integer|min:1|max:100',
        ], [
            'assessment_type_id' => 'Harap pilih tipe asesmen.',
            'school_year' => 'Harap pilih tahun ajaran.',
            'weight.required' => 'Bobot asesmen tidak boleh kosong.',
            'weight.min' => 'Bobot asesmen harus lebih dari 0.',
            'weight.max' => 'Bobot asesmen tidak boleh lebih dari 100.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $exists = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->where('school_year', $request->school_year)->where('assessment_type_id', $request->assessment_type_id)
        ->where('id', '!=', $assessmentWeightId)->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'errors' => [
                    'assessment_type_id' => ['Bobot pada asesmen di tahun ajaran ini telah terdaftar.']
                ]
            ], 422);
        }

        $assessmentTypeWeight = SchoolAssessmentTypeWeight::findOrFail($assessmentWeightId);

        $totalWeight = SchoolAssessmentTypeWeight::where('school_partner_id', $schoolId)->sum('weight');

        $oldWeight = $assessmentTypeWeight->weight;

        $newTotal = $totalWeight - $oldWeight + $request->weight;

        if ($newTotal > 100) {
            return response()->json([
                'status' => 'error',
                'error_type' => 'weight_limit_exceeded',
                'message' => 'Total bobot semua jenis asesmen tidak boleh melebihi 100%. Silakan sesuaikan bobot yang ada.',
            ], 422);
        }

        $assessmentTypeWeight->update([
            'user_id' => $user->id,
            'school_partner_id' => $schoolId,
            'assessment_type_id' => $request->assessment_type_id,
            'weight' => $request->weight,
            'school_year' => $request->school_year,
        ]);

        broadcast(new LmsAssessmentWeightManagement('SchoolAssessmentTypeWeight', 'update', $assessmentTypeWeight))->toOthers();

        return response()->json([
            'status'  => 'success',
            'message' => 'data berhasil diubah.',
            'data'    => $assessmentTypeWeight,
        ]);
    }
}