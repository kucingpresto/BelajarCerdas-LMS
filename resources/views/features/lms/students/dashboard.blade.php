@include('components/sidebar-beranda', ['headerSideNav' => 'Beranda Siswa'])

@if (Auth::user()->role === 'Siswa')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen">

        <div class="p-6 md:p-8">
            
            <div class="mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                    Halo, {{ explode(' ', Auth::user()->StudentProfile->nama_lengkap)[0] ?? 'Siswa' }}! 👋
                </h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Mari bersiap untuk kegiatan belajarmu hari ini.</p>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <div class="xl:col-span-2 flex flex-col gap-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 h-full">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                            <h2 class="text-xl font-bold text-[#0071BC] flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-book-open text-lg"></i>
                                </div>
                                Jadwal Pelajaran
                            </h2>
                            <span class="text-sm font-semibold text-gray-600 bg-gray-100 px-5 py-2 rounded-full border border-gray-200">
                                {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-4">
                            @foreach($jadwalHariIni as $jadwal)
                                <div class="flex flex-col md:flex-row md:items-center gap-4 p-4 rounded-2xl border {{ $jadwal['mapel'] == 'ISTIRAHAT' ? 'bg-orange-50 border-orange-100' : 'bg-white border-gray-100 hover:border-blue-200 hover:shadow-md transition-all' }}">
                                    
                                    <div class="w-full md:w-32 shrink-0">
                                        <span class="font-bold text-gray-800 {{ $jadwal['mapel'] == 'ISTIRAHAT' ? 'text-orange-600' : '' }}">
                                            <i class="far fa-clock mr-1"></i> {{ $jadwal['jam'] }}
                                        </span>
                                    </div>
                                    
                                    <div class="hidden md:block w-1 h-10 {{ $jadwal['mapel'] == 'ISTIRAHAT' ? 'bg-orange-200' : 'bg-gray-200' }} rounded-full"></div>

                                    <div class="flex-1">
                                        <h3 class="font-bold text-lg {{ $jadwal['mapel'] == 'ISTIRAHAT' ? 'text-orange-600' : 'text-gray-800' }}">
                                            {{ $jadwal['mapel'] }}
                                        </h3>
                                        @if($jadwal['mapel'] != 'ISTIRAHAT')
                                            <p class="text-sm text-gray-500 mt-1 flex items-center gap-4">
                                                <span><i class="fas fa-user-tie text-gray-400 mr-1"></i> {{ $jadwal['guru'] }}</span>
                                                <span><i class="fas fa-door-open text-gray-400 mr-1"></i> {{ $jadwal['ruang'] }}</span>
                                            </p>
                                        @endif
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>

                <div class="xl:col-span-1">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 flex flex-col max-h-[500px]">
                        
                        <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100 shrink-0">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-orange-50 text-orange-500 rounded-xl flex items-center justify-center font-bold text-lg">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-bold text-gray-800 text-lg leading-tight">Agenda Kegiatan</h3>
                                    <p class="text-xs text-gray-500 font-medium">Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-6 overflow-y-auto pr-2 custom-scrollbar flex-1">
                            
                            @forelse($monthlyEvents as $event)
                                <div class="flex gap-4 items-start group">
                                    <div class="flex flex-col items-center mt-1">
                                        <div class="w-4 h-4 rounded-full shadow-sm border-2 border-white" style="background-color: {{ $event->color }}"></div>
                                        @if(!$loop->last)
                                            <div class="w-0.5 h-12 bg-gray-100 my-1 group-hover:bg-gray-200 transition"></div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex-1 pb-1">
                                        <span class="font-bold text-gray-800 block text-[15px] mb-1">
                                            {{ \Carbon\Carbon::parse($event->date)->translatedFormat('d F') }}
                                        </span>
                                        <span class="text-sm text-gray-600 block leading-snug">
                                            {{ $event->title }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 flex flex-col items-center justify-center h-full">
                                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-calendar-check text-2xl text-gray-300"></i>
                                    </div>
                                    <p class="text-sm text-gray-400 font-medium">Belum ada agenda<br>di bulan ini.</p>
                                </div>
                            @endforelse

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@else
    <div class="flex flex-col min-h-screen items-center justify-center bg-gray-50">
        <i class="fas fa-lock text-5xl text-red-400 mb-4"></i>
        <p class="font-bold text-xl text-red-500">Akses Ditolak</p>
        <p class="text-gray-500">Halaman ini khusus untuk Siswa.</p>
    </div>
@endif

<style>
    /* CSS Scrollbar untuk List Agenda di kanan */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>