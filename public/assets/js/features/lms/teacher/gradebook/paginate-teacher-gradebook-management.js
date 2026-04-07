function teacherGradebook() {
    const container = document.getElementById('container-teacher-gradebook');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const subjectTeacherId = container.dataset.subjectTeacherId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;
    if (!subjectTeacherId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-class-list/teacher-gradebook/subject-teacher/${subjectTeacherId}/paginate`,
        method: 'GET',
        success: function (response) {
            $('#tbody-teacher-gradebook').empty();
            $('.pagination-container-teacher-gradebook').empty();

            const teacherMapel = response.teacherMapel;
            const gradebookInfo = $('#header-gradebook-info');
            const summary = response.summary;

            gradebookInfo.html(`
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">

                    <!-- TITLE -->
                    <div>
                        <h1 class="text-xl font-bold flex items-center gap-2">
                            <i class="fa-solid fa-book-open"></i>
                            Buku Nilai
                        </h1>

                        <p class="text-sm text-blue-100 mt-2 flex items-center gap-2">
                            ${teacherMapel.mapel?.mata_pelajaran}
                            <i class="fa-solid fa-circle text-[5px]"></i>

                            ${teacherMapel.school_class?.class_name}
                        </p>
                    </div>

                    <!-- ACTION -->
                    <div class="flex flex-col xl:flex-row gap-3 items-end lg:items-center">

                        <button id="btn-export-gradebook" class="bg-white text-[#4189E0] font-bold px-4 py-2 rounded-lg text-sm hover:bg-gray-100 cursor-pointer">
                            <i class="fa-solid fa-download"></i>
                            Export Excel
                        </button>

                    </div>

                </div>

                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-xs text-gray-500">Total Siswa</p>
                        <h2 class="text-xl font-bold text-blue-600">
                            ${summary.total_students}
                        </h2>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-xs text-gray-500">Rata-rata Kelas (Saat Ini)</p>
                        <h2 class="text-xl font-bold text-blue-600">
                            ${summary.avg_normalized}
                        </h2>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-xs text-gray-500">Nilai Tertinggi</p>
                        <h2 class="text-xl font-bold text-blue-600">
                            ${summary.max_normalized}
                        </h2>
                    </div>

                    <div class="bg-white rounded-xl p-4 shadow">
                        <p class="text-xs text-gray-500">Nilai Terendah</p>
                        <h2 class="text-xl font-bold text-red-500">
                            ${summary.min_normalized}
                        </h2>
                    </div>

                </div>
            `);

            gradebookInfo.show();

            const thead = $('.thead-table-teacher-gradebook');

            let header = `
                <tr>
                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nama Siswa</th>
                    `;

                        response.assessmentTypes.forEach(type => {
                            header += `
                            <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                ${type.name}
                            </th>
                        `;
                        });

                        header += `
                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Nilai Saat Ini</th>
                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">Kontribusi Raport</th>
                </tr>
            `;

            thead.html(header);

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {

                    let row = `<tr>`;

                    row += `<td class="border border-gray-300 px-3 py-2">${item.name}</td>`;

                    item.types.forEach(t => {
                        row += `
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                ${t.avg} (${t.count})
                            </td>
                        `;
                    });

                    row += `
                        <td class="border border-gray-300 px-3 py-2 text-center font-bold">
                            ${item.final_normalized}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <div class="font-semibold">${item.final_absolute}</div>
                        </td>
                    `;

                    row += `</tr>`;

                    $('#tbody-teacher-gradebook').append(row);
                });

                $('.thead-table-teacher-gradebook').show(); // Tampilkan tabel thead
                $('#empty-message-teacher-gradebook').hide(); // sembunyikan pesan kosong
            } else {
                $('#tbody-teacher-gradebook').empty(); // Clear existing rows
                $('.thead-table-teacher-gradebook').hide(); // Tampilkan tabel thead
                $('#empty-message-teacher-gradebook').show();
            }
        },

        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}

$(document).ready(function () {
    teacherGradebook();
});

$(document).on('click', '#btn-export-gradebook', function () {
    const role = $('#container-teacher-gradebook').data('role');
    const schoolName = $('#container-teacher-gradebook').data('schoolName');
    const schoolId = $('#container-teacher-gradebook').data('schoolId');
    const subjectTeacherId = $('#container-teacher-gradebook').data('subjectTeacherId');

    window.open(
        `/lms/${role}/${schoolName}/${schoolId}/teacher-class-list/teacher-gradebook/subject-teacher/${subjectTeacherId}/export`,
        '_blank'
    );
});