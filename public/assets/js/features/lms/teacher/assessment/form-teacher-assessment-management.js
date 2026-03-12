function formAssessment(search_year = null, search_class = null, mapel_id = null) {
    const container = document.getElementById('container');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/form`,
        method: 'GET',
        data: {
            search_year,
            search_class,
            mapel_id,
        },
        success: function (response) {
            enableFlatpickrCreate(); // inisialisasi flatpickr

            // ketika load ulang, maka reset
            document.getElementById('total-rombel-selected').innerText = '0 Dipilih';
            const toggleSelectRombel = document.getElementById('toggle-select-rombel');
            toggleSelectRombel.innerText = 'Select All';

            // Dropdown Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Tahun Ajaran</label>
                    <select id="dropdown-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm cursor-pointer outline-none">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-class');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Kelas</label>
                    <select id="dropdown-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm cursor-pointer outline-none">
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
                        ${response.subject.map(item => `<option value="${item.id}" ${mapel_id == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </div>
            `;

            const listContainer = $('#grid-rombel-class-list');
            listContainer.empty();

            if (response.rombel.length > 0) {

                (response.rombel || []).forEach((item) => {

                    const rombelClassList = `
                        <label class="rombel-card cursor-pointer border border-gray-300 rounded-xl p-4 flex flex-col transition hover:border-blue-400">
                            <div class="flex items-start justify-between">
                                <div>
                                    <div class="font-semibold text-gray-800 text-base">
                                        ${item.school_class?.class_name ?? ''}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        ${item.school_class.student_school_class_count ?? 0} Siswa Aktif
                                    </div>
                                </div>

                                <input type="checkbox" name="school_class_id[]" value="${item.school_class?.id ?? ''}" data-mapel="${item.mapel?.id}" 
                                    data-rombel-name="${item.school_class?.class_name ?? ''}" data-mapel-name="${item.mapel?.mata_pelajaran ?? ''}"
                                    class="rombel-checkbox text-blue-600 cursor-pointer">
                            </div>

                            <!-- MAPEL BADGE -->
                            <div class="mt-3">
                                <span class="inline-block text-xs bg-blue-50 text-blue-700 px-2 py-1 rounded-md">
                                    ${item.mapel?.mata_pelajaran ?? '-'}
                                </span>
                            </div>
                        </label>
                    `;

                    listContainer.append(rombelClassList);
                });

                setupReview();
                updateRombelSelectedCount();
                $('#empty-message-rombel-class-assessment-management-list').hide();
            } else {
                $('#empty-message-rombel-class-assessment-management-list').show();
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    formAssessment();
    setupAssessmentMode();
    setupReview();
});

function setupAssessmentMode() {
    const assessmentTypeSelect = document.querySelector('[name="assessment_type_id"]');
    const questionSettingSection = document.getElementById('question-settings-section');
    const projectSettingSection = document.getElementById('project-settings-section');
    const durationSection = document.getElementById('duration-section');

    const questionReviewExtra = document.getElementById('question-review-extra');
    const projectReviewExtra = document.getElementById('project-review-extra');
    const durationWrapper = document.getElementById('review-duration-wrapper');

    function applyAssessmentMode(mode) {
        const isProject = mode === 'project';

        // Form Section
        questionSettingSection?.classList.toggle('hidden', isProject);
        projectSettingSection?.classList.toggle('hidden', !isProject);
        durationSection?.classList.toggle('hidden', isProject);

        // Review Section
        questionReviewExtra?.classList.toggle('hidden', isProject);
        projectReviewExtra?.classList.toggle('hidden', !isProject);
        durationWrapper?.classList.toggle('hidden', isProject);

        // Reset duration if project
        if (isProject) {
            const durationInput = document.querySelector('[name="duration"]');
            if (durationInput) durationInput.value = '';
        }
    }

    assessmentTypeSelect?.addEventListener('change', function () {
        const selectedOption = this.options[this.selectedIndex];
        const mode = selectedOption.dataset.mode;
        applyAssessmentMode(mode);
    });

    // Init on load
    const initialMode = assessmentTypeSelect?.selectedOptions?.[0]?.dataset?.mode;
    if (initialMode) applyAssessmentMode(initialMode);
}

function setupReview() {
    const form = document.getElementById('create-assessment-form');
    if (!form) return;

    function updateReview() {

        // Ambil rombel setiap update (fix reactive bug)
        const checkedRombel = document.querySelectorAll('.rombel-checkbox:checked');
        const rombelNames = Array.from(checkedRombel).map(cb => cb.dataset.rombelName);
        const subjectNames = Array.from(checkedRombel).map(cb => cb.dataset.mapelName);

        document.getElementById('review-class').textContent = rombelNames.length ? rombelNames.join(', ') : '-';

        document.getElementById('review-subject').textContent = subjectNames.length ? subjectNames.join(', ') : '-';

        document.getElementById('review-tahun-ajaran').textContent = document.querySelector('#dropdown-tahun-ajaran')?.value || '-';

        document.getElementById('review-semester').textContent = document.getElementById('semester')?.value || '-';

        document.getElementById('review-title').textContent = form.querySelector('input[name="title"]')?.value || '-';

        document.getElementById('review-type').textContent = form.querySelector('select[name="assessment_type_id"]')?.selectedOptions?.[0]?.text || '-';

        document.getElementById('review-description').textContent = form.querySelector('textarea[name="description"]')?.value || '-';

        document.getElementById('review-duration').textContent = form.querySelector('input[name="duration"]')?.value || '-';

        document.getElementById('review-start').textContent = form.querySelector('input[name="start_date"]')?.value || '-';

        document.getElementById('review-end').textContent = form.querySelector('input[name="end_date"]')?.value || '-';

        document.getElementById('review-shuffle-questions').textContent = form.querySelector('input[name="shuffle_questions"]')?.checked ? 'Ya' : 'Tidak';

        document.getElementById('review-shuffle-options').textContent = form.querySelector('input[name="shuffle_options"]')?.checked ? 'Ya' : 'Tidak';

        document.getElementById('review-show-score').textContent = form.querySelector('input[name="show_score"]')?.checked ? 'Ya' : 'Tidak';

        document.getElementById('review-project-show-score').textContent = form.querySelector('input[name="show_project_score"]')?.checked ? 'Ya' : 'Tidak';

        document.getElementById('review-show-answer').textContent = form.querySelector('input[name="show_answer"]')?.checked ? 'Ya' : 'Tidak';

        // Project file
        const fileInput = form.querySelector('input[name="assessment_value_file"]');
        document.getElementById('review-filename').textContent = fileInput?.files?.[0]?.name || '-';

        document.getElementById('review-instruction').textContent = form.querySelector('textarea[name="assessment_instruction"]')?.value || '-';
    }

    form.addEventListener('input', updateReview);
    form.addEventListener('change', updateReview);

    updateReview();
}

$(document).on('change', '#dropdown-tahun-ajaran', function () {
    formAssessment($(this).val(), null, null); // null supaya auto pilih kelas paling rendah
});

$(document).on('change', '#dropdown-filter-class', function () {
    formAssessment($('#dropdown-tahun-ajaran').val(), $(this).val(), null);
});

$(document).on('change', '#dropdown-filter-mapel', function () {
    formAssessment($('#dropdown-tahun-ajaran').val(), $('#dropdown-filter-class').val(), $(this).val());
})

document.addEventListener('change', function (e) {

    if (e.target.classList.contains('rombel-checkbox')) {
        updateRombelSelectedCount();
    }
});

$(document).off('change', '.rombel-checkbox').on('change', '.rombel-checkbox', function () {
    // SET HIDDEN MAPEL
    const mapelId = $(this).data('mapel');

    if ($(this).is(':checked')) {
        $('#create-assessment-form').append(
            `<input type="hidden" id="dynamic_mapel_id" name="mapel_id[]" value="${mapelId}" data-mapel-id="${mapelId}">`
        );
    } else {
        // hapus input hidden yang sesuai
        $(`#create-assessment-form input[data-mapel-id="${mapelId}"]`).remove();
    }

    setupReview();
});

