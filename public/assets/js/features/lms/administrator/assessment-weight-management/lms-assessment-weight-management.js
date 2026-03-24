function assessmentWeightManagement(search_year = null, page = 1) {
    const container = document.getElementById('container-assessment-weight-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    fetchData(schoolName, schoolId);

    function fetchData() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/assessment-weight-management/paginate`,
            method: 'GET',
            data: {
                search_year,
                page: page
            },
            success: function (response) {
                $('#table-list-assessment-weight-management').empty();
                $('.pagination-container-assessment-weight-management').empty();

                // Dropdown Tahun Ajaran
                const containerDropdownTahunAjaran = document.getElementById('container-dropdown-assessment-weight-tahun-ajaran');
                containerDropdownTahunAjaran.innerHTML = `
                    <div class="flex flex-col w-full mb-2">
                        <label class="text-sm font-medium text-gray-600 mb-1">Filter Tahun Ajaran</label>
                        <select id="dropdown-assessment-weight-filter-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-6 cursor-pointer
                            outline-none">
                            <option value="" class="hidden">Pilih Tahun Ajaran</option>
                            ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                        </select>
                    </div>
                `;

                const schoolDetailCard = document.getElementById('school-detail-card');
                const schoolIdentity = response.schoolIdentity;

                schoolDetailCard.innerHTML = `
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

                            <!-- KIRI : ICON + NAMA SEKOLAH -->
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#EEF6FF] flex items-center justify-center text-[#0071BC] text-2xl shadow-sm">
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-gray-800 leading-tight">
                                        ${schoolIdentity.nama_sekolah}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Detail langganan LMS sekolah
                                    </p>
                                </div>
                            </div>

                            <!-- KANAN : INFO SEKOLAH -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full lg:w-auto">

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NPSN</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.npsn}</p>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NIK Kepala Sekolah</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.user_account?.school_staff_profile?.nik}</p>
                                </div>

                                <div class="bg-[#EEF6FF] rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-[#0071BC] mb-1">Total Pengguna</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${response.countUsers}</p>
                                </div>

                            </div>
                        </div>
                    `;

                if (response.data.length > 0) {
                    $.each(response.data, function (index, item) {
                        const formatDate = (dateString) => {
                            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

                            const date = new Date(dateString);
                            const day = date.getDate();
                            const monthName = months[date.getMonth()];
                            const year = date.getFullYear();

                            return `${day}-${monthName}-${year}`;
                        };

                        const updatedAt = item.updated_at ? `${formatDate(item.updated_at)}` : '-';

                        $('#table-list-assessment-weight-management').append(`
                            <tr class="text-xs">
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.school_assessment_type?.name ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.weight ? item.weight + `%` : 0}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">${item.school_year ?? '-'}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">
                                    <div class="dropdown dropdown-left">
                                        <div tabindex="0" role="button">
                                            <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                        </div>
                                        <ul tabindex="0"
                                            class="dropdown-content menu bg-base-100 rounded-box w-max p-2 shadow-sm z-9999">
                                            <li>
                                                <a href="#" class="btn-edit-assessment-weight" data-assessment-type-id="${item.assessment_type_id}" data-school-year="${item.school_year}"
                                                    data-assessment-weight-id="${item.id}" data-assessment-weight="${item.weight}">
                                                    <i class="fa-solid fa-pen text-[#0071BC]"></i>
                                                    Edit Assessment weight
                                                </a>
                                            </li>
                                            <li onclick="historyAssessmentType(this)" class="btn-history-assessment-weight"
                                                data-assessment-weight-id="${item.id}"
                                                data-nama_lengkap="${item.user_account?.office_profile?.nama_lengkap || item.user_account?.school_staff_profile?.nama_lengkap}"
                                                data-role="${item.user_account?.role ?? '-'}"
                                                data-updated_at="${updatedAt}">
                                                <span>
                                                    <i class="fa-solid fa-clock-rotate-left text-[#0071BC]"></i>
                                                    History Assessment weight
                                                </span>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });

                    $('#school-detail-card').show();
                    $('.pagination-container-assessment-weight-management').html(response.links);
                    bindPaginationLinks();
                    $('#empty-message-assessment-weight-management').hide();
                    $('.thead-table-assessment-weight-management').show();
                } else {
                    $('#school-detail-card').show();
                    $('#empty-message-assessment-weight-management').show();
                    $('.thead-table-assessment-weight-management').hide();
                }
            },
            error: function (xhr, status, error) {
                console.error('Terjadi kesalahan:', status, error);
            }
        });
    }
}

