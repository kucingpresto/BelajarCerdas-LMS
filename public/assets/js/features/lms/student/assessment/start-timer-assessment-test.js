let countdown = null;
let finalExamDuration = 0;
let examFinished = false;
let cheatingListenerAttached = false;
let attemptStatusChecked = false;
let lastCheatReport = 0;
let isPageReloading = false;
let antiCheatCooldown = true;

// function untuk menampilkan modal jika waktu habis
function emptyTime() {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Maaf, waktu ujian kamu sudah habis.',
    });
}


function startTimer() {
    if (!containerFormAssessment) return;
    if (countdown !== null) return;

    const timerExam = document.getElementById('timer-assessment-test');
    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const EXPIRE_KEY = `timer_assessment_test_expire_${assessmentId}`;

    const expireTime = localStorage.getItem(EXPIRE_KEY);

    if (expireTime) {
        const remaining = Math.floor((parseInt(expireTime) - Date.now()) / 1000);
        if (remaining > 0) {
            runCountdown(remaining);
        } else {

            clearInterval(countdown);
            countdown = null;

            timerExam.textContent = 'Waktu Habis';

            finalExamDuration = getTotalExamDuration();

            saveQuestionDuration();

            emptyTime();

            autoSubmitUnSavedQuestions();

            localStorage.removeItem(EXPIRE_KEY);
            localStorage.removeItem(START_KEY);
        }
    } else {
        startNewCountdown();
    }

    function startNewCountdown() {
        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/start-timer`,
            method: 'GET',
            success: function (response) {
                const startTime = response.start_time;
                const expireTime = response.expire_time;

                localStorage.setItem(START_KEY, startTime);
                localStorage.setItem(EXPIRE_KEY, expireTime);

                const remaining = Math.ceil((expireTime - Date.now()) / 1000);

                runCountdown(remaining);
            }
        });
    }

    function runCountdown(seconds) {
        updateTimerDisplay(seconds);

        countdown = setInterval(() => {
            seconds--;
            updateTimerDisplay(seconds);

            if (seconds <= 0 && !examFinished) {
                examFinished = true;

                clearInterval(countdown);
                countdown = null;

                timerExam.textContent = 'Waktu Habis';

                // simpan durasi terakhir soal yang sedang dibuka
                saveQuestionDuration();

                finalExamDuration = getTotalExamDuration();

                emptyTime();

                // auto submit dulu
                autoSubmitUnSavedQuestions();

                localStorage.removeItem(START_KEY);
                localStorage.removeItem(EXPIRE_KEY);
            }
        }, 1000);
    }

    function updateTimerDisplay(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        timerExam.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
}


function stopTimer() {
    clearInterval(countdown);
    countdown = null;

    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const EXPIRE_KEY = `timer_assessment_test_expire_${assessmentId}`;

    const startTime = parseInt(localStorage.getItem(START_KEY));
    const expireTime = parseInt(localStorage.getItem(EXPIRE_KEY));
    if (!startTime || !expireTime) return;

    const totalDuration = Math.floor((expireTime - startTime) / 1000);
    const usedDuration = Math.floor((Date.now() - startTime) / 1000);
    const finalUsed = Math.min(usedDuration, totalDuration);

    const hours = Math.floor(finalUsed / 3600);
    const minutes = Math.floor((finalUsed % 3600) / 60);
    const seconds = finalUsed % 60;

    const formatted = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

function getTotalExamDuration() {

    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const EXPIRE_KEY = `timer_assessment_test_expire_${assessmentId}`;

    const startTime = parseInt(localStorage.getItem(START_KEY));
    const expireTime = parseInt(localStorage.getItem(EXPIRE_KEY));

    if (!startTime || !expireTime) return 0;

    const totalDuration = Math.floor((expireTime - startTime) / 1000);
    const remaining = Math.max(0, Math.floor((expireTime - Date.now()) / 1000));

    const usedDuration = totalDuration - remaining;

    return usedDuration;
}


function cheatingDetection() {

    if (cheatingListenerAttached) return;

    cheatingListenerAttached = true;

    document.addEventListener("visibilitychange", function () {

        if (examFinished) return;
        if (isPageReloading) return;

        if (document.hidden) {
            reportCheating();
        }

    });

}

function checkAttemptStatus() {

    if (attemptStatusChecked) return;

    attemptStatusChecked = true;

    if (examFinished) return;
    if (isPageReloading) return;
    if (antiCheatCooldown) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/attempt-status`,
        method: 'GET',
        success: function (res) {

            if (examFinished) return;

            if (res.status === 'warning') {

                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: `Kamu terdeteksi meninggalkan halaman ujian, batas kesempatan (${res.count}/3)`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        examStarted = true;
                        enterFullscreen();
                    }
                });

            }

            if (res.status === 'blocked') {

                examFinished = true;

                finalExamDuration = getTotalExamDuration();

                saveQuestionDuration();

                stopTimer();
                stopQuestionTimer();

                Swal.fire({
                    icon: 'error',
                    title: 'Ujian dihentikan',
                    text: 'Terlalu sering meninggalkan halaman.'
                });

                autoSubmitUnSavedQuestions();
            }

        }
    });

}

