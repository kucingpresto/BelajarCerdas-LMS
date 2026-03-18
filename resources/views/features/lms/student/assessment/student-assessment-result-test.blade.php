@include('components.sidebar-beranda', [
    'headerSideNav' => 'Result Assessment',
    'linkBackButton' => route('lms.studentPreviewAssessment.view', [
        $role, $schoolName, $schoolId, $curriculumId,
        $mapelId, $assessmentTypeId, $semester
    ]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
])

@if (Auth::user()->role === 'Siswa')

<div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
    <div class="my-15 mx-7.5">

        <section class="bg-white border border-gray-200 rounded-xl shadow-lg p-0 lg:p-6">

            {{-- ================= HERO SECTION ================= --}}
            <div class="relative w-full min-h-100 bg-[#0071BC] p-10 text-white overflow-visible flex items-center shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]"
                style="background-image: url('{{ asset('assets/images/components/background-bc.svg') }}'); background-size: cover; background-position: center;">

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-10 items-center w-full">

                    {{-- LEFT --}}
                    <div class="flex flex-col text-center space-y-3 lg-space-y-0 items-center xl:items-start">
                        <h2 class="text-2xl font-semibold">
                            Nilai Kamu Telah
                        </h2>
                        <h1 class="text-3xl font-bold leading-14 lg:leading-none">
                            Melampaui Batas KKM!
                        </h1>

                        <button class="mt-6 bg-green-500 hover:bg-green-600 px-6 py-2 rounded-lg font-semibold shadow-md transition">
                            Pengayaan
                        </button>
                    </div>

                    {{-- RIGHT MINI STATS --}}
                    <div class="w-full xl:flex xl:justify-end">
                        <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-2 xl:max-w-xl gap-6 text-gray-800 justify-center">

                            <div class="bg-white rounded-2xl h-36 px-6 py-4 shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)] flex flex-col relative">
                                
                                <div>
                                    <p class="text-sm text-gray-500 text-center font-bold leading-tight">
                                        Durasi Pengerjaan
                                    </p>
                                    <hr class="my-2 border-gray-400">
                                </div>

                                <p class="text-2xl font-bold text-center flex-1 flex items-center justify-center">
                                    {{ $totalDuration }}
                                </p>

                                <!-- INFO DROPDOWN -->
                                <div class="dropdown dropdown-end absolute bottom-3 right-4">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-circle-info text-gray-500 text-lg cursor-pointer"></i>
                                    </div>

                                    <div tabindex="0"
                                        class="dropdown-content z-9999 w-73 xl:w-100 h-37.5 bg-white rounded-xl shadow-xl p-4 text-center">

                                        <div class="relative flex items-center justify-center mb-2">
                                            <i class="fa-solid fa-circle-info text-gray-500 absolute left-0"></i>

                                            <span class="font-semibold text-gray-700 text-center w-full">
                                                Durasi Pengerjaan Ujian
                                            </span>
                                        </div>

                                        <hr class="border border-gray-400 mb-3">

                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-gray-600 leading-relaxed">
                                                Kamu lebih cepat <span class="font-bold text-black">{{ $percentileDuration }}%</span> dari
                                                <span class="font-bold text-black">{{ $totalStudents }} Siswa</span>
                                                dalam penyelesaian ujian ini!.
                                            </p>

                                            <div class="absolute right-0 bottom-0">
                                                <img src="{{ asset('assets/images/assessment-asset/rocket.svg') }}" alt="" class="w-14">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-2xl h-36 px-6 py-4 shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)] flex flex-col relative">
                                
                                <div>
                                    <p class="text-sm text-gray-500 text-center font-bold leading-tight">
                                        Tingkat Kepercayaan Diri
                                    </p>
                                    <hr class="my-2 border-gray-400">
                                </div>

                                <p class="text-3xl font-bold text-center flex-1 flex items-center justify-center">
                                    {{ $confidence ?? 0 }}%
                                </p>

                                <div class="dropdown dropdown-end absolute bottom-3 right-4">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-circle-info text-gray-500 text-lg cursor-pointer"></i>
                                    </div>

                                    <div tabindex="0" class="dropdown-content z-9999 w-73 xl:w-100 h-37.5 bg-white rounded-xl shadow-xl p-4">

                                        <div class="relative flex items-center justify-center mb-2">
                                            <i class="fa-solid fa-circle-info text-gray-500 absolute left-0"></i>
                                            <span class="font-semibold text-gray-700 text-center w-full">
                                                Tingkat Kepercayaan Diri
                                            </span>
                                        </div>

                                        <hr class="border-gray-300 mb-3">

                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-gray-600 leading-relaxed">
                                                Tingkat kepercayaan dirimu lebih tinggi <span class="font-bold text-black">{{ $percentileConfidence }}%</span> dari
                                                <span class="font-bold text-black">{{ $totalStudents }} siswa.</span>
                                            </p>

                                            <div class="absolute right-0 bottom-0">
                                                <img src="{{ asset('assets/images/assessment-asset/bullseye.svg') }}" alt="" class="w-14">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <div class="bg-white rounded-2xl h-36 px-6 py-4 shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)] flex flex-col relative">

                                <div>
                                    <p class="text-sm text-gray-500 text-center font-bold leading-tight">
                                        Performa Tercepat
                                    </p>
                                    <hr class="my-2 border-gray-400">
                                </div>

                                <p class="text-2xl font-bold text-center flex-1 flex items-center justify-center">
                                    {{ $fastest }}
                                </p>

                                <div class="dropdown dropdown-end absolute bottom-3 right-4">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-circle-info text-gray-500 text-lg cursor-pointer"></i>
                                    </div>

                                    <div tabindex="0" class="dropdown-content z-9999 w-73 xl:w-100 h-37.5 bg-white rounded-xl shadow-xl p-4">

                                        <div class="relative flex items-center justify-center mb-2">
                                            <i class="fa-solid fa-circle-info text-gray-500 absolute left-0"></i>
                                            <span class="font-semibold text-gray-700 text-center w-full">
                                                Performa Tercepat
                                            </span>
                                        </div>

                                        <hr class="border-gray-300 mb-3">

                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-gray-600 leading-relaxed">
                                                Kecepatan menjawab soalmu lebih cepat <span class="font-bold text-black">{{ $percentileFastest }}%</span> dari
                                                <span class="font-bold text-black">{{ $totalStudents }} siswa</span>
                                                dalam pengerjaan satu soal.
                                            </p>

                                            <div class="absolute right-0 bottom-0">
                                                <img src="{{ asset('assets/images/assessment-asset/up-time.svg') }}" alt="" class="w-14">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <div class="bg-white rounded-2xl h-36 px-6 py-4 shadow-[0_6px_14px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)] flex flex-col relative">

                                <div>
                                    <p class="text-sm text-gray-500 text-center font-bold leading-tight">
                                        Performa Terlambat
                                    </p>
                                    <hr class="my-2 border-gray-400">
                                </div>

                                <p class="text-2xl font-bold text-center flex-1 flex items-center justify-center">
                                    {{ $slowest }}
                                </p>

                                <div class="dropdown dropdown-end absolute bottom-3 right-4">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-circle-info text-gray-500 text-lg cursor-pointer"></i>
                                    </div>

                                    <div tabindex="0" class="dropdown-content z-9999 w-73 xl:w-100 h-37.5 bg-white rounded-xl shadow-xl p-4">

                                        <div class="relative flex items-center justify-center mb-2">
                                            <i class="fa-solid fa-circle-info text-gray-500 absolute left-0"></i>
                                            <span class="font-semibold text-gray-700 text-center w-full">
                                                Performa Terlambat
                                            </span>
                                        </div>

                                        <hr class="border-gray-300 mb-3">

                                        <div class="flex items-center justify-between">
                                            <p class="text-sm text-gray-600 leading-relaxed">
                                                Waktu terlama menjawab soalmu lebih lambat <span class="font-bold text-black">{{ $percentileSlowest }}%</span> dari
                                                <span class="font-bold text-black">{{ $totalStudents }} siswa</span>
                                                dalam pengerjaan satu soal.
                                            </p>

                                            <div class="absolute right-0 bottom-0">
                                                <img src="{{ asset('assets/images/assessment-asset/up-time.svg') }}" alt="" class="w-14">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= MAIN STATS ================= --}}
            @if($schoolAssessment->show_score)

                {{-- ================= NORMAL RESULT UI ================= --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mt-8">

                    {{-- LEFT COLUMN --}}
                    <div class="space-y-8 lg:col-span-2 xl:col-span-1">

                        <div class="text-sm font-bold bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-xl p-5 flex gap-4 items-center justify-between
                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">
                            <span>Jumlah Soal Yang Harus Dijawab</span>
                            <span class="font-bold text-lg">{{ $totalQuestions }}</span>
                        </div>

                        <div class="text-sm font-bold bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-xl p-5 flex gap-4 items-center justify-between
                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">
                            
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                    <i class="fa-solid fa-minus text-white text-xs"></i>
                                </div>
                                <span>Jumlah Soal Terjawab</span>
                            </div>

                            <span class="font-bold text-lg">
                                {{ $totalCorrect + $totalWrong }}
                            </span>
                        </div>

                        <div class="text-sm font-bold bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-xl p-5 flex gap-4 items-center justify-between
                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">
                            <div class="flex items-center gap-3">
                                <div class="w-6 h-6 rounded-full bg-gray-500 flex items-center justify-center">
                                    <i class="fa-solid fa-minus text-white text-xs"></i>
                                </div>
                                <span>Jumlah Soal Tidak Terjawab</span>
                            </div>

                            <span class="font-bold text-lg">
                                {{ $totalUnanswered }}
                            </span>
                        </div>

                    </div>

                    {{-- MIDDLE COLUMN --}}
                    <div class="space-y-8">

                        <div class="text-center flex flex-col items-center justify-center bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-xl px-6 h-32
                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">

                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                                    <i class="fa-solid fa-check text-white text-xs"></i>
                                </div>
                                <p class="text-2xl font-bold">{{ $totalCorrect }}</p>
                            </div>

                            <p class="text-sm mt-2 font-bold">Jumlah Soal Benar</p>
                        </div>

                        <div class="text-center flex flex-col items-center justify-center bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-xl px-6 h-32
                            shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)]">

                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-red-500 flex items-center justify-center">
                                    <i class="fa-solid fa-xmark text-white text-xs"></i>
                                </div>
                                <p class="text-2xl font-bold">{{ $totalWrong }}</p>
                            </div>

                            <p class="text-sm mt-2 font-bold">Jumlah Soal Salah</p>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN (SCORE CARD) --}}
                    <div class="bg-[linear-gradient(to_bottom,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-8 flex flex-col justify-center items-center
                        shadow-[0_6px_14px_rgba(0,0,0,0.35),3px_3px_0px_rgba(0,0,0,0.8)] relative">

                        {{-- STATUS BADGE --}}
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

                        <div class="text-6xl font-extrabold mt-6">
                            {{ $finalScore }}
                        </div>

                        <p class="text-md font-bold mt-2 text-center">
                            Nilai Ujian Siswa
                        </p>

                        {{-- INFO TEXT --}}
                        @if(!$isFullyGraded)
                            <p class="text-xs text-white/80 mt-4 text-center max-w-xs">
                                Nilai ini bersifat sementara karena masih terdapat beberapa jawaban
                                yang sedang dalam proses penilaian oleh guru.
                            </p>
                        @endif

                    </div>

                </div>

            @else

                {{-- ================= SCORE DISABLED UI ================= --}}
                <div class="mt-12">

                    <div class="bg-[linear-gradient(to_right,#0071BC_45%,#003456_100%)] text-white rounded-2xl p-16 
                        flex flex-col items-center justify-center text-center
                        shadow-[0_8px_20px_rgba(0,0,0,0.35),4px_4px_0px_rgba(0,0,0,0.8)]">

                        <div class="w-24 h-24 rounded-full bg-white/20 flex items-center justify-center mb-6">
                            <i class="fa-solid fa-eye-slash text-4xl"></i>
                        </div>

                        <h2 class="text-3xl font-bold mb-4">
                            Nilai Belum Ditampilkan
                        </h2>

                        @if(!$isFullyGraded)
                            <p class="text-white/80 max-w-xl">
                                Hasil penilaian masih dalam proses verifikasi oleh guru. Nilai akhir akan ditampilkan setelah seluruh jawaban selesai diperiksa.
                            </p>
                        @else
                            <p class="text-white/80 max-w-xl">
                                Guru kamu sedang menonaktifkan tampilan nilai untuk asesmen ini.
                                Silakan tunggu hingga nilai resmi diumumkan.
                            </p>
                        @endif

                        <div class="mt-8 px-6 py-3 bg-white/10 rounded-lg text-sm">
                            <i class="fa-solid fa-clock mr-2"></i>
                            Silahkan cek kembali secara berkala.
                        </div>

                    </div>

                </div>

            @endif

            {{-- ================= ACTION BUTTON ================= --}}
            <div class="flex flex-col lg:flex-row gap-4 justify-center mt-10">

                <a href="{{ route('lms.studentPreviewAssessment.view', [ $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId]) }}"
                    class="bg-[#0071BC] text-white px-6 py-3 rounded-xl shadow-lg hover:scale-105 transition font-semibold flex items-center justify-center gap-2">
                    <i class="fa-solid fa-house"></i>
                    Kembali ke halaman asesmen
                </a>

                <a href="{{ route('lms.studentAssessmentExan.view', [ $role, $schoolName, $schoolId, $curriculumId, $mapelId, $assessmentTypeId, $semester, $assessmentId]) }}">
                    <button
                            class="w-full border border-gray-300 text-gray-600 px-6 py-3 rounded-xl hover:bg-gray-100 transition font-semibold cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Lihat Review Jawaban
                    </button>
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