@include('components/sidebar-beranda', [
    'headerSideNav' => 'Scoring',
    'linkBackButton' => route('lms.assessmentGradingStudentList.view', [$role, $schoolName, $schoolId, $assessmentId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-assesment-grading"></div>

            <main>
                <section id="container-assessment-grading-student-project" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}"
                    data-assessment-id="{{ $assessmentId }}" data-student-id="{{ $studentId }}">

                    <!-- HEADER -->
                    <div id="header-assessment-info" class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-5 md:p-7 mb-8 shadow-lg">
                        <!-- show assessment info in ajax -->
                    </div>

                    <div id="form-assessment-grading">
                        <!-- show form in ajax -->
                    </div>
                </section>

                <div id="empty-message-school-assessment-project" class="w-full h-80 hidden">
                    <span class="flex h-full items-center justify-center text-gray-500">
                        Tidak ada project yang terdaftar pada asesmen ini.
                    </span>
                </div>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/teacher/assessment-grading/paginate-teacher-assessment-grading-student-project-detail.js') }}"></script> <!--- paginate teacher assessment grading student project ---->