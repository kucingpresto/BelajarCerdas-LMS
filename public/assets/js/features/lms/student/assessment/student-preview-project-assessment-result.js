function previewStudentFile(fileUrl) {

    const container = document.getElementById('student-file-preview-content');

    container.innerHTML = `
            <iframe 
                src="${fileUrl}" 
                class="w-full h-[70vh] rounded-lg border">
            </iframe>
        `;

    document.getElementById('student-file-preview-modal').showModal();
}