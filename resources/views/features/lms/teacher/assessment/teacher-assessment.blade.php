@include('components/sidebar-beranda', ['headerSideNav' => 'Assessment Management'])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <!-- alert success create assessment -->
            <div id="alert-success-create-assessment"></div>
            <div id="alert-success-edit-assessment"></div>

            <main class="bg-white rounded-2xl shadow-lg p-8 border border-gray-300">
                <section id="container" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="border-b border-gray-200 pb-10">
                    <div>
                        <div>
                            <!-- HEADER -->
                            <div class="mb-10">
                                <h1 class="text-2xl font-bold text-gray-800">Create Assessment</h1>
                                <p class="text-gray-500 mt-1 text-sm">
                                    Buat asesmen dan atur jadwal serta pengaturannya
                                </p>
                            </div>

                            <!-- STEP INDICATOR -->
                            <div class="flex items-center justify-between mb-12 relative">

                                <div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200"></div>
                                <div id="progress-line"
                                    class="absolute top-5 left-0 h-0.5 bg-[#0071BC] transition-all duration-500"
                                    style="width: 0%">
                                </div>

                                <div class="relative z-10 flex justify-between w-full">

                                    <div class="text-center w-1/3">
                                        <div id="circle-1"
                                            class="mx-auto w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-bold transition-all">
                                            1
                                        </div>
                                        <p class="mt-2 text-sm font-bold opacity-70">Academic Info</p>
                                    </div>

                                    <div class="text-center w-1/3">
                                        <div id="circle-2"
                                            class="mx-auto w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-bold transition-all">
                                            2
                                        </div>
                                        <p class="mt-2 text-sm font-bold opacity-70">Settings</p>
                                    </div>

                                    <div class="text-center w-1/3">
                                        <div id="circle-3"
                                            class="mx-auto w-10 h-10 rounded-full border-2 flex items-center justify-center text-sm font-bold transition-all">
                                            3
                                        </div>
                                        <p class="mt-2 text-sm font-bold opacity-70">Review</p>
                                    </div>

                                </div>
                            </div>

                            <!-- FORM -->
                            <form id="create-assessment-form">

                                <!-- STEP 1 Academic Info -->
                                <div class="step" id="step-1">
                                    
                                    <div class="space-y-8">

                                        <!-- Academic Information -->
                                        <div class="border border-gray-300 rounded-xl p-6">
                                            <h2 class="text-lg text-[#0071BC] font-bold mb-6">
                                                <i class="fas fa-graduation-cap mr-2"></i>
                                                Academic Information
                                            </h2>

                                            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6">

                                                <!-- Tahun Ajaran -->
                                                <div id="container-dropdown-tahun-ajaran">
                                                    <!-- show data in ajax -->
                                                </div>

                                                <!-- Kelas -->
                                                <div id="container-dropdown-class">
                                                    <!-- show data in ajax -->
                                                </div>

                                                <!-- Mapel -->
                                                <div id="container-dropdown-subject-rombel-class">
                                                    <!-- show data in ajax -->
                                                </div>

                                                <!-- Semester -->
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-600 mb-1">
                                                        Semester
                                                        <sup class="text-red-500">&#42;</sup>
                                                    </label>
                                                    <select id="semester" name="semester" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm cursor-pointer outline-none">
                                                            <option value="" hidden>Pilih Semester</option>
                                                            <option value="1">Semester 1</option>
                                                            <option value="2">Semester 2</option>
                                                    </select>
                                                    <span id="error-semester" class="text-red-500 text-xs mt-1 font-bold"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Target Rombel -->
                                        <div class="border border-gray-300 rounded-xl p-6 bg-white">
                                            <div class="flex justify-between items-center mb-6">
                                                <h2 class="text-lg text-[#0071BC] font-bold">
                                                    <i class="fas fa-users mr-2"></i>
                                                    Target Rombel
                                                </h2>

                                                <button type="button" class="text-sm text-blue-600 hover:underline cursor-pointer" id="toggle-select-rombel">
                                                    Select All
                                                </button>
                                            </div>

                                            <!-- Selected Count -->
                                            <div class="text-sm text-gray-500 mb-2">
                                                <span id="total-rombel-selected"
                                                    class="text-blue-600 font-semibold">
                                                    0 Dipilih
                                                </span>
                                            </div>

                                            <span id="error-school_class_id"
                                                class="text-red-500 text-xs font-semibold hidden">
                                            </span>

                                            <!-- Rombel Checkbox Grid -->
                                            <div id="grid-rombel-class-list" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4 mt-2">
                                                <!-- show data in ajax -->
                                            </div>

                                            <div id="empty-message-rombel-class-assessment-management-list" class="w-full h-80 hidden">
                                                <span class="flex h-full items-center justify-center text-gray-500">
                                                    Tidak ada rombel kelas yang terdaftar.
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Assessment Details -->
                                        <div class="border border-gray-300 rounded-xl p-6 bg-white">
                                            <h2 class="text-lg text-[#0071BC] font-bold mb-6">
                                                <i class="fas fa-pen-to-square"></i>  
                                                Assessment Details
                                            </h2>
        
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
                                                <div>
                                                    <label class="block text-sm font-medium">
                                                        Assessment Title
                                                        <sup class="text-red-500">&#42;</sup>
                                                    </label>
                                                    <input type="text" name="title" class="mt-2 w-full border border-gray-300 rounded-lg px-4 h-12 outline-none text-sm" 
                                                        placeholder="Masukkan Judul Asesmen">
                                                    <span id="error-title" class="text-red-500 text-xs mt-1 font-bold"></span>
                                                </div>
        
                                                <div>
                                                    <label class="block text-sm font-medium">
                                                        Assessment Type
                                                        <sup class="text-red-500">&#42;</sup>
                                                    </label>
                                                    <select name="assessment_type_id"
                                                        class="mt-2 w-full border border-gray-300 rounded-lg px-4 h-12 outline-none text-sm cursor-pointer">
                                                        <option value="" class="hidden">Pilih Tipe Asesmen</option>
                                                        @foreach ($schoolAssessmentType as $item)
                                                            <option value="{{ $item->id }}" data-mode="{{ $item->assessmentMode->code }}">{{ $item->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <span id="error-assessment_type_id" class="text-red-500 text-xs mt-1 font-bold"></span>
                                                </div>
        
                                            </div>
        
                                            <!-- Instruction -->
                                            <div class="mt-4">
                                                <label class="block text-sm mb-2">
                                                    Instructions
                                                    <sup class="text-red-500">&#42;</sup>
                                                </label>

                                                <textarea name="assessment_instruction" rows="5"
                                                    class="w-full border border-gray-300 rounded-lg p-4 text-sm resize-none outline-none"
                                                    placeholder="Tuliskan instruksi..."></textarea>
                                                <span id="error-assessment_instruction" class="text-red-500 text-xs font-semibold"></span>
                                            </div>
                                        </div>

                                        <!-- Duration -->
                                        <div id="duration-section" class="border border-gray-300 rounded-xl p-6 hidden">
                                            <h2 class="text-lg text-[#0071BC] font-bold mb-6">
                                                <i class="fas fa-clock"></i> 
                                                Duration
                                            </h2>
        
                                            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
        
                                                <div>
                                                    <label class="block text-sm font-medium">
                                                        Duration (minutes)
                                                        <sup class="text-red-500">&#42;</sup>
                                                    </label>
                                                    <input type="number" name="duration" placeholder="Masukkan Durasi Asesmen" 
                                                        class="mt-2 w-full border border-gray-300 rounded-lg px-4 py-2 outline-none text-sm">
                                                    <span id="error-duration" class="text-red-500 text-xs mt-1 font-bold"></span>
                                                </div>
        
                                            </div>
                                        </div>

                                        <!-- Schedule -->
                                        <div class="border border-gray-300 rounded-xl p-6 bg-white">
                                            <h2 class="text-lg text-[#0071BC] font-bold mb-6">
                                                <i class="fas fa-calendar-days"></i> 
                                                Schedule
                                            </h2>
        
                                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
                                                <div class="w-full">
                                                    <label class="block text-sm font-medium mb-2">
                                                        Start Date
                                                        <sup class="text-red-500">&#42;</sup>
                                                    </label>

                                                    <div class="relative">
                                                        <input 
                                                            type="text" id="start-date" name="start_date" 
                                                                class="w-full bg-white border border-gray-300 rounded-lg px-3 py-4 text-sm shadow-sm outline-none
                                                                disabled:bg-gray-100 disabled:text-gray-400 transition duration-200" placeholder="Pilih Tanggal">
                                                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                                                            <i class="fa-regular fa-calendar-days text-sm"></i>
                                                        </span>
                                                    </div>
                                                    <span id="error-start_date" class="text-red-500 text-xs font-semibold"></span>
                                                </div>
                            
                                                <div class="w-full">
                                                    <label class="block text-sm font-medium mb-2">
                                                        End Date
                                                        <sup class="text-red-500">&#42;</sup>
                                                    </label>
                                                    <div class="relative">
                                                        <input 
                                                            type="text" id="end-date" name="end_date"
                                                                class=" w-full bg-white border border-gray-300 rounded-lg px-3 py-4 text-sm shadow-sm outline-none
                                                                disabled:bg-gray-100 disabled:text-gray-400 transition duration-200" placeholder="Pilih Tanggal">
                                                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-400 pointer-events-none">
                                                            <i class="fa-regular fa-calendar-days text-sm"></i>
                                                        </span>
                                                    </div>
                                                    <span id="error-end_date" class="text-red-500 text-xs font-semibold"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 2 Settings -->
                                <div class="step hidden" id="step-2">

                                    <!-- QUESTION MODE SETTINGS -->
                                    <div id="question-settings-section"
                                        class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6 hidden">

                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                                Question Settings
                                            </h3>

                                            <div class="space-y-4">
                                                <label class="flex items-start gap-3 cursor-pointer">
                                                    <input type="checkbox" name="shuffle_questions" class="mt-1">
                                                    <div>
                                                        <p class="text-sm font-medium">Shuffle Questions</p>
                                                        <p class="text-xs text-gray-500">
                                                            Soal akan ditampilkan secara acak untuk setiap siswa.
                                                        </p>
                                                    </div>
                                                </label>

                                                <label class="flex items-start gap-3 cursor-pointer">
                                                    <input type="checkbox" name="shuffle_options" class="mt-1">
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
                                                    <input type="checkbox" name="show_score" class="mt-1">
                                                    <div>
                                                        <p class="text-sm font-medium">Show Score After Submit</p>
                                                    </div>
                                                </label>

                                                <label class="flex items-start gap-3 cursor-pointer">
                                                    <input type="checkbox" name="show_answer" class="mt-1">
                                                    <div>
                                                        <p class="text-sm font-medium">Show Correct Answer</p>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PROJECT MODE SETTINGS -->
                                    <div id="project-settings-section"
                                        class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">

                                        <div>
                                            <h2 class="text-lg font-bold text-[#0071BC] flex items-center gap-2">
                                                <i class="fas fa-folder-open"></i>
                                                File Configuration
                                            </h2>
                                        </div>

                                        <!-- Upload Area -->
                                        <div id="container-assessment-file">
                                            <label for="assessment_value_file"
                                                class="flex flex-col items-center justify-center w-full p-8 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer 
                                                hover:border-[#0071BC] hover:bg-blue-50 transition text-center">
    
                                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-3"></i>
    
                                                <p class="text-sm text-gray-600 font-medium">
                                                    Click to upload file
                                                </p>
    
                                                <p class="text-xs text-gray-400 mt-2">
                                                    Supported: PDF / MP4 (Max 100MB)
                                                </p>
                                            </label>
    
                                            <input type="file" name="assessment_value_file" id="assessment_value_file" class="hidden" accept="application/pdf, video/mp4">
                                            <span id="error-assessment_value_file" class="text-red-500 text-xs font-semibold"></span>
                                        </div>

                                        <!-- File Preview -->
                                        <div id="file-preview" class="hidden">
                                            <div class="flex items-center justify-between p-4 bg-gray-50 border border-gray-200 rounded-lg">
                                                <div class="flex items-center gap-3">
                                                    <i class="fas fa-file-alt text-[#0071BC] text-lg"></i>
    
                                                    <div>
                                                        <p id="file-name" class="text-sm font-medium text-gray-700"></p>
                                                        <p id="file-size" class="text-xs text-gray-400"></p>
                                                    </div>
                                                </div>
    
                                                <button type="button" id="remove-file" class="text-red-500 hover:text-red-700 text-sm font-medium cursor-pointer">
                                                    Remove
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Result Settings -->
                                        <div class="border-t border-gray-200 pt-6">

                                            <h3 class="text-sm font-semibold text-gray-700 mb-3">
                                                Result Settings
                                            </h3>

                                            <label class="flex items-start gap-3 cursor-pointer">
                                                <input type="checkbox" name="show_project_score" class="mt-1">

                                                <div>
                                                    <p class="text-sm font-medium">
                                                        Show Score After Submit
                                                    </p>

                                                    <p class="text-xs text-gray-500">
                                                        Nilai akan langsung ditampilkan setelah siswa mengirim jawaban.
                                                    </p>
                                                </div>

                                            </label>

                                        </div>
                                    </div>
                                </div>

                                <!-- STEP 3: Review Assessment -->
                                <div class="step hidden" id="step-3">

                                    <div class="space-y-6 text-sm">

                                        <!-- Academic Info -->
                                        <div class="p-4 bg-gray-100 rounded-xl">
                                            <h3 class="font-semibold mb-2">Academic Info</h3>
                                            <p><strong>Tahun Ajaran:</strong> <span id="review-tahun-ajaran"></span></p>
                                            <p><strong>Kelas:</strong> <span id="review-class"></span></p>
                                            <p><strong>Mapel:</strong> <span id="review-subject"></span></p>
                                            <p><strong>Semester:</strong> <span id="review-semester"></span></p>
                                        </div>

                                        <!-- Schedule -->
                                        <div class="p-4 bg-gray-100 rounded-xl">
                                            <h3 class="font-semibold mb-2">Schedule</h3>
                                            <p><strong>Start:</strong> <span id="review-start"></span></p>
                                            <p><strong>End:</strong> <span id="review-end"></span></p>
                                        </div>

                                        <!-- Assessment Details -->
                                        <div class="p-4 bg-gray-100 rounded-xl">
                                            <h3 class="font-semibold mb-2">Assessment Details</h3>
                                            <p><strong>Judul:</strong> <span id="review-title"></span></p>
                                            <p><strong>Tipe Asesmen:</strong> <span id="review-type"></span></p>
                                            <p><strong>Instruction:</strong> <span id="review-instruction"></span></p>
                                            <p id="review-duration-wrapper">
                                                <strong>Durasi:</strong> <span id="review-duration"></span> Menit
                                            </p>
                                        </div>

                                        <!-- Question Settings -->
                                        <div id="question-review-extra" class="p-4 bg-gray-100 rounded-xl">
                                            <h3 class="font-semibold mb-2">Question Settings</h3>
                                            <p><strong>Shuffle Questions:</strong> <span id="review-shuffle-questions"></span></p>
                                            <p><strong>Shuffle Options:</strong> <span id="review-shuffle-options"></span></p>
                                            <p><strong>Show Score After Submit:</strong> <span id="review-show-score"></span></p>
                                            <p><strong>Show Correct Answer:</strong> <span id="review-show-answer"></span></p>
                                        </div>

                                        <!-- Project Settings -->
                                        <div id="project-review-extra" class="p-4 bg-gray-100 rounded-xl hidden">
                                            <h3 class="font-semibold mb-2">Project Settings</h3>
                                            <p><strong>Filename:</strong> <span id="review-filename"></span></p>
                                            <p><strong>Show Score After Submit:</strong> <span id="review-project-show-score"></span></p>
                                        </div>

                                    </div>
                                </div>

                                <!-- BUTTONS -->
                                <div class="flex flex-col md:flex-row justify-between mt-10 gap-4">

                                    <!-- BACK -->
                                    <button type="button" id="backBtn"
                                        class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 bg-gray-200 transitionw-auto hidden
                                        cursor-pointer disabled:cursor-default">
                                        Back
                                    </button>

                                    <!-- NEXT -->
                                    <div class="flex ml-auto">
                                        <button type="button" id="next-btn" class="px-6 py-2 rounded-lg border border-gray-300 bg-blue-50 text-blue-600 hover:bg-blue-100 transition
                                            whitespace-nowrap hidden cursor-pointer disabled:cursor-default">
                                            Next
                                        </button>
                                    </div>

                                    <!-- RIGHT GROUP -->
                                    <div class="flex gap-3">

                                        <!-- SAVE DRAFT -->
                                        <button type="button" id="submit-button-draft-create-assessment" data-status="draft" class="w-full md:w-auto px-6 py-2 rounded-lg 
                                            border border-gray-300 bg-blue-100 text-blue-700 transition whitespace-nowrap hidden cursor-pointer disabled:cursor-default">
                                            Save Draft
                                        </button>

                                        <!-- PUBLISH -->
                                        <div class="flex-0 w-full">
                                            <button type="button" id="submit-button-publish-create-assessment" data-status="published" class="w-full md:w-auto px-6 py-2 rounded-lg
                                                border border-gray-300 bg-[#0071BC] text-white transition whitespace-nowrap hidden cursor-pointer disabled:cursor-default">
                                                Publish
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <section id="container-teacher-assessment-management-list" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="mt-10">
                    <h2 class="text-xl font-semibold text-gray-800">
                        Assessment Management List
                    </h2>

                    <!-- FILTER CONTAINER -->
                    <div class="my-6 bg-white shadow-sm border border-gray-300 rounded-2xl p-6">

                        <!-- Header Filter -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                <h3 class="text-base font-semibold text-gray-800">
                                    Filter Assessment
                                </h3>
                            </div>
                        </div>

                        <!-- Filter Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">

                            <div id="container-dropdown-assessment-management-tahun-ajaran"></div>

                            <div id="container-dropdown-assessment-management-class"></div>

                            <div id="container-dropdown-assessment-type"></div>

                        </div>
                    </div>

                    <div class="overflow-x-auto mt-6 pb-4">
                        <table id="table-teacher-assessment-management-list" class="min-w-175 lg:min-w-full text-sm border-collapse">
                            <thead class="thead-table-teacher-assessment-management-list bg-gray-50 hidden shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">No</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Rombel Kelas</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tahun Ajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Mata Pelajaran</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tipe Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Judul Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Semester</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Tanggal Asesmen</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Action</th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbody-teacher-assessment-management-list">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-teacher-assessment-management-list flex justify-center my-10"></div>

                    <div id="empty-message-teacher-assessment-management-list" class="w-full h-96 hidden">
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

<script src="{{ asset('assets/js/Features/lms/teacher/assessment/form-teacher-assessment-management.js') }}"></script> <!--- form teacher assessment management ---->
<script src="{{ asset('assets/js/Features/lms/teacher/assessment/teacher-assessment-management-step-form.js') }}"></script> <!--- teacher assessment management step form ---->
<script src="{{ asset('assets/js/Features/lms/teacher/assessment/paginate-teacher-assessment-management.js') }}"></script> <!--- paginate teacher assessment management ---->
<script src="{{ asset('assets/js/Features/lms/teacher/assessment/assessment-file-upload-preview.js') }}"></script> <!--- assessment file upload preview ---->

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->