$('#submit-button-bulkUpload-subject-passing-grade-criteria').on('click', function (e) {
    e.preventDefault();
    const container = document.getElementById('container');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    if (isProcessing) return; // abaikan jika sedang proses

    isProcessing = true; // tandai sedang proses

    const form = $('#subject-passing-grade-criteria-bulkUpload-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    const btn = $(this);
    btn.prop('disabled', true); // Disable button UI

    $.ajax({
        url: `/lms/school-subscription/${schoolName}/${schoolId}/subject-passing-grade-criteria-management/bulkupload-store`,
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            const modal = document.getElementById('my_modal_3');

            if (modal) {
                modal.close();

                $('#alert-success-import-bulkUpload').html(`
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
            }

            // Reset form
            $('#subject-passing-grade-criteria-bulkUpload-form')[0].reset();
            $('#excelPreviewContainer-bulkUpload-excel').addClass('hidden');
            $('#textPreview-bulkUpload-excel').text('');
            $('#textSize-bulkUpload-excel').text('');
            $('#textPages-bulkUpload-excel').text('');
            $('#textCircle-bulkUpload-excel').html('');
            $('#logo-bulkUpload-excel img').attr('src', '').hide();

            setTimeout(function () {
                $('#alertSuccess').remove();
            }, 3000);

            $('#btnClose').on('click', function () {
                $('#alertSuccess').remove();
            });

            paginateSubjectPassingGradeCriteria();

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;

                // error validation form dan bulkUpload
                const formErrors = response.errors.form_errors ?? {};
                const excelErrors = response.errors.excel_validation_errors ?? [];

                let errorList = '';

                $.each(formErrors, function (field, messages) {
                    $(`#error-${field}`).text(messages[0]);
                    $(`[name="${field}"]`).addClass('border-red-400 border-2');
                });

                if (excelErrors.length > 0) {
                    excelErrors.forEach(err => {
                        errorList += `<li class="text-sm">${err}</li>`;
                    });

                    const html = `
                            <ul class="text-red-500 text-sm list-disc pl-5">
                                ${errorList}
                            </ul>
                        `;

                    const showError = `
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 my-2 h-max rounded">
                                <span class="font-bold text-sm">Terjadi Kesalahan :</span>
                                ${html}
                            </div>
                        `;

                    $('#error-bulkUpload').html(showError);
                    my_modal_3.showModal();
                }
            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }

    });
});
