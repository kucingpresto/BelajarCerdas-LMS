function paginateQuestionForRelease(search_year = null, search_class = null, search_assessment_type = null, page = 1) {
    const container = document.getElementById('container-paginate-teacher-question-bank-for-release-list');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/paginate`,
        method: 'GET',
        data: {
            search_year,
            search_class,
            search_assessment_type,
            page: page,
        },
        success: function (response) {
            $('#tbody-paginate-teacher-question-bank-for-release-list').empty();
            $('.pagination-container-paginate-teacher-question-bank-for-release-list').empty();

            // Dropdown Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran-paginate-question-bank-for-release');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tahun Ajaran</label>
                    <select id="dropdown-filter-tahun-ajaran-paginate-question-bank-for-release" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-6 cursor-pointer
                        outline-none">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-class-paginate-question-bank-for-release');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-filter-class-paginate-question-bank-for-release" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer
                        outline-none">
                        <option value="" class="hidden">Filter Kelas</option>
                        ${response.className.map(item => `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Assessment Type
            const containerAssessmentType = document.getElementById('container-dropdown-assessment-type-paginate-question-bank-for-release');
            containerAssessmentType.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tipe Asesmen</label>
                    <select id="dropdown-filter-assessment-type-paginate-question-bank-for-release" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24
                        cursor-pointer outline-none">
                        <option value="" class="hidden">Filter Tipe Asesmen</option>
                        ${response.schoolAssessmentType.map(item => `<option value="${item.id}" ${search_assessment_type == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </div>
            `;

            if (response.data.length > 0) {

                $.each(response.data, function (index, item) {
                    const first = item[0];
                    const formatDate = (dateString) => {
                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                        const date = new Date(dateString);
                        const day = date.getDate();
                        const monthName = months[date.getMonth()];
                        const year = date.getFullYear();

                        return `${day}-${monthName}-${year}`;
                    };

                    const timeFormatter = new Intl.DateTimeFormat('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    });

                    // Format tanggal mulai dan akhir
                    const startDate = first.school_assessment?.start_date ? `
                        ${formatDate(first.school_assessment?.start_date)}, ${timeFormatter.format(new Date(first.school_assessment?.start_date))}` : 'Tanggal tidak tersedia';
                    
                    const endDate = first.school_assessment?.end_date ? `
                        ${formatDate(first.school_assessment?.end_date)}, ${timeFormatter.format(new Date(first.school_assessment?.end_date))}` : 'Tanggal tidak tersedia';

                    const teacherReviewQuestionBankForRelease = response.teacherReviewQuestionBankForRelease.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId)
                        .replace(':assessmentQuestionId', first.school_assessment_id);

                    $('#tbody-paginate-teacher-question-bank-for-release-list').append(`
                        <tr>
                            <td class="border border-gray-300 px-3 py-2 text-center">${(response.current_page - 1) * response.per_page + index + 1}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${first.school_assessment?.school_class?.class_name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${first.school_assessment?.school_class?.tahun_ajaran ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${first.school_assessment?.mapel?.mata_pelajaran ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${first.school_assessment?.school_assessment_type?.name ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${first.school_assessment?.title ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${first.school_assessment?.semester ?? '-'}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${startDate} - ${endDate}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">${item.length}</td>
                            <td class="border border-gray-300 px-3 py-2 text-center">
                                <div class="dropdown dropdown-left">
                                    <div tabindex="0" role="button">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </div>
                                    <ul tabindex="0"
                                        class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                        <li class="text-md">
                                            <a href="${teacherReviewQuestionBankForRelease}">
                                                <i class="fa-solid fa-eye text-[#0071BC]"></i>
                                                Review Question
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    `);
                });

                $('.pagination-container-paginate-teacher-question-bank-for-release-list').html(response.links);
                bindPaginationLinks();
                $('#empty-message-paginate-teacher-question-bank-for-release-list').hide(); // sembunyikan pesan kosong
                $('.thead-table-paginate-teacher-question-bank-for-release-list').show(); // Tampilkan tabel thead

            } else {
                $('#tbody-paginate-teacher-question-bank-for-release-list').empty(); // Clear existing rows
                $('.thead-table-paginate-teacher-question-bank-for-release-list').hide(); // Tampilkan tabel thead
                $('#empty-message-paginate-teacher-question-bank-for-release-list').show();
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    paginateQuestionForRelease();
});

$(document).on('change', '#dropdown-filter-tahun-ajaran-paginate-question-bank-for-release', function () {
    paginateQuestionForRelease($(this).val(), null, $('#dropdown-filter-assessment-type-paginate-question-bank-for-release').val(), 1); // null supaya auto pilih kelas paling rendah
});

$(document).on('change', '#dropdown-filter-class-paginate-question-bank-for-release', function () {
    paginateQuestionForRelease($('#dropdown-filter-tahun-ajaran-paginate-question-bank-for-release').val(), $(this).val(), $('#dropdown-filter-assessment-type-paginate-question-bank-for-release').val(), 1);
});

$(document).on('change', '#dropdown-filter-assessment-type-paginate-question-bank-for-release', function () {
    paginateQuestionForRelease($('#dropdown-filter-tahun-ajaran-paginate-question-bank-for-release').val(), null, $(this).val(), 1);
});

function bindPaginationLinks() {
    $('.pagination-container-paginate-teacher-question-bank-for-release-list').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_year = $('#dropdown-filter-tahun-ajaran-paginate-question-bank-for-release').val();
        const search_class = $('#dropdown-filter-class-paginate-question-bank-for-release').val();
        const search_assessment_type = $('#dropdown-filter-assessment-type-paginate-question-bank-for-release').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        paginateQuestionForRelease(search_year, search_class, search_assessment_type, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}
