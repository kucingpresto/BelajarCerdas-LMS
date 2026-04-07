// ===== FILE PREVIEW HELPER =====
function renderFilePreview(fileUrl, fileName) {

    if (!fileUrl || !fileName) {
        return `
            <div class="text-sm text-gray-400 italic">
                Tidak ada file
            </div>
        `;
    }

    return `
        <div class="flex flex-col gap-2">

            <div class="border rounded-lg overflow-hidden bg-gray-50">
                <iframe 
                    src="${fileUrl}" 
                    class="w-full h-72"
                ></iframe>
            </div>

            <div class="flex items-center justify-between bg-gray-100 border rounded-lg px-3 py-2 text-sm">

                <div class="flex items-center gap-2 text-gray-700">
                    <i class="fa-solid fa-file"></i>
                    <span class="truncate max-w-50">
                        ${fileName}
                    </span>
                </div>

            </div>

        </div>
    `;
}

function assesmentGradingStudentProject() {

    const container = document.getElementById('container-assessment-grading-student-project');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const studentId = container.dataset.studentId;

    if (!role || !schoolName || !schoolId || !assessmentId || !studentId) return;

    fetchData();


    function fetchData() {

        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/student-list/${studentId}/scoring/project`,
            method: 'GET',

            success: function (response) {

                $('#tbody-assessment-grading-student-answer').empty();
                $('.pagination-container-assessment-grading-student-project').empty();
                $('#header-assessment-info').empty();

                const formAssessmentGrading = $('#form-assessment-grading');
                formAssessmentGrading.empty();

                const assessment = response.assessment;
                const student = response.student;
                const submission = response.submission ?? null;

                // FILE PATH
                const teacherFile = assessment.assessment_value_file ? `/assessment/assessment-file/${assessment.assessment_value_file}` : null;

                const studentFile = submission?.file_path ? `/assessment/assessment-file-submission/${submission.file_path}` : null;

                const teacherFilePreview = renderFilePreview(teacherFile, assessment.assessment_original_filename);

                const studentFilePreview = renderFilePreview(studentFile, submission?.original_filename);

                // HEADER INFO
                const assessmentInfo = $('#header-assessment-info');

                assessmentInfo.html(`
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                        <div class="space-y-3 min-w-0 flex-1">

                            <h1 class="text-md md:text-lg font-semibold leading-snug tracking-wide wrap-break-word">
                                ${assessment.title ?? '-'}
                            </h1>

                            <p class="text-sm flex flex-col gap-4">
                                <span class="font-semibold text-white">
                                    Siswa: ${student.student_profile?.nama_lengkap ?? '-'}
                                </span>

                                <span class="font-semibold text-white">
                                    Mata Pelajaran: ${assessment.mapel?.mata_pelajaran ?? '-'}
                                </span>

                                <span class="font-semibold text-white">
                                    Kelas: ${student.student_school_class?.[0]?.school_class?.class_name ?? '-'}
                                </span>
                            </p>
                        </div>
                    </div>
                `);

                assessmentInfo.show();

                if (response.assessment) {
    
                    // MAIN FORM
                    const studentHasSubmitted = submission !== null;
    
                    const form = `
                        <form id="teacher-assessment-grading-student-project-form" autocomplete="OFF">
    
                            <input type="hidden" id="submission_id" name="submission_id" value="${submission?.id ?? ''}">
    
                            <div class="grid grid-cols-12 gap-6">
    
                                <!-- TEACHER ATTACHMENT -->
                                <div class="col-span-12 xl:col-span-6">
    
                                    <div class="bg-white border border-gray-300 rounded-xl shadow-sm p-6">
    
                                        <h3 class="font-semibold text-gray-800 mb-4">
                                            Project Instruction
                                        </h3>
    
                                        ${assessment.assessment_instruction
                                            ? `
                                                <div class="text-sm text-gray-700 leading-relaxed bg-gray-50 p-2 border border-gray-300 rounded-lg">
                                                    ${assessment.assessment_instruction ?? '-'}
                                                </div>                
                                            `
                            
                                        : ''}
    
                                        <div class="mt-6">
    
                                            <span class="text-xs text-gray-500">
                                                Teacher Attachment
                                            </span>
    
                                            <div class="mt-2">
                                                ${teacherFilePreview}
                                            </div>
    
                                        </div>
    
                                    </div>
    
                                </div>
    
                                <!-- STUDENT SUBMISSION -->
                                <div class="col-span-12 xl:col-span-6">
    
                                    <div class="bg-white border border-gray-300 rounded-xl shadow-sm p-6">
    
                                        <h3 class="font-semibold text-gray-800 mb-4">
                                            Student Submission
                                        </h3>
    
                                        ${studentHasSubmitted
                                                ? `
                                                <div class="text-sm text-gray-700 leading-relaxed">
                                                    ${submission.text_answer ?? 'Tidak ada jawaban teks'}
                                                </div>
    
                                                <div class="mt-6">
    
                                                    <span class="text-xs text-gray-500">
                                                        Uploaded File
                                                    </span>
    
                                                    <div class="mt-2">
                                                        ${studentFilePreview}
                                                    </div>
    
                                                </div>
                                            `
                                                : `
                                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-700">
    
                                                    <div class="flex items-center gap-2">
                                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                                        <span>Siswa belum mengumpulkan project.</span>
                                                    </div>
    
                                                </div>
                                            `
                                            }
    
                                    </div>
    
                                </div>
    
                            </div>
    
                            <!-- TEACHER GRADING -->
                            <div class="bg-white border border-gray-300 rounded-xl shadow-sm p-6 mt-6">
    
                                <h3 class="font-semibold text-gray-800 mb-4">
                                    Penilaian Guru
                                </h3>
    
                                <div class="flex flex-col gap-4">
    
                                    <div>
    
                                        <label class="text-sm font-medium text-gray-700">
                                            Score
                                            <sup class="text-red-500 relative">&#42;</sup>
                                        </label>
    
                                        <div class="flex items-center gap-2 mt-1">
    
                                            <input type="number" min="0" max="100" id="score" name="score" value="${submission?.score ?? ''}" 
                                                class="w-24 border border-gray-300 rounded-lg p-2 text-sm outline-none" placeholder="0" ${!studentHasSubmitted ? 'disabled' : ''}>
    
                                            <span class="text-sm text-gray-500">
                                                / 100
                                            </span>
    
                                        </div>
    
                                        <span id="error-score" class="text-xs text-red-500 hidden"></span>
    
                                    </div>
    
                                    <div>
                                        <label class="text-sm font-medium text-gray-700">
                                            Feedback Guru (Optional)
                                        </label>
    
                                        <textarea rows="3" id="teacher_feedback" name="teacher_feedback" class="w-full border border-gray-300 resize-none rounded-lg p-3 text-sm outline-none"
                                            placeholder="Tambahkan komentar..." ${!studentHasSubmitted ? 'disabled' : ''}>${submission?.teacher_feedback ?? ''}</textarea>
                                    </div>
    
                                    <div class="flex justify-end">
                                        <button type="button" id="submit-button-assessment-grading-student-project" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm
                                            ${!studentHasSubmitted ? 'opacity-50 cursor-default' : 'cursor-pointer disabled:cursor-default'}" ${!studentHasSubmitted ? 'disabled' : ''}>
                                            Simpan Nilai
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    `;
    
                    const $card = $(form);
                    formAssessmentGrading.append($card);

                } else {
                    $('#form-assessment-grading').hide();
                    $('#empty-message-school-assessment-project').show();
                }
            }
        });
    }
}

$(document).ready(function () {
    assesmentGradingStudentProject();
});

$(document).on('input', '#score', function () {
    $('#error-score').text('').addClass('hidden');
    $(this).removeClass('border-red-400');
});

let isProcessing = false;

// Form Action assessment grading student answer
$(document).on('click', '#submit-button-assessment-grading-student-project', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-assessment-grading-student-project');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const studentId = container.dataset.studentId;

    const submissionId = $('#submission_id').val();

    if (!role || !schoolName || !schoolId || !assessmentId || !studentId || !submissionId) return;

    const form = $('#teacher-assessment-grading-student-project-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/student-list/${studentId}/scoring/submission/${submissionId}/project`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-assesment-grading').html(`
                    <div class=" w-full flex justify-center">
                        <div class="fixed z-9999">
                            <div id="alertSuccess"
                                class="relative -top-11.25 opacity-100 scale-90 bg-green-200 w-max p-3 flex items-center space-x-2 rounded-lg shadow-lg transition-all duration-300 ease-out">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current text-green-600" fill="none"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-green-600 text-sm">${response.message}</span>
                                <i class="fas fa-times cursor-pointer text-green-600" id="btnClose"></i>
                        </div>
                    </div>
                </div>
            `);

            assesmentGradingStudentProject();

            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function () {
                $('#alertSuccess').remove();
            });

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                console.log(xhr.responseJSON.errors);

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#teacher-assessment-grading-student-project-form').find(`#error-${field}`).text(messages[0]).removeClass('hidden');

                    // Tambahkan style error ke input (jika ada)
                    $('#teacher-assessment-grading-student-project-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});