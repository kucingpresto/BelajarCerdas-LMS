@include('components/sidebar-beranda', [
    'headerSideNav' => 'Jadwal Pelajaran'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="pt-8 mx-6 lg:mx-10">
            
            <div class="sticky top-[85px] lg:top-[100px] z-40 flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-5 bg-white/85 backdrop-blur-md p-6 rounded-2xl shadow-sm border border-gray-100 transition-all">
                <div>
                    <h1 class="text-2xl font-extrabold text-[#0071BC] tracking-tight">Manajemen Jadwal</h1>
                    <p class="text-gray-500 mt-1.5 text-sm font-medium">Tarik (Drag) kartu mapel ke dalam tabel untuk menyusun jadwal kelas.</p>
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-4 w-full xl:w-auto">
                    <div class="w-full sm:w-56 relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fas fa-users-class text-gray-400"></i>
                        </div>
                        <select id="class-selector" class="w-full border border-gray-300 rounded-xl pl-10 pr-4 py-2.5 text-sm font-bold text-gray-700 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all cursor-pointer appearance-none">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls }}">{{ $cls }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 w-full sm:w-auto">
                        <button onclick="saveScheduleData('draft')" class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-[#0071BC] hover:bg-blue-100 transition-colors text-sm font-semibold cursor-pointer shadow-sm text-center">
                            Simpan Draft
                        </button>
                        <button onclick="saveScheduleData('published')" class="flex-1 sm:flex-none flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#0071BC] to-[#005B94] text-white hover:shadow-lg hover:shadow-[#0071BC]/30 transition-all text-sm font-semibold cursor-pointer transform hover:-translate-y-0.5">
                            <i class="fas fa-paper-plane"></i> Publish
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col xl:flex-row gap-8">
                
                <div class="w-full xl:w-72 shrink-0 relative">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sticky top-[270px] lg:top-[220px] z-30">
                        <div class="border-b border-gray-100 pb-4 mb-5">
                            <h3 class="font-extrabold text-gray-800 text-lg flex items-center gap-2">
                                <i class="fas fa-layer-group text-[#0071BC]"></i> Daftar Mapel
                            </h3>
                            <p class="text-xs text-gray-500 mt-1 font-medium">Tarik kartu ke papan jadwal dikanan.</p>
                        </div>
                        
                        <div class="flex flex-col gap-3.5 max-h-[450px] overflow-y-auto custom-scrollbar pr-2 pb-2">
                            @foreach($teachers as $teacher)
                                <div draggable="true" 
                                     ondragstart="dragStart(event)"
                                     data-teacher-id="{{ $teacher['id'] }}"
                                     data-teacher-name="{{ $teacher['name'] }}"
                                     data-subject="{{ $teacher['subject'] }}"
                                     data-color="{{ $teacher['color'] }}"
                                     class="draggable-card relative p-3.5 rounded-xl border border-gray-100 shadow-sm cursor-grab hover:shadow-md transition-all bg-white group overflow-hidden"
                                     style="border-left: 6px solid {{ $teacher['color'] }};">
                                     
                                    <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity" style="background-color: {{ $teacher['color'] }};"></div>
                                    
                                    <div class="relative z-10 flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-[13px] mb-1 leading-tight">{{ $teacher['subject'] }}</h4>
                                            <p class="text-[11px] text-gray-500 font-semibold flex items-center gap-1.5"><i class="fas fa-user-tie text-gray-400"></i> {{ Str::limit($teacher['name'], 20) }}</p>
                                        </div>
                                        <i class="fas fa-grip-vertical text-gray-300 group-hover:text-gray-400 transition-colors"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 relative overflow-hidden z-10">
                    
                    <div id="schedule-overlay" class="absolute inset-0 bg-white/70 backdrop-blur-sm z-20 flex items-center justify-center transition-all duration-300">
                        <div class="bg-white px-8 py-6 rounded-2xl shadow-2xl font-bold text-[#0071BC] border border-blue-100 flex flex-col items-center gap-3 transform scale-100 transition-transform">
                            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mb-2">
                                <i class="fas fa-hand-pointer text-3xl animate-bounce"></i>
                            </div>
                            <span class="text-lg">Pilih Kelas Dulu</span>
                            <span class="text-sm font-medium text-gray-500 text-center max-w-xs">Silakan pilih nama kelas di bagian atas untuk mulai menyusun jadwal.</span>
                        </div>
                    </div>

                    <div class="overflow-x-auto custom-scrollbar pb-4">
                        <table class="w-full min-w-[850px] border-collapse">
                            <thead>
                                <tr>
                                    <th class="py-4 px-4 bg-gray-50 border-b-2 border-r border-gray-200 text-gray-500 font-extrabold text-xs tracking-wider uppercase w-28 rounded-tl-xl">Waktu</th>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                        <th class="py-4 px-4 bg-gray-50 border-b-2 border-gray-200 text-gray-700 font-extrabold text-xs tracking-wider uppercase {{ $loop->last ? 'rounded-tr-xl' : 'border-r' }}">{{ $day }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($timeSlots as $index => $slot)
                                    @if($slot['is_break'])
                                        <tr class="bg-orange-50/50 hover:bg-orange-50 transition-colors">
                                            <td class="py-3.5 px-4 border-b border-r border-gray-100 text-center text-xs font-bold text-orange-600">
                                                {{ $slot['start'] }}<br><span class="text-[10px] text-orange-400 font-medium">{{ $slot['end'] }}</span>
                                            </td>
                                            <td colspan="5" class="py-3.5 px-4 border-b border-gray-100 text-center text-sm font-bold text-orange-500 tracking-[0.4em] relative overflow-hidden">
                                                <div class="absolute inset-0 opacity-10" style="background-image: repeating-linear-gradient(45deg, transparent, transparent 10px, #f97316 10px, #f97316 20px);"></div>
                                                <span class="relative z-10 bg-orange-50 px-4 py-1 rounded-full"><i class="fas fa-mug-hot mr-2"></i> ISTIRAHAT</span>
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="hover:bg-gray-50/30 transition-colors">
                                            <td class="py-4 px-4 border-b border-r border-gray-100 text-center text-xs font-bold text-gray-600 align-middle">
                                                {{ $slot['start'] }}<br><span class="text-[10px] text-gray-400 font-medium">{{ $slot['end'] }}</span>
                                            </td>
                                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                                <td class="drop-zone p-2.5 border-b border-gray-100 h-[5.5rem] align-top {{ $loop->last ? '' : 'border-r' }}"
                                                    data-day="{{ $day }}" 
                                                    data-start="{{ $slot['start'] }}"
                                                    ondragover="dragOver(event)" 
                                                    ondragleave="dragLeave(event)"
                                                    ondrop="drop(event)">
                                                    <div class="slot-content w-full h-full min-h-[4.5rem] rounded-xl flex flex-col justify-center items-center text-gray-300 border-2 border-dashed border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all cursor-pointer">
                                                        <i class="fas fa-plus text-gray-200 mb-1 text-sm"></i>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif

<style>
    /* Animasi Drag & Drop */
    .draggable-card:active { cursor: grabbing !important; transform: scale(0.97); opacity: 0.9; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
    .drop-zone.drag-over .slot-content { 
        border-color: #0071BC; 
        background-color: #eff6ff; 
        transform: scale(0.98);
    }
    
    /* Scrollbar Estetik */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px;}
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    // --- LOGIKA KUNCI KELAS ---
    const classSelector = document.getElementById('class-selector');
    const overlay = document.getElementById('schedule-overlay');

    classSelector.addEventListener('change', async function() {
        if(this.value !== "") {
            overlay.classList.add('opacity-0', 'invisible'); 
            
            // --- FITUR TARIK DATA JADWAL DARI DATABASE ---
            // 1. Kosongkan papan jadwal dulu
            document.querySelectorAll('.drop-zone .slot-content').forEach(slot => {
                slot.innerHTML = `<i class="fas fa-plus text-gray-200 mb-1 text-sm"></i>`;
                slot.classList.remove('border-transparent');
                slot.classList.add('border-dashed', 'border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-50');
            });

            try {
                // 2. Ambil data dari Database
                const response = await fetch(`/lms/{{ $schoolId }}/teacher-schedule/get-data/${this.value}`);
                const result = await response.json();

                // 3. Pasang kartu jadwal ke tabel jika datanya ada
                if (result.success && result.data.length > 0) {
                    result.data.forEach(item => {
                        // Cari kotak yang cocok dengan hari dan jamnya
                        const zone = document.querySelector(`.drop-zone[data-day="${item.day_of_week}"][data-start="${item.start_time}"]`);
                        if (zone) {
                            const slotContent = zone.querySelector('.slot-content');
                            slotContent.innerHTML = `
                                <div class="schedule-item w-full h-full rounded-xl p-2 flex flex-col justify-center items-center text-center shadow-sm relative group cursor-default transition-all hover:shadow-md" 
                                     style="background-color: ${item.color}15; border: 1px solid ${item.color}30;"
                                     data-teacher-id="${item.teacher_id}"
                                     data-teacher-name="${item.teacher_name}"
                                     data-subject="${item.subject_name}"
                                     data-color="${item.color}">
                                     
                                    <div class="absolute top-0 left-0 w-1.5 h-full rounded-l-xl" style="background-color: ${item.color}; opacity: 0.8;"></div>
                                    
                                    <span class="font-extrabold text-[11px] leading-tight text-gray-800 px-1 truncate w-full" title="${item.subject_name}">${item.subject_name}</span>
                                    <span class="text-[9px] text-gray-600 font-semibold mt-1 truncate w-full" title="${item.teacher_name}"><i class="fas fa-user-tie mr-1 opacity-60"></i>${item.teacher_name}</span>
                                    
                                    <button onclick="removeSchedule(this, event)" class="absolute -top-2.5 -right-2.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] opacity-0 group-hover:opacity-100 hover:bg-red-600 hover:scale-110 transition-all shadow-md z-10">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            `;
                            slotContent.classList.remove('border-dashed', 'border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-50');
                            slotContent.classList.add('border-transparent');
                        }
                    });
                }
            } catch (error) {
                console.error("Gagal menarik data:", error);
            }

        } else {
            overlay.classList.remove('opacity-0', 'invisible'); 
        }
    });

    // --- LOGIKA DRAG & DROP ---
    let draggedData = null;

    function dragStart(event) {
        const target = event.currentTarget;
        draggedData = {
            id: target.getAttribute('data-teacher-id'),
            name: target.getAttribute('data-teacher-name'),
            subject: target.getAttribute('data-subject'),
            color: target.getAttribute('data-color')
        };
        event.dataTransfer.setData('text/plain', 'draggable-card'); 
        event.dataTransfer.effectAllowed = 'copy';
    }

    function dragOver(event) {
        event.preventDefault(); 
        event.currentTarget.classList.add('drag-over');
    }

    function dragLeave(event) {
        event.currentTarget.classList.remove('drag-over');
    }

    function drop(event) {
        event.preventDefault();
        const zone = event.currentTarget;
        zone.classList.remove('drag-over');

        if (!draggedData) return;

        const slotContent = zone.querySelector('.slot-content');
        
        // Render Desain Kartu setelah di-drop
        slotContent.innerHTML = `
            <div class="schedule-item w-full h-full rounded-xl p-2 flex flex-col justify-center items-center text-center shadow-sm relative group cursor-default transition-all hover:shadow-md" 
                 style="background-color: ${draggedData.color}15; border: 1px solid ${draggedData.color}30;"
                 data-teacher-id="${draggedData.id}"
                 data-teacher-name="${draggedData.name}"
                 data-subject="${draggedData.subject}"
                 data-color="${draggedData.color}">
                 
                <div class="absolute top-0 left-0 w-1.5 h-full rounded-l-xl" style="background-color: ${draggedData.color}; opacity: 0.8;"></div>
                
                <span class="font-extrabold text-[11px] leading-tight text-gray-800 px-1 truncate w-full" title="${draggedData.subject}">${draggedData.subject}</span>
                <span class="text-[9px] text-gray-600 font-semibold mt-1 truncate w-full" title="${draggedData.name}"><i class="fas fa-user-tie mr-1 opacity-60"></i>${draggedData.name}</span>
                
                <button onclick="removeSchedule(this, event)" class="absolute -top-2.5 -right-2.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] opacity-0 group-hover:opacity-100 hover:bg-red-600 hover:scale-110 transition-all shadow-md z-10">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        slotContent.classList.remove('border-dashed', 'border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-50');
        slotContent.classList.add('border-transparent');
    }

    function removeSchedule(buttonElement, event) {
        event.stopPropagation(); // Mencegah interaksi aneh
        const slotContent = buttonElement.closest('.slot-content');
        slotContent.innerHTML = `<i class="fas fa-plus text-gray-200 mb-1 text-sm"></i>`;
        slotContent.classList.remove('border-transparent');
        slotContent.classList.add('border-dashed', 'border-gray-200', 'hover:border-gray-300', 'hover:bg-gray-50');
    }

    // --- FUNGSI SIMPAN & CEK BENTROK KE DATABASE (AJAX) ---
    async function saveScheduleData(statusType) {
        const className = classSelector.value;
        if (!className) {
            alert("Silakan pilih kelas di bagian atas terlebih dahulu!");
            return;
        }

        let schedules = [];
        document.querySelectorAll('.drop-zone').forEach(zone => {
            const item = zone.querySelector('.schedule-item');
            if (item) {
                schedules.push({
                    day: zone.getAttribute('data-day'),
                    start_time: zone.getAttribute('data-start'),
                    teacher_id: item.getAttribute('data-teacher-id'),
                    teacher_name: item.getAttribute('data-teacher-name'),
                    subject_name: item.getAttribute('data-subject'),
                    color: item.getAttribute('data-color')
                });
            }
        });

        const btn = event.currentTarget;
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...`;
        btn.disabled = true;

        try {
            const url = `{{ route('lms.teacherSchedule.save', ['role' => $role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}`;
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    class_name: className,
                    status: statusType,
                    schedules: schedules
                })
            });

            const result = await response.json();
            
            if (result.success) {
                alert(result.message);
            } else {
                alert(result.message);
            }
        } catch (error) {
            console.error("Error:", error);
            alert("Terjadi kesalahan sistem saat menyimpan jadwal.");
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
</script>