function enterFullscreen() {

    const el = document.documentElement;

    if (el.requestFullscreen) {
        el.requestFullscreen();
    }
    else if (el.webkitRequestFullscreen) { // Safari
        el.webkitRequestFullscreen();
    }
    else if (el.msRequestFullscreen) { // IE
        el.msRequestFullscreen();
    }

}

function detectFullscreenExit() {

    document.addEventListener("fullscreenchange", function () {

        if (examFinished) return;
        if (isPageReloading) return;
        if (antiCheatCooldown) return;

        if (!document.fullscreenElement) {
            reportCheating();
        }

    });

}

function detectKeyboardCheating() {

    document.addEventListener("keydown", function (e) {

        if (examFinished) return;
        if (isPageReloading) return;

        // CTRL + T
        if (e.ctrlKey && e.key === "t") {
            e.preventDefault();
            reportCheating();
        }

        // CTRL + W
        if (e.ctrlKey && e.key === "w") {
            e.preventDefault();
            reportCheating();
        }

        // CTRL + TAB
        if (e.ctrlKey && e.key === "Tab") {
            e.preventDefault();
            reportCheating();
        }

        // ALT + TAB
        if (e.altKey && e.key === "Tab") {
            reportCheating();
        }

        // F11
        if (e.key === "F11") {
            e.preventDefault();
            reportCheating();
        }

        // ESC
        if (e.key === "Escape") {
            reportCheating();
        }

        // CTRL + C
        if (e.ctrlKey && e.key === "c") {
            e.preventDefault();
        }

        // CTRL + V
        if (e.ctrlKey && e.key === "v") {
            e.preventDefault();
        }

        // CTRL + X
        if (e.ctrlKey && e.key === "x") {
            e.preventDefault();
        }

    });

}

function disableCopyPaste() {

    document.addEventListener("copy", function (e) {
        e.preventDefault();
    });

    document.addEventListener("paste", function (e) {
        e.preventDefault();
    });

    document.addEventListener("cut", function (e) {
        e.preventDefault();
    });

}

function disableRightClick() {
    document.addEventListener("contextmenu", function (e) {
        e.preventDefault();
    });
}

function disableTextSelection() {
    document.body.style.userSelect = "none";
}

function detectWindowBlur() {

    window.addEventListener("blur", function () {

        if (examFinished) return;
        if (isPageReloading) return;

        // jika tab memang disembunyikan, sudah ditangani visibilitychange
        if (document.hidden) return;

        reportCheating();

    });

}

function enforceFullscreenAfterReload() {

    if (examFinished) return;

    const START_KEY = `timer_assessment_test_start_${assessmentId}`;
    const examWasStarted = localStorage.getItem(START_KEY);

    // jika ujian belum dimulai, jangan paksa fullscreen
    if (!examWasStarted) return;

    // jika sudah fullscreen, tidak perlu apa-apa
    if (document.fullscreenElement) return;

    Swal.fire({
        icon: 'warning',
        title: 'Mode Fullscreen Wajib',
        text: 'Ujian harus dilakukan dalam mode fullscreen.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonText: 'Masuk Fullscreen'
    }).then(() => {
        enterFullscreen();
    });

}

function reportCheating() {

    if (examFinished) return;
    if (isPageReloading) return;
    if (antiCheatCooldown) return;

    const now = Date.now();

    // cooldown 1 detik supaya tidak double trigger
    if (now - lastCheatReport < 1000) {
        return;
    }

    lastCheatReport = now;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/report-tab-switch`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (res) {

            if (examFinished) return;

            if (res.status === 'warning') {

                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan!',
                    text: `Kamu terdeteksi meninggalkan halaman ujian, batas kesempatan (${res.count}/3)`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        examStarted = true;
                        enterFullscreen();
                    }
                });

            }

            if (res.status === 'blocked') {

                examFinished = true;

                finalExamDuration = getTotalExamDuration();

                saveQuestionDuration();

                stopTimer();
                stopQuestionTimer();

                Swal.fire({
                    icon: 'error',
                    title: 'Ujian dihentikan',
                    text: 'Terlalu sering meninggalkan halaman.'
                });

                autoSubmitUnSavedQuestions();
            }

        }
    });

}

function initAntiCheatSystem() {

    cheatingDetection(); // visibility change
    detectFullscreenExit(); // keluar fullscreen
    detectKeyboardCheating(); // shortcut
    detectWindowBlur(); // alt tab

    disableCopyPaste();
    disableRightClick();
    disableTextSelection();

    enforceFullscreenAfterReload();
}