function updateRombelSelectedCount() {

    const checkboxes = document.querySelectorAll('.rombel-checkbox');
    const checked = document.querySelectorAll('.rombel-checkbox:checked');
    const total = checked.length;

    const allSelected = checkboxes.length === checked.length;

    document.getElementById('toggle-select-rombel').innerText = allSelected ? 'Unselect All' : 'Select All';

    if (total > 0) {
        $('#total-rombel-selected').html(`
            <div class="flex items-center gap-1">
                <span>${total} Dipilih</span>
            </div>
        `);

        $('#error-school_class_id').text('');
    } else {
        $('#total-rombel-selected').html(`
            <div class="flex items-center gap-1">
                <span>0 Dipilih</span>
            </div>
        `);
    }
}

document.getElementById('toggle-select-rombel').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('.rombel-checkbox');
    const checked = document.querySelectorAll('.rombel-checkbox:checked');

    const allSelected = checkboxes.length === checked.length;

    checkboxes.forEach(checkbox => {
        checkbox.checked = !allSelected;
    });

    // Hapus semua input hidden mapel dahulu
    $('#create-assessment-form input[data-mapel-id]').remove();

    // Tambahkan input hidden mapel baru
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            const mapelId = checkbox.getAttribute('data-mapel');
            $('#create-assessment-form').append(
                `<input type="hidden" id="dynamic_mapel_id" name="mapel_id[]" value="${mapelId}" data-mapel-id="${mapelId}">`
            );
        }
    });
    
    updateRombelSelectedCount();

    this.innerText = allSelected ? 'Select All' : 'Unselect All';
});

