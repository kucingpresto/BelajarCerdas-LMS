@include('components/sidebar-beranda', [
    'headerSideNav' => 'Gradebook',
    'linkBackButton' => route('lms.teacherClassList.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
    <div class="my-10 mx-6">

        <!-- HEADER -->
        <div id="header-gradebook-info" class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-6 md:p-8 mb-8 shadow-lg hidden">
            <!-- show header in ajax -->
        </div>

        <section>
            <div id="container-teacher-gradebook" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-subject-teacher-id="{{ $subjectTeacherId }}"
                class="overflow-x-auto mt-6 pb-20">
                <table id="table-teacher-gradebook" class="min-w-full text-sm border-collapse">
                    <thead class="thead-table-teacher-gradebook bg-gray-50 shadow-inner">
                        <!-- show th in ajax -->
                    </thead>
                    <tbody id="tbody-teacher-gradebook">
                        <!-- show data in ajax -->
                    </tbody>
                </table>
            </div>

            <div id="empty-message-teacher-gradebook" class="w-full h-80 hidden">
                <span class="flex h-full items-center justify-center text-gray-500">
                    Tidak ada buku nilai siswa yang terdaftar.
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

<script src="{{ asset('assets/js/Features/lms/teacher/gradebook/paginate-teacher-gradebook-management.js') }}"></script> <!--- paginate teacher gradebook management ---->