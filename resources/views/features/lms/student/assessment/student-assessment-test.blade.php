@include('components.navbar-assessment-test')

@if (Auth::user()->role === 'Siswa')
    <main>
        <section id="container-assessment-test-form" data-role="{{ $role }}" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" data-curriculum-id="{{ $curriculumId }}" 
            data-mapel-id="{{ $mapelId }}" data-assessment-type-id="{{ $assessmentTypeId }}" data-semester="{{ $semester }}" data-assessment-id="{{ $assessmentId }}"
            data-upload-url="{{ route('assessment-test.storeImage', ['_token' => csrf_token()]) }}"
            data-delete-url="{{ route('assessment-test.deleteImage') }}">
            
            <div id="form-assessment-test">
                <!-- form in ajax -->
            </div>

            <div id="empty-message-assessment-form" class="w-full h-96 hidden">
                <span class="flex h-full items-center justify-center text-gray-500">
                    Tidak ada soal yang terdaftar pada asesmen ini.
                </span>
            </div>
        </section>
    </main>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/student/assessment/student-form-assessment-test.js') }}"></script> <!--- student form assessment test ---->
<script src="{{ asset('assets/js/features/lms/student/assessment/start-timer-assessment-test-by-question.js') }}"></script> <!--- start timer assessment test by question ---->
<script src="{{ asset('assets/js/features/lms/student/assessment/student-assessment-matching-renderer.js') }}"></script> <!--- student assessment matching renderer ---->