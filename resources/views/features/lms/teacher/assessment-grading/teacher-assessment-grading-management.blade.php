@include('components/sidebar-beranda', ['headerSideNav' => 'Assessment Grading'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

        <div class="my-15 mx-7.5">

            <main>
                <!-- HEADER -->
                <section class="bg-linear-to-br from-[#0071BC] to-[#003456] text-white rounded-2xl p-6 md:p-8 mb-6 shadow-lg">
                    
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

                        <!-- Title -->
                        <div>
                            <h1 class="text-xl font-semibold">
                                Assessment Grading Management
                            </h1>
                            <p class="text-sm text-blue-100 mt-1">
                                Manage and grade student assessments that require manual grading.
                            </p>
                        </div>

                        <!-- Search -->
                        <div class="w-full lg:w-80">
                            <label class="flex items-center gap-2 bg-white text-gray-700 px-4 py-2 rounded-md shadow-sm focus-within:ring-2 focus-within:ring-blue-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                                </svg>
                                <input id="search_assessment" type="search"
                                    class="w-full text-sm focus:outline-none"
                                    placeholder="Cari judul asesmen..." autocomplete="off" />
                            </label>
                        </div>

                    </div>
                </section>


                <!-- FILTER -->
                <section class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm">

                    <!-- Header -->
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-filter text-[#0071BC]"></i>
                            <h3 class="text-base font-semibold text-gray-800">
                                Filter Assessment
                            </h3>
                        </div>
                    </div>

                    <!-- Filter Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">

                        <div id="container-dropdown-assessment-management-tahun-ajaran"></div>

                        <div id="container-dropdown-assessment-management-class"></div>

                        <div id="container-dropdown-assessment-type"></div>

                    </div>

                </section>
                
                <section>
                    <div id="container-assessment-grading-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="overflow-x-auto mt-6 pb-20">
                        <table id="table-assessment-grading-list" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-assessment-grading-list bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Judul Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Total Submit</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Menunggu Penilaian</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-assessment-grading-list">  
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>
        
                    <div class="pagination-container-teacher-assessment-grading-list flex justify-center my-10"></div>
        
                    <div id="empty-message-assessment-grading-list" class="w-full h-80 hidden">
                        <span class="flex h-full items-center justify-center text-gray-500">
                            Tidak ada asesmen yang terdaftar.
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

<script src="{{ asset('assets/js/Features/lms/teacher/assessment-grading/paginate-teacher-assessment-grading.js') }}"></script> <!--- paginate teacher assessment grading ---->