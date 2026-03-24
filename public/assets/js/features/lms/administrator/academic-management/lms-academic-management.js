function managementAcademic() {
    const container = document.getElementById('container-academic-management-list');
    const schoolName = container.dataset.schoolName;
    const schoolId = container.dataset.schoolId;

    if (!container) return;
    if (!schoolName) return;
    if (!schoolId) return;

    fetchRoleAccount(schoolName, schoolId);

    function fetchRoleAccount() {
        $.ajax({
            url: `/lms/school-subscription/${schoolName}/${schoolId}/academic-management/paginate`,
            method: 'GET',
            success: function (response) {
                const containerManagementAcademic = $('#grid-academic-management-list');
                containerManagementAcademic.empty();

                if (response.data.length > 0) {
                    const schoolDetailCard = document.getElementById('school-detail-card');
                    const schoolIdentity = response.schoolIdentity;

                    schoolDetailCard.innerHTML = `
                        <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-6">

                            <!-- KIRI : ICON + NAMA SEKOLAH -->
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-[#EEF6FF] flex items-center justify-center text-[#0071BC] text-2xl shadow-sm">
                                    <i class="fa-solid fa-school"></i>
                                </div>

                                <div>
                                    <h2 class="text-lg font-bold text-gray-800 leading-tight">
                                        ${schoolIdentity.nama_sekolah}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        Detail langganan LMS sekolah
                                    </p>
                                </div>
                            </div>

                            <!-- KANAN : INFO SEKOLAH -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 w-full lg:w-auto">

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NPSN</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.npsn}</p>
                                </div>

                                <div class="bg-gray-50 rounded-xl p-4 min-w-40 h-max">
                                    <p class="text-xs text-gray-500 mb-1">NIK Kepala Sekolah</p>
                                    <p class="font-semibold text-gray-800">${schoolIdentity.user_account?.school_staff_profile?.nik}</p>
                                </div>

                                <div class="bg-[#EEF6FF] rounded-xl p-4 min-w-40">
                                    <p class="text-xs text-[#0071BC] mb-1">Total Pengguna</p>
                                    <p class="font-bold text-2xl text-[#0071BC]">${response.countUsers}</p>
                                </div>

                            </div>
                        </div>
                    `;

                    const lmsRoleManagement = response.lmsRoleManagement.replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                    const lmsQuestionBankManagement = response.lmsQuestionBankManagement.replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                    const lmsCurriculumManagementBySchool = response.lmsCurriculumManagementBySchool.replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                    const lmsContentManagement = response.lmsContentManagement.replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                    const lmsAssessmentTypeManagement = response.lmsAssessmentTypeManagement.replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                    const lmsTeacherSubjectManagement = response.lmsTeacherSubjectManagement.replace(':schoolName', schoolName).replace(':schoolId', schoolId);
                    const lmsAssessmentWeightManagement = response.lmsAssessmentWeightManagement.replace(':schoolName', schoolName).replace(':schoolId', schoolId);

                    const academicManagementGroups = [
                        {
                            key: 'role',
                            title: 'Role Management',
                            icon: 'fa-solid fa-users',
                            description: 'Manajemen akun & hak akses sekolah',
                            items: [
                                'Manajemen Role',
                                'Manajemen Kelas, Jurusan',
                                'Import Users',
                                'Aktif / Nonaktif Akun',
                            ],
                            link: {
                                href: lmsRoleManagement,
                            }
                        },
                        {
                            key: 'curriculum',
                            title: 'Curriculum Management',
                            icon: 'fa-solid fa-sitemap',
                            description: 'Struktur & hirarki kurikulum pembelajaran',
                            items: [
                                'Struktur Kurikulum'
                            ],
                            link: {
                                href: lmsCurriculumManagementBySchool,
                            }
                        },
                        {
                            key: 'academic_content',
                            title: 'Academic Content',
                            icon: 'fa-solid fa-book-open',
                            description: 'Kelola materi pembelajaran sekolah',
                            items: [
                                'Upload Materi',
                                'Materi Default',
                                'Materi Sekolah',
                                'Aktif / Nonaktif Materi',
                            ],
                            link: {
                                href: lmsContentManagement,
                            }
                        },
                        {
                            key: 'assessment_types',
                            title: 'Assessment Type Management',
                            icon: 'fa-solid fa-clipboard-list',
                            description: 'Jenis Asesmen',
                            items: [
                                'Jenis Asesmen',
                                'Manajemen Asesmen',
                            ],
                            link: {
                                href: lmsAssessmentTypeManagement,
                            }
                        },
                        {
                            key: 'assessment_weight',
                            title: 'Assessment Weight',
                            icon: 'fa-solid fa-scale-balanced',
                            description: 'Pengaturan bobot nilai tiap jenis asesmen',
                            items: [
                                'Kelola Bobot Asesmen',
                            ],
                            link: {
                                href: lmsAssessmentWeightManagement,
                            }
                        },
                        {
                            key: 'question_bank',
                            title: 'Question Bank Management',
                            icon: 'fa-solid fa-file-circle-question',
                            description: 'Bank soal',
                            items: [
                                'Soal UH',
                                'Ujian & Remed ASTS',
                                'Pengayaan ASTS',
                                'Ujian & Remed ASAS',
                                'Pengayaan ASAS'
                            ],
                            link: {
                                href: lmsQuestionBankManagement,
                            }
                        },
                        {
                            key: 'teacher_subject',
                            title: 'Teacher Subject Management',
                            icon: 'fa-solid fa-chalkboard-user',
                            description: 'Manajemen guru pengampu mata pelajaran per kelas',
                            items: [
                                'Assign Guru ke Mapel',
                                'Guru Mengajar per Kelas',
                                'Daftar Mapel Guru',
                            ],
                            link: {
                                href: lmsTeacherSubjectManagement,
                            }
                        }

                    ];

                    academicManagementGroups.forEach(group => {
                        const itemsHtml = group.items.map(item => `
                            <li class="text-sm text-gray-600">• ${item}</li>
                        `).join('');

                        const card = `
                            <div class="group h-full flex flex-col bg-white border border-gray-200 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">

                                <!-- HEADER -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-[#EEF6FF] to-[#D6EBFF] flex items-center justify-center text-[#0071BC] shadow-sm">
                                            <i class="${group.icon} text-2xl"></i>
                                        </div>

                                        <div>
                                            <h3 class="font-bold text-gray-800 text-base leading-tight">
                                                ${group.title}
                                            </h3>
                                            <p class="text-xs text-gray-500 mt-1">
                                                ${group.description}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- CONTENT -->
                                <ul class="space-y-2 mb-6 flex-1 font-bold">
                                    ${itemsHtml}
                                </ul>

                                <!-- FOOTER -->
                                <div class="flex items-center justify-end pt-4 border-t border-gray-400 border-dashed">
                                    <a href="${group.link.href}" class="inline-flex items-center gap-2 text-sm font-bold text-[#0071BC] group-hover:gap-3 transition-all">
                                        Kelola
                                        <i class="fa-solid fa-arrow-right text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        `;

                        containerManagementAcademic.append(card);
                    });


                    $('#school-detail-card').show();
                    $('#empty-message-academic-management-list').hide();
                } else {
                    $('#school-detail-card').hide();
                    $('#empty-message-academic-management-list').show();
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    }
}

$(document).ready(function () {
    managementAcademic();
});