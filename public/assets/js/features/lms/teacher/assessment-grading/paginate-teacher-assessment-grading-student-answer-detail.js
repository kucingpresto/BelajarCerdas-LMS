let currentQuestionIndex = 0;

function paginateAssessmentGradingStudentAnswer(selectedIndex = 0) {

    const container = document.getElementById('container-assessment-grading-student-answer');

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const mode = container.dataset.mode;
    const studentId = container.dataset.studentId;

    if (!role || !schoolName || !schoolId || !assessmentId || !mode || !studentId) return;

    fetchData();

    function fetchData() {

        $.ajax({
            url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/mode/${mode}/student-list/${studentId}/scoring/paginate`,
            method: 'GET',

            success: function (response) {

                $('#tbody-assessment-grading-student-answer').empty();
                $('.pagination-container-assessment-grading-student-answer').empty();
                $('#header-assessment-info').empty();

                const formAssessmentGrading = $('#form-assessment-grading');
                formAssessmentGrading.empty();

                const assessment = response.assessment;
                const student = response.student;

                let correctCount = 0;

                const assessmentInfo = $('#header-assessment-info');

                assessmentInfo.html(`
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">

                        <div class="space-y-3 min-w-0 flex-1">

                            <h1 class="text-md md:text-lg font-semibold leading-snug tracking-wide wrap-break-word">
                                ${assessment.title ?? '-'}
                            </h1>

                            <p class="text-sm flex flex-col gap-4">

                                <span class="font-semibold text-white">
                                    Siswa: ${student.student_profile?.nama_lengkap ?? '-'}
                                </span>

                                <span class="font-semibold text-white">
                                    Mata Pelajaran: ${assessment.mapel?.mata_pelajaran ?? '-'}
                                </span>

                                <span class="font-semibold text-white">
                                    Kelas: ${student.student_school_class?.[0]?.school_class?.class_name ?? '-'}
                                </span>

                            </p>

                        </div>

                    </div>
                `);

                assessmentInfo.show();

                if (response.data.length > 0) {

                    const questions = response.data;

                    // Mendapatkan data jawaban
                    const questionsAnswer = response.questionsAnswer;

                    const question = questions[selectedIndex];
                    const key = `${assessment.id}_${question.id}`;
                    const answerData = questionsAnswer[key] ?? {};

                    const scoreUser = answerData?.question_score ?? 0;
                    const teacherFeedback = answerData?.teacher_feedback ?? '';
                    const statusAnswer = answerData?.status_answer ?? 'draft';

                    const questionType = question?.lms_question_bank?.tipe_soal?.toLowerCase();
                    const options = question?.lms_question_bank?.lms_question_option ?? [];
                    const studentAnswer = question?.student_assessment_answer?.length ? question.student_assessment_answer[0].answer_value : [];

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

                    let questionStatus = '';

                    if (questionType === 'matching') {

                        let pairs = {};

                        if (studentAnswer) {
                            try {
                                pairs = typeof studentAnswer === 'string' ? JSON.parse(studentAnswer) : studentAnswer;
                            } catch {
                                pairs = {};
                            }
                        }

                        const leftItems = options.filter(o => o.options_key.startsWith('LEFT'));

                        const correctPairs = {};
                        leftItems.forEach(item => {
                            correctPairs[item.options_key] = item.extra_data?.pair_with;
                        });

                        const total = Object.keys(correctPairs).length;

                        if (!pairs || Object.keys(pairs).length === 0) {

                            questionStatus = 'not_answered';

                        } else {

                            let correctCount = 0;

                            Object.keys(correctPairs).forEach(leftKey => {
                                if (pairs[leftKey] === correctPairs[leftKey]) {
                                    correctCount++;
                                }
                            });

                            if (correctCount === total) {
                                questionStatus = 'correct';
                            } else {
                                questionStatus = 'wrong';
                            }
                        }

                    }
                    else if (questionType === 'pg_kompleks') {
                        let answers = {};

                        if (studentAnswer) {
                            try {
                                answers = typeof studentAnswer === 'string' ? JSON.parse(studentAnswer) : studentAnswer;
                            } catch {
                                answers = {};
                            }
                        }

                        const leftItems = options.filter(o => o.options_key.startsWith('ITEM'));

                        const correctAnswers = {};
                        leftItems.forEach(item => {
                            correctAnswers[item.options_key] = item.extra_data?.answer;
                        });

                        const total = Object.keys(correctAnswers).length;

                        if (!answers || Object.keys(answers).length === 0) {

                            questionStatus = 'not_answered';

                        } else {

                            let correctCount = 0;

                            Object.keys(correctAnswers).forEach(leftKey => {
                                if (answers[leftKey] === correctAnswers[leftKey]) {
                                    correctCount++;
                                }
                            });

                            if (correctCount === total) {
                                questionStatus = 'correct';
                            } else {
                                questionStatus = 'wrong';
                            }
                        }
                    }
                    else {

                        if (!studentAnswer || studentAnswer.length === 0) {
                            questionStatus = 'not_answered';
                        } else {

                            const correctOptions = options.filter(o => o.is_correct == 1).map(o => o.options_key);

                            const studentOptions = Array.isArray(studentAnswer) ? studentAnswer : [studentAnswer];

                            const isCorrect = studentOptions.length === correctOptions.length && studentOptions.every(o => correctOptions.includes(o));

                            questionStatus = isCorrect ? 'correct' : 'wrong';
                        }

                    }

                    let statusBadge = '';

                    if (questionType !== 'essay') {
                        if (questionStatus === 'not_answered') {
                            statusBadge = `
                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700">
                                    Tidak Dijawab
                                </span>
                            `;
                        }
                        else if (questionStatus === 'correct') {
                            statusBadge = `
                                <span class="text-xs font-semibold px-3 py-1 rounded-full bg-green-100 text-green-700">
                                    Jawaban Benar
                                </span>
                            `;
                        }
                        else if (questionStatus === 'wrong') {
                            statusBadge =
                                `<span class="text-xs font-semibold px-3 py-1 rounded-full bg-red-100 text-red-700">
                                    Jawaban Salah
                                </span>
                            `;
                        }
                    }

                    // RENDER MCQ / MCMA
                    function renderMCQOptions() {
                        const optionKeys = ['A', 'B', 'C', 'D', 'E'];
                        
                        return options.map((item, index) => {
                            const containsImage = /<img\s+[^>]*src=/.test(item.options_value);

                            const key = optionKeys[index];

                            const studentOptions = Array.isArray(studentAnswer) ? studentAnswer : studentAnswer ? [studentAnswer] : [];

                            const isStudent = studentOptions.includes(item.options_key);
                            const isCorrect = item.is_correct == 1;

                            let statusClass = '';
                            let badge = '';

                            let content = item.options_value;

                            // Tambahkan class img jika ada gambar
                            if (containsImage) {
                                content = addClassToImgTags(item.options_value, 'max-w-[300px] rounded my-2');
                            }

                            if (questionStatus === 'not_answered') {

                                if (isCorrect) {
                                    statusClass = 'bg-green-100 text-green-700';
                                    badge = `<span class="text-xs font-semibold text-green-700">Jawaban Benar</span>`;
                                }

                            }

                            else if (questionStatus === 'correct') {

                                if (isStudent) {
                                    statusClass = 'bg-green-200 text-green-800 font-semibold';
                                    badge = `<span class="text-xs font-bold text-green-800">Jawaban Siswa</span>`;
                                }

                            }

                            else if (questionStatus === 'wrong') {

                                if (isStudent && !isCorrect) {
                                    statusClass = 'bg-red-200 text-red-700 font-semibold';
                                    badge = `<span class="text-xs font-bold text-red-700">Jawaban Siswa</span>`;
                                }

                                if (isCorrect) {
                                    statusClass = 'bg-green-100 text-green-700';
                                    badge = `<span class="text-xs font-semibold text-green-700">Jawaban Benar</span>`;
                                }

                            }

                            return `
                                <div class="border rounded-lg p-3 mb-3 flex justify-between items-start gap-3 ${statusClass}">
                                    
                                    <div class="flex text-sm gap-3">
                                        <span class="font-bold">${key}.</span>
                                        <div>${content}</div>
                                    </div>

                                    ${badge}

                                </div>
                            `;

                        }).join('');
                    }
                    const leftItems = options.filter(item => item.options_key.startsWith('LEFT'));
                    const rightItems = options.filter(item => item.options_key.startsWith('RIGHT'));

                    // RENDER MATCHING
                    function renderMatching() {

                        const rightLabelMap = {};
                        rightItems.forEach((item, index) => {
                            rightLabelMap[item.options_key] = String.fromCharCode(65 + index);
                        });

                        const correctPairs = {};
                        leftItems.forEach(item => {
                            correctPairs[item.options_key] = item.extra_data?.pair_with;
                        });

                        let pairs = {};

                        if (studentAnswer) {
                            try {
                                pairs = typeof studentAnswer === 'string' ? JSON.parse(studentAnswer) : studentAnswer;
                            } catch {
                                pairs = {};
                            }
                        }

                        return `
                            <!-- DEKSTOP -->
                            <div class="relative matching-container hidden lg:block" data-student='${JSON.stringify(pairs)}' data-correct='${JSON.stringify(correctPairs)}'>

                                <!-- SVG GARIS -->
                                <svg class="absolute inset-0 w-full h-full pointer-events-none matching-lines"></svg>

                                <div class="grid grid-cols-2 gap-40 relative z-10">
                                    <div class="flex flex-col justify-center">
                                        <h4 class="font-bold mb-3">Kolom A</h4>
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

                                    <div>
                                        <h4 class="font-bold mb-3">Kolom B</h4>
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

                                <!-- GARIS TENGAH -->
                                <div class="matching-center-line absolute top-0 bottom-0 left-1/2 w-0"></div>
                            </div>

                            <!-- MOBILE -->
                            <div class="block lg:hidden">

                                <div class="grid grid-cols-1 gap-3 lg:hidden">
                                    <p class="font-semibold mb-2">Kolom A:</p>
                                    ${leftItems.map(item => `
                                        <div class="flex justify-between items-center border rounded p-3">
                                            <span>${item.options_value}</span>
                                            <span class="font-bold text-[#0071BC]">
                                                <i class="fa-solid fa-arrow-right"></i>
                                                ${rightLabelMap[item.extra_data?.pair_with] ?? '-'}
                                            </span>
                                        </div>
                                    `).join('')}
                                </div>

                                <div class="mt-4 lg:hidden border-t border-gray-400 pt-3 grid grid-cols-1 gap-3 text-sm text-gray-700">
                                    <p class="font-semibold mb-2">Kolom B:</p>
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
                        `;
                    }

                    function renderPgKompleks() {
                        if (!Array.isArray(options)) return '';

                        const categories = options.filter(item => item.extra_data?.side === 'category');
                        const items = options.filter(item => item.extra_data?.side === 'item');

                        let existingAnswer = answerData?.answer_value || {};

                        if (typeof existingAnswer === 'string') {
                            try {
                                existingAnswer = JSON.parse(existingAnswer);
                            } catch (e) {
                                existingAnswer = {};
                            }
                        }

                        return `
                        <div class="overflow-x-auto mt-6">

                            <div class="flex flex-wrap gap-4 text-xs mb-4">
                                <span class="flex items-center gap-1 text-green-600 font-semibold">
                                    <i class="fa-solid fa-check"></i> Jawaban Benar
                                </span>
                                <span class="flex items-center gap-1 text-red-600 font-semibold">
                                    <i class="fa-solid fa-xmark"></i> Jawaban Salah
                                </span>
                                <span class="flex items-center gap-1 text-[#4189E0] font-semibold">
                                    <input type="radio" class="w-4 h-4" checked onclick="return false"> 
                                    Jawaban Siswa
                                </span>
                            </div>

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

                            rowClass = userAnswer === correctAnswer ? 'bg-green-50' : 'bg-red-50';

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

                                        // Jawaban benar & dipilih
                                        if (selected && isCorrect) {
                                            cellClass += ' bg-green-100 border-green-400';
                                            icon = '<i class="fa-solid fa-check text-green-600"></i>';
                                            badge = '<span class="text-[10px] text-green-700">jawaban Siswa</span>';

                                            // Jawaban salah
                                        } else if (selected && !isCorrect) {
                                            cellClass += ' bg-red-100 border-red-400';
                                            icon = '<i class="fa-solid fa-xmark text-red-600"></i>';
                                            badge = '<span class="text-[10px] text-red-700">Jawaban Siswa</span>';

                                            // Kunci jawaban
                                        } else if (!selected && isCorrect) {
                                            cellClass += ' bg-green-50 border-green-300';
                                            icon = '<i class="fa-solid fa-check text-green-500"></i>';
                                            badge = '<span class="text-[10px] text-green-600">Jawaban Benar</span>';
                                        }

                                        return `
                                            <td class="border">
                                                <div class="flex flex-col items-center justify-center gap-1 py-2 ${cellClass}">

                                                    <input type="radio" name="pg_kompleks_${item.options_key}" value="${cat.options_key}" class="w-4 h-4" ${selected ? 'checked' : ''} 
                                                        onclick="return false">

                                                    <div class="flex flex-col items-center text-xs">
                                                        ${icon}
                                                        ${badge}
                                                    </div>

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

                    const isRemedial = assessment.assessment_category === 'remedial';
                    const totalQuestions = questions.length;
                    const scorePerQuestion = totalQuestions > 0 ? (100 / totalQuestions) : 0;

                    const isCorrect = questionStatus === 'correct';

                    // ================= SCORE =================
                    let displayScore = 0;
                    let maxScore = 0;

                    if (isRemedial) {

                        // REMEDIAL → AUTO SCORING
                        displayScore = isCorrect ? scorePerQuestion : 0;
                        maxScore = scorePerQuestion;

                    } else {

                        // NON REMEDIAL → MANUAL / DARI DB
                        displayScore = answerData?.question_score ?? 0;
                        maxScore = question.question_weight ?? 100;

                    }

                    // OPTIONAL: hitung total benar (kalau mau dipakai nanti)
                    if (isCorrect) {
                        correctCount++;
                    }

                    // RENDER ESSAY
                    function renderEssay() {

                        const isAnswered = statusAnswer === 'submitted';

                        return `

                            <h4 class="font-semibold text-gray-800 mb-4">
                                Jawaban Siswa
                            </h4>

                            ${!isAnswered
                                ? `
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700 mb-4">
                                        <i class="fa-solid fa-circle-exclamation mr-2"></i>
                                        Siswa tidak menjawab soal ini.
                                    </div>
                                `
                            : ''}

                            <textarea rows="6"
                                class="editor w-full border border-gray-300 rounded-xl p-4 text-sm resize-none"
                                ${!isAnswered ? 'disabled' : ''}>
                                ${studentAnswer ?? ''}
                            </textarea>

                            <div class="mt-8 border-t border-gray-300 pt-6">

                                <h4 class="font-semibold text-gray-800 mb-3">
                                    Penilaian Guru
                                </h4>

                                <div class="flex flex-col gap-2 mb-4">

                                    <label class="text-sm font-medium text-gray-700">
                                        Score
                                        <sup class="text-red-500 relative">&#42;</sup>
                                    </label>

                                    <div class="text-sm text-gray-500 flex flex-col gap-4">

                                        <span class="flex items-center gap-2">

                                            <input type="number" min="0" max="${question.question_weight}" id="question_score" name="question_score" value="${scoreUser ?? ''}"
                                                class="w-24 border border-gray-300 rounded-lg p-2 text-sm outline-none" placeholder="0" ${!isAnswered ? 'disabled' : ''}>

                                            / ${question.question_weight}

                                        </span>

                                        <span id="error-question_score"
                                            class="text-xs text-red-500 hidden">
                                        </span>

                                    </div>

                                </div>

                                <div class="mb-4">

                                    <label class="text-sm font-medium text-gray-700">
                                        Feedback Guru (Optional)
                                    </label>

                                    <textarea rows="3" id="teacher_feedback" name="teacher_feedback" class="w-full border border-gray-300 resize-none rounded-lg p-3 text-sm outline-none"
                                        placeholder="Tambahkan komentar..." ${!isAnswered ? 'disabled' : ''}>${teacherFeedback ?? ''}</textarea>

                                </div>

                                <div class="flex justify-end">

                                    <button type="button" id="submit-button-assessment-grading-student-answer" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm
                                        ${!isAnswered ? 'opacity-50 cursor-default' : 'cursor-pointer'}"
                                        ${!isAnswered ? 'disabled' : ''}>
                                        Simpan Nilai
                                    </button>

                                </div>

                            </div>
                        `;
                    }

                    // SELECT RENDERER
                    let answerHTML = '';

                    if (questionType === 'essay') {
                        answerHTML = renderEssay();
                    }
                    else if (questionType === 'matching') {
                        answerHTML = renderMatching();
                    }
                    else if (questionType === 'pg_kompleks') {
                        answerHTML = renderPgKompleks();
                    }
                    else {
                        answerHTML = renderMCQOptions();
                    }

                    // NAVIGATION NUMBER
                    const nomorSoalHTML = response.data.map((item, index) => {

                        const itemKey = `${assessment.id}_${item.id}`;
                        const answer = questionsAnswer[itemKey];
                        const itemType = item?.lms_question_bank?.tipe_soal?.toLowerCase();
                        const itemStatus = answer?.status_answer ?? 'draft';
                        const itemGradingStatus = answer?.grading_status ?? 'pending';

                        // Format nama tipe soal
                        let questionTypeLabel = 'Unknown';

                        if (itemType === 'mcq') questionTypeLabel = 'MCQ';
                        else if (itemType === 'mcma') questionTypeLabel = 'MCMA';
                        else if (itemType === 'essay') questionTypeLabel = 'Essay';
                        else if (itemType === 'matching') questionTypeLabel = 'Matching';
                        else if (itemType === 'pg_kompleks') questionTypeLabel = 'PG Kompleks';

                        // Badge tipe soal (tanpa beda warna)
                        const typeBadge = `
                            <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-700 text-center rounded font-medium">
                                ${questionTypeLabel}
                            </span>
                        `;

                        let questionBadge = '';

                        if (!answer || itemStatus !== 'submitted') {

                            questionBadge = `
                                <span class="text-xs text-red-600 font-medium text-end">
                                    Tidak Dijawab
                                </span>
                            `;

                        }
                        else if (itemType !== 'essay') {

                            if (itemStatus === 'submitted') {
                                questionBadge = `
                                    <span class="text-xs text-green-600 font-medium text-end">
                                        Sudah Dinilai
                                    </span>
                                `;
                            }

                        }
                        else {

                            if (itemStatus === 'submitted' && itemGradingStatus === 'graded') {

                                questionBadge = `
                                    <span class="text-xs text-green-600 font-medium text-end">
                                        Sudah Dinilai
                                    </span>
                                `;

                            }
                            else {

                                questionBadge = `
                                    <span class="text-xs text-orange-500 font-medium text-end">
                                        Menunggu Penilaian
                                    </span>
                                `;

                            }

                        }

                        return `
                            <input type="radio" id="nomor${index}" class="hidden">

                            <label for="nomor${index}" class="nomor-soal text-xs p-3 rounded-lg border flex flex-col gap-1 cursor-pointer hover:bg-gray-100 mb-3 mr-3" data-index="${index}">

                                <div class="flex items-center justify-between gap-2 font-bold">
                                    Question ${index + 1}
                                    ${typeBadge}
                                </div>

                                <div class="text-[11px]">
                                    ${questionBadge}
                                </div>

                            </label>
                        `;

                    }).join('');

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

                    // MAIN FORM
                    const form = `
                        <form id="teacher-assessment-grading-student-answer-form" autocomplete="OFF">
                            <input type="hidden" id="school_assessment_question_id" name="school_assessment_question_id" value="${question.id}">

                            <div class="grid grid-cols-12 gap-6">

                                <div class="col-span-12 xl:col-span-3">

                                    <div class="bg-white border rounded-xl shadow-sm p-4">

                                        <h3 class="font-semibold text-gray-700 mb-4">
                                            Questions
                                        </h3>

                                        <div class="max-h-163.5 overflow-y-auto">
                                            ${nomorSoalHTML}
                                        </div>

                                    </div>

                                </div>

                                <div class="col-span-12 xl:col-span-9">

                                    <div class="bg-white border rounded-xl shadow-sm p-6">

                                        <div class="mb-6">

                                            <div class="mb-4 flex flex-col gap-2">

                                                <!-- Question + Score -->
                                                <div class="flex items-center justify-between text-sm sm:text-base font-semibold text-gray-800">
                                                    <span>
                                                        Question ${selectedIndex + 1}
                                                    </span>

                                                    <span class="text-[#4189E0]">
                                                        ${displayScore.toFixed(2)} / ${maxScore.toFixed(2)}
                                                    </span>
                                                </div>

                                                <!-- Status -->
                                                <div>
                                                    ${statusBadge}
                                                </div>

                                            </div>

                                            <!-- Question -->
                                            <div class="max-h-163.5 overflow-y-auto">
                                                <div class="question-content mb-6 text-sm sm:text-[15px] leading-relaxed text-gray-700 mr-3">
                                                    <div class="mb-4 list-style">${questionTextOnly}</div>
                                                    <div class="list-style">${questionImageAndTextAfter}</div>
                                                </div>

                                                <div class="mt-6 mr-3 list-style">
                                                    ${answerHTML}
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            </div>
                        </form>
                    `;

                    const $card = $(form); // ubah string jadi jQuery element
                    formAssessmentGrading.append($card);

                    if (questionType === 'matching') {

                        requestAnimationFrame(() => {
                            const container = $card.find('.matching-container')[0]; // ambil element DOM

                            if (!container) return;

                            const pairs = leftItems.filter(i => i.extra_data?.pair_with).map(i => ({
                                    left: i.options_key,
                                    right: i.extra_data.pair_with
                                }));

                            drawMatchingLines(container, pairs);
                            initMatchingContainer(container);
                        });

                    }

                    const editors = formAssessmentGrading.find('.editor');
                    editors.each((index, textarea) => {
                        ClassicEditor.create(textarea, {
                            toolbar: {
                                shouldNotGroupWhenFull: true
                            },
                        })
                            .then(editor => {
                                editor.enableReadOnlyMode('lock-student-answer');
                            })
                            .catch(error => console.error('Error CKEditor:', error));
                    });

                    $(`#nomor${selectedIndex}`).prop('checked', true);

                    $(document).off('click', '.nomor-soal').on('click', '.nomor-soal', function () {
                        const index = parseInt($(this).data('index'));

                        currentQuestionIndex = index;

                        paginateAssessmentGradingStudentAnswer(index);
                    });
                } else {
                    $('#form-assessment-grading').hide();
                    $('#empty-message-school-assessment-question').show();
                }
            }
        });
    }
}

