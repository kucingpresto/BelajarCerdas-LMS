let currentQuestionIndex = 0;
const editorInstances = [];
const previousImageUrlsMap = {};

const containerFormAssessment = $('#container-assessment-test-form');
const role = containerFormAssessment.data('role');
const schoolName = containerFormAssessment.data('school-name');
const schoolId = containerFormAssessment.data('school-id');
const curriculumId = containerFormAssessment.data('curriculum-id');
const mapelId = containerFormAssessment.data('mapel-id');
const assessmentTypeId = containerFormAssessment.data('assessment-type-id');
const semester = containerFormAssessment.data('semester');
const assessmentId = containerFormAssessment.data('assessment-id');

const START_KEY = `timer_assessment_test_start_${assessmentId}`;

const questionDurations = {};
let currentQuestionId = null;
let questionStartTime = null;
let questions = [];
let questionsAnswer = {};
let examStarted = false;

function studentFormAssessment(selectedIndex = 0) {

    if (!containerFormAssessment.length) return;

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}`,
        method: 'GET',
        success: function (response) {

            const formAssessment = $('#form-assessment-test');
            formAssessment.empty();

            const now = new Date(); // waktu lokal user
            const start = parseLocalDateTime(response.start_date);
            const end = parseLocalDateTime(response.end_date);

            const isBefore = now < start;
            const isActive = now >= start && now <= end;
            const isAfter = now > end;

            function formatAssessmentDate(start, end) {

                const days = [
                    'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
                ];

                const months = [
                    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                ];

                const startDate = new Date(start);
                const endDate = new Date(end);

                const day = days[startDate.getDay()];
                const date = startDate.getDate();
                const month = months[startDate.getMonth()];
                const year = startDate.getFullYear();

                const startTime = startDate.toTimeString().slice(0, 5);
                const endTime = endDate.toTimeString().slice(0, 5);

                return {
                    date: `${day}, ${date} ${month} ${year}`,
                    time: `${startTime} - ${endTime}`
                };
            }

            const formatted = formatAssessmentDate(response.start_date, response.end_date);

            $('#assessment-date').text(formatted.date);
            $('#assessment-time').text(formatted.time);
            $('#assessment-title').text(response.assessment_title + ' - Semester ' + response.semester);

            // Mendapatkan data soal
            questions = response.data;

            // Mendapatkan data jawaban
            questionsAnswer = response.questionsAnswer;

            const question = questions[selectedIndex];
            const questionType = question?.lms_question_bank?.tipe_soal?.toLowerCase();
            const showScore = response.schoolAssessment.show_score ?? false;
            const showAnswer = response.schoolAssessment.show_answer ?? false;

            if (question) {
                const totalSoal = questions.length;
    
                let jumlahSoalTerjawab = Object.values(questionsAnswer).filter(q => q.status_answer === 'submitted').length;
    
                // Cek apakah semua soal sudah dijawab
                const isAllAnswered = jumlahSoalTerjawab === totalSoal;
    
                // Helper tambah class img
                function addClassToImgTags(html, className) {
                    return html
                        .replace(/<img\b(?![^>]*class=)[^>]*>/g, (imgTag) => {
                            return imgTag.replace('<img', `<img class="${className}"`);
                        })
                        .replace(/<img\b([^>]*?)class="(.*?)"/g, (imgTag, before, existingClasses) => {
                            return `<img ${before}class="${existingClasses} ${className}"`;
                        });
                }
    
                // ===== GENERATE OPTIONS =====
                const generateOptions = (options = []) => {
    
                    if (!Array.isArray(options)) return '';
    
                    const optionKeys = ['A', 'B', 'C', 'D', 'E'];
    
                    const shuffleOptions = [...options];
    
                    return shuffleOptions.map((item, index) => {
    
                        const newKey = optionKeys[index] ?? '';
                        const containsImage = /<img\s+[^>]*src=/.test(item.options_value ?? '');
    
                        let content = item.options_value;
    
                        if (containsImage) {
                            content = addClassToImgTags(content, 'max-w-[300px] rounded my-2');
                        }
    
                        let statusClass = '';
    
                        const userAnswer = questionsAnswer[question.id]?.answer_value ?? [];
                        const correctOption = options.find(opt => opt.is_correct == 1);
                        const correctKey = correctOption?.options_key;
                        const correctKeys = options.filter(opt => opt.is_correct == 1).map(opt => opt.options_key);
    
                        if (isAllAnswered && showAnswer) {
                            if (questionsAnswer[question.id]?.status_answer === 'submitted') {
    
                                if (questionType === 'mcma') {
    
                                    if (correctKeys.includes(item.options_key) && userAnswer.includes(item.options_key)) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    }
                                    else if (!correctKeys.includes(item.options_key) && userAnswer.includes(item.options_key)) {
                                        statusClass = 'bg-red-200 text-red-700 font-bold';
                                    }
                                    else if (correctKeys.includes(item.options_key)) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    }
    
                                } else {
    
                                    if (userAnswer === correctKey && item.options_key === correctKey) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    } else if (userAnswer !== correctKey && item.options_key === userAnswer) {
                                        statusClass = 'bg-red-200 text-red-700 font-bold';
                                    } else if (item.options_key === correctKey) {
                                        statusClass = 'bg-green-200 text-green-700 font-bold';
                                    }
    
                                }
                            }
                        } else {
                            const answerValue = questionsAnswer[question.id]?.answer_value;
                            const status = questionsAnswer[question.id]?.status_answer;
    
                            if (status === 'submitted' || status === 'draft') {
                                if (questionType === 'mcma') {
                                    if (Array.isArray(answerValue) && answerValue.includes(item.options_key)) {
                                        statusClass = 'bg-gray-200 font-bold opacity-70';
                                    }
                                } else {
                                    if (answerValue === item.options_key) {
                                        statusClass = 'bg-gray-200 font-bold opacity-70';
                                    }
                                }
                            }
                        }
    
                        let optionsValue = '';
    
                        const inputType = questionType === 'mcma' ? 'checkbox' : 'radio';
    
                        // memeriksa apakah soal sudah dijawab oleh pengguna atau jawaban masih ditandai
                        if (!questionsAnswer[question.id] || questionsAnswer[question.id]?.status_answer === 'draft') {
                            if (containsImage) {
                                optionsValue = `
                                        <input type="${inputType}" name="options_value_${question.id}${questionType === 'mcma' ? '[]' : ''}" id="soal${question.id}_${item.options_key}" value="${item.options_key}" class="hidden peer" data-soal-id="${question.id}">
                                        <label for="soal${question.id}_${item.options_key}" class="border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 cursor-pointer checked-option ${statusClass}">
                                            <div class="font-bold min-w-7.5">${newKey}.</div>
                                            <div class="w-full flex flex-col gap-8 list-style">${item.options_value}</div>
                                        </label>
                                    `;
                            } else {
                                optionsValue = `
                                        <input type="${inputType}" name="options_value_${question.id}${questionType === 'mcma' ? '[]' : ''}" id="soal${question.id}_${item.options_key}" value="${item.options_key}" class="hidden" data-soal-id="${question.id}">
                                        <label for="soal${question.id}_${item.options_key}" class="list-style border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 cursor-pointer checked-option ${statusClass}">
                                            ${newKey}. ${item.options_value}
                                        </label>
                                    `;
                            }
                        } else if (questionsAnswer[question.id] && questionsAnswer[question.id]?.status_answer === 'submitted') {
                            if (containsImage) {
                                optionsValue = `
                                    <div class="border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 checked-option ${statusClass}">
                                        <div class="font-bold min-w-7.5">${newKey}.</div>
                                        <div class="w-full flex flex-col gap-8 list-style">${item.options_value}</div>
                                    </div>
                                `;
                            } else {
                                optionsValue = `
                                    <div class="list-style border border-gray-300 rounded-md p-3 sm:px-4 mb-4 text-sm flex gap-2 checked-option ${statusClass}">
                                        ${newKey}. ${item.options_value}
                                    </div>
                                `;
                            }
                        }
    
                        // Render opsi jawaban
                        return `
                            ${optionsValue}
                        `;
                    }).join('');
                };
    
                const generateEssay = () => {
                    const teacherFeedback = questionsAnswer[question.id]?.teacher_feedback ?? '';
                    const score = questionsAnswer[question.id]?.question_score ?? null;

                    let renderEssay = '';
    
                    if (!questionsAnswer[question.id] || questionsAnswer[question.id]?.status_answer === 'draft') {
                        renderEssay = `
                            <div class="space-y-3">
                                <textarea id="essay_${question.id}" name="essay_${question.id}" rows="6" class="editor w-full border border-gray-300 rounded-xl p-4 text-sm resize-none transition"
                                placeholder="Tulis jawaban kamu di sini...">${questionsAnswer[question.id]?.answer_value ?? ''}</textarea>
                            </div>
                        `;
                    } else if (questionsAnswer[question.id]?.status_answer === 'submitted') {
                        renderEssay = `
                            <div class="space-y-3">
                                <textarea id="essay_${question.id}" name="essay_${question.id}" rows="6" class="editor w-full border border-gray-300 rounded-xl p-4 text-sm resize-none transition"
                                placeholder="Tulis jawaban kamu di sini...">${questionsAnswer[question.id]?.answer_value ?? ''}</textarea>
                            </div>
                        `;
                    }

                    if (questionType === 'essay' && isAllAnswered && showAnswer && teacherFeedback) {

                        renderEssay += `
                            <div class="mt-6 p-5 rounded-2xl bg-purple-50 border border-purple-200">

                                <div class="flex items-start gap-3">

                                    <div class="text-purple-600 text-lg">
                                        <i class="fas fa-comment-dots"></i>
                                    </div>

                                    <div class="w-full">

                                        <h3 class="font-semibold text-purple-800 text-sm">
                                            Teacher Feedback
                                        </h3>

                                        <div class="text-sm text-purple-700 mt-2 leading-relaxed list-style">
                                            ${teacherFeedback}
                                        </div>

                                        ${score !== null ?
                                            `
                                                <div class="mt-3 text-sm font-semibold text-gray-700">
                                                    Score: ${score} / ${question.question_weight}
                                                </div>`
                                            : ''
                                        }

                                    </div>

                                </div>

                            </div>
                        `;
                    }
    
                    return renderEssay;
                };
    
                // Render Nomor Soal
                const nomorSoalHTML = questions.map((item, index) => {
    
                    let statusClassNumberQuestions = '';
                    const answer = questionsAnswer[item.id];
                    const itemType = item?.lms_question_bank?.tipe_soal?.toLowerCase();
    
                    // === ESSAY (manual grading) ===
                    if (itemType === 'essay') {
                        if (answer?.status_answer === 'submitted') {
                            statusClassNumberQuestions = '!bg-[#F79D65] text-white font-bold';
                        } else if (answer?.status_answer === 'draft') {
                            statusClassNumberQuestions = '!bg-[#F79D65] text-white font-bold';
                        }
    
                    } else {
                        if (isAllAnswered && showAnswer) {
                            if (answer?.status_answer === 'submitted') {
                                if (answer?.is_correct === true) {
                                    statusClassNumberQuestions = '!bg-green-200 text-green-600 font-bold';
                                }
                                else if (answer?.is_correct === false) {
                                    statusClassNumberQuestions = '!bg-red-200 text-red-600 font-bold';
                                }
                            }
    
                        } else {
                            if (answer?.status_answer === 'submitted') {
                                statusClassNumberQuestions = '!bg-[#0071BC] text-white font-bold';
                            }
                            else if (answer?.status_answer === 'draft') {
                                statusClassNumberQuestions = '!bg-[#FFC107] text-white font-bold';
                            }
                        }
                    }
    
                    return `                    
                        <input type="radio" id="nomor${index}" name="nomorSoal" class="hidden">
                        <label for="nomor${index}" 
                            class="nomor-soal border border-gray-300 rounded-lg py-1.5 hover:bg-gray-100 transition text-center cursor-pointer ${statusClassNumberQuestions}"
                            data-index="${index}">
                            <span class="font-bold">${index + 1}</span>
                        </label>
                    `;
                }).join('');
    
                function generateMatching(leftItems, rightItems) {
    
                    const rightLabelMap = {};
                    rightItems.forEach((item, index) => {
                        rightLabelMap[item.options_key] = String.fromCharCode(65 + index);
                    });
    
                    let correctAnswer = '';
    
                    if (isAllAnswered && showAnswer) {
                        correctAnswer = `
                            <div class="relative matching-container bg-green-50 border border-green-200 rounded-2xl p-6 mt-8 shadow-sm">
    
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-white">
                                        <i class="fa-solid fa-check text-sm"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-green-700">
                                        Jawaban Benar
                                    </h3>
                                </div>
    
                                <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines hidden lg:block"></svg>
    
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 relative z-10 mt-4">
    
                                    <!-- KOLOM A -->
                                    <div>
                                        <h4 class="font-semibold mb-4">Kolom A</h4>
                                        <div class="space-y-3">
                                            ${leftItems.map(item => `
                                                <div 
                                                    class="px-3 min-h-10 border rounded flex justify-between items-center left-item" data-key="${item.options_key}">
                                                    <span>${item.options_value}</span>
                                                    <span class="text-sm bg-blue-100 text-[#0071BC] px-2 py-1 rounded">
                                                        <i class="fa-solid fa-arrow-right"></i>
                                                        ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                                    </span>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
    
                                    <!-- KOLOM B -->
                                    <div>
                                        <h4 class="font-semibold mb-4">Kolom B</h4>
                                        <div class="space-y-3">
                                            ${rightItems.map(item => {
                                                const content = addClassToImgTags(item.options_value, 'max-w-[200px] rounded');
    
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
    
                            </div>
                        `;
                    }
    
                    return `
                        <div class="relative matching-container bg-white rounded-2xl shadow-md border border-gray-100 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-800">
                                    Jawaban Kamu
                                </h3>
                            </div>
    
                            <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines hidden lg:block"></svg>
    
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-20 relative z-10 ${isAllAnswered ? 'pointer-events-none' : ''}">
    
                                <!-- KOLOM A -->
                                <div>
                                    <h4 class="font-semibold mb-4">Kolom A</h4>
                                    <div class="space-y-3">
                                        ${leftItems.map(item => `
                                            <div class="matching-left p-3 flex items-center justify-between border rounded cursor-pointer hover:bg-blue-50"
                                                data-key="${item.options_key}">
                                                ${item.options_value}
    
                                                <div class="text-sm font-bold bg-blue-100 text-[#0071BC] px-2 py-1 rounded">
                                                    <span class="match-icon"><i class="fa-solid fa-arrow-right"></i></span>
                                                    <span class="match-label"> - </span>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
    
                                <!-- KOLOM B -->
                                <div>
                                    <h4 class="font-semibold mb-4">Kolom B</h4>
                                    <div class="space-y-3">
                                        ${rightItems.map((item, index) => `
                                            <div class="matching-right flex gap-2 p-3 border rounded cursor-pointer hover:bg-green-50"
                                                data-key="${item.options_key}">
                                                <span class="match-letter font-bold">${String.fromCharCode(65 + index)}.</span>
                                                ${item.options_value}
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
    
                            </div>
                        </div>
    
                        ${correctAnswer}
                    `;
                }

                function generatePgKompleks(options = []) {

                    if (!Array.isArray(options)) return '';

                    const categories = options.filter(item => item.extra_data?.side === 'category');
                    const items = options.filter(item => item.extra_data?.side === 'item');

                    let existingAnswer = questionsAnswer[question.id]?.answer_value || {};

                    if (typeof existingAnswer === 'string') {
                        try {
                            existingAnswer = JSON.parse(existingAnswer);
                        } catch (e) {
                            existingAnswer = {};
                        }
                    }

                    const isReviewMode = isAllAnswered && showAnswer;

                    return `
                        <div class="overflow-x-auto mt-6">

                            ${isReviewMode ? `
                                <div class="flex flex-wrap gap-4 text-xs mb-4">
                                    <span class="flex items-center gap-1 text-green-600 font-semibold">
                                        <i class="fa-solid fa-check"></i> Jawaban Benar
                                    </span>
                                    <span class="flex items-center gap-1 text-red-600 font-semibold">
                                        <i class="fa-solid fa-xmark"></i> Jawaban Salah
                                    </span>
                                    <span class="flex items-center gap-1 text-[#4189E0] font-semibold">
                                        <input type="radio" class="w-4 h-4" checked onclick="return false"> 
                                        Jawaban Kamu
                                    </span>
                                </div>
                            ` : ''}

                            <table class="w-full border border-gray-300 text-sm">
                                <thead>
                                    <tr class="bg-gray-100 text-center">
                                        <th class="border px-4 py-2">
                                            ${question.lms_question_bank?.header_item ?? 'Pernyataan'}
                                        </th>
                                        ${categories.map(category => `
                                            <th class="border px-4 py-2">
                                                ${category.options_value}
                                            </th>
                                        `).join('')}
                                    </tr>
                                </thead>

                                <tbody>
                                    ${items.map(item => {

                                        const correctAnswer = item.extra_data?.answer;
                                        const userAnswer = existingAnswer[item.options_key];

                                        let rowClass = '';
                                        if (isReviewMode) {
                                            rowClass = userAnswer === correctAnswer ? 'bg-green-50' : 'bg-red-50';
                                        }

                                        const content = addClassToImgTags(item.options_value, 'max-w-[200px] w-full rounded');

                                        return `
                                            <tr class="${rowClass}">
                                                <td class="border px-4 py-3">
                                                    ${content}
                                                </td>

                                                ${categories.map(cat => {

                                            const selected = userAnswer === cat.options_key;
                                            const isCorrect = correctAnswer === cat.options_key;

                                            let cellClass = '';
                                            let icon = '';
                                            let badge = '';

                                            if (isReviewMode) {

                                                // Jawaban benar & dipilih
                                                if (selected && isCorrect) {
                                                    cellClass += ' bg-green-100 border-green-400';
                                                    icon = '<i class="fa-solid fa-check text-green-600"></i>';
                                                    badge = '<span class="text-[10px] text-green-700">Jawabanmu</span>';

                                                // Jawaban salah
                                                } else if (selected && !isCorrect) {
                                                    cellClass += ' bg-red-100 border-red-400';
                                                    icon = '<i class="fa-solid fa-xmark text-red-600"></i>';
                                                    badge = '<span class="text-[10px] text-red-700">Jawabanmu</span>';

                                                // Kunci jawaban
                                                } else if (!selected && isCorrect) {
                                                    cellClass += ' bg-green-50 border-green-300';
                                                    icon = '<i class="fa-solid fa-check text-green-500"></i>';
                                                    badge = '<span class="text-[10px] text-green-600">Jawaban Benar</span>';
                                                }
                                            }

                                            return `
                                                <td class="border">
                                                    <div class="flex flex-col items-center justify-center gap-1 py-2 ${cellClass}">

                                                        <input type="radio" name="pg_kompleks_${item.options_key}" value="${cat.options_key}" class="w-4 h-4"
                                                            ${selected ? 'checked' : ''} ${isReviewMode ? 'onclick="return false"' : ''}>

                                                        ${isReviewMode ? `
                                                            <div class="flex flex-col items-center text-xs">
                                                                ${icon}
                                                                ${badge}
                                                            </div>
                                                        ` : ''}

                                                    </div>
                                                </td>
                                                `;
                                        }).join('')}
                                            </tr>
                                        `;
                                    }).join('')}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
    
                let submitAnswerType = '';
    
                const options = question?.lms_question_bank?.lms_question_option ?? [];
    
                const leftItems = options.filter(item => item.options_key.startsWith('LEFT'));
                const rightItems = options.filter(item => item.options_key.startsWith('RIGHT'));
    
                if (questionType === 'essay') {
                    submitAnswerType = generateEssay();
                } else if (questionType === 'matching') {
                    submitAnswerType = generateMatching(leftItems, rightItems);
                } else if (questionType === 'pg_kompleks') {
                    submitAnswerType = generatePgKompleks(question.lms_question_bank?.lms_question_option);
                } else {
                    submitAnswerType = generateOptions(question?.lms_question_bank?.lms_question_option);
                }
    
                const isAnswered = !!questionsAnswer[question.id] && questionsAnswer[question.id]?.status_answer === 'submitted';
    
                const isCorrect = !!questionsAnswer[question.id]?.is_correct; // jadikan boolean true or false
    
                let submitButtonAnswerHTML = '';
    
                submitButtonAnswerHTML = isAnswered
                    ? `
                        <button type="button" class="bg-gray-200 px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold opacity-70 cursor-default" disabled>
                            Simpan Jawaban
                        </button>
                    `
                    : `
                        <button type="button" id="btn-submit-save-answer" data-status-answer="submitted" class="bg-[#43AB3C] text-white px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold cursor-pointer">
                            Simpan Jawaban
                        </button>
                    `;
    
                let btnMarkAnswerHTML = '';
    
                btnMarkAnswerHTML = isAnswered
                    ? `
                        <button type="button" class="bg-gray-200 px-6 py-2.5 rounded-md
                            shadow-md hover:shadow-lg transition-all duration-200 text-sm font-semibold opacity-70 cursor-default" disabled>
                            Tandai Jawaban
                        </button>
                    `
                    : `
                        <button type="button" id="btn-submit-draft-answer" data-status-answer="draft" class="px-5 py-2.5 rounded-md text-sm font-semibold border
                            bg-[#FFC107] text-white shadow-md hover:shadow-lg transition cursor-pointer">
                            Tandai Jawaban
                        </button>
                    `;
    
                let buttonCorrectOrWrongHTML = '';
    
                if (questionType !== 'essay') {
                    buttonCorrectOrWrongHTML = isAllAnswered && showAnswer
                        ? (isCorrect
                            ? `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm text-center bg-green-200 text-green-600 font-bold rounded-md" disabled>Jawaban Benar</button>`
                            : `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm text-center bg-red-200 text-red-600 font-bold opacity-70 rounded-md" disabled>Jawaban Salah</button>`)
                        : `<button class="border border-gray-300 px-5 py-2.5 text-xs lg:text-sm font-semibold text-center bg-gray-200 opacity-70 rounded-md" disabled>Jawaban Benar/Salah</button>`;
                }
    
                // QUESTION SPLIT IMAGE
                const splitQuestions = question?.lms_question_bank?.questions.split('<img');
                const questionTextOnly = splitQuestions[0];
    
                let questionImage = '', textAfterImage = '';
    
                if (splitQuestions.length > 1) {
                    const imgSplit = splitQuestions[1].split('>'); // pisahkan tag <img> dan sisa teks
                    const imgTag = imgSplit[0]; // bagian src dan atribut gambar
                    const restText = imgSplit.slice(1).join('>'); // gabungkan sisa setelah tag img
    
                    questionImage = `<img class="w-full sm:max-w-[45%]" ${imgTag}>`; // Susun tag <img> lengkap dengan class tambahan
                    textAfterImage = restText.trim(); // Hapus spasi berlebih pada teks setelah gambar
                }
    
                // Gabungkan menjadi HTML: bungkus gambar dan teks
                const questionImageAndTextAfter = `
                    <div class="flex flex-col gap-4 items-start">
                        ${questionImage}
                        <div>${textAfterImage}</div>
                    </div>
                `;
    
                function generateModeInfo() {
    
                    if (isAfter && !isAllAnswered) {
                        return `
                            <div class="mb-6 p-5 rounded-2xl bg-red-50 border border-red-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-red-600 text-xl">
                                        <i class="fas fa-lock"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-red-800 text-sm">
                                            Asesmen Telah Selesai
                                        </h3>
                                        <p class="text-xs text-red-700 mt-1">
                                            Waktu pengerjaan telah berakhir. Jawaban tidak dapat diubah.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
    
                    // ===== CASE 1: Review Only =====
                    if (isAllAnswered && !showScore && showAnswer) {
                        return `
                            <div class="mb-6 p-5 rounded-2xl bg-yellow-50 border border-yellow-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-yellow-600 text-xl">
                                        <i class="fas fa-user-lock"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-yellow-800 text-sm">
                                            Mode Review Aktif
                                        </h3>
                                        <p class="text-xs text-yellow-700 mt-1">
                                            Nilai belum dirilis oleh guru. 
                                            Kamu dapat melihat kembali jawaban yang telah dikirimkan.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
    
                    // ===== CASE 2: Score Only =====
                    if (isAllAnswered && showScore && !showAnswer) {
                        return `
                            <div class="mb-6 p-5 rounded-2xl bg-blue-50 border border-blue-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-blue-600 text-xl">
                                        <i class="fas fa-chart-bar"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-blue-800 text-sm">
                                            Nilai Telah Dirilis
                                        </h3>
                                        <p class="text-xs text-blue-700 mt-1">
                                            Kamu dapat melihat hasil evaluasi, 
                                            tetapi tidak dapat mereview kembali detail jawaban.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
    
                    // ===== CASE 3: Full Access =====
                    if (isAllAnswered && showScore && showAnswer) {
                        return `
                            <div class="mb-6 p-5 rounded-2xl bg-green-50 border border-green-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-green-600 text-xl">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-green-800 text-sm">
                                            Hasil Telah Dipublikasikan
                                        </h3>
                                        <p class="text-xs text-green-700 mt-1">
                                            Kamu dapat melihat nilai dan detail jawaban.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
    
                    // ===== CASE 4: Locked =====
                    if (isAllAnswered) {
                        return `
                            <div class="mb-6 p-5 rounded-2xl bg-gray-100 border border-gray-200">
                                <div class="flex items-start gap-3">
                                    <div class="text-gray-600 text-xl">
                                        <i class="fas fa-hourglass-half"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 text-sm">
                                            Menunggu Publikasi
                                        </h3>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Hasil Asesmen belum tersedia.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
    
                    return '';
                }
    
                // tampilkan link result test ketika semua pertanyaan sudah dijawab
                let linkResultTest = '';
                const resultTestHref = response.resultTestHref.replace(':role', role).replace(':schoolName', schoolName).replace(':schoolId', schoolId).replace(':curriculumId', curriculumId)
                    .replace(':mapelId', mapelId).replace(':assessmentTypeId', assessmentTypeId).replace(':semester', semester).replace(':assessmentId', assessmentId);
    
                if (isAllAnswered || isAfter) {
                    linkResultTest = `
                        <div class="mt-10 pt-6 border-t border-gray-100">
                            <a href="${resultTestHref}" 
                            class="w-full flex items-center justify-center gap-3 bg-green-500 hover:bg-green-600 transition text-white text-sm font-semibold py-3 rounded-xl shadow-md">
                                <i class="fas fa-chart-line"></i>
                                Lihat Nilai Asesmen
                            </a>
                        </div>
                    `;
                }
    
                const form = `
                    <form id="assessment-test-submit-form">
                        <div class="max-w-450 mx-auto px-4 sm:px-6 lg:px-8 mt-6 lg:mt-10 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-14 items-stretch">
    
                            <!-- ================= LEFT ================= -->
                            <div class="lg:col-span-8 flex shadow-xl order-1 lg:order-0">
                                <div class="w-full bg-white rounded-2xl border border-gray-200 
                                    shadow-[0_8px_30px_rgba(0,0,0,0.04)] p-5 sm:p-6 lg:p-8 flex flex-col">
    
                                    ${generateModeInfo()}
    
                                    <!-- Header Soal -->
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-base font-semibold text-gray-800">
                                                Soal ${selectedIndex + 1}
                                            </span>
    
                                            <span class="text-[11px] sm:text-xs px-2.5 py-1 rounded-full bg-blue-50 text-blue-600 font-semibold">
                                                Bobot: ${question.question_weight}
                                            </span>
    
                                            <span class="text-xs px-4 py-1.5 rounded-full 
                                                bg-blue-50 text-[#0071BC] font-semibold tracking-wide">
                                                ${questionType?.toUpperCase() ?? '-'}
                                            </span>
                                        </div>
    
                                        <span class="text-sm text-gray-500 font-medium">
                                            ${selectedIndex + 1} / ${questions.length}
                                        </span>
                                    </div>
    
                                    <!-- Question -->
                                    <div class="question-content mb-6 text-sm sm:text-[15px] leading-relaxed text-gray-700">
                                        <div class="mb-4 list-style">${questionTextOnly}</div>
                                        <div class="list-style">${questionImageAndTextAfter}</div>
                                    </div>
    
                                    <!-- Answer -->
                                    <div class="submit-answer-type space-y-4 grow">
                                        ${submitAnswerType}
                                    </div>
    
                                    <input type="hidden" name="school_assessment_question_id" value="${question.id}">
                                    <input type="hidden" name="status_answer" id="status_answer" value="${questionsAnswer[question.id]?.status_answer ?? 'draft'}">
                                    <input type="hidden" name="answer_value" id="userAnswer${question.id}" value="">
                                    <span id="error-answer_value" class="text-red-500 font-bold text-xs pt-2 mb-4"></span>
                                    <input type="hidden" name="question_score" id="question_score" value="${question.question_weight}">
                                    <input type="hidden" name="answer_duration" id="answer_duration" value="0">
                                    <input type="hidden" name="total_exam_duration" id="total_exam_duration" value="0">
                                    <input type="hidden" name="status_attempt" id="status_attempt" value="submitted">
    
                                    <!-- Buttons -->
                                    <div class="flex flex-col sm:flex-row sm:justify-end items-stretch sm:items-center gap-3 sm:gap-6 mt-8 pt-6 border-t border-gray-100">
                                        ${buttonCorrectOrWrongHTML}
    
                                        ${btnMarkAnswerHTML}
    
                                        ${submitButtonAnswerHTML}
                                    </div>
    
                                </div>
                            </div>
    
                            <!-- ================= RIGHT ================= -->
                            <div class="lg:col-span-4 flex shadow-xl order-0 lg:order-1">
                                <div class="w-full bg-white rounded-3xl border border-gray-200 
                                    shadow-[0_10px_40px_rgba(0,0,0,0.05)] p-5 sm:p-6 lg:p-8 flex flex-col">
    
                                    <h3 class="bg-[#0071BC] text-white text-center text-sm py-3 
                                        rounded-2xl font-semibold mb-8">
                                        Kartu Asesmen Siswa
                                    </h3>
    
                                    <div class="border border-gray-200 rounded-2xl p-6 flex justify-between items-center 
                                        mb-10 bg-gray-50 shadow-sm">
    
                                        <div>
                                            <p class="font-semibold text-gray-800 text-sm">
                                                ${response.user?.student_profile?.nama_lengkap}
                                            </p>
    
                                            <p class="text-sm text-gray-500 mt-1">
                                                ${question?.lms_question_bank?.mapel?.mata_pelajaran ?? '-'}
                                            </p>
                                        </div>
                                    </div>
    
                                    <div class="grid grid-cols-5 sm:grid-cols-6 lg:grid-cols-4 xl:grid-cols-8 gap-2 sm:gap-3 text-sm">
                                        ${nomorSoalHTML}
                                    </div>
    
                                    ${linkResultTest}
                                </div>
                            </div>
    
                        </div>
                    </form>
                `;
                formAssessment.append(form);
    
                if (questionType === 'matching') {
    
                    const existingAnswer = questionsAnswer[question.id]?.answer_value;
    
                    studentPairs = {};
    
                    if (existingAnswer) {
                        try {
                            studentPairs = typeof existingAnswer === 'string' ? JSON.parse(existingAnswer) : existingAnswer;
                        } catch (e) {
                            studentPairs = {};
                        }
                    }
    
                    setTimeout(() => {
    
                        const activeContainer = Array.from(document.querySelectorAll('.matching-container'))
                            .find(el => el.offsetParent !== null);
    
                        if (!activeContainer) return;
    
                        Object.entries(studentPairs).forEach(([leftKey, rightKey]) => {
    
                            const rightEl = activeContainer.querySelector(`.matching-right[data-key="${rightKey}"]`);
                            const leftEl = activeContainer.querySelector(`.matching-left[data-key="${leftKey}"]`);
    
                            if (!rightEl || !leftEl) return;
    
                            const rightLabel = rightEl.querySelector('.match-letter')
                                .textContent.trim().replace('.', '');
    
                            leftEl.querySelector('.match-label').textContent = rightLabel;
                        });
    
                        drawStudentMatchingLines();
                        drawCorrectMatchingLines();
    
                    }, 150);
                }
    
                if (isAfter) {
                    $('input[type="radio"], input[type="checkbox"]').removeClass('cursor-pointer').addClass('cursor-default').prop('disabled', true);
                    $('label.checked-option').removeClass('cursor-pointer').addClass('cursor-default').css('pointer-events', 'none');
    
                    $('.matching-container').addClass('pointer-events-none opacity-70');
    
                    $('#btn-submit-save-answer').hide();
                    $('#btn-submit-draft-answer').hide();
                } else if (isBefore) {
                    $('.question-content').addClass('blur-xs');
                    $('.submit-answer-type').addClass('blur-xs');
    
                    $('#btn-submit-save-answer').hide();
                    $('#btn-submit-draft-answer').hide();
                }
    
                $('#btn-submit-end-assessment-test').show();
                $('#btn-submit-exit-assessment-test').hide();
    
                // jika semua soal sudah terjawab maka hentikan bersihkan timer per soal
                if (isAllAnswered || isAfter) {
                    examFinished = true;
    
                    stopQuestionTimer();
                    resetAllQuestionDurations();
                    stopTimer();
    
                    document.getElementById('timer-assessment-test').textContent = 'Waktu Habis';
    
                    $('#btn-submit-end-assessment-test').hide();
                    $('#btn-submit-exit-assessment-test').show();
    
                } else if (isAnswered) {
                    stopQuestionTimer();
                    startTimer();
    
                } else if (examStarted && isActive) {
                    startQuestionTimer(question.id);
    
                } else if (!examStarted && isActive) {
                    confirmStartExam();
                }
    
                $('#answer_duration').val(questionDurations[question.id] || 0);
                $('#total_exam_duration').val(getTotalExamDuration());
    
                // Set aktif pertama
                $(`#nomor${selectedIndex}`).prop('checked', true);
    
                $(document).off('click', '.nomor-soal').on('click', '.nomor-soal', function () {
                    saveQuestionDuration();
    
                    const index = parseInt($(this).data('index'));
                    currentQuestionIndex = index;
                    studentFormAssessment(index);
                });
    
                // Inisialisasi CKEditor jika ada
                const editorContainer = document.getElementById('container-assessment-test-form');
                const uploadUrl = editorContainer.getAttribute('data-upload-url');
                const deleteUrl = editorContainer.getAttribute('data-delete-url');
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
                const editors = formAssessment.find('.editor');
                editors.each((index, textarea) => {
                    ClassicEditor.create(textarea, {
                        ckfinder: {
                            uploadUrl: uploadUrl
                        },
                        toolbar: {
                            shouldNotGroupWhenFull: true
                        },
                    })
                        .then(editor => {
                            previousImageUrlsMap[index] = [];
    
                            const soalId = editor.sourceElement.id.split('_')[1];
    
                            $(`#userAnswer${soalId}`).val(editor.getData());
    
                            const isSubmitted = questionsAnswer[soalId]?.status_answer === 'submitted';
    
                            if (isSubmitted || isAfter) {
                                editor.enableReadOnlyMode('assessment-lock');
                            }
    
                            // Hapus text error ketika konten CKEditor berubah
                            editor.model.document.on('change:data', () => {
                                const currentContent = editor.getData();
    
                                const imageUrls = Array.from(currentContent.matchAll(/<img[^>]+src="([^">]+)"/g)).map(match => match[1]);
    
                                const removedImages = previousImageUrlsMap[index].filter(url => !imageUrls.includes(url));
    
                                removedImages.forEach(url => {
                                    fetch(deleteUrl, {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': csrfToken
                                        },
                                        body: JSON.stringify({ imageUrl: url })
                                    })
                                        .then(response => response.json())
                                        .then(data => console.log('Gambar berhasil dihapus:', data))
                                        .catch(error => console.error('Error saat menghapus gambar:', error));
                                });
    
                                previousImageUrlsMap[index] = imageUrls;
                            });
                            editorInstances.push({ element: textarea, instance: editor });
    
                            editor.model.document.on('change:data', () => {
                                const textarea = editor.sourceElement;
    
                                const value = editor.getData();
                                $(`#userAnswer${soalId}`).val(value);
    
                                $('#error-answer_value').text('');
                            });
                        })
                        .catch(error => console.error('Error CKEditor:', error));
                });

                $('#empty-message-assessment-form').hide();
                
            } else {
                examFinished = true;
                $('#btn-submit-end-assessment-test').hide();
                $('#btn-submit-exit-assessment-test').show();
                $('#empty-message-assessment-form').show();
            }
        }
    });
}

function parseLocalDateTime(dateStr) {
    if (!dateStr) return null;

    const [datePart, timePart] = dateStr.split(' ');
    const [year, month, day] = datePart.split('-').map(Number);
    const [hour, minute] = timePart.split(':').map(Number);

    return new Date(year, month - 1, day, hour, minute);
}

$(document).ready(function () {
    $.getJSON(`/lms/check-assessment-status/${assessmentId}`, function (response) {
        const now = new Date(); // waktu lokal user
        const start = parseLocalDateTime(response.start_date);
        const end = parseLocalDateTime(response.end_date);

        if (now < start) {
            Swal.fire({
                icon: 'warning',
                title: 'Asesmen Belum Dimulai',
                text: 'Sesi Asesmen belum dimulai. Silakan kembali saat waktu Asesmen telah dimulai.',
                confirmButtonText: 'OK',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    // jika user ok, redirect ke halaman preview assessment
                    window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`
                }
            });
            return;
        }

        if (now > end) {
            Swal.fire({
                icon: 'warning',
                title: 'Asesmen Telah Berakhir',
                text: 'Waktu pengerjaan Asesmen telah berakhir.',
                confirmButtonText: 'OK',
            });
            return;
        }
    });

    if (sessionStorage.getItem('exam_reloading')) {
        isPageReloading = true;
        antiCheatCooldown = true;
        sessionStorage.removeItem('exam_reloading');

        // aktifkan kembali anti-cheat setelah page stabil
        setTimeout(() => {
            isPageReloading = false;
            antiCheatCooldown = false;
        }, 1500);
    }

    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };

    const hasTimer = localStorage.getItem(START_KEY);

    if (hasTimer) {
        examStarted = true;
        startTimer();
    }

    studentFormAssessment(0);
    checkAttemptStatus();
    initAntiCheatSystem();

});

window.addEventListener('beforeunload', function () {
    isPageReloading = true;
    sessionStorage.setItem('exam_reloading', '1');
    saveQuestionDuration();
});

function confirmStartExam() {
    if (examStarted) return;

    // cek apakah ada timer
    if (document.getElementById("timer-assessment-test")) {

        Swal.fire({
            title: 'Konfirmasi Mulai Asesmen',
            text: "Klik 'Mulai Asesmen' untuk masuk mode fullscreen dan mulai timer!",
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Mulai Asesmen',
            cancelButtonText: 'Batal',
            allowOutsideClick: false,
            allowEscapeKey: false,
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                examStarted = true;
                enterFullscreen();
                studentFormAssessment(0); // load soal pertama
                startQuestionTimer(questions[0].id); // mulai timer soal pertama
                startTimer(); // mulai timer global
                checkAttemptStatus();
                initAntiCheatSystem();
            } else {
                // jika user batal, redirect ke halaman preview assessment
                window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
            }
        });
    }
}

$(document).off('change', 'input[type=radio], input[type=checkbox]').on('change', 'input[type=radio], input[type=checkbox]', function () {
    $(`#error-answer_value`).text('');
});

$(document).off('click', '.matching-left, .matching-right')
    .on('click', '.matching-left, .matching-right', function () {

        $('#error-answer_value').text('');
    });

// Listener radio -> update input hidden (MCQ, MCMA TYPE)
$(document).on('change', 'input[name^="options_value_"]', function () {
    const soalId = $(this).data('soal-id');
    if (!soalId) return;

    if ($(this).attr('type') === 'checkbox') {
        // MCMA
        let selectedValue = [];
        $(`input[name="options_value_${soalId}[]"]:checked`).each(function () {
            selectedValue.push($(this).val());
        });
        $(`#userAnswer${soalId}`).val(JSON.stringify(selectedValue));
    } else {
        // MCQ single choice
        $(`#userAnswer${soalId}`).val($(this).val());
    }
});

// Listener radio -> update input hidden (PG Kompleks)
function collectPgKompleksAnswer(soalId) {
    const result = {};

    document.querySelectorAll('input[name^="pg_kompleks_"]:checked')
        .forEach(input => {
            const key = input.name.replace('pg_kompleks_', '');
            result[key] = input.value;
        });

    // simpan ke hidden input
    $(`#userAnswer${soalId}`).val(JSON.stringify(result));

    return result;
}

$(document).on('change', 'input[name^="pg_kompleks_"]', function () {
    const soalId = $('input[name="school_assessment_question_id"]').val();

    collectPgKompleksAnswer(soalId);

    $('#error-answer_value').text('');
});

$(document).on('click', '#btn-submit-save-answer, #btn-submit-draft-answer', function (e) {
    const btn = $(this);
    const status = btn.text().toLowerCase().includes('tandai') ? 'draft' : 'submitted';
    $('#status_answer').val(status);
});

function successAssessmentTest() {
    Swal.fire({
        icon: 'success',
        title: 'Asesmen Berhasil Diselesaikan',
        text: 'Semua jawaban telah dikirim.',
    });

}

function resetQuestionDuration(questionId) {

    // hapus dari memory
    delete questionDurations[questionId];

    // hapus dari localStorage
    localStorage.removeItem('duration_' + questionId);

    // reset input
    $('#answer_duration').val(0);
}

let isProcessing = false;

// Submit form jawaban
$(document).on('click', '#btn-submit-save-answer, #btn-submit-draft-answer', function (e) {
    e.preventDefault();
    if (isProcessing) return; // Abaikan jika sedang proses

    isProcessing = true; // Tandai sedang diproses

    const containerFormAssessment = $('#container-assessment-test-form');
    const role = containerFormAssessment.data('role');
    const schoolName = containerFormAssessment.data('school-name');
    const schoolId = containerFormAssessment.data('school-id');
    const curriculumId = containerFormAssessment.data('curriculum-id');
    const mapelId = containerFormAssessment.data('mapel-id');
    const assessmentTypeId = containerFormAssessment.data('assessment-type-id');
    const semester = containerFormAssessment.data('semester');
    const assessmentId = containerFormAssessment.data('assessment-id');

    const status = $(this).data('status-answer'); // draft / submitted
    const statusAnswer = status;

    saveQuestionDuration();

    const form = $('#assessment-test-submit-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    formData.append('status_answer', statusAnswer);
    const totalQuestions = questions.length;
    const totalSubmitted = Object.values(questionsAnswer).filter(q => q.status_answer === 'submitted').length;
    if (totalSubmitted + 1 === totalQuestions && statusAnswer === 'submitted') {
        formData.append('total_exam_duration', getTotalExamDuration());
    }

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/answer`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            stopQuestionTimer();
            resetQuestionDuration(formData.get('school_assessment_question_id'));

            $.get(`/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}`, function (data) {

                const totalQuestions = data.data.length;

                const totalSubmitted = Object.values(data.questionsAnswer).filter(q => q.status_answer === 'submitted').length;

                if (totalSubmitted === totalQuestions) {

                    examFinished = true;

                    stopTimer(); // hentikan timer global
                    stopQuestionTimer(); // hentikan timer per pertanyaan

                    localStorage.removeItem(`timer_assessment_test_start_${assessmentId}`);
                    localStorage.removeItem(`timer_assessment_test_expire_${assessmentId}`);

                    successAssessmentTest();
                    studentFormAssessment(currentQuestionIndex);
                    saveQuestionDuration();

                    localStorage.setItem(`assessment_${assessmentId}_all_answered`, '1');

                } else {
                    studentFormAssessment(currentQuestionIndex);
                }
            });

            isProcessing = false;
            btn.prop('disabled', false);
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const response = xhr.responseJSON;

                // EXAM EXPIRED
                if (response?.status === 'expired') {

                    finalExamDuration = getTotalExamDuration();

                    autoSubmitUnSavedQuestions(function () {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Waktu Asesmen Habis',
                            text: response.message,
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        }).then(() => {
                            location.reload(); // atau redirect ke halaman selesai
                        });

                    });

                    return;
                }

                // VALIDATION ERROR
                if (xhr.status === 422 && response?.errors) {
                    const errors = response.errors;

                    $.each(errors, function (field, messages) {
                        $(`#error-${field}`).text(messages[0]);
                    });

                }
            }

            $('#status_answer').val('draft');

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

function autoSubmitUnSavedQuestions(onFinish = null) {

    examFinished = true;

    stopTimer();
    stopQuestionTimer();

    // reset timer storage
    localStorage.removeItem(`timer_assessment_test_start_${assessmentId}`);
    localStorage.removeItem(`timer_assessment_test_expire_${assessmentId}`);

    // simpan durasi terakhir
    if (currentQuestionId && questionStartTime) {

        const now = Date.now();
        const duration = Math.floor((now - questionStartTime) / 1000);

        if (!questionDurations[currentQuestionId]) {
            questionDurations[currentQuestionId] = 0;
        }

        questionDurations[currentQuestionId] += duration;

        questionStartTime = null;
    }

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}`,
        method: 'GET',
        success: function (response) {

            const questions = response.data;
            const questionsAnswer = response.questionsAnswer;

            const requests = [];

            questions.forEach((data) => {

                const isSubmitted = questionsAnswer[data.id]?.status_answer === 'submitted';

                let duration = 0;

                // 1. dari database
                if (questionsAnswer[data.id]?.answer_duration) {
                    duration = questionsAnswer[data.id].answer_duration;
                }

                // 2. dari memory
                else if (questionDurations[data.id]) {
                    duration = questionDurations[data.id];
                }

                // 3. dari localStorage (penting saat refresh)
                else {
                    const saved = localStorage.getItem('duration_' + data.id);
                    if (saved) {
                        duration = parseInt(saved);
                    }
                }

                const request = $.ajax({
                    url: `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}/semester/${semester}/form/${assessmentId}/answer`,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        school_assessment_id: data.assessment_id,
                        school_assessment_question_id: data.id,
                        status_answer: 'submitted',
                        auto_submit: true,
                        answer_duration: duration,
                        total_exam_duration: finalExamDuration,
                        status_attempt: 'timeout',
                    }
                });

                requests.push(request);

            });

            $.when.apply($, requests).always(function () {
                studentFormAssessment(currentQuestionIndex);

                if (typeof onFinish === "function") {
                    onFinish();
                }
            });

        }
    });
}

$(document).on('click', '#btn-submit-end-assessment-test', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'Konfirmasi Akhiri Asesmen',
        text: "Apakah kamu yakin ingin mengakhiri Asesmen?",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Akhiri Asesmen',
        cancelButtonText: 'Batal',
        allowOutsideClick: false,
        allowEscapeKey: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            examStarted = true;

            saveQuestionDuration();
            finalExamDuration = getTotalExamDuration();

            autoSubmitUnSavedQuestions(function () {
                window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
            });

        }
    });
});

$(document).on('click', '#btn-submit-exit-assessment-test', function (e) {
    e.preventDefault();

    // jika Asesmen sudah selesai -> langsung keluar
    if (examFinished) {
        window.location.href = `/lms/${role}/${schoolName}/${schoolId}/curriculum/${curriculumId}/subject/${mapelId}/learning/assessment/${assessmentTypeId}`;
        return;
    }
});