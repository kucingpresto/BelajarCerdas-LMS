function teacherClassList(search_year = null, search_class = null, search_subject = null) {
    const container = document.getElementById('container-teacher-class-list');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role) return;
    if (!schoolName) return;
    if (!schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-class-list/paginate`,
        method: 'GET',
        data: {
            search_year: search_year,
            search_class: search_class,
            search_subject: search_subject
        },
        success: function (response) {
            const teacherClassList = $('#grid-teacher-class-list');
            teacherClassList.empty();

            // Dropdown Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-teacher-class-list-tahun-ajaran');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tahun Ajaran</label>
                    <select id="dropdown-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-6 cursor-pointer
                        outline-none">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-teacher-class-list-rombel');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer
                        outline-none">
                        <option value="" class="hidden">Filter Kelas</option>
                        ${response.className.map(item => `<option value="${item}" ${response.selectedClass == item ? 'selected' : ''}>Kelas ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // dropdown mapel rombel kelas
            const containerDropdownSubject = document.getElementById('container-dropdown-subject-rombel-class');
            containerDropdownSubject.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Mata Pelajaran</label>
                    <select id="dropdown-filter-mapel" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm cursor-pointer outline-none">
                        <option value="" class="hidden">Mata Pelajaran</option>
                        ${response.subject.map(item => `<option value="${item.id}" ${search_subject == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </div>
            `;

            if (response.data.length > 0) {
                $.each(response.data, function (index, item) {
                    const teacherGradebook = response.teacherGradebook.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':subjectTeacherId', item.id);

                    const card = `
                        <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-300">

                            <!-- Header -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-800">
                                        ${item.school_class?.class_name ? `Kelas ` + item.school_class?.class_name : '-'}
                                    </h4>
                                    <p class="text-xs text-gray-500">
                                        ${item.school_class?.tahun_ajaran ? `Tahun Ajaran ` + item.school_class?.tahun_ajaran : '-'}
                                    </p>
                                </div>

                                <span class="text-xs px-3 py-1 rounded-full bg-blue-50 text-blue-600">
                                    ${item.school_class?.status_class ?? '-'}
                                </span>
                            </div>

                            <!-- Divider -->
                            <div class="border-t border-gray-300 my-4"></div>

                            <!-- Info -->
                            <div class="space-y-2 text-sm">

                                <div class="flex justify-between text-xs font-bold opacity-70">
                                    <span>Wali Kelas</span>
                                    <span>
                                        ${item.school_class?.user_account?.school_staff_profile?.nama_lengkap ?? '-'}
                                    </span>
                                </div>

                                <div class="flex justify-between text-xs font-bold opacity-70">
                                    <span>Mapel Anda</span>
                                    <span>
                                        ${item.mapel?.mata_pelajaran ?? '-'}
                                    </span>
                                </div>

                                <div class="flex justify-between text-xs font-bold opacity-70">
                                    <span>Jumlah Siswa</span>
                                    <span>
                                        ${item.school_class?.student_school_class_count ?? 0} siswa
                                    </span>
                                </div>

                            </div>

                            <!-- Action -->
                            <div class="mt-5">
                                <a href="${teacherGradebook}"
                                class="w-full inline-flex items-center justify-center gap-2 text-sm bg-[#0071BC] text-white py-2 rounded-lg hover:bg-[#005a96] transition">
                                    <i class="fa-solid fa-book-open"></i>
                                    Lihat Buku Nilai
                                </a>
                            </div>

                        </div>
                    `;

                    teacherClassList.append(card);
                });

                $('#empty-message-teacher-class-list').hide();

            } else {
                $('#empty-message-teacher-class-list').show();
            }
        },

        error: function (xhr, status, error) {
            console.log(error);
        }
    });
}

$(document).ready(function () {
    teacherClassList();
});

$(document).on('change', '#dropdown-tahun-ajaran', function () {
    teacherClassList($(this).val(), null, $('#dropdown-filter-mapel').val()); // null supaya auto pilih kelas paling rendah
});

$(document).on('change', '#dropdown-filter-class', function () {
    teacherClassList($('#dropdown-tahun-ajaran').val(), $(this).val(), $('#dropdown-filter-mapel').val());
});

$(document).on('change', '#dropdown-filter-mapel', function () {
    teacherClassList($('#dropdown-tahun-ajaran').val(), null, $(this).val());
});