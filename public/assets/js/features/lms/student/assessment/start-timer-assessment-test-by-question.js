let durationInterval = null;
function startQuestionTimer(soalId) {

    currentQuestionId = soalId;

    const savedDuration = localStorage.getItem('duration_' + soalId);

    if (savedDuration) {
        questionDurations[soalId] = parseInt(savedDuration);
    } else if (!questionDurations[soalId]) {
        questionDurations[soalId] = 0;
    }

    questionStartTime = Date.now();

    if (durationInterval) {
        clearInterval(durationInterval);
    }

    durationInterval = setInterval(() => {

        if (!questionStartTime) return;

        const now = Date.now();
        const duration = Math.ceil((now - questionStartTime) / 1000);

        const examDuration = getTotalExamDuration();

        const totalDuration = Math.min(
            questionDurations[currentQuestionId] + duration,
            examDuration
        );

        $('#answer_duration').val(totalDuration);

    }, 1000);
}

function stopQuestionTimer() {

    if (durationInterval) {
        clearInterval(durationInterval);
        durationInterval = null;
    }

    currentQuestionId = null;
    questionStartTime = null;
}

function saveQuestionDuration() {

    if (!questionStartTime || !currentQuestionId) return;

    const now = Date.now();
    const duration = Math.ceil((now - questionStartTime) / 1000);

    const examDuration = getTotalExamDuration();

    const totalDuration = Math.min(
        questionDurations[currentQuestionId] + duration,
        examDuration
    );

    questionDurations[currentQuestionId] = totalDuration;

    localStorage.setItem(
        'duration_' + currentQuestionId,
        totalDuration
    );

    $('#answer_duration').val(totalDuration);

    questionStartTime = now;
}

function resetAllQuestionDurations() {

    Object.keys(localStorage).forEach(key => {
        if (key.startsWith('duration_')) {
            localStorage.removeItem(key);
        }
    });

    for (let key in questionDurations) {
        delete questionDurations[key];
    }

    $('#answer_duration').val(0);

}