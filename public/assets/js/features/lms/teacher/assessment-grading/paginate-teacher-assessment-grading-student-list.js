function paginateAssessmentGradingStudentList(search_student = null) {
    const container = document.getElementById('container-assessment-grading-student-list');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;

    if (!role || !schoolName || !schoolId, !assessmentId) return;

    fetchData(role, schoolName, schoolId, assessmentId);

    function fetchData() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/student-list/paginate`,
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
    
                if (response.data.length > 0) {
    
                    $.each(response.data, function (index, item) {
                        const assessmentGradingStudentAnswer = response.assessmentGradingStudentAnswer.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                            .replace(':assessmentId', assessmentId).replace(':studentId', item.student_id);
    
                        $('#tbody-assessment-grading-student-list').append(`
                            <tr>
                                <td class="border border-gray-300 px-3 py-2 text-center">${index + 1}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.user_account?.student_profile?.nama_lengkap ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.submission_status ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.score ?? 0}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.grading_status ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <a href="${assessmentGradingStudentAnswer}" class="text-[#0071BC] font-bold text-xs">
                                        Lihat Detail
                                    </a>    
                                </td>
                            </tr>
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