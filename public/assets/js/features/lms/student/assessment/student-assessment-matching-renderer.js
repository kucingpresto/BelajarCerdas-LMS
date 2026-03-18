// RESET STATE
selectedLeft = null;

window.addEventListener('resize', () => {
    drawStudentMatchingLines();
    drawCorrectMatchingLines();
});

$(document).off('click', '.matching-left').on('click', '.matching-left', function () {

    const status = $('#status_answer').val();
    if (status === 'submitted') return;

    $('.matching-left').removeClass('bg-blue-100 border-blue-500');
    $(this).addClass('bg-blue-100 border-blue-500');

    selectedLeft = $(this).data('key');
});

$(document).off('click', '.matching-right').on('click', '.matching-right', function () {

    const status = $('#status_answer').val();
    if (status === 'submitted') return;

    if (!selectedLeft) return;

    const rightKey = $(this).data('key');

    // Ambil huruf (A, B, C)
    const rightLabel = $(this).find('.match-letter').text().trim().replace('.', '');

    // Simpan pasangan
    studentPairs[selectedLeft] = rightKey;

    // Update label di kiri
    const leftElement = $(`.matching-left[data-key="${selectedLeft}"]`);
    leftElement.find('.match-label').text(rightLabel);

    selectedLeft = null;

    $('.matching-left').removeClass('bg-blue-100 border-blue-500');

    drawStudentMatchingLines();

    const soalId = $('#assessment-test-submit-form input[name="school_assessment_question_id"]').val();

    // simpan sebagai JSON
    $(`#userAnswer${soalId}`).val(JSON.stringify(studentPairs));
});

function drawStudentMatchingLines() {

    const container = Array.from(document.querySelectorAll('.matching-container')).find(el => el.offsetParent !== null);
    if (!container) return;

    const svg = container.querySelector('.matching-lines');
    svg.innerHTML = '';

    const cRect = container.getBoundingClientRect();

    Object.entries(studentPairs).forEach(([leftKey, rightKey]) => {

        const leftEl = container.querySelector(`[data-key="${leftKey}"]`);
        const rightEl = container.querySelector(`[data-key="${rightKey}"]`);

        if (!leftEl || !rightEl) return;

        const l = leftEl.getBoundingClientRect();
        const r = rightEl.getBoundingClientRect();

        const x1 = l.right - cRect.left;
        const y1 = l.top + l.height / 2 - cRect.top;

        const x2 = r.left - cRect.left;
        const y2 = r.top + r.height / 2 - cRect.top;

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

        path.setAttribute(
            'd',
            `M ${x1} ${y1} L ${x2} ${y2}`
        );

        path.setAttribute('stroke', '#0071BC');
        path.setAttribute('stroke-width', '3');
        path.setAttribute('fill', 'none');
        path.setAttribute('stroke-linecap', 'round');

        svg.appendChild(path);
    });
}

function drawCorrectMatchingLines() {

    const containers = document.querySelectorAll('.matching-container');

    containers.forEach(container => {

        // hanya container yang punya .left-item (correct answer)
        if (!container.querySelector('.left-item')) return;

        const svg = container.querySelector('.matching-lines');
        if (!svg) return;

        svg.innerHTML = '';

        const cRect = container.getBoundingClientRect();

        const leftItems = container.querySelectorAll('.left-item');

        leftItems.forEach(leftEl => {

            const arrowSpan = leftEl.querySelector('span:last-child');
            const label = arrowSpan?.textContent.trim();

            if (!label || label === '-') return;

            const rightEl = Array.from(container.querySelectorAll('.right-item'))
                .find(r => r.querySelector('span').textContent.trim().replace('.', '') === label);

            if (!rightEl) return;

            const l = leftEl.getBoundingClientRect();
            const r = rightEl.getBoundingClientRect();

            const x1 = l.right - cRect.left;
            const y1 = l.top + l.height / 2 - cRect.top;

            const x2 = r.left - cRect.left;
            const y2 = r.top + r.height / 2 - cRect.top;

            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');

            path.setAttribute('d', `M ${x1} ${y1} L ${x2} ${y2}`);
            path.setAttribute('stroke', '#16A34A');
            path.setAttribute('stroke-width', '3');
            path.setAttribute('fill', 'none');
            path.setAttribute('stroke-linecap', 'round');

            svg.appendChild(path);
        });
    });
}