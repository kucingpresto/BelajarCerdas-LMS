@include('components/sidebar-beranda', [
    'headerSideNav' => 'Student List',
    'linkBackButton' => route('lms.assessmentGradingManagement.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

    <div class="my-15 mx-7.5">

        <!-- HEADER -->
        <div id="header-assessment-info" class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-6 md:p-8 mb-8 shadow-lg hidden">
            <!-- show header in ajax -->
        </div>
        
        <section>
            <div id="container-assessment-grading-student-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-assessment-id="{{ $assessmentId }}" 
                class="overflow-x-auto mt-6 pb-20">
                <table id="table-assessment-grading-student-list" class="min-w-full text-sm border-collapse">
                    <thead class="thead-table-assessment-grading-student-list bg-gray-50 shadow-inner">
                        <tr>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Siswa</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Status Submit</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nilai</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Status Nilai</th>
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-assessment-grading-student-list">
                        <!-- show data in ajax -->
                    </tbody>
                </table>
            </div>

            <div id="empty-message-assessment-grading-student-list" class="w-full h-80 hidden">
                <span class="flex h-full items-center justify-center text-gray-500">
                    Tidak ada siswa yang terdaftar pada asesmen ini.
                </span>
            </div>
        </section>
    </div>
</div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/teacher/assessment-grading/paginate-teacher-assessment-grading-student-list.js') }}"></script> <!--- paginate teacher assessment grading student list ---->