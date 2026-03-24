@include('components/sidebar-beranda', [
    'headerSideNav' => 'Assessment Weight',
    'linkBackButton' => route('lms.academicManagement.view', [$schoolName, $schoolId]),
    'backButton' => "<i class='fa-solid fa-chevron-left'></i>",
]);

@if (Auth::user()->role === 'Administrator')
    <div class="relative left-0 md:left-62.5 w-full md:w-[calc(100%-250px)] transition-all duration-500 ease-in-out z-20">
        <div class="my-15 mx-7.5">

            <div id="alert-success-insert-data-assessment-weight"></div>
            <div id="alert-success-edit-data-assessment-weight"></div>

            <main>
                <section>
                    <!---- DETAIL SEKOLAH ---->
                    <div id="school-detail-card" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-8 hidden"></div>
                </section>

                <section class="bg-white shadow-lg p-6 rounded-lg border-gray-200 border">
                    <!---- Form input assessment weight ---->
                    <form id="create-assessment-weight-form" autocomplete="off">
                        <div class="py-6 space-y-8">

                            <!-- HEADER -->
                            <div class="space-y-2">
                                <h2 class="text-lg font-bold text-gray-800">
                                    Atur Bobot Asesmen
                                </h2>
                                <p class="text-sm text-gray-500">
                                    Tentukan bobot nilai untuk setiap jenis asesmen. Total bobot seluruh asesmen harus mencapai 100%.
                                </p>
                            </div>

                            <!-- BASIC INFO -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                                <div>
                                    <label class="text-sm font-semibold text-gray-700">
                                        Tipe Asesmen
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <select name="assessment_type_id"
                                        class="mt-2 w-full h-11 rounded-full border border-gray-200 px-4 text-xs shadow-sm outline-none cursor-pointer">
                                        <option value="" class="hidden">Pilih Tipe Asesmen</option>
                                        @foreach ($assessmentType as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                    <span id="error-assessment_type_id" class="text-red-500 text-xs mt-1 font-bold"></span>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-gray-700">
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
                                    <label class="text-sm font-semibold text-gray-700">
                                        Bobot Asesmen (%)
                                        <sup class="text-red-500">&#42;</sup>
                                    </label>
                                    <input type="number" name="weight" min="0" max="100" class="mt-2 w-full h-11 rounded-full border border-gray-200 px-4 text-xs 
                                        shadow-sm outline-none" placeholder="Masukkan bobot nilai asesmen">
                                    <span id="error-weight" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    <p class="text-xs text-gray-400 relative pt-2">Bobot antara 1–100</p>
                                </div>

                            </div>

                            <!-- SUBMIT -->
                            <div class="flex justify-end pt-4">
                                <button type="button" id="submit-button-create-assessment-weight"
                                    class="bg-[#0071BC] text-white font-semibold text-sm px-8 py-3 rounded-full shadow-md transition cursor-pointer disabled:cursor-default">
                                    Simpan Bobot Asesmen
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="border-b-2 border-gray-200 mt-4"></div>

                    <!---- Table list data assessment weight ---->
                    <div id="container-assessment-weight-management" data-school-name="{{ $schoolName }}" data-school-id="{{ $schoolId }}" class="overflow-x-auto mt-8 pb-24">

                        <h2 class="text-lg font-bold text-gray-800 pb-6">
                            Assessment Weight List
                        </h2>

                        <!-- FILTER CONTAINER -->
                        <div class="my-6 bg-white shadow-sm border border-gray-300 rounded-2xl p-6">

                            <!-- Header Filter -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-filter text-[#0071BC]"></i>
                                    <h3 class="text-base font-semibold text-gray-800">
                                        Filter Assessment
                                    </h3>
                                </div>
                            </div>

                            <!-- Filter Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">

                                <div id="container-dropdown-assessment-weight-tahun-ajaran"></div>

                            </div>
                        </div>
                                
                        <table id="table-assessment-weight-management" class="min-w-full text-sm border-collapse">
                            <thead class="thead-table-assessment-weight-management hidden bg-gray-50 shadow-inner">
                                <tr>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Nama Asesmen
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Bobot Asesmen
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        Tahun Ajaran
                                    </th>
                                    <th class="border border-gray-300 px-3 py-2 opacity-70 text-xs">
                                        <i class="fa-solid fa-ellipsis-vertical cursor-pointer"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="table-list-assessment-weight-management">
                                <!-- show data in ajax -->
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container-assessment-weight-management flex justify-center my-4 sm:my-0"></div>

                    <div id="empty-message-assessment-weight-management" class="w-full h-96 hidden">
                        <span class="w-full h-full flex items-center justify-center">
                            Tidak ada data.
                        </span>
                    </div>

                    <!---- modal edit assessment weight ---->
                    <dialog id="my_modal_1" class="modal">
                        <div class="modal-box bg-white w-max lg:w-6xl">
                            <form id="edit-assessment-weight-form" autocomplete="OFF" class="">
                                <span class="text-xl font-bold flex justify-center">Edit Assessment Weight</span>

                                <input type="hidden" id="edit-assessment-weight-id">

                                <div class="flex flex-col gap-8 w-full mt-8">

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">
                                            Tipe Asesmen
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>
                                        <select id="edit-assessment-type-id" name="assessment_type_id"
                                            class="mt-2 w-full h-11 rounded-full border border-gray-200 px-4 text-xs shadow-sm outline-none cursor-pointer">
                                            <option value="" class="hidden">Pilih Tipe Asesmen</option>
                                            @foreach ($assessmentType as $item)
                                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                        <span id="error-assessment_type_id" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">
                                            Tahun Ajaran
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>
                                        <select id="edit-school-year" name="school_year"
                                            class="mt-2 w-full h-11 rounded-full border border-gray-200 px-4 text-xs shadow-sm outline-none cursor-pointer">
                                            <option value="" class="hidden">Pilih Tahun Ajaran</option>
                                            @foreach ($tahunAjaran as $item)
                                                <option value="{{ $item }}">{{ $item }}</option>
                                            @endforeach
                                        </select>
                                        <span id="error-school_year" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    </div>

                                    <div>
                                        <label class="text-sm font-semibold text-gray-700">
                                            Bobot Asesmen (%)
                                            <sup class="text-red-500">&#42;</sup>
                                        </label>
                                        <input type="number" id="edit-weight" name="weight" min="0" max="100" class="mt-2 w-full h-11 rounded-full border 
                                            border-gray-200 px-4 text-xs shadow-sm outline-none" placeholder="Masukkan bobot nilai asesmen">
                                        <span id="error-weight" class="text-red-500 text-xs mt-1 font-bold"></span>
                                    </div>
                                </div>

                                <div class="flex justify-end mt-8">
                                    <button id="submit-button-edit-assessment-weight" type="button"
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

                    <!---- modal history assessment weight ---->
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

<script src="{{ asset('assets/js/features/lms/administrator/assessment-weight-management/lms-assessment-weight-management.js') }}"></script>

<!--- COMPONENTS ---->
<script src="{{ asset('assets/js/components/clear-error-on-input.js') }}"></script> <!--- clear error on input ---->

<!--- PUSHER LISTENER ---->
<script src="{{ asset('assets/js/pusher-listener/assessment-weight-management/lms-assessment-weight-management-listener.js') }}"></script>