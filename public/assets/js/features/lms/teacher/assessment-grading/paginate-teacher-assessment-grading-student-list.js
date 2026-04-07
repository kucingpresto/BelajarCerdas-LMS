function paginateAssessmentGradingStudentList(search_student = null) {
    const container = document.getElementById('container-assessment-grading-student-list');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const mode = container.dataset.mode;

    if (!role || !schoolName || !schoolId || !assessmentId || !mode) return;

    fetchData(role, schoolName, schoolId, assessmentId, mode);

    function fetchData() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/mode/${mode}/student-list/paginate`,
            method: 'GET',
            data: {
                search_student
            },

            success: function (response) {
                const searchValue = $('#search_student').val();
                $('#tbody-assessment-grading-student-list').empty();
                $('.pagination-container-assessment-grading-student-list').empty();

                $('#header-assessment-info').empty();

                const assessment = response.assessment;
                const assessmentInfo = $('#header-assessment-info');
                const actionButtons = $('#action-buttons');

                assessmentInfo.html(`
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6 mb-8">

                        <!-- TITLE -->
                        <div class="flex-1 min-w-0">

                            <h1 class="text-lg font-bold leading-snug wrap-break-word">
                                ${assessment.title ?? '-'}
                            </h1>

                            <p class="text-sm text-blue-100 mt-2 flex items-center gap-1">
                                ${assessment.mapel?.mata_pelajaran ?? '-'}

                                <i class="fa-solid fa-circle text-[4px]"></i>
                                
                                ${assessment.school_class?.class_name ?? '-'}
                            </p>

                        </div>

                        <!--- search bar --->
                        <label class="input input-bordered outline-none border-gray-300 text-gray-700 flex items-center gap-2 rounded-md w-40 sm:w-66 md:w-max">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 opacity-70" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1111 3a7.5 7.5 0 015.65 13.65z" />
                            </svg>
                            <input id="search_student" type="search" class="grow text-sm"
                                placeholder="Cari Siswa..." autocomplete="OFF" />
                        </label>
                    </div>

                    <!-- STATISTIC -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

                        <div class="bg-white rounded-xl p-4 shadow">
                            <p class="text-xs text-gray-500">Total Siswa</p>
                            <h2 class="text-xl font-bold text-blue-600">
                                ${response.statistics.total_students}
                            </h2>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow">
                            <p class="text-xs text-gray-500">Sudah Mengumpulkan</p>
                            <h2 class="text-xl font-bold text-blue-600">
                                ${response.statistics.submitted}
                            </h2>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow">
                            <p class="text-xs text-gray-500">Belum Mengumpulkan</p>
                            <h2 class="text-xl font-bold text-red-500">
                                ${response.statistics.not_submitted}
                            </h2>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow">
                            <p class="text-xs text-gray-500">Menunggu Penilaian</p>
                            <h2 class="text-xl font-bold text-orange-500">
                                ${response.statistics.pending_score}
                            </h2>
                        </div>

                        <div class="bg-white rounded-xl p-4 shadow">
                            <p class="text-xs text-gray-500">Sudah Dinilai</p>
                            <h2 class="text-xl font-bold text-green-600">
                                ${response.statistics.final_score}
                            </h2>
                        </div>

                    </div>
                `);

                $('#search_student').val(searchValue);
                assessmentInfo.show();

                const global = response.global_action;

                actionButtons.empty();
                const isProject = assessment.school_assessment_type?.assessment_mode?.code === 'project';

                if (mode === 'main') {
                    actionButtons.html(`
                        ${global.can_susulan ? `
                            <button id="btn-susulan" data-mode="susulan" class="bg-orange-500 text-white px-4 py-2 rounded text-sm cursor-pointer">
                                Susulan (${global.total_susulan_students})
                            </button>
                        ` : ``}
    
                        ${global.can_remedial ? `
                            <button id="btn-remedial" data-mode="remedial" class="bg-red-500 text-white px-4 py-2 rounded text-sm cursor-pointer">
                                Remedial (${global.total_remedial_students})
                            </button>
                        ` : ``}
    
                        ${global.can_pengayaan ? `
                            <button id="btn-pengayaan" data-mode="pengayaan" class="bg-green-600 text-white px-4 py-2 rounded text-sm cursor-pointer">
                                Pengayaan (${global.total_pengayaan_students})
                            </button>
                        ` : ``}
                    `);
                }
    
                if (response.data.length > 0) {
    
                    $.each(response.data, function (index, item) {
                        function buildUrl(attempt, studentId, item) {

                            let realAssessmentId = assessmentId;

                            if (attempt === 'remedial') {
                                realAssessmentId = item.remedial_assessment_id ?? assessmentId;
                            }

                            if (attempt === 'susulan') {
                                realAssessmentId = item.susulan_assessment_id ?? assessmentId;
                            }

                            if (attempt === 'pengayaan') {
                                realAssessmentId = item.pengayaan_assessment_id ?? assessmentId;
                            }

                            return response.assessmentGradingStudentAnswer.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                                .replace(':assessmentId', realAssessmentId).replace(':mode', attempt).replace(':studentId', studentId);
                        }
                        function renderRow(item, index, mode) {

                            function renderRemedial(item) {
                                if (!item.remedial_attempts || item.remedial_attempts.length === 0) {
                                    return '-';
                                }

                                function formatScore(score) {
                                    const num = parseFloat(score);
                                    const rounded = Math.round(num);

                                    return Math.abs(num - rounded) < 0.00001 ? rounded : num.toFixed(2);
                                }

                                let html = '';

                                item.remedial_attempts.forEach((attempt, i) => {
                                    let color = 'text-red-500';

                                    if (attempt.score >= item.kkm) {
                                        color = 'text-green-600';
                                    }

                                    // pakai assessment_id dari masing-masing attempt
                                    const url = response.assessmentGradingStudentAnswer.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                                        .replace(':assessmentId', attempt.assessment_id).replace(':mode', 'remedial').replace(':studentId', item.student_id);

                                    html += `
                                        <a href="${url}" class="${color} font-bold hover:underline">
                                            ${formatScore(attempt.score)}
                                        </a>
                                    `;

                                    if (i < item.remedial_attempts.length - 1) {
                                        html += ` <i class="fa-solid fa-arrow-right text-xs text-gray-500"></i> `;
                                    }
                                });

                                return html;
                            }

                            if (mode === 'main') {
                                return `
                                    <tr>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${index + 1}</td>

                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            ${item.user_account?.student_profile?.nama_lengkap ?? '-'}
                                        </td>

                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            ${item.submission_status ?? '-'}
                                        </td>

                                        <!-- MAIN -->
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            ${item.main_score !== null ? `
                                                <a href="${buildUrl('main', item.student_id, item)}" class="text-blue-600 font-bold hover:underline" title="Lihat detail assessment awal">
                                                    ${item.main_score}
                                                </a>
                                            ` : '-'}
                                        </td>

                                        ${!isProject ? `
                                            <!-- SUSULAN -->
                                            <td class="border border-gray-300 px-3 py-2 text-center">
                                                ${item.susulan_score !== null ? `
                                                    <a href="${buildUrl('susulan', item.student_id, item)}" class="text-orange-500 font-bold hover:underline" title="Lihat detail susulan">
                                                        ${item.susulan_score}
                                                    </a>
                                                ` : '-'}
                                            </td>

                                            <!-- REMEDIAL -->
                                            <td class="border border-gray-300 px-3 py-2 text-center">
                                                ${renderRemedial(item)}
                                            </td>
                                        ` : ``}

                                        <!-- FINAL SCORE -->
                                        <td class="border border-gray-300 px-3 py-2 text-center font-bold">
                                            ${item.score ?? 0}
                                        </td>

                                        ${!isProject ? `
                                            <!-- PENGAYAAN -->
                                            <td class="border border-gray-300 px-3 py-2 text-center">
                                                ${item.pengayaan_score !== null ? `
                                                    <a href="${buildUrl('pengayaan', item.student_id, item)}" class="text-green-600 font-bold hover:underline" title="Lihat detail pengayaan">
                                                        ${item.pengayaan_score}
                                                    </a>
                                                ` : '-'}
                                            </td>

                                        ` : ``}

                                        <!-- STATUS -->
                                        <td class="border border-gray-300 px-3 py-2 text-center">
                                            ${item.grading_status ?? '-'}
                                        </td>
                                    </tr>
                                `;
                            }

                            if (mode === 'remedial') {
                                return `
                                    <tr>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${index + 1}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.student_profile?.nama_lengkap}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.main_score ?? '-'}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.kkm ?? '-'}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.need_remedial ? 'Butuh Remedial' : 'Sudah'}</td>
                                    </tr>
                                `;
                            }

                            if (mode === 'susulan') {
                                return `
                                    <tr>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${index + 1}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.student_profile?.nama_lengkap}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.submission_status}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.has_susulan ? 'Sudah ikut' : 'Belum ikut'}</td>
                                    </tr>
                                `;
                            }

                            if (mode === 'pengayaan') {
                                return `
                                    <tr>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${index + 1}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.student_profile?.nama_lengkap}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">${item.score}</td>
                                        <td class="border border-gray-300 px-3 py-2 text-center">Siap Pengayaan</td>
                                    </tr>
                                `;
                            }
                        }
    
                        $('#tbody-assessment-grading-student-list').append(`
                            ${renderRow(item, index, mode)}
                        `);
                    });
    
                    $('#empty-message-assessment-grading-student-list').hide(); // sembunyikan pesan kosong
                    $('.thead-table-assessment-grading-student-list').show(); // Tampilkan tabel thead
    
                } else {
                    $('#tbody-assessment-grading-student-list').empty(); // Clear existing rows
                    $('.thead-table-assessment-grading-student-list').hide(); // Tampilkan tabel thead
                    $('#empty-message-assessment-grading-student-list').show();
                }
            }
        });
    }
}

$(document).ready(function () {
    paginateAssessmentGradingStudentList();
});

$(document).on('input', '#search_student', function () {
    paginateAssessmentGradingStudentList($(this).val());
});

$(document).on('click', '#btn-susulan, #btn-remedial, #btn-pengayaan', function () {
    const container = document.getElementById('container-assessment-grading-student-list');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;

    const btn = $(this);
    const mode = btn.data('mode');

    window.location.href = `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/${mode}/${assessmentId}`;
});