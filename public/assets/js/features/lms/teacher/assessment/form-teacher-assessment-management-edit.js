let isProcessing = false;

// Form Action edit content
$('#submit-button-edit-assessment').on('click', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-edit-assessment');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;

    if (!role || !schoolName || !schoolId || !assessmentId) return;

    const form = $('#edit-assessment-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/teacher-assessment-management/${assessmentId}/edit`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-edit-assessment').html(`
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

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                console.log(xhr.responseJSON.errors);

                $.each(errors, function (field, messages) {
                    // Tampilkan pesan error
                    $('#edit-assessment-form').find(`#error-${field}`).text(messages[0]);

                    // Tambahkan style error ke input (jika ada)
                    $('#edit-assessment-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

$(document).ready(function () {
    showData();
    enableFlatpickrEdit();
});

function showData() {
    const container = document.getElementById('container-edit-assessment');
    if (!container) return;

    const title = container.dataset.title;
    const semester = container.dataset.semester;
    const duration = container.dataset.duration;
    const startDate = container.dataset.startDate;
    const endDate = container.dataset.endDate;
    const instruction = container.dataset.instruction;

    const shuffleQuestions = container.dataset.shuffleQuestions === '1';
    const shuffleOptions = container.dataset.shuffleOptions === '1';
    const showScore = container.dataset.showScore === '1';
    const showAnswer = container.dataset.showAnswer === '1';

    if (shuffleQuestions) {
        document.getElementById('edit-shuffle-questions').checked = shuffleQuestions;
    }
    
    if (shuffleOptions) {
        document.getElementById('edit-shuffle-options').checked = shuffleOptions;
    }
    
    if (showScore) {
        document.getElementById('edit-show-score').checked = showScore;
    } 
    
    if (showAnswer) {
        document.getElementById('edit-show-answer').checked = showAnswer;
    }

    document.getElementById('edit-assessment-title').value = title;
    document.getElementById('edit-semester').value = semester;
    document.getElementById('edit-start-date').value = startDate;
    document.getElementById('edit-end-date').value = endDate;
    
    if (duration) {
        document.getElementById('edit-duration').value = duration;
    }
    
    if (instruction) {
        document.getElementById('edit-assessment-instruction').value = instruction;
    }
}

function enableFlatpickrEdit() {
    const startInput = document.getElementById('edit-start-date');
    const endInput = document.getElementById('edit-end-date');
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
    if (durationInput) {
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
}

function disableFlatpickr(el) {
    if (el._flatpickr) {
        el._flatpickr.destroy();
    }
}