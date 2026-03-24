<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LmsController;
use App\Http\Controllers\MasterAcademicController;
use App\Http\Controllers\SchoolPartnerController;
use App\Http\Controllers\SchoolSyllabusController;
use App\Http\Controllers\ServiceRuleController;
use App\Http\Controllers\StudentAssessmentController;
use App\Http\Controllers\StudentAssessmentExamController;
use App\Http\Controllers\StudentLearningController;
use App\Http\Controllers\StudentSubjectProgressController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\TeacherAssessmentController;
use App\Http\Controllers\TeacherAssessmentGradingController;
use App\Http\Controllers\TeacherContentController;
use App\Http\Controllers\TeacherContentReleaseController;
use App\Http\Controllers\TeacherQuestionBankController;
use App\Http\Controllers\TeacherQuestionBankReleaseController;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Middleware\RedirectIfAuthenticated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ROUTE FALLBACK
Route::fallback(function () {
    // Sudah login → arahkan ke dashboard
    if (Auth::check()) {
        return redirect()->route('beranda');
    }

    // Belum login → arahkan ke login
    return redirect()->route('login');
});

Route::get('/', fn () => redirect('/login'));

// middleware redirect if authenticated
Route::middleware([RedirectIfAuthenticated::class])->group(function () {
    Route::get('/login', [AuthController::class, 'loginView'])->name('login');
});

// routes auth login & logout
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ROUTES DROPDOWN KURIKULUM, KELAS, MAPEL, BAB, SUB BAB
Route::get('/kelas/{id}', [MasterAcademicController::class, 'getKelas']); // kelas by fase

// service for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']); // service by kurikulum
Route::get('/kurikulum/{curriculumId}/{schoolId}/service', [MasterAcademicController::class, 'getServiceByKurikulum']); // service by kurikulum

// kelas for school partner & non school partner
Route::get('/kurikulum/{curriculumId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']); // kelas by kurikulum
Route::get('/kurikulum/{curriculumId}/{schoolId}/kelas', [MasterAcademicController::class, 'getKelasByKurikulum']); // kelas by kurikulum

// route dependent dropdown mapel by kelas non school partner & school partner
Route::get('/kelas/{kelasId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']); // mapel by kelas
Route::get('/kelas/{kelasId}/{schoolId}/mapel', [MasterAcademicController::class, 'getMapelByKelas']); // mapel by kelas

// route dependent dropdown rombel kelas by kelas
Route::get('/kelas/{kelasId}/rombel-kelas/{schoolId}', [MasterAcademicController::class, 'getRombelByKelas']); // rombel kelas by kelas

Route::get('/mapel/{mapelId}/bab', [MasterAcademicController::class, 'getBabByMapel']); // bab by mapel
Route::get('/bab/{babId}/sub-bab', [MasterAcademicController::class, 'getSubBabByBab']); // sub bab by bab

Route::get('/service/{service}/rules', [ServiceRuleController::class, 'index']); // rules by service