$(document).ready(function () {
    paginateAssessmentGradingStudentAnswer();
});

$(document).on('input', '#question_score', function () {
    $('#error-question_score').text('').addClass('hidden');
    $(this).removeClass('border-red-400');
});

let isProcessing = false;

// Form Action assessment grading student answer
$(document).on('click', '#submit-button-assessment-grading-student-answer', function (e) {
    e.preventDefault();

    const container = document.getElementById('container-assessment-grading-student-answer');
    if (!container) return;

    const role = container.dataset.role;
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;
    const assessmentId = container.dataset.assessmentId;
    const studentId = container.dataset.studentId;

    const schoolAssessmentQuestionId = $('#school_assessment_question_id').val();

    if (!role || !schoolName || !schoolId || !assessmentId || !studentId || !schoolAssessmentQuestionId) return;

    const form = $('#teacher-assessment-grading-student-answer-form')[0]; // ambil DOM Form-nya
    const formData = new FormData(form); // buat FormData dari form, BUKAN dari tombol

    if (isProcessing) return;
    isProcessing = true;

    const btn = $(this);
    btn.prop('disabled', true);

    $.ajax({
        url: `/lms/${role}/${schoolName}/${schoolId}/assessment-grading/${assessmentId}/student-list/${studentId}/scoring/submission/${schoolAssessmentQuestionId}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {

            $('#alert-success-assesment-grading').html(`
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

            paginateAssessmentGradingStudentAnswer(currentQuestionIndex);

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
                    $('#teacher-assessment-grading-student-answer-form').find(`#error-${field}`).text(messages[0]).removeClass('hidden');

                    // Tambahkan style error ke input (jika ada)
                    $('#teacher-assessment-grading-student-answer-form').find(`[name="${field}"]`).addClass('border-red-400 border');
                });

            } else {
                alert('Terjadi kesalahan saat mengirim data.');
            }

            isProcessing = false;
            btn.prop('disabled', false);
        }
    });
});