function bindPaginationLinks() {
    $('.pagination-container-assessment-weight-management').off('click', 'a').on('click', 'a', function (event) {
        event.preventDefault(); // Cegah perilaku default link
        const search_year = $('#dropdown-assessment-weight-filter-tahun-ajaran').val();
        const page = new URL(this.href).searchParams.get('page'); // Dapatkan nomor halaman dari link
        assessmentWeightManagement(search_year, page); // Ambil data yang difilter untuk halaman yang ditentukan
    });
}

$(document).ready(function () {
    assessmentWeightManagement();
});

$(document).on('change', '#dropdown-assessment-weight-filter-tahun-ajaran', function () {
    assessmentWeightManagement($(this).val(), 1);
});


// open modal history assessment weight
function historyAssessmentType(element) {
    const namaLengkap = element.dataset.nama_lengkap;
    const role = element.dataset.role;
    const updatedAt = element.dataset.updated_at;

    // BASIC INFO
    document.getElementById('text-nama_lengkap').innerText = namaLengkap;
    document.getElementById('text-role').innerText = role;
    document.getElementById('text-updated_at').innerText =
        updatedAt ? `Terakhir diperbarui: ${updatedAt}` : '';

    document.getElementById('my_modal_2').showModal();
}

let isProcessing = false;

// Form Action create assessment weight
$('#submit-button-create-assessment-weight').on('click', function (e) {
    e.preventDefault();

    // Kosongkan error sebelumnya
    $('#error-name').text('');

    const form = $('#create-assessment-weight-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const container = document.getElementById('container-assessment-weight-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/assessment-weight-management/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            $('#alert-success-insert-data-assessment-weight').html(
                `
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
                `
            );

            setTimeout(function () {
                document.getElementById('alertSuccess').remove();
            }, 3000);

            document.getElementById('btnClose').addEventListener('click', function () {
                document.getElementById('alertSuccess').remove();
            });

            // reset form
            $('#create-assessment-weight-form')[0].reset();

            // Memanggil fungsi untuk memuat ulang data
            assessmentWeightManagement();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const res = xhr.responseJSON;

                if (res.error_type === 'weight_limit_exceeded') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Bobot Melebihi Batas',
                        text: res.message,
                        confirmButtonColor: '#0071BC'
                    });
                }

                const errors = res.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#create-assessment-weight-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-assessment-weight-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

// Event listener tombol "edit assessment weight" (open modal)
$(document).off('click', '.btn-edit-assessment-weight').on('click', '.btn-edit-assessment-weight', function (e) {
    e.preventDefault();

    const assessmentTypeId =  $(this).data('assessment-type-id');
    const schoolYear = $(this).data('school-year');
    const assessmentTypeWeightId = $(this).data('assessment-weight-id');
    const assessmentTypeWeight = $(this).data('assessment-weight');

    // set value ke form
    $('#edit-assessment-type-id').val(assessmentTypeId);
    $('#edit-school-year').val(schoolYear);
    $('#edit-assessment-weight-id').val(assessmentTypeWeightId);
    $('#edit-weight').val(assessmentTypeWeight);

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();
});

// form edit assessment weight
$('#submit-button-edit-assessment-weight').on('click', function (e) {
    e.preventDefault();

    const form = $('#edit-assessment-weight-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const assessmentTypeId = $('#edit-assessment-weight-id').val();

    const container = document.getElementById('container-assessment-weight-management');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/assessment-weight-management/${assessmentTypeId}/edit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            // Menutup modal
            const modal = document.getElementById('my_modal_1');
            if (modal) {
                modal.close();

                $('#alert-success-edit-data-assessment-weight').html(
                    `
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
                    `
                );

                setTimeout(function () {
                    document.getElementById('alertSuccess').remove();
                }, 3000);

                document.getElementById('btnClose').addEventListener('click', function () {
                    document.getElementById('alertSuccess').remove();
                });

                $('#edit-assessment-weight-form')[0].reset();

                // Memanggil fungsi untuk memuat ulang data
                assessmentWeightManagement();

                isProcessing = false;
                btn.prop('disabled', false);
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const res = xhr.responseJSON;
                const modal = document.getElementById('my_modal_1');

                if (res.error_type === 'weight_limit_exceeded') {
                    modal.close();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Bobot Melebihi Batas',
                        text: res.message,
                        confirmButtonColor: '#0071BC'
                    });
                }

                const errors = res.errors;

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-assessment-weight-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-assessment-weight-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});