// MIDDLEWARE LOGIN
Route::middleware([AuthMiddleware::class])->group(function () {
    // DASHBOARD
    Route::get('/beranda', [DashboardController::class, 'index'])->name('beranda');

    //ROUTES SYLLABUS-SERVICES
    // VIEWS
    Route::get('/syllabus/curriculum', [SyllabusController::class, 'curriculumView'])->name('kurikulum.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/fase', [SyllabusController::class, 'faseView'])->name('fase.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/kelas', [SyllabusController::class, 'kelasView'])->name('kelas.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SyllabusController::class, 'mapelView'])->name('mapel.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SyllabusController::class, 'babView'])->name('bab.view');
    Route::get('/syllabus/curriculum/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SyllabusController::class, 'subBabView'])->name('subBab.view');

    // CRUD Kurikulum
    Route::post('/syllabus/curriculum/store', [SyllabusController::class, 'curiculumStore'])->name('kurikulum.store');
    Route::post('/syllabus/curriculum/edit/{curriculumId}', [SyllabusController::class, 'curiculumEdit'])->name('kurikulum.edit');

    // CRUD Fase
    Route::post('/syllabus/{curriculumId}/fase/store', [SyllabusController::class, 'faseStore'])->name('fase.store');
    Route::post('/syllabus/curriculum/fase/edit/{curriculumId}/{faseId}', [SyllabusController::class, 'faseEdit'])->name('fase.edit');

    // CRUD Kelas
    Route::post('/syllabus/{curriculumId}/{faseId}/kelas/store', [SyllabusController::class, 'kelasStore'])->name('kelas.store');
    Route::post('/syllabus/curriculum/kelas/edit/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'kelasEdit'])->name('kelas.edit');

    // CRUD Mapel
    Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SyllabusController::class, 'mapelStore'])->name('mapel.store');
    Route::post('/syllabus/curriculum/mapel/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'mapelEdit'])->name('mapel.edit');
    Route::put('/syllabus/curriculum/mapel/activate/{mapelId}', [SyllabusController::class, 'mapelActivate'])->name('mapel.activate');

    // CRUD Bab
    Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SyllabusController::class, 'babStore'])->name('bab.store');
    Route::post('/syllabus/curriculum/bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'babEdit'])->name('bab.edit');
    Route::put('/syllabus/curriculum/bab/activate/{babId}', [SyllabusController::class, 'babActivate'])->name('bab.activate');

    // CRUD Sub Bab
    Route::post('/syllabus/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/store', [SyllabusController::class, 'subBabStore'])->name('subBab.store');
    Route::post('/syllabus/curriculum/sub-bab/edit/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/{subBabId}', [SyllabusController::class, 'subBabEdit'])->name('subBab.edit');
    Route::put('/syllabus/curriculum/sub-bab/activate/{subBabId}', [SyllabusController::class, 'subBabActivate'])->name('subBab.activate');

    // PAGINATE SYLLABUS-SERVICES
    Route::get('/paginate-syllabus-service-kurikulum', [SyllabusController::class, 'paginateSyllabusCuriculum'])->name('syllabus.kurikulum');
    Route::get('/paginate-syllabus-service-fase/{curriculumName}/{curriculumId}', [SyllabusController::class, 'paginateSyllabusFase'])->name('syllabus.fase');
    Route::get('/paginate-syllabus-service-kelas/{curriculumName}/{curriculumId}/{faseId}', [SyllabusController::class, 'paginateSyllabusKelas'])->name('syllabus.kelas');
    Route::get('/paginate-syllabus-service-mapel/{curriculumName}/{curriculumId}/{faseId}/{kelasId}', [SyllabusController::class, 'paginateSyllabusMapel'])->name('syllabus.mapel');
    Route::get('/paginate-syllabus-service-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}', [SyllabusController::class, 'paginateSyllabusBab'])->name('syllabus.bab');
    Route::get('/paginate-syllabus-service-sub-bab/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}', [SyllabusController::class, 'paginateSyllabusSubBab'])->name('syllabus.subBab');

    // BULKUPLOAD SYLLABUS
    Route::post('/syllabus/bulkupload/syllabus', [SyllabusController::class, 'bulkUploadSyllabus'])->name('syllabus.bulkupload');

    // ROUTES SCHOOL CURRICULUM MANAGEMENT HIERARCHY
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/kurikulum', [SchoolSyllabusController::class, 'curriculumView'])->name('schoolCurriculumManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/fase', [SchoolSyllabusController::class, 'faseView'])->name('schoolFaseManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/kelas', [SchoolSyllabusController::class, 'kelasView'])->name('schoolKelasManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel', [SchoolSyllabusController::class, 'mapelView'])->name('schoolMapelManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab', [SchoolSyllabusController::class, 'babView'])->name('schoolBabManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab', [SchoolSyllabusController::class, 'subBabView'])->name('schoolSubBabManagement.view');

    // crud mapel
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/store', [SchoolSyllabusController::class, 'mapelStore'])->name('schoolMapelManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/edit', [SchoolSyllabusController::class, 'mapelEdit'])->name('schoolMapelManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/{mapelId}/activate', [SchoolSyllabusController::class, 'mapelActivate'])->name('schoolMapelManagement.activate');

    // crud bab
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/store', [SchoolSyllabusController::class, 'babStore'])->name('schoolBabManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/edit', [SchoolSyllabusController::class, 'babEdit'])->name('schoolBabManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/{babId}/activate', [SchoolSyllabusController::class, 'babActivate'])->name('schoolBabManagement.activate');

    // crud sub bab
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/store', [SchoolSyllabusController::class, 'subBabStore'])->name('schoolSubBabManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/edit', [SchoolSyllabusController::class, 'subBabEdit'])->name('schoolSubBabManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/{subBabId}/activate', [SchoolSyllabusController::class, 'subBabActivate'])->name('schoolSubBabManagement.activate');
    
    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/kurikulum/paginate', [SchoolSyllabusController::class, 'paginateCurriculum'])->name('schoolCurriculumManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/fase/paginate', [SchoolSyllabusController::class, 'paginateFase'])->name('schoolFaseManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/kelas/paginate', [SchoolSyllabusController::class, 'paginateKelas'])->name('schoolKelasManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/mapel/paginate', [SchoolSyllabusController::class, 'paginateMapel'])->name('schoolMapelManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/bab/paginate', [SchoolSyllabusController::class, 'paginateBab'])->name('schoolBabManagement.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/{curriculumName}/{curriculumId}/{faseId}/{kelasId}/{mapelId}/{babId}/sub-bab/paginate', [SchoolSyllabusController::class, 'paginateSubBab'])->name('schoolSubBabManagement.paginate');

    // ROUTES LMS FEATURE
    // views (administrator)
    Route::get('/lms/school-subscription', [LmsController::class, 'lmsSchoolSubscriptionView'])->name('lms.schoolSubscription.view');

    // routes academic management
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management', [LmsController::class, 'lmsAcademicManagementView'])->name('lms.academicManagement.view');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/academic-management/paginate', [LmsController::class, 'paginateLmsAcademicManagement'])->name('lms.academicManagement.paginate');

    // route management role account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account', [LmsController::class, 'lmsManagementRolesView'])->name('lms.managementRoles.view');

    // route management account
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-accounts', [LmsController::class, 'lmsManagementAccountView'])->name('lms.managementAccount.view');

    // route management majors
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors', [LmsController::class, 'lmsManagementMajorsView'])->name('lms.managementMajors.view');

    // routes views class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/management-class', [LmsController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class', [LmsController::class, 'lmsManagementClassView'])->name('lms.managementClass.view.noMajor');

    // routes views students in class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/management-majors/{majorId}/management-students', [LmsController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/management-students', [LmsController::class, 'lmsManagementStudentsView'])->name('lms.managementStudents.view.noMajor');

    // CRUD
    // routes crud majors
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/create', [LmsController::class, 'lmsManagementCreateMajor'])->name('lms.managementCreateMajor.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/edit', [LmsController::class, 'lmsManagementEditMajor'])->name('lms.managementEditMajor.store');

    // routes create management class by major and no major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-majors/{majorId}/management-class/create', [LmsController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.major');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/create', [LmsController::class, 'lmsManagementCreateClass'])->name('lms.managementCreateClass.store.noMajor');

    // routes edit management class by major and no major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/management-majors/{majorId}/edit', [LmsController::class, 'lmsManagementEditClass'])->name('lms.managementClassWithMajor.edit');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/edit', [LmsController::class, 'lmsManagementEditClass'])->name('lms.managementClassNoMajor.edit');

    // routes activate school subscription, account, major, class, student in class
    Route::put('/lms/school-subscription/{subscriptionId}/activate', [LmsController::class, 'lmsSchoolSubscriptionActivate'])->name('lms.schoolSubscription.activate');
    Route::put('/lms/school-subscription/{schoolId}/management-account/{id}/activate-account', [LmsController::class, 'lmsActivateAccount'])->name('lms.account.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-major', [LmsController::class, 'lmsActivateMajor'])->name('lms.major.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-class', [LmsController::class, 'lmsActivateClass'])->name('lms.class.activate');
    Route::put('/lms/school-subscription/management-class/{id}/activate-student-in-class', [LmsController::class, 'lmsActivateStudentInClass'])->name('lms.studentInClass.activate');

    // routes promote class, repeat class, move class, move major
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/promote-class', [LmsController::class, 'lmsManagementPromoteClass'])->name('lms.managementPromoteClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/repeat-class', [LmsController::class, 'lmsManagementRepeatClass'])->name('lms.managementRepeatClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-class', [LmsController::class, 'lmsManagementMoveClass'])->name('lms.managementMoveClass.create');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-class/{classId}/move-major', [LmsController::class, 'lmsManagementMoveMajor'])->name('lms.managementMoveMajor.create');

    // get promotion next class / repeat class / move class / move major options
    // promote-to-next-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/promotion-to-next-class-options',[LmsController::class, 'promotionClassOptions']);
    Route::get('/lms/school/{schoolId}/promotion-to-next-class-options',[LmsController::class, 'promotionClassOptions']);

    // promote-to-repeat-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/repeat-class-options',[LmsController::class, 'repeatClassOptions']);
    Route::get('/lms/school/{schoolId}/repeat-class-options',[LmsController::class, 'repeatClassOptions']);

    // promote-to-move-class routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/move-class-options',[LmsController::class, 'moveClassOptions']);
    Route::get('/lms/school/{schoolId}/move-class-options',[LmsController::class, 'moveClassOptions']);

    // move major routes by major and no major
    Route::get('/lms/school/{schoolId}/{majorId}/move-major-options',[LmsController::class, 'moveMajorOptions']);
    Route::get('/lms/school/{schoolId}/move-major-options',[LmsController::class, 'moveMajorOptions']);

    // paginate
    Route::get('/lms/school-subscription/paginate', [LmsController::class, 'paginateLmsSchoolSubscription'])->name('lms.schoolSubscription.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionRoleAccount'])->name('lms.SchoolSubscriptionRoleAccount.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/management-role-account/{role}/management-accounts/paginate', [LmsController::class, 'paginateLmsSchoolAccount'])->name('lms.SchoolSubscriptionAccount.paginate');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-majors/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionMajors'])->name('lms.SchoolSubscriptionMajors.paginate');

    // paginate class by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-majors/{majorId}/management-class/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionClass'])->name('lms.SchoolSubscriptionClass.paginate.noMajor');

    // paginate users by major and no major
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/{classId}/management-majors/{majorId}/management-students/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.major');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/role-account/{role}/management-class/{classId}/management-students/paginate', [LmsController::class, 'paginateLmsSchoolSubscriptionUsers'])->name('lms.SchoolSubscriptionUsers.paginate.noMajor');

    // ROUTES QUESTION BANK MANAGEMENT
    // view
    // question bank management no school partner & school partner
    Route::get('/lms/question-bank-management', [LmsController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management', [LmsController::class, 'lmsQuestionBankManagementView'])->name('lms.questionBankManagement.view.schoolPartner');

    // review question bank no school partner & school partner
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}', [LmsController::class, 'lmsQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.noSchoolPartner');
    Route::get('/lms/school-subscription/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/{schoolName}/{schoolId}', [LmsController::class, 'lmsQuestionBankManagementDetailView'])->name('lms.questionBankManagementDetail.view.schoolPartner');

    // edit question bank no school partner & school partner
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/edit', [LmsController::class, 'lmsQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.noSchoolPartner');
    Route::get('/lms/school-subscription/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/{schoolName}/{schoolId}/edit', [LmsController::class, 'lmsQuestionBankManagementEditView'])->name('lms.questionBankManagementEdit.view.schoolPartner');

    // form question bank edit no school partner & school partner
    Route::get('/lms/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/edit', [LmsController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.noSchoolPartner');
    Route::get('/lms/school-subscription/question-bank-management/bank-soal/form/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/{schoolName}/{schoolId}/edit', [LmsController::class, 'formEditQuestion'])->name('lms.bankSoal.form.edit.question.schoolPartner');

    // crud bank soal
    // upload bank soal no school partner & school partner
    Route::post('/lms/question-bank-management/store', [LmsController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.noSchoolPartner');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/store', [LmsController::class, 'lmsQuestionBankManagementStore'])->name('lms.questionBankManagement.store.schoolPartner');

    // edit & delete image bank soal with ckeditor
    Route::post('/lms/bank-soal/edit-image', [LmsController::class, 'editImageBankSoal'])->name('lms.editImage');
    Route::post('/lms/bank-soal/delete-image/endpoint', [LmsController::class, 'deleteImageBankSoal'])->name('lms.deleteImage');

    // activate question bank no school partner & school partner
    Route::put('/lms/question-bank-management/{subBabId}/source/{source}/question-type/{questionType}/activate', [LmsController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.noSchoolPartner');
    Route::put('/lms/school-subscription/question-bank-management/{subBabId}/source/{source}/question-type/{questionType}/{schoolName}/{schoolId}/activate', [LmsController::class, 'lmsActivateQuestionBank'])->name('lms.questionBank.activate.schoolPartner');

    // edit bank soal no school partner & school partner submit form
    Route::post('/lms/question-bank-management/{questionId}/edit', [LmsController::class, 'lmsQuestionBankManagementEdit'])->name('lms.questionBankManagement.edit');

    // paginate
    // question bank management no school partner & school partner
    Route::get('/lms/question-bank-management/paginate', [LmsController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/question-bank-management/paginate', [LmsController::class, 'paginateLmsQuestionBankManagement'])->name('lms.questionBankManagement.paginate.schoolPartner');

    // paginate review question bank no school partner & school partner
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/paginate', [LmsController::class, 'paginateReviewQuestionBank'])->name('lms.questionBankManagementDetail.paginate.noSchoolPartner');
    Route::get('/lms/question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/school-subscription/{schoolName}/{schoolId}/paginate', [LmsController::class, 'paginateReviewQuestionBank'])->name('lms.reviewQuestionBank.paginate.schoolPartner');

    // ROUTES CONTENT MANAGEMENT
    // view content management no school partner & school partner
    Route::get('/lms/content-management', [LmsController::class, 'lmsContentManagementView'])->name('lms.contentManagement.view.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/content-management', [LmsController::class, 'lmsContentManagementView'])->name('lms.contentManagement.view.schoolPartner');

    // view content management edit no school partner & school partner
    Route::get('/lms/content-management/{contentId}/edit', [LmsController::class, 'lmsContentManagementEditView'])->name('lms.contentManagement.edit.view.noSchoolPartner');
    Route::get('/lms/school-subscription/content-management/{contentId}/{schoolName}/{schoolId}/edit', [LmsController::class, 'lmsContentManagementEditView'])->name('lms.contentManagement.edit.view.schoolPartner');
    
    // view content management review no school partner & school partner
    Route::get('/lms/content-management/{contentId}/review', [LmsController::class, 'lmsReviewContent'])->name('lms.contentManagement.review.noSchoolPartner');
    Route::get('/lms/school-subscription/content-management/{contentId}/{schoolName}/{schoolId}/review', [LmsController::class, 'lmsReviewContent'])->name('lms.contentManagement.review.schoolPartner');

    // form edit content
    Route::get('/lms/content-management/{contentId}/form/edit', [LmsController::class, 'lmsContentManagementFormEdit'])->name('lms.contentManagementForm.edit');

    // crud
    // create content management no school partner & school partner
    Route::post('/lms/content-management/store', [LmsController::class, 'lmsContentManagementStore'])->name('lms.contentManagement.store.noSchoolPartner');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/content-management/store', [LmsController::class, 'lmsContentManagementStore'])->name('lms.contentManagement.store.schoolPartner');

    Route::post('/lms/content-management/{contentId}/edit-action', [LmsController::class, 'lmsContentManagementEdit'])->name('lms.contentManagement.edit');

    // routes activate content management no school partner & school partner
    Route::put('/lms/content-management/{contentId}/activate', [LmsController::class, 'lmsContentManagementActivate'])->name('lms.contentManagement.activate.noSchoolPartner');
    Route::put('/lms/school-subscription/content-management/{contentId}/{schoolName}/{schoolId}/activate', [LmsController::class, 'lmsContentManagementActivate'])->name('lms.contentManagement.activate.schoolPartner');

    // paginate content management no school partner & school partner
    Route::get('/lms/content-management/paginate', [LmsController::class, 'paginateLmsContentManagement'])->name('lms.contentManagement.paginate.noSchoolPartner');
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/content-management/paginate', [LmsController::class, 'paginateLmsContentManagement'])->name('lms.contentManagement.paginate.schoolPartner');

    // ROUTES ASSESSMENT TYPE MANAGEMENT
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management', [LmsController::class, 'lmsAssessmentTypeManagementView'])->name('lms.assessmentTypeManagement.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/store', [LmsController::class, 'lmsAssessmentTypeManagementStore'])->name('lms.assessmentTypeManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/edit', [LmsController::class, 'lmsAssessmentTypeManagementEdit'])->name('lms.assessmentTypeManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/{assessmentTypeId}/activate', [LmsController::class, 'lmsAssessmentTypeManagementActivate'])->name('lms.assessmentTypeManagement.activate');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-type-management/paginate', [LmsController::class, 'paginateLmsAssessmentTypeManagement'])->name('lms.assessmentTypeManagement.paginate');

    // ROUTES TEACHER SUBJECT MANAGEMENT
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management', [LmsController::class, 'lmsTeacherSubjectManagement'])->name('lmsTeacherSubjectManagement.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/store', [LmsController::class, 'lmsTeacherSubjectManagementStore'])->name('lmsTeacherSubjectManagement.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/edit', [LmsController::class, 'lmsTeacherSubjectManagementEdit'])->name('lmsTeacherSubjectManagement.edit');
    Route::put('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/{teacherSubjectId}/activate', [LmsController::class, 'lmsTeacherSubjectManagementActivate'])->name('lmsTeacherSubjectManagement.activate');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/subject-teacher-management/paginate', [LmsController::class, 'paginateLmsTeacherSubjectManagement'])->name('lmsTeacherSubjectManagement.paginate');

    // ROUTES ASSESSMENT WEIGHT MANAGEMENT
    // views
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management', [LmsController::class, 'assessmentWeight'])->name('lms.assessmentWeight.view');

    // crud
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/store', [LmsController::class, 'assessmentWeightStore'])->name('lms.assessmentWeight.store');
    Route::post('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/{assessmentWeightId}/edit', [LmsController::class, 'assessmentWeightEdit'])->name('lms.assessmentWeight.edit');

    // paginate
    Route::get('/lms/school-subscription/{schoolName}/{schoolId}/assessment-weight-management/paginate', [LmsController::class, 'paginateAssessmentWeight'])->name('lms.assessmentWeight.paginate');

    // ROUTES STUDENT LMS
    // components routes
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress', [StudentSubjectProgressController::class, 'index'])->name('lms.studentSubjectProgress.index');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/subject-progress/data', [StudentSubjectProgressController::class, 'data'])->name('lms.studentSubjectProgress.data');

    // learning routes
    Route::get('/lms/{role}/{schoolName}/{schoolId}', [StudentLearningController::class, 'lmsStudentView'])->name('lms.student.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning', [StudentLearningController::class, 'studentLearning'])->name('lms.studentLearning.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}', [StudentLearningController::class, 'studentReviewMeeting'])->name('lms.studentReviewMeeting.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/show-content/{meetingContentId}', [StudentLearningController::class, 'showStudentReviewContent'])->name('lms.studentReviewContent.show');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/download-content/{meetingContentId}', [StudentLearningController::class, 'downloadStudentContent'])->name('lms.studentContent.download');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/paginate', [StudentLearningController::class, 'paginateLmsStudent'])->name('lms.student.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/paginate', [StudentLearningController::class, 'paginateStudentLearning'])->name('lms.studentLearning.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/service/{serviceId}/paginate', [StudentLearningController::class, 'paginateStudentReviewMeeting'])->name('lms.studentReviewMeeting.paginate');

    // preview assessment routes
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}', [StudentAssessmentController::class, 'studentPreviewAssessment'])->name('lms.studentPreviewAssessment.view');

    // load assessment data by semester
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}', [StudentAssessmentController::class, 'loadStudentPreviewAssessment'])->name('lms.loadStudentPreviewAssessment');
    Route::get('/lms/check-assessment-status/{assessmentId}', [StudentAssessmentController::class, 'checkAssessmentStatus'])->name('lms.checkAssessmentStatus');

    // assessment (exam)
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/test/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExam'])->name('lms.studentAssessmentExan.view');

    // form
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}', [StudentAssessmentExamController::class, 'studentAssessmentExamForm'])->name('lms.studentAssessmentExan.form');

    // routes timer
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/start-timer', [StudentAssessmentExamController::class, 'startTimer'])->name('lms.startTimer.test');

    // routes report tab switch (cheating detection)
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/report-tab-switch', [StudentAssessmentExamController::class, 'reportTabSwitch'])->name('lms.reportTabSwitch.cheating');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/attempt-status', [StudentAssessmentExamController::class, 'checkAttemptStatus'])->name('lms.checkAttemptStatus.cheating');

    // answer
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/answer', [StudentAssessmentExamController::class, 'studentAssessmentExamAnswer'])->name('lms.studentAssessmentExan.answer');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/project-submission', [StudentAssessmentExamController::class, 'studentProjectSubmission'])->name('lms.studentProjectSubmission.answer');

    // end assessment
    Route::post('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/form/{assessmentId}/emd', [StudentAssessmentExamController::class, 'studentAssessmentExamEnd'])->name('lms.studentAssessmentExan.emd');
    
    // routes store and delete image essay
    Route::post('/lms/image-essay/store-image/endpoint', [StudentAssessmentExamController::class, 'storeImageEssay'])->name('assessment-test.storeImage');
    Route::post('/lms/image-essay/delete-image/endpoint', [StudentAssessmentExamController::class, 'deleteImageEssay'])->name('assessment-test.deleteImage');

    // results
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/result-test', [StudentAssessmentExamController::class, 'studentResultAssessment'])->name('lms.studentAssessment.result');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/curriculum/{curriculumId}/subject/{mapelId}/learning/assessment/{assessmentTypeId}/semester/{semester}/assessment/{assessmentId}/project-result', [StudentAssessmentExamController::class, 'studentProjectResult'])->name('lms.studentProjectAssessment.result');

    // ROUTES TEACHER LMS
    // content management
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management', [TeacherContentController::class, 'teacherContentManagement'])->name('lms.teacherContentManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/review', [TeacherContentController::class, 'teacherReviewContent'])->name('lms.teacherContentManagement.review.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/{contentId}/edit', [TeacherContentController::class, 'teacherEditContent'])->name('lms.teacherContentManagement.edit.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-content-management/paginate', [TeacherContentController::class, 'paginateTeacherContentManagement'])->name('lms.teacherContentManagement.paginate');

    // content for release
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release', [TeacherContentReleaseController::class, 'teacherContentForRelease'])->name('lms.teacherContentForRelease.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-content/{meetingContentId}', [TeacherContentReleaseController::class, 'teacherContentForReleaseReviewContent'])->name('lms.teacherContentForReleaseReviewContent.view');

    // crud
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/form', [TeacherContentReleaseController::class, 'teacherFormContentForRelease'])->name('lms.teacherContentForRelease.form');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/store', [TeacherContentReleaseController::class, 'teacherContentForReleaseStore'])->name('lms.teacherContentForRelease.store');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/edit', [TeacherContentReleaseController::class, 'teacherContentForReleaseEdit'])->name('lms.teacherContentForRelease.edit');
    Route::put('/lms/{role}/{schoolName}/{schoolId}/content-for-release/{meetingContentId}/activate', [TeacherContentReleaseController::class, 'teacherContentForReleaseActivate'])->name('lms.teacherContentForRelease.activate');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForRelease'])->name('lms.teacherContentForRelease.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/content-for-release/rombel-kelas/{schoolClassId}/subject/{mapelId}/semester/{semester}/service/{serviceId}/review-meetings/paginate', [TeacherContentReleaseController::class, 'paginateTeacherContentForReleaseReviewMeeting'])->name('lms.teacherContentForReleaseReviewMeeting.paginate');

    // assessment management
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management', [TeacherAssessmentController::class, 'teacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit', [TeacherAssessmentController::class, 'teacherAssessmentManagementEdit'])->name('lms.teacherAssessmentManagementEdit.view');

    // form
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagement'])->name('lms.teacherFormAssessmentManagement.form');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit/form', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementEdit'])->name('lms.teacherFormAssessmentManagement.edit');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/validate-step-form/store', [TeacherAssessmentController::class, 'teacherFormAssessmentValidateStep'])->name('lms.teacherAssessmentManagementValidateStep.form');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/store', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementStore'])->name('lms.teacherAssessmentManagement.store');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/edit', [TeacherAssessmentController::class, 'teacherAssessmentManagementEditSubmission'])->name('lms.teacherAssessmentManagement.edit');
    Route::put('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/{assessmentId}/activate', [TeacherAssessmentController::class, 'teacherFormAssessmentManagementActivate'])->name('lms.teacherAssessmentManagement.activate');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-assessment-management/paginate', [TeacherAssessmentController::class, 'paginateTeacherAssessmentManagement'])->name('lms.teacherAssessmentManagement.paginate');

    // question bank management
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management', [TeacherQuestionBankController::class, 'teacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementDetail'])->name('lms.teacherQuestionBankManagement.detail.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/source/{source}/review/question-type/{questionType}/{subBabId}/{questionId}/edit', [TeacherQuestionBankController::class, 'teacherQuestionBankManagementEdit'])->name('lms.teacherQuestionBankManagement.edit.view');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-management/paginate', [TeacherQuestionBankController::class, 'paginateTeacherQuestionBankManagement'])->name('lms.teacherQuestionBankManagement.paginate');

    // question bank for release
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}', [TeacherQuestionBankReleaseController::class, 'teacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.view');

    // form
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/form', [TeacherQuestionBankReleaseController::class, 'teacherFormQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.form');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/store', [TeacherQuestionBankReleaseController::class, 'teacherQuestionBankForReleaseStore'])->name('lms.teacherQuestionBankForRelease.store');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherQuestionBankForRelease'])->name('lms.teacherQuestionBankForRelease.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/teacher-question-bank-for-release/review/{assessmentQuestionId}/paginate', [TeacherQuestionBankReleaseController::class, 'paginateTeacherReviewQuestionBankForRelease'])->name('lms.teacherReviewQuestionBankForRelease.paginate');

    // TEACHER ASSESSMENT GRADING
    // assessment grading management
    // views
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading', [TeacherAssessmentGradingController::class, 'assessmentGradingManagement'])->name('lms.assessmentGradingManagement.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.view');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring', [TeacherAssessmentGradingController::class, 'assessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.view');

    // crud
    Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{schoolAssessmentQuestionId}', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentScore'])->name('lms.assessmentGradingStudentAnswer.submission');
    Route::post('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/submission/{submissionId}/project', [TeacherAssessmentGradingController::class, 'submitAssessmentStudentProjectScore'])->name('lms.assessmentGradingStudentProject.submission');

    // paginate
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGrading'])->name('lms.assessmentGrading.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentList'])->name('lms.assessmentGradingStudentList.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/paginate', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentAnswer'])->name('lms.assessmentGradingStudentAnswer.paginate');
    Route::get('/lms/{role}/{schoolName}/{schoolId}/assessment-grading/{assessmentId}/student-list/{studentId}/scoring/project', [TeacherAssessmentGradingController::class, 'paginateAssessmentGradingStudentProject'])->name('lms.assessmentGradingStudentProject');
});

// ROUTES SCHOOL PARTNER
Route::post('/school-subcsription/store', [SchoolPartnerController::class, 'bulkUploadSchoolPartner'])->name('bulkUploadSchoolPartner.store');
Route::post('/school-subscription/add-users/store', [SchoolPartnerController::class, 'bulkUploadAddUsers'])->name('bulkUploadAddUsers.store');