function enableFlatpickrCreate() {
    const startInput = document.getElementById('start-date');
    const endInput = document.getElementById('end-date');
    const durationInput = document.querySelector('input[name="duration"]');

    if (!startInput || !endInput) return; // exit jika elemen tidak ada

    const endPicker = flatpickr(endInput, {
        enableTime: true,
        time_24hr: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        disableMobile: true,
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length > 0) {
                startPicker.set('maxDate', selectedDates[0]);
            }
            document.getElementById('error-end_date').textContent = '';
        }
    });

    const startPicker = flatpickr(startInput, {
        enableTime: true,
        time_24hr: true,
        dateFormat: "Y-m-d H:i",
        minDate: "today",
        disableMobile: true,
        onChange: function (selectedDates, dateStr, instance) {
            if (selectedDates.length === 0) return;

            let startDate = selectedDates[0];
            let durationMinutes = parseInt(durationInput.value) || 0;

            // Atur minDate endPicker sesuai duration
            let minEndDate = new Date(startDate.getTime() + durationMinutes * 60000);
            endPicker.set('minDate', minEndDate);

            document.getElementById('error-start_date').textContent = '';
        }
    });

    // update minDate ketika duration berubah
    durationInput.addEventListener('input', function () {
        const startDate = startPicker.selectedDates[0];
        if (startDate) {
            const durationMinutes = parseInt(this.value) || 0;
            const minEndDate = new Date(startDate.getTime() + durationMinutes * 60000);
            endPicker.set('minDate', minEndDate);

            // jika end date saat ini lebih kecil dari minEndDate, reset end date
            if (endPicker.selectedDates[0] && endPicker.selectedDates[0] < minEndDate) {
                endPicker.clear();
            }

            // jika end date saat ini lebih besar dari minEndDate, reset end date
            if (endPicker.selectedDates[0] && endPicker.selectedDates[0] > minEndDate) {
                endPicker.clear();
            }
        }
    });
}

function disableFlatpickr(el) {
    if (el._flatpickr) {
        el._flatpickr.destroy();
    }
}

let isProcessing = false;

// Form Action assessment management
$('#submit-button-publish-create-assessment, #submit-button-draft-create-assessment').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role || !schoolName || !schoolId) return;

    const status = $(this).data('status'); // draft / publish
    const isActive = status;

    const form = $('#create-assessment-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    formData.append('status', isActive);

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-create-assessment').html(`
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

            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function () {
                $('#alertSuccess').remove();
            });

            $('#dropdown-filter-class').html('<option disabled selected>Pilih Kelas</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer').addClass('opacity-50 cursor-default');
            $('#dropdown-filter-mapel').html('<option disabled selected>Pilih Mata Pelajaran</option>').prop('disabled', true).removeClass('opacity-100 cursor-pointer')
                .addClass('opacity-50 cursor-default');

            // RESET SEMUA
            $('#create-assessment-form')[0].reset();

            window.location.reload();

            isProcessing = false;
            btn.prop('disabled', false);

            formAssessment();
            paginateContentForRelease();
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors;

                // reset error
                $('.border-red-400').removeClass('border-red-400 border');
                $('.error-meeting-date').text('');
                $('.text-error').text('');

                $.each(errors, function (field, messages) {

                    // Tampilkan pesan error
                    $('#create-assessment-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#create-assessment-form').find(`[name="${field}"]`).addClass('border-red-400 border');

                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

const assesmentTypeSelect = document.querySelector('[name="assessment_type_id"]');
const questionSettingSection = document.getElementById('question-settings-section');
const projectSettingSection = document.getElementById('project-settings-section');
const durationSection = document.getElementById('duration-section');

assesmentTypeSelect.addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const mode = selectedOption.dataset.mode;

    if (mode === 'project') {
        questionSettingSection.classList.add('hidden');
        projectSettingSection.classList.remove('hidden');

        durationSection.classList.add('hidden');
        document.querySelector('[name="duration"]').value = '';
    } else {
        questionSettingSection.classList.remove('hidden');
        projectSettingSection.classList.add('hidden');
        durationSection.classList.remove('hidden');
    }
});