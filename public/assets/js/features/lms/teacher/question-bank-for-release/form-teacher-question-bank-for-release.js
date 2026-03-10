let selectedAssessmentId = null;
function formQuestionForRelease(search_year = null, search_class = null, search_assessment_type = null, search_subject = null, search_semester = null, search_question = null,
    kurikulum_id = null, kelas_id = null, mapel_id = null, bab_id = null, sub_bab_id = null) {
    const container = document.getElementById('container-form-teacher-question-bank-for-release');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    if (!role || !schoolName || !schoolId) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/form`,
        method: 'GET',
        data: {
            search_year,
            search_class,
            search_assessment_type,
            search_subject,
            search_semester,
            search_question,
            kurikulum_id,
            kelas_id,
            mapel_id,
            bab_id,
            sub_bab_id
        },
        success: function (response) {
            // Dropdown Tahun Ajaran
            const containerDropdownTahunAjaran = document.getElementById('container-dropdown-tahun-ajaran');
            containerDropdownTahunAjaran.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Tahun Ajaran</label>
                    <select id="dropdown-filter-tahun-ajaran" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm cursor-pointer outline-none">
                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                        ${response.tahunAjaran.map(item => `<option value="${item}" ${response.selectedYear == item ? 'selected' : ''}>Tahun Ajaran ${item}</option>`).join('')}
                    </select>
                </div>
            `;

            // Dropdown Kelas
            const containerDropdownClass = document.getElementById('container-dropdown-class');
            containerDropdownClass.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Kelas</label>
                    <select id="dropdown-filter-class" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm pr-24 cursor-pointer outline-none">
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
                        <option value="" class="hidden">Tipe Asesmen</option>
                        ${response.schoolAssessmentType.map(item => `<option value="${item.id}" ${search_assessment_type == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </div>
            `;

            // dropdown mapel rombel kelas
            const containerDropdownSubject = document.getElementById('container-dropdown-subject-rombel-class');
            containerDropdownSubject.innerHTML = `
                <div class="flex flex-col w-full mb-2">
                    <label class="text-sm font-medium text-gray-600 mb-1">Filter Mata Pelajaran</label>
                    <select id="dropdown-filter-mapel" class="w-full bg-white shadow-lg rounded-md h-12 border border-gray-300 text-sm cursor-pointer outline-none">
                        <option value="" class="hidden">Mata Pelajaran</option>
                        ${response.subject.map(item => `<option value="${item.id}" ${search_subject == item.id ? 'selected' : ''}>${item.name}</option>`).join('')}
                    </select>
                </div>
            `;

            // Table rombel class assessment
            const tbodyContent = document.getElementById('tbody-rombel-class-teacher-assessment');
            tbodyContent.innerHTML = '';

            if (response.data.length > 0) {
                (response.data || []).forEach((item) => {

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
                    const startDate = item.start_date ? `${formatDate(item.start_date)}, ${timeFormatter.format(new Date(item.start_date))}` : 'Tanggal tidak tersedia';
                    const endDate = item.end_date ? `${formatDate(item.end_date)}, ${timeFormatter.format(new Date(item.end_date))}` : 'Tanggal tidak tersedia';

                    const row = document.createElement('tr');
                    row.classList.add('rombel-row');

                    const mapelName = item.mapel?.mata_pelajaran ?? '';

                    row.innerHTML = `
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <input type="radio" name="school_assessment_id" value="${item.id}" class="school-assessment-checkbox cursor-pointer"
                                ${selectedAssessmentId == item.id ? 'checked' : ''}>
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            ${item.school_class?.class_name ?? ''}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            ${mapelName}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            ${item.school_assessment_type?.name ?? ''}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            ${item.title ?? ''}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            ${item.semester ?? ''}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            ${startDate ?? ''} - ${endDate ?? ''}
                        </td>
                    `;

                    tbodyContent.appendChild(row);
                });

                $('.thead-table-rombel-class-teacher-assessment').show();
                $('#empty-message-rombel-class-teacher-assessment-list').hide();
            } else {
                $('.thead-table-rombel-class-teacher-assessment').hide();
                $('#empty-message-rombel-class-teacher-assessment-list').show();
            }

            // Table question bank list
            const tbodyQuestionBankList = document.getElementById('tbody-question-bank-list');
            tbodyQuestionBankList.innerHTML = '';

            if (response.questionBank.length > 0) {
                tbodyQuestionBankList.innerHTML = ''; // supaya tidak double render

                (response.questionBank || []).forEach((item) => {

                    const row = document.createElement('tr');
                    row.classList.add('question-bank-row');

                    const modalContent = document.getElementById("modal-preview-content");

                    function addClassToImgTags(html, className) {
                        return html
                            .replace(/<img\b(?![^>]*class=)[^>]*>/g, (imgTag) => {
                                // Tambahkan class jika belum ada atribut class
                                return imgTag.replace('<img', `<img class="${className}"`);
                            })
                            .replace(/<img\b([^>]*?)class="(.*?)"/g, (imgTag, before, existingClasses) => {
                                // Tambahkan class ke img yang sudah punya class
                                return `<img ${before}class="${existingClasses} ${className}"`;
                            });
                    }

                    row.innerHTML = `
                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <input type="checkbox" class="question-checkbox cursor-pointer" data-question-id="${item.id}">
                        </td>

                        <td class="border border-gray-300 px-3 py-2">
                            <span class="flex gap-4 leading-8">
                                ${stripHtmlAndLimit(item.questions, 50)}
                            </span>
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center text-xs">
                            ${item.difficulty}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center text-xs">
                            ${item.tipe_soal}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <div class="flex flex-col items-center gap-1">
                                <input type="number" min="1" step="1" max="100" name="question_weight[${item.id}]" data-question-id="${item.id}" placeholder="0" 
                                    class="question-weight-input w-full h-9 text-sm text-center border border-gray-300 rounded-lg outline-none" disabled>
                                <span id="error-question_weight_${item.id}" class="text-xs text-red-500 hidden"></span>
                            </div>
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center text-xs">
                            ${item.school_partner_id ? item.school_partner?.nama_sekolah : 'belajarcerdas.id'}
                        </td>

                        <td class="border border-gray-300 px-3 py-2 text-center">
                            <a href="#" class="btn-preview-question text-[#0071BC] font-bold opacity-70 text-xs hover:underline">
                                Lihat Soal
                            </a>
                        </td>
                    `;

                    row.addEventListener("click", function (e) {

                        if (e.target.closest("input")) return;

                        let optionsHtml = '';

                        if (['mcq', 'mcma'].includes(item.tipe_soal?.toLowerCase())) {

                            optionsHtml = `
                                <div class="mt-4 space-y-2">
                                    ${item.lms_question_option.map((opt, index) => `
                                        <div class="flex items-start gap-3 p-3 rounded-xl border
                                            ${opt.is_correct ? 'border-green-400 bg-green-400 opacity-70 text-white font-bold' : 'bg-white border-gray-200'}">

                                            <div class="text-sm font-semibold">
                                                ${String.fromCharCode(65 + index)}.
                                            </div>

                                            <div class="text-sm">
                                                ${opt.options_value}
                                            </div>

                                        </div>
                                    `).join('')}
                                </div>
                            `;
                        }

                        const leftItems = item.lms_question_option.filter(item => item.options_key.startsWith('LEFT'));
                        const rightItems = item.lms_question_option.filter(item => item.options_key.startsWith('RIGHT'));

                        const rightLabelMap = {};
                        rightItems.forEach((item, index) => {
                            rightLabelMap[item.options_key] = String.fromCharCode(65 + index); // A, B, C
                        });

                        const pairsData = leftItems.filter(i => i.extra_data?.pair_with).map(i => ({
                            left: i.options_key,
                            right: i.extra_data.pair_with
                        }));

                        const matchingHTML = `
                            <!-- DEKSTOP -->
                            <div class="relative matching-container hidden lg:block" data-pairs='${JSON.stringify(pairsData)}'>

                                <!-- SVG GARIS -->
                                <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines"></svg>

                                <div class="grid grid-cols-2 gap-40 relative z-10">
                                    <div class="flex flex-col justify-center">
                                        <h4 class="font-bold mb-3">Kolom A</h4>
                                        <div class="space-y-3">
                                            ${leftItems.map(item => {
                                                const content = addClassToImgTags(item.options_value, 'max-w-[100px] rounded');

                                                return `
                                                    <div
                                                        class="px-3 min-h-10 border rounded flex justify-between items-center left-item" data-key="${item.options_key}">
                                                        <span>${content}</span>
                                                        <span class="text-sm bg-blue-100 text-[#0071BC] px-2 py-1 rounded">
                                                            <i class="fa-solid fa-arrow-right"></i>
                                                            ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                                        </span>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-bold mb-3">Kolom B</h4>
                                        <div class="space-y-3">
                                            ${rightItems.map(item => {
                                                const content = addClassToImgTags(item.options_value, 'max-w-[100px] rounded');

                                                return `
                                                    <div class="right-item p-3 border rounded flex gap-2 items-center" data-key="${item.options_key}">
                                                        <span class="font-bold">${rightLabelMap[item.options_key]}.</span>
                                                        ${content}
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                </div>

                                <!-- GARIS TENGAH -->
                                <div class="matching-center-line absolute top-0 bottom-0 left-1/2 w-0"></div>
                            </div>

                            <!-- MOBILE -->
                            <div class="block lg:hidden">

                                <div class="grid grid-cols-1 gap-3 lg:hidden">
                                    <p class="font-semibold mb-2">Kolom A:</p>
                                        ${leftItems.map(item => {
                                            const content = addClassToImgTags(item.options_value, 'max-w-[100px] rounded');

                                            return `
                                                <div class="flex justify-between items-center border rounded p-3">
                                                    <span>${content}</span>
                                                    <span class="font-bold text-[#0071BC]">
                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                                    </span>
                                                </div>
                                            `;
                                        }).join('')}
                                </div>

                                <div class="mt-4 lg:hidden border-t border-gray-400 pt-3 grid grid-cols-1 gap-3 text-sm text-gray-700">
                                    <p class="font-semibold mb-2">Kolom B:</p>
                                        ${rightItems.map(item => {
                                            const content = addClassToImgTags(item.options_value, 'max-w-[100px] rounded');

                                            return `
                                                <div class="right-item p-3 border rounded flex gap-2 items-center" data-key="${item.options_key}">
                                                    <span class="font-bold">${rightLabelMap[item.options_key]}.</span>
                                                    ${content}
                                                </div>
                                            `;
                                        }).join('')}
                                </div>
                            </div>
                        `;

                        modalContent.innerHTML = `
                            <div class="bg-white rounded-2xl space-y-6">

                                <!-- Question Section -->
                                <div class="bg-gray-50 p-5 rounded-xl border border-gray-200">
                                    <h3 class="text-sm font-semibold text-gray-500 mb-2 uppercase tracking-wide">
                                        Soal
                                    </h3>

                                    <div class="question-bank-preview leading-relaxed text-gray-800">
                                        <div>${addClassToImgTags(item.questions, 'max-w-full md:max-w-[300px] h-auto')}</div>
                                        <div>${item.tipe_soal === 'MATCHING' ? matchingHTML : optionsHtml}</div>
                                    </div>
                                </div>

                                <!-- Explanation Section -->
                                ${item.explanation ? `
                                <div class="bg-green-50 p-5 rounded-xl border border-green-200">
                                    <h3 class="text-sm font-semibold text-green-700 mb-2 uppercase tracking-wide">
                                        Pembahasan
                                    </h3>

                                    <div class="question-bank-preview text-sm text-gray-700 leading-relaxed">
                                        ${item.explanation}
                                    </div>
                                </div>
                                ` : ''}

                                <!-- Metadata Section -->
                                <div class="bg-white border border-gray-200 rounded-xl p-5">

                                    <!-- Badge Row -->
                                    <div class="grid grid-cols-2 lg:grid-cols-4 items-center gap-2 mb-4">

                                        <span class="px-3 py-1 text-xs rounded-full font-bold bg-green-100 text-green-700 text-center">
                                            ${item.difficulty ?? '-'}
                                        </span>

                                        <span class="px-3 py-1 text-xs rounded-full font-bold bg-blue-100 text-blue-700 text-center">
                                            ${item.tipe_soal ?? '-'}
                                        </span>

                                        <span class="px-3 py-1 text-xs rounded-full font-bold bg-purple-100 text-purple-700 text-center">
                                            ${item.mapel?.mata_pelajaran ?? '-'}
                                        </span>

                                        <span class="px-3 py-1 text-xs rounded-full font-bold bg-indigo-100 text-indigo-700 text-center">
                                            ${item.kelas?.kelas ?? '-'}
                                        </span>

                                    </div>

                                    <!-- Info Grid -->
                                    <div class="bg-white border border-gray-200 rounded-xl p-5 space-y-5">

                                        <!-- Academic Path -->
                                        <div>
                                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">
                                                Struktur Materi
                                            </p>

                                            <div class="flex flex-col md:flex-row md:items-center gap-4 text-xs font-medium text-gray-700">

                                                <!-- Kurikulum -->
                                                <div class="flex items-center gap-2">
                                                    <span class="bg-gray-100 px-3 py-1 rounded-lg wrap-break-word">
                                                        ${item.kurikulum?.nama_kurikulum ?? '-'}
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400 text-[10px] hidden md:inline"></i>
                                                </div>

                                                <!-- Bab -->
                                                <div class="flex items-center gap-2">
                                                    <span class="bg-gray-100 px-3 py-1 rounded-lg wrap-break-word">
                                                        ${item.bab?.nama_bab ?? '-'}
                                                    </span>
                                                    <i class="fas fa-chevron-right text-gray-400 text-[10px] hidden md:inline"></i>
                                                </div>

                                                <!-- Sub Bab -->
                                                <div>
                                                    <span class="bg-gray-100 px-3 py-1 rounded-lg wrap-break-word">
                                                        ${item.sub_bab?.sub_bab ?? '-'}
                                                    </span>
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Source -->
                                        <div>
                                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">
                                                Sumber Soal
                                            </p>

                                            <div class="text-sm text-gray-700 bg-gray-50 px-3 py-2 rounded-lg border border-gray-300">
                                                ${item.school_partner_id ? item.school_partner?.nama_sekolah : 'belajarcerdas.id'}
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        `;
                    });

                    tbodyQuestionBankList.appendChild(row);
                });

                restoreCheckedState();
                updateSelectedCount();

                $('.thead-table-question-bank-list').show();
                $('#empty-message-question-bank-list').hide();
            } else {
                $('.thead-table-question-bank-list').hide();
                $('#empty-message-question-bank-list').show();
                calculateTotal();
            }

            questionVisibilityInfo();

        },
        error: function (err) {
            console.log(err);
        }
    });
}

$(document).ready(function () {
    formQuestionForRelease();
});

// FILTERING TAHUN AJARAN
$(document).on('change', '#dropdown-filter-tahun-ajaran', function () {
    selectedAssessmentId = null;

    formQuestionForRelease(
        $(this).val(), null, $('#dropdown-assessment-type').val(),
        $('#dropdown-filter-mapel').val(), $('#dropdown-filter-semester').val(), $('#search_question').val()); // null supaya auto pilih kelas paling rendah
});

// FILTERING ROMBEL KELAS
$(document).on('change', '#dropdown-filter-class', function () {
    selectedAssessmentId = null;

    formQuestionForRelease(
        $('#dropdown-filter-tahun-ajaran').val(), $(this).val(), $('#dropdown-assessment-type').val(),
        $('#dropdown-filter-mapel').val(), $('#dropdown-filter-semester').val(), $('#search_question').val());
});

// FILTERING ASSESSMENT TPE
$(document).on('change', '#dropdown-assessment-type', function () {
    selectedAssessmentId = null;

    formQuestionForRelease(
        $('#dropdown-filter-tahun-ajaran').val(), null, $(this).val(),
        $('#dropdown-filter-mapel').val(), $('#dropdown-filter-semester').val(), $('#search_question').val());
});

// FILTERING MAPEL
$(document).on('change', '#dropdown-filter-mapel', function () {
    selectedAssessmentId = null;
    
    formQuestionForRelease(
        $('#dropdown-filter-tahun-ajaran').val(), null, $('#dropdown-assessment-type').val(),
        $(this).val(), $('#dropdown-filter-semester').val(), $('#search_question').val());
});

// FILTERING SEMESTER
$(document).on('change', '#dropdown-filter-semester', function () {
    selectedAssessmentId = null;
    
    formQuestionForRelease(
        $('#dropdown-filter-tahun-ajaran').val(), null, $('#dropdown-assessment-type').val(),
        $('#dropdown-filter-mapel').val(), $(this).val(), $('#search_question').val());
});

// FILTERING SEARCH QUESTION
$(document).on('input', '#search_question', function () {
    formQuestionForRelease(
        $('#dropdown-filter-tahun-ajaran').val(), null, $('#dropdown-assessment-type').val(),
        $('#dropdown-filter-mapel').val(), $('#dropdown-filter-semester').val(), $(this).val()
    );
});

// FILTERING CURRICULUM CORE
$(document).on('change', '#id_kurikulum, #id_kelas, #id_mapel, #id_bab, #id_sub_bab', function () {
    formQuestionForRelease(
        $('#dropdown-filter-tahun-ajaran').val(),
        $('#dropdown-filter-class').val(),
        $('#dropdown-assessment-type').val(),
        $('#dropdown-filter-mapel').val(),
        $('#dropdown-filter-semester').val(),
        $('#search_question').val(),
        $('#id_kurikulum').val(),
        $('#id_kelas').val(),
        $('#id_mapel').val(),
        $('#id_bab').val(),
        $('#id_sub_bab').val()
    );

    // Hapus semua input hidden question
    selectedQuestions.clear();
    syncHiddenInputs();
    updateSelectedCount();
});

$(document).on('change', '.school-assessment-checkbox', function () {
    selectedAssessmentId = $(this).val();
});

// FUNCTION UPDATE SELECTED COUNT
function updateSelectedCount() {

    // ambil dari Set
    const totalSelected = selectedQuestions.size;

    // Checkbox yang sedang terlihat di DOM
    const visibleCheckboxes = document.querySelectorAll('.question-checkbox');
    const visibleChecked = document.querySelectorAll('.question-checkbox:checked');

    // Cek apakah semua checkbox yang terlihat sudah dicentang
    const allVisibleSelected = visibleCheckboxes.length > 0 && visibleCheckboxes.length === visibleChecked.length;

    document.querySelector('.question-all-checkbox').checked = allVisibleSelected;

    if (totalSelected > 0) {
        $('#total-selected').html(`
            <div class="flex items-center gap-1">
                <span>${totalSelected} Total aktif</span>
            </div>
        `);

        $('#error-question_id').text('');
    } else {
        $('#total-selected').html(`
            <div class="flex items-center gap-1">
                <span>0 Total aktif</span>
            </div>
        `);
    }

    calculateTotal();
}

// FUNCTION STRIP HTML
function stripHtmlAndLimit(html, limit = 120) {
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = html;

    // Hapus image
    tempDiv.querySelectorAll("img").forEach(img => img.remove());

    let text = tempDiv.textContent || tempDiv.innerText || "";
    text = text.trim();

    if (text.length > limit) {
        text = text.substring(0, limit) + "...";
    }

    return text;
}

// FUNCTION QUESTION VISIBILITY INFO
function questionVisibilityInfo() {
    const visibleCheckboxes = document.querySelectorAll('.question-checkbox');
    let visibleSelectedCount = 0;

    visibleCheckboxes.forEach(cb => {
        const id = String(cb.dataset.questionId);
        if (selectedQuestions.has(id)) {
            visibleSelectedCount++;
        }
    });

    const totalSelected = selectedQuestions.size;

    $('#question-visibility-info').text(`Menampilkan ${visibleCheckboxes.length} soal`);

    if (totalSelected > 0) {
        $('#selected-detail')
            .removeClass('hidden')
            .text(`(${visibleSelectedCount} aktif di halaman ini)`);
    } else {
        $('#selected-detail').addClass('hidden');
    }
}

// Event listener tombol "preview question" (open modal)
$(document).off('click', '.btn-preview-question').on('click', '.btn-preview-question', function (e) {
    e.preventDefault();

    // buka modal
    const modal = document.getElementById('my_modal_1');
    if (modal) modal.showModal();

    setTimeout(() => {
        document.querySelectorAll('.matching-container').forEach(container => {
            const pairs = JSON.parse(container.dataset.pairs || '[]');
            drawMatchingLines(container, pairs);
        });
    }, 270);
});


let isProcessing = false;

// Form Action assessment management
$('#submit-button-publish-question-for-release, #submit-button-draft-question-for-release').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-form-teacher-question-bank-for-release');
    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!role || !schoolName || !schoolId) return;

    const status = $(this).data('status'); // draft / publish
    const isActive = status;

    const form = $('#teacher-create-question-bank-for-release-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    formData.append('status', isActive);

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-question-bank-for-release/store`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-create-question-for-release').html(`
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

            // RESET SEMUA
            $('#teacher-create-question-bank-for-release-form')[0].reset();

            isProcessing = false;
            btn.prop('disabled', false);

            selectedQuestions.clear();

            // kalau ada hidden sync
            syncHiddenInputs?.();

            // kalau ada counter
            updateSelectedCount?.();

            // kalau ada total weight
            calculateTotal?.();
            formQuestionForRelease();
            paginateQuestionForRelease();
        },
        error: function (xhr) {

            if (xhr.status === 422) {

                const errors = xhr.responseJSON.errors;

                $.each(errors, function (field, messages) {

                    if (field.startsWith('question_weight.')) {

                        const questionId = field.split('.')[1];

                        const input = document.querySelector(
                            `.question-weight-input[data-question-id="${questionId}"]`
                        );

                        const errorSpan = document.getElementById(
                            `error-question_weight_${questionId}`
                        );

                        if (input) {
                            input.classList.add('border-red-400');
                        }

                        if (errorSpan) {
                            errorSpan.classList.remove('hidden');
                            errorSpan.textContent = messages[0];
                        }

                        return; // skip default handler
                    }

                    // Tampilkan pesan error
                    $('#teacher-create-question-bank-for-release-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#teacher-create-question-bank-for-release-form').find(`[name="${field}"]`).addClass('border-red-400 border');

                });

                if (errors.school_assessment_id) {
                    $('#error-school_assessment_id').removeClass('hidden').text(errors.school_assessment_id[0]);
                }

                if (errors.question_id) {
                    $('#error-question_id').removeClass('hidden').text(errors.question_id[0]);
                }

                if (errors.total_weight) {
                    $('#error-total_weight').removeClass('hidden').text(errors.total_weight[0]);
                }

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});