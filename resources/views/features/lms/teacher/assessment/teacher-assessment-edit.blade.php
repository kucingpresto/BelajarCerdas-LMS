@include('components/sidebar-beranda', [
    'headerSideNav' => 'Edit Assessment',
    'linkBackButton' => route('lms.teacherAssessmentManagement.view', [$role, $schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-edit-assessment"></div>

            <div id="container-edit-assessment" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-assessment-id="{{ $assessment->id }}"
                    data-semester="{{ $assessment->semester }}" data-title="{{ $assessment->title }}" data-description="{{ $assessment->description }}" data-duration="{{ $assessment->duration }}"
                    data-start-date="{{ $assessment->start_date }}" data-end-date="{{ $assessment->end_date }}" data-instruction="{{ $assessment->assessment_instruction }}" 
                    data-shuffle-questions="{{ $assessment->shuffle_questions }}" data-shuffle-options="{{ $assessment->shuffle_options }}" 
                    data-show-score="{{ $assessment->show_score }}" data-show-answer="{{ $assessment->show_answer }}"
                    class="py-10 space-y-8">    

                <form id="edit-assessment-form" class="space-y-8">

                    <input type="hidden" id="edit-assessment-type-id" name="assessment_type_id" value="{{ $assessment->assessment_type_id }}">

                    <!-- BASIC INFORMATION -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-300 space-y-6">
                        <h2 class="font-semibold text-gray-700">Basic Information</h2>
            
                        <!-- Semester -->
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">
                                Semester
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <select id="edit-semester" name="semester" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm cursor-pointer outline-none">
                                <option value="" hidden>Pilih Semester</option>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                            <span id="error-semester" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>
            
                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium">
                                Assessment Title
                                <sup class="text-red-500">&#42;</sup>
                            </label>
                            <input type="text" id="edit-assessment-title" name="title" value="" placeholder="Masukkan Judul Asesmen"
                                class="mt-2 w-full border border-gray-300 rounded-lg px-4 h-12 outline-none text-sm">
                            <span id="error-title" class="text-red-500 text-xs font-semibold"></span>
                        </div>
            
                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium">
                                Description (Optional)
                            </label>
                            <textarea id="edit-description" name="description" rows="3" placeholder="Masukkan Deskripsi Asesmen" 
                                class="mt-2 w-full border border-gray-300 rounded-lg p-4 outline-none text-sm resize-none"></textarea>
                            <span id="error-description" class="text-red-500 text-xs mt-1 font-bold"></span>
                        </div>
                    </div>
            
                    <!-- SCHEDULE -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-300 space-y-6">
                        <h2 class="font-semibold text-gray-700">Schedule</h2>
            
                        @if ($assessment->SchoolAssessmentType->AssessmentMode->code !== 'project')
                            <div>
                                <label class="text-sm font-medium">
                                    Duration (minutes)
                                    <sup class="text-red-500">&#42;</sup>
                                </label>
                                <input type="number" id="edit-duration" name="duration" value="" class="mt-2 w-full border border-gray-300 rounded-lg px-4 h-12 outline-none text-sm">
                                <span id="error-duration" class="text-red-500 text-xs mt-1 font-bold"></span>
                            </div>
                        @endif
            
                        <div class="grid lg:grid-cols-2 gap-4">
                            <div class="">
                                <div class="relative gap-2 mt-4">
                                    <label class="block text-sm font-medium mb-2">
                                        Start Date
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>

                                    <input type="text" id="edit-start-date" name="start_date" class="w-full bg-white border border-gray-300 rounded-lg 
                                        px-3 h-12 text-sm shadow-sm outline-none
                                        disabled:bg-gray-100 disabled:text-gray-400 transition duration-200" placeholder="Pilih Tanggal">
                                    <span class="absolute top-[60%] right-4 flex items-center text-gray-400 pointer-events-none">
                                        <i class="fa-regular fa-calendar-days text-sm"></i>
                                    </span>
                                </div>
                                <span id="error-start_date" class="text-red-500 text-xs font-semibold"></span>
                            </div>

                            <div class="">
                                <div class="relative gap-2 mt-4">
                                    <label class="block text-sm font-medium mb-2">
                                        End Date
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
    
                                    <input type="text" id="edit-end-date" name="end_date" class="w-full bg-white border border-gray-300 rounded-lg 
                                        px-3 h-12 text-sm shadow-sm outline-none
                                        disabled:bg-gray-100 disabled:text-gray-400 transition duration-200" placeholder="Pilih Tanggal">
                                    <span class="absolute top-[60%] right-4 flex items-center text-gray-400 pointer-events-none">
                                        <i class="fa-regular fa-calendar-days text-sm"></i>
                                    </span>
                                </div>
                                <span id="error-end_date" class="text-red-500 text-xs font-semibold"></span>
                            </div>
                        </div>
                    </div>
            
                    <!-- MODE SPECIFIC -->
                    @if($assessment->SchoolAssessmentType->AssessmentMode->code === 'project')
            
                        <!-- PROJECT FILE SECTION -->
                        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-300 space-y-6">
                            <h2 class="font-semibold text-gray-700">File</h2>

                            <div class="flex flex-col gap-6">
                                <!-- File upload / preview -->
                                <div id="dynamic-form" class="w-full xl:w-2/4"></div>

                                <!-- Instruction textarea -->
                                <div class="w-full xl:w-2/4">
                                    <label class="block text-sm font-semibold mb-2">
                                        Instructions
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>

                                    <textarea id="edit-assessment-instruction" name="assessment_instruction" rows="5"
                                        class="w-full border border-gray-300 rounded-lg p-4 text-sm resize-none outline-none"
                                        placeholder="Tuliskan instruksi..."></textarea>
                                    <span id="error-assessment_instruction" class="text-red-500 text-xs font-semibold"></span>
                                </div>

                                <!-- RESULT SETTINGS -->
                                <div>
                                    <div class="grid grid-cols1 lg:grid-cols-2 gap-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-300 space-y-6">
                                        <div>

                                            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                                Result Settings
                                            </h3>

                                            <div class="space-y-4">

                                                <label class="flex items-start gap-3 cursor-pointer">
                                                    <input type="checkbox" id="edit-show-score" name="show_score" class="mt-1">
                                                    <div>
                                                        <p class="text-sm font-medium">Show Score After Submit</p>
                                                        <p class="text-xs text-gray-500">
                                                            Nilai akan langsung ditampilkan setelah siswa mengirim jawaban.
                                                        </p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
            
                    @else
            
                        <!-- QUESTION & RESULT SETTINGS -->
                        <div>
                            <div class="grid grid-cols1 lg:grid-cols-2 gap-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-300 space-y-6">
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                        Question Settings
                                    </h3>

                                    <div class="space-y-4">

                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="checkbox" id="edit-shuffle-questions" name="shuffle_questions" class="mt-1">
                                                <div>
                                                <p class="text-sm font-medium">Shuffle Questions</p>
                                                <p class="text-xs text-gray-500">
                                                    Soal akan ditampilkan secara acak untuk setiap siswa.
                                                </p>
                                            </div>
                                        </label>

                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="checkbox" id="edit-shuffle-options" name="shuffle_options" class="mt-1">
                                            <div>
                                                <p class="text-sm font-medium">Shuffle Options</p>
                                                <p class="text-xs text-gray-500">
                                                    Urutan opsi jawaban akan diacak.
                                                </p>
                                            </div>
                                        </label>

                                        </div>
                                    </div>

                                    <div>

                                    <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                        Result Settings
                                    </h3>

                                    <div class="space-y-4">

                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="checkbox" id="edit-show-score" name="show_score" class="mt-1">
                                            <div>
                                                <p class="text-sm font-medium">Show Score After Submit</p>
                                                <p class="text-xs text-gray-500">
                                                    Nilai akan langsung ditampilkan setelah siswa mengirim jawaban.
                                                </p>
                                            </div>
                                        </label>

                                        <label class="flex items-start gap-3 cursor-pointer">
                                            <input type="checkbox" id="edit-show-answer" name="show_answer" class="mt-1">
                                            <div>
                                                <p class="text-sm font-medium">Show Correct Answer</p>
                                                <p class="text-xs text-gray-500">
                                                    Jawaban yang benar akan ditampilkan setelah pengerjaan selesai.
                                                </p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
            
                    <!-- ACTION -->
                    <div id="submit-button-edit-assessment" class="flex justify-end gap-4">            
                        <button type="submit" class="px-6 py-2 bg-[#0071BC] text-white rounded-lg cursor-pointer disabled:cursor-default">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/Features/lms/teacher/assessment/form-teacher-assessment-management-edit.js') }}"></script> <!--- form teacher assessment management edit ---->
<script src="{{ asset('assets/js/Features/lms/teacher/assessment/assessment-render-file-viewer.js') }}"></script> <!--- teacher assessment management edit ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->