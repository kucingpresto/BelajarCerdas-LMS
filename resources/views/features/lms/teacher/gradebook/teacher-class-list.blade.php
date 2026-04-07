@include('components/sidebar-beranda', ['headerSideNav' => 'Class'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">
            <main>
                <!-- FILTER -->
                <section class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">

                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-filter text-[#0071BC]"></i>
                            <h3 class="text-base font-semibold text-gray-800">
                                Filter Kelas
                            </h3>
                        </div>
                    </div>

                    <!-- Filter Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div id="container-dropdown-teacher-class-list-tahun-ajaran"></div>
                        
                        <div id="container-dropdown-teacher-class-list-rombel"></div>

                        <div id="container-dropdown-subject-rombel-class"></div>
                    </div>

                </section>

                <section class="mt-6">
                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2 px-6">
                            <i class="fa-solid fa-chalkboard text-[#0071BC]"></i>
                            <h3 class="text-base font-semibold text-gray-800">
                                Kelas yang Kamu Ajar
                            </h3>
                        </div>
                    </div>

                    <div id="container-teacher-class-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}">
                        <div id="grid-teacher-class-list" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                            <!-- show data in ajax -->
                        </div>
                    </div>

                    <div id="empty-message-teacher-class-list" class="bg-white shadow-lg border border-gray-300 rounded-2xl w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada kelas yang kamu ajar.
                        </span>
                    </div>
                </section>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/teacher/gradebook/paginate-teacher-class-list.js') }}"></script> <!--- paginate teacher class list ---->