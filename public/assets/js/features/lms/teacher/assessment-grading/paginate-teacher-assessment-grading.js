function paginateAssessmentGrading(search_year = null, search_class = null, search_assessment_type = null, page = 1) {
    const container = document.getElementById('container-assessment-grading-list');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/paginate`,
        method: 'GET',
        data: {
            search_year,
            search_class,
            search_assessment_type,
            page: page,
        },
        success: function(response) {
            $('#tbody-assessment-grading-list').empty();
            $('.pagination-container-teacher-assessment-grading-list').empty();

            // Dropdown Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-assessment-management-tahun-ajaran');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tahun Ajaran</label>
                    <select id="dropdown-assessment-management-filter-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-6 cursor-pointer outline-none">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-assessment-management-class');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-assessment-management-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer outline-none">
                        <option value="" class="hidden">Filter Kelas</option>
                        ${response.className.map(item => `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Assessment Type
            const containerAssessmentType = document.getElementById('container-dropdown-assessment-type');
            containerAssessmentType.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tipe Asesmen</label>
                    <select id="dropdown-assessment-type" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer outline-none">
                        <option value="" class="hidden">Filter Tipe Asesmen</option>
                        ${response.schoolAssessmentType.map(item => `<option value="${item.id}" ${search_assessment_type == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </div>
            `;

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {
                    const assessmentGradingStudentList = response.assessmentGradingStudentList.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                        .replace(':assessmentId', item.id);
                    
                    $('#tbody-assessment-grading-list').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2">${item.title ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.mapel?.mata_pelajaran ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_class?.class_name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.school_assessment_type?.name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.submission_count} / ${item.total_students} Siswa</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.pending_count} Siswa</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <a href="${assessmentGradingStudentList}" class="text-[#0071BC] font-bold text-xs">
                                    Lihat Detail
                                </a>    
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-teacher-assessment-grading-list').html(response.links);
                bindPaginationLinks();
                $('#empty-message-assessment-grading-list').hide(); // sembunyikan pesan kosong
                $('.thead-table-assessment-grading-list').show(); // Tampilkan tabel thead

            } else {
                $('#tbody-assessment-grading-list').empty(); // Clear existing rows
                $('.thead-table-assessment-grading-list').hide(); // Tampilkan tabel thead
                $('#empty-message-assessment-grading-list').show();
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function() {
    paginateAssessmentGrading();
});

$(document).on('change', '#dropdown-assessment-management-filter-tahun-ajaran', function () {
    paginateAssessmentGrading($(this).val(), null, $('#dropdown-assessment-type').val(), 1); // null supaya auto pilih kelas paling rendah
});

$(document).on('change', '#dropdown-assessment-management-filter-class', function () {
    paginateAssessmentGrading($('#dropdown-assessment-management-filter-tahun-ajaran').val(), $(this).val(), $('#dropdown-assessment-type').val(), 1);
});

$(document).on('change', '#dropdown-assessment-type', function () {
    paginateAssessmentGrading($('#dropdown-assessment-management-filter-tahun-ajaran').val(), null, $(this).val(), 1);
});


function bindPaginationLinks() {
    $('.pagination-container-teacher-assessment-grading-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_year = $('#dropdown-assessment-management-filter-tahun-ajaran').val();
        const search_class = $('#dropdown-assessment-management-filter-class').val();
        const search_assessment_type = $('#dropdown-assessment-type').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateAssessmentGrading(search_year, search_class, search_assessment_type, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}