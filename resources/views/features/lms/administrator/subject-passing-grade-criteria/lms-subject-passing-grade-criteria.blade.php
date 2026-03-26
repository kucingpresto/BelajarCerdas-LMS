@include('components/sidebar-beranda', [
    'headerSideNav' => 'KKM',
    'linkBackButton' => route('lms.academicManagement.view', [$schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-insert-data-subject-passing-grade-criteria"></div>
            <div id="alert-success-edit-data-subject-passing-grade-criteria"></div>
            <div id="alert-success-import-bulkUpload"></div>

            <main>
                <section>
                    <!---- DETAIL SEKOLAH ---->
                    <div id="school-detail-card" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden"></div>
                </section>

                <section id="container" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="bg-white shadow-lg p-6 rounded-lg border-gray-200 border">
                    <!---- Form input subject passing grade criteria ---->
                    <form id="create-subject-passing-grade-criteria-form" autocomplete="off">
                        <div class="py-6 space-y-8">

                            <!-- HEADER -->
                            <div class="space-y-2">
                                <h2 class="text-lg font-bold text-gray-800">
                                    Kelola Kriteria Ketuntasan Minimal (KKM)
                                </h2>

                                <p class="text-sm text-gray-500">
                                    Tentukan nilai minimum (KKM) untuk setiap mata pelajaran berdasarkan kelas dan tahun ajaran.
                                </p>
                            </div>

                            <div class="w-full flex justify-end">
                                <!--- button bulkupload school partner --->
                                <button type="button" onclick="my_modal_3.showModal()"
                                    class="w-max bg-[#0071BC] text-white font-bold h-10 px-6 rounded-lg shadow-md transition-all text-sm flex gap-2 items-center justify-center 
                                        cursor-pointer">
                                    <i class="fa-solid fa-circle-plus"></i>
                                    Bulk Upload
                                </button>
                            </div>

                            <!-- BASIC INFO -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!--- Kurikulum --->
                                <div class="flex flex-col order-1 xl:order-0">
                                    <label class="mb-2 text-sm">
                                        Kurikulum
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <select name="kurikulum_id" id="id_kurikulum"
                                        class="w-full bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-full px-2 focus:border cursor-pointer">
                                        <option value="" class="hidden">Pilih Kurikulum</option>
                                        @foreach ($getCurriculum as $item)
                                            <option value="{{ $item->id }}">{{ $item->nama_kurikulum }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error-kurikulum_id" class="text-red-500 font-bold text-xs pt-2"></span>
                                </div>

                                <!--- Mapel --->
                                <div class="flex flex-col order-3 lg:order-2 xl:order-0">
                                    <label class="mb-2 text-sm">
                                        Mata Pelajaran
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <select name="mapel_id" id="id_mapel"
                                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-full px-2 opacity-50 focus:border cursor-default" disabled>
                                        <option class="hidden">Pilih Mata Pelajaran</option>
                                    </select>
                                    <span id="error-mapel_id" class="text-red-500 font-bold text-xs pt-2"></span>
                                </div>

                                <!--- Kelas --->
                                <div class="flex flex-col order-2 xl:order-0">
                                    <label class="mb-2 text-sm">
                                        Kelas
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <select name="kelas_id" id="id_kelas"
                                        class="bg-white shadow-lg h-12 text-sm border-gray-200 border outline-none rounded-full px-2 opacity-50 focus:border cursor-default" disabled>
                                        <option class="hidden">Pilih Kelas</option>
                                    </select>
                                    <span id="error-kelas_id" class="text-red-500 font-bold text-xs pt-2"></span>
                                </div>
                            
                                <div>
                                    <label class="text-sm">
                                        Tahun Ajaran
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <select name="school_year"
                                        class="mt-2 w-full h-11 rounded-full border border-gray-200 px-4 text-xs shadow-sm outline-none cursor-pointer">
                                        <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                        @foreach ($tahunAjaran as $item)
                                            <option value="{{ $item }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error-school_year" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                                <div>
                                    <label class="text-sm">
                                        KKM
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <input type="number" name="kkm_value" min="0" max="100" class="mt-2 w-full h-11 rounded-full border border-gray-200 px-4 text-xs 
                                        shadow-sm outline-none" placeholder="Masukkan Nilai KKM">
                                    <span id="error-kkm_value" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                            </div>

                            <!-- SUBMIT -->
                            <div class="flex justify-end pt-4">
                                <button type="button" id="submit-button-create-subject-passing-grade-criteria"
                                    class="bg-[#0071BC] text-white font-semibold text-sm px-8 py-3 rounded-full shadow-md transition cursor-pointer disabled:cursor-default">
                                    Simpan KKM
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="border-b-2 border-gray-200 mt-4"></div>

                    <!---- Table list data subject passing grade criteria ---->
                    <div id="container-subject-passing-grade-criteria-management" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="overflow-x-auto mt-8 pb-24">

                        <h2 class="text-lg font-bold text-gray-800 pb-6">
                            KKM List
                        </h2>

                        <!-- FILTER CONTAINER -->
                        <div class="my-6 bg-white shadow-sm border border-gray-300 rounded-2xl p-6">

                            <!-- Header Filter -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                    <h3 class="text-base font-semibold text-gray-800">
                                        Filter
                                    </h3>
                                </div>
                            </div>

                            <!-- Filter Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">

                                <!-- Tahun Ajaran -->
                                <div id="container-dropdown-tahun-ajaran">
                                    <!-- show data in ajax -->
                                </div>

                                <!-- Kelas -->
                                <div id="container-dropdown-class">
                                    <!-- show data in ajax -->
                                </div>

                            </div>
                        </div>
                                
                        <table id="table-subject-passing-grade-criteria-management" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-subject-passing-grade-criteria-management hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        No
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Tahun Ajaran
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Kelas
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Mata Pelajaran
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        KKM
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="table-list-subject-passing-grade-criteria-management">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-subject-passing-grade-criteria-management flex justify-center my-4 sm:my-0"></div>

                    <div id="empty-message-subject-passing-grade-criteria-management" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada data.
                        </span>
                    </div>

                    <!---- modal edit subject passing grade criteria ---->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white lg:w-6xl">
                            <form id="edit-subject-passing-grade-criteria-form" autocomplete="OFF" class="">
                                <span class="text-xl font-bold flex justify-center">Edit KKM</span>

                                <input type="hidden" id="edit-subject-passing-grade-criteria-id">

                                <div class="flex flex-col mt-4">
                                    <label class="text-sm">
                                        KKM
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <input type="number" id="edit-kkm" name="kkm_value" min="0" max="100" class="mt-2 w-full h-11 rounded-full border border-gray-200 px-4 text-xs 
                                        shadow-sm outline-none" placeholder="Masukkan Nilai KKM">
                                    <span id="error-kkm_value" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button id="submit-button-edit-subject-passing-grade-criteria" type="button"
                                        class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all cursor-pointer disabled:cursor-default">
                                        Simpan
                                    </button>
                                </div>
                            </form>
                        </div>

                        <form method="dialog" class="modal-backdrop">
                            <button>Close</button>
                        </form>
                    </dialog>

                    <!---- modal history subject passing grade criteria ---->
                    <dialog id="my_modal_2" class="modal">
                        <div class="modal-box bg-white max-w-md">
                            <h3 class="font-bold text-lg mb-6 text-center">History</h3>

                            <!-- USER / PUBLISHER -->
                            <div class="flex items-center gap-4 mb-5">
                                <i class="fa-solid fa-circle-user text-5xl text-gray-400"></i>

                                <div class="flex flex-col gap-1 flex-1">
                                    <span id="text-nama_lengkap" class="font-semibold text-gray-800"></span>
                                    <span id="text-role" class="text-sm text-gray-500"></span>
                                    <span id="text-updated_at" class="text-xs text-gray-400"></span>
                                </div>

                                <div class="">
                                    <span class="text-[#0071BC] font-bold text-sm">Publisher</span>
                                </div>
                            </div>
                        </div>

                        <form method="dialog" class="modal-backdrop">
                            <button>close</button>
                        </form>
                    </dialog>

                    <!--- modal bulkupload subject passing grade criteria --->
                    <dialog id="my_modal_3" class="modal">
                        <div class="modal-box bg-white w-max max-h-150">
                            <span class="text-md flex justify-center font-bold opacity-70">Upload File</span>
                                <form id="subject-passing-grade-criteria-bulkUpload-form" enctype="multipart/form-data">

                                <!--- show bulkUpload excel errors --->
                                <div id="error-bulkUpload" class="w-96.25 my-4 max-h-42 overflow-y-auto"></div>

                                <div class="w-full mt-8">
                                    <div class="w-full h-auto">
                                        <div class="text-xs mt-1">
                                            <span>Maksimum ukuran file 100MB. <br> File dapat dalam format .xlsx.</span>
                                        </div>
                                        <div class="upload-icon">
                                                <div class="flex flex-col max-w-65">
                                                    <div id="excelPreview" class="max-w-70 cursor-pointer mt-4">
                                                        <div id="excelPreviewContainer-bulkUpload-excel" class="bg-white shadow-lg rounded-lg w-max py-2 pr-4 border border-gray-200 hidden">
                                                        <div class="flex items-center">
                                                                <img id="logo-bulkUpload-excel" class="w-14 h-max">
                                                            <div class="mt-2 leading-5">
                                                                <span id="textPreview-bulkUpload-excel" class="font-bold text-sm"></span><br>
                                                                <span id="textSize-bulkUpload-excel" class="text-xs"></span>
                                                                <span id="textCircle-bulkUpload-excel" class="relative -top-0.5 text-[5px]"></span>
                                                                <span id="textPages-bulkUpload-excel" class="text-xs"></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="content-upload w-96.25 h-9 bg-[#4189e0] hover:bg-blue-500 text-white font-bold rounded-lg mt-6 mb-2">
                                        <label for="file-bulkUpload-excel"
                                            class="w-full h-full flex justify-center items-center cursor-pointer gap-2">
                                            <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                            <span>Upload File</span>
                                        </label>
                                        <input id="file-bulkUpload-excel" name="bulkUpload-subject-passing-grade-criteria" class="hidden" onchange="previewExcel(event, 'bulkUpload-excel')" 
                                            type="file" accept=".xlsx">
                                        <span id="error-bulkUpload-subject-passing-grade-criteria" class="text-red-500 font-bold text-xs pt-2"></span>
                                    </div>
                                </div>

                                <!-- Tombol Kirim -->
                                <div class="flex justify-end mt-8 z-[-1]">
                                    <button id="submit-button-bulkUpload-subject-passing-grade-criteria" type="button"
                                        class="bg-[#4189e0] hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg shadow-md transition-all outline-none 
                                            cursor-pointer disabled:cursor-default">
                                        Kirim
                                    </button>
                                </div>
                            </form>
                        </div>

                        <form method="dialog" class="modal-backdrop">
                            <button>Close</button>
                        </form>
                    </dialog>
                </section>
            </main>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center">
        <p>ALERT SEMENTARA</p>
        <p>You do not have access to this pages.</p>
    </div>
@endif

<script src="{{ asset('assets/js/features/lms/administrator/subject-passing-grade-criteria/lms-subject-passing-grade-criteria.js') }}"></script>
<script src="{{ asset('assets/js/features/lms/administrator/subject-passing-grade-criteria/form-action-bulkUpload-subject-passing-grade-criteria.js') }}"></script>

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/dependent-dropdown/kurikulum-kelas-mapel-bab-sub_bab-dropdown.js') }}"></script> <!--- dependent dropdown ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->
<script src="{{ asset('assets/js/components/preview/excel-upload-preview.js') }}"></script> <!--- show excel preview ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/subject-passing-grade-criteria/lms-subject-passing-grade-criteria-listener.js') }}"></script>