<x-script></x-script>

@if (Auth::user()->role === 'Siswa')
<main>
    <section>

        <!-- HEADER -->
        <header class="bg-white shadow-md">
            <div id="container-timer-assessment-test" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-curriculum-id="{{ $curriculumId }}"
                data-mapel-id="{{ $mapelId }}" data-assessment-type-id="{{ $assessmentTypeId }}" data-semester="{{ $semester }}" data-assessment-id="{{ $assessmentId }}"
                class="w-full h-27.5 mx-auto px-4 md:px-8 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3 relative">

                <!-- TOP ROW -->
                <div class="flex items-center justify-between w-full">

                    <!-- LOGO -->
                    <div class="w-10 h-10 md:w-14 md:h-14 rounded-full bg-linear-to-br from-blue-600 to-blue-400 flex items-center justify-center text-white text-lg md:text-xl">
                        <i class="fas fa-school"></i>
                    </div>

                    <!-- TIMER -->
                    <div class="bg-[#0071BC] text-white px-4 py-2 md:px-6 md:py-3 rounded-lg shadow flex items-center gap-2">
                        <i class="fas fa-clock"></i>
                        <span id="timer-assessment-test" class="font-semibold text-sm md:text-lg">
                            -
                        </span>
                    </div>

                </div>

                <!-- TITLE -->
                <div class="text-center md:absolute md:left-1/2 md:-translate-x-1/2">
                    <h2 id="assessment-title" class="text-lg md:text-xl font-semibold text-gray-700 tracking-wide">
                        -
                    </h2>
                </div>

            </div>
        </header>


        <!-- DATE BAR -->
        <div class="w-full px-4 md:px-10 mt-6">

            <div class="bg-white rounded-xl shadow-lg px-5 py-4 md:px-10 border border-gray-200 flex flex-col md:flex-row md:justify-between md:items-center gap-4">

                <!-- DATE INFO -->
                <div class="flex items-center gap-3">

                    <i class="fas fa-calendar-days text-xl md:text-2xl text-[#0071BC]"></i>

                    <div class="flex flex-col">
                        <p id="assessment-date" class="font-medium text-gray-700 text-sm md:text-base">
                            -
                        </p>

                        <p id="assessment-time" class="text-gray-500 text-xs md:text-sm">
                            -
                        </p>
                    </div>

                </div>

                <!-- BUTTON GROUP -->
                <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">

                    <button id="btn-submit-end-assessment-test" class="bg-[#F64650] text-white px-4 py-2 md:px-5 md:py-3 rounded-lg shadow cursor-pointer hidden w-full md:w-auto">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Akhiri Asesmen
                    </button>

                    <button id="btn-submit-exit-assessment-test" class="bg-[#F64650] text-white px-4 py-2 md:px-5 md:py-3 rounded-lg shadow cursor-pointer hidden w-full md:w-auto">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Keluar
                    </button>

                </div>

            </div>

        </div>

    </section>
</main>

@else

<div class="flex flex-col min-h-screen items-center justify-center">
    <p>ALERT SEMENTARA</p>
    <p>You do not have access to this pages.</p>
</div>

@endif


<script src="{{ asset('assets/js/features/lms/student/assessment/start-timer-assessment-test.js') }}"></script>