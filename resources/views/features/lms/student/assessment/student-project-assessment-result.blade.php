@include('components.sidebar-beranda', [
    'headerSideNav' => 'Result Assessment',
    'linkBackButton' => route('lms.studentPreviewAssessment.view', [$role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
])

@if (Auth::user()->role === 'Siswa')

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">

    <div class="my-15 mx-7.5">

        <section class="bg-white border border-gray-200 rounded-xl shadow-lg p-0 lg:p-6">

            <!-- SCORE SECTION -->
            @if($schoolAssessment->show_score)

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-10">

                    <!-- SCORE CARD -->
                    <div>

                        <div class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-8 flex flex-col items-center justify-center
                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)] relative">

                            <!-- STATUS BADGE -->
                            @if(!$isFullyGraded)

                                <div class="absolute top-4 right-4 bg-yellow-400 text-black text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                    Nilai Sementara
                                </div>

                                @else

                                <div class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i>
                                    Nilai Final
                                </div>

                            @endif

                            <p class="text-sm opacity-80">
                                Nilai Project
                            </p>

                            <div class="text-7xl font-extrabold mt-3">
                                {{ $score ?? 0 }}
                            </div>

                            <p class="text-sm mt-4 text-center opacity-80 max-w-xs">
                                Penilaian berdasarkan evaluasi guru terhadap project yang kamu kirim.
                            </p>

                            @if(!$isFullyGraded)
                                <p class="text-xs text-white/80 mt-4 text-center max-w-xs">
                                    Nilai ini bersifat sementara karena project kamu masih dalam proses penilaian oleh guru.
                                </p>
                            @endif

                        </div>

                    </div>


                    <!-- FILE PROJECT -->
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-lg">

                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-file text-[#0071BC]"></i>
                            File Project Kamu
                        </h3>

                        @if($submission && $submission->file_path)

                        <div class="flex items-center justify-between bg-gray-50 border border-gray-300 rounded-lg p-4">

                            <div class="flex items-center gap-3">

                                <i class="fa-solid fa-file-lines text-[#0071BC] text-xl"></i>

                                <div class="flex flex-col break-all">
                                    <span class="text-sm font-medium text-gray-700 wrap-break-word truncate max-w-50">
                                        {{ $submission->original_filename }}
                                    </span>

                                    <span class="text-xs text-gray-400">
                                        Dikirim pada {{ $submission->created_at->format('d M Y - H:i') }}
                                    </span>
                                </div>

                            </div>

                            <button onclick="previewStudentFile('{{ asset('assessment/assessment-file-submission/'.$submission->file_path) }}')" class="bg-[#0071BC] text-white text-sm px-4 py-2 
                                rounded-lg hover:bg-[#005a96] transition cursor-pointer">
                                Lihat File
                            </button>

                        </div>

                        @else

                        <div class="text-sm text-gray-500">
                            Tidak ada file yang diupload.
                        </div>

                        @endif

                    </div>

                </div>

                <!-- TEXT ANSWER -->
                @if($submission && $submission->text_answer)

                    <div class="mt-8 bg-white border border-gray-200 rounded-xl p-6 shadow-lg">

                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-file-lines text-[#0071BC]"></i>
                            Jawaban Text
                        </h3>

                        <div class="bg-gray-50 border rounded-lg p-4 text-gray-700 leading-relaxed whitespace-pre-line">
                            {{ $submission->text_answer }}
                        </div>

                    </div>

                @endif

                <!-- FEEDBACK -->
                @if($submission && $submission->teacher_feedback)

                    <div class="mt-8 bg-white border border-gray-200 rounded-xl p-6 shadow-lg">

                        <h3 class="font-bold text-lg mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-comment-dots text-[#0071BC]"></i>
                            Feedback Guru
                        </h3>

                        <textarea class="w-full resize-none bg-gray-50 border border-gray-300 rounded-lg p-4 text-gray-700 leading-relaxed whitespace-pre-line
                        ">{{ $submission->teacher_feedback }}</textarea>

                    </div>

                @endif

            @else

                <!-- SCORE DISABLED -->
                <div class="mt-12">

                    <div class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-16 flex flex-col items-center justify-center text-center
                        shadow-[0_8px_20px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]">

                        <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center mb-6">
                            <i class="fa-solid fa-eye-slash text-4xl"></i>
                        </div>

                        <h2 class="text-3xl font-bold mb-4">
                            Nilai Belum Ditampilkan
                        </h2>

                        <p class="text-white/80 max-w-xl">
                            Guru kamu sedang menonaktifkan tampilan nilai untuk asesmen ini.
                        </p>

                    </div>

                </div>

            @endif

            <dialog id="student-file-preview-modal" class="modal">
                <div class="modal-box max-w-6xl">

                    <h3 class="font-bold text-lg mb-4">
                        Preview File
                    </h3>

                    <div id="student-file-preview-content"></div>

                    <div class="modal-action">
                        <form method="dialog">
                            <button class="btn">Tutup</button>
                        </form>
                    </div>

                </div>
            </dialog>

            <!-- ACTION BUTTON -->
            <div class="flex flex-col lg:flex-row gap-4 justify-center mt-10">

                <a href="{{ route('lms.studentPreviewAssessment.view', [$role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester]) }}"
                    class="bg-[#0071BC] text-white px-6 py-3 rounded-xl shadow-lg hover:scale-105 transition font-semibold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-house"></i>
                    Kembali ke halaman asesmen
                </a>

            </div>

        </section>

    </div>

</div>

@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>You do not have access to this page.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student/assessment/student-preview-project-assessment-result.js') }}"></script> <!--- student preview project assessment result ---->