function drawMatchingLines(container) {

    if (!container) return;

    const svg = container.querySelector('.matching-lines');
    if (!svg) return;

    svg.innerHTML = '';

    // supaya SVG selalu mengikuti container
    svg.setAttribute('width', container.offsetWidth);
    svg.setAttribute('height', container.offsetHeight);

    const cRect = container.getBoundingClientRect();

    const studentPairs = JSON.parse(container.dataset.student || '{}');
    const correctPairs = JSON.parse(container.dataset.correct || '{}');

    Object.keys(correctPairs).forEach(leftKey => {

        const studentRight = studentPairs[leftKey];
        const correctRight = correctPairs[leftKey];

        const leftEl = container.querySelector(`[data-key="${leftKey}"]`);
        if (!leftEl) return;

        const l = leftEl.getBoundingClientRect();

        const x1 = l.right - cRect.left;
        const y1 = l.top + l.height / 2 - cRect.top;

        // GARIS JAWABAN SISWA
        if (studentRight) {

            const rightEl = container.querySelector(`[data-key="${studentRight}"]`);

            if (rightEl) {

                const r = rightEl.getBoundingClientRect();

                const x2 = r.left - cRect.left;
                const y2 = r.top + r.height / 2 - cRect.top;

                const studentPath = document.createElementNS(
                    'http://www.w3.org/2000/svg',
                    'path'
                );

                const color = studentRight === correctRight ? '#16A34A' : '#DC2626';

                studentPath.setAttribute('d', `M ${x1} ${y1} L ${x2} ${y2}`);

                studentPath.setAttribute('stroke', color);
                studentPath.setAttribute('stroke-width', '3');
                studentPath.setAttribute('fill', 'none');
                studentPath.setAttribute('stroke-linecap', 'round');

                svg.appendChild(studentPath);

            }

        }

        // GARIS JAWABAN BENAR
        if (studentRight !== correctRight) {

            const correctEl = container.querySelector(`[data-key="${correctRight}"]`);
            if (!correctEl) return;

            const r = correctEl.getBoundingClientRect();

            const x2 = r.left - cRect.left;
            const y2 = r.top + r.height / 2 - cRect.top;

            const correctPath = document.createElementNS('http://www.w3.org/2000/svg','path');

            correctPath.setAttribute('d', `M ${x1} ${y1} L ${x2} ${y2}`);

            correctPath.setAttribute('stroke', '#16A34A');
            correctPath.setAttribute('stroke-width', '2');
            correctPath.setAttribute('stroke-dasharray', '6,6');
            correctPath.setAttribute('fill', 'none');

            svg.appendChild(correctPath);

        }

    });

}

const matchingObservers = new WeakMap();

function initMatchingContainer(container) {

    if (!container) return;

    const pairs = JSON.parse(container.dataset.student || '{}');

    drawMatchingLines(container, pairs);

    if (matchingObservers.has(container)) return;

    const observer = new ResizeObserver(() => {

        requestAnimationFrame(() => {
            drawMatchingLines(container, pairs);
        });

    });

    observer.observe(container);

    matchingObservers.set(container, observer);

}