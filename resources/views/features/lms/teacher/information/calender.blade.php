@include('components/sidebar-beranda', [
    'headerSideNav' => 'Kalender Akademik'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="my-8 mx-6 lg:mx-10">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-5 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h1 class="text-2xl font-extrabold text-[#0071BC] tracking-tight">Kalender Akademik 2026</h1>
                    <p class="text-gray-500 mt-1.5 text-sm font-medium">Kelola agenda dan sinkronisasi libur nasional secara otomatis.</p>
                </div>
                <div class="flex gap-3 w-full md:w-auto">
                    <button id="btn-draft" onclick="saveCalendarData('draft')" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl border border-blue-200 bg-blue-50 text-[#0071BC] hover:bg-blue-100 transition-colors text-sm font-semibold cursor-pointer shadow-sm">
                        <i class="fas fa-file-lines"></i> Simpan Draft
                    </button>
                    <button id="btn-upload" onclick="saveCalendarData('published')" class="flex-1 md:flex-none flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#0071BC] to-[#005B94] text-white hover:shadow-lg hover:shadow-[#0071BC]/30 transition-all text-sm font-semibold cursor-pointer transform hover:-translate-y-0.5">
                        <i class="fas fa-cloud-arrow-up"></i> Upload & Publish
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-40 h-40 bg-blue-50 rounded-bl-full -z-0 opacity-50"></div>
                    
                    <div class="flex justify-between items-center mb-8 relative z-10">
                        <h2 id="calendar-month-year" class="text-2xl lg:text-3xl font-bold text-gray-800"></h2>
                        <div class="flex gap-2 bg-gray-50 p-1.5 rounded-full border border-gray-100">
                            <button id="prev-month" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white hover:shadow-sm transition-all cursor-pointer text-gray-600 hover:text-[#0071BC]">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button id="next-month" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white hover:shadow-sm transition-all cursor-pointer text-gray-600 hover:text-[#0071BC]">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-2 text-center text-xs lg:text-sm font-bold text-gray-400 mb-6 uppercase tracking-wider relative z-10">
                        <div class="text-red-500">Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                    </div>
                    
                    <div id="calendar-days" class="grid grid-cols-7 gap-y-4 lg:gap-y-6 gap-x-2 text-center text-md font-bold relative z-10"></div>
                </div>

                <div class="flex flex-col gap-6">
                    
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7">
                        <h3 class="font-extrabold text-gray-800 mb-5 text-lg flex items-center gap-2">
                            <i class="fas fa-palette text-[#0071BC]"></i> Panduan Warna
                        </h3>
                        <div class="flex flex-col gap-4 text-sm font-semibold text-gray-600">
                            <div class="flex items-center gap-4 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-6 h-6 rounded-full bg-[#B91C1C] shadow-sm"></div> <span>Libur Nasional</span>
                            </div>
                            <div class="flex items-center gap-4 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-6 h-6 rounded-full border-[3px] border-[#B91C1C] bg-white"></div> <span>Cuti / Libur Sekolah</span>
                            </div>
                            <div class="flex items-center gap-4 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-6 h-6 rounded-full border-[3px] border-[#10B981] bg-white"></div> <span>Hari Ujian</span>
                            </div>
                            <div class="flex items-center gap-4 hover:translate-x-1 transition-transform cursor-default">
                                <div class="w-6 h-6 rounded-full border-[3px] border-[#F59E0B] bg-white"></div> <span>Kegiatan Sekolah</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-7 flex flex-col max-h-[450px]">
                        <h3 class="font-extrabold text-gray-800 mb-5 text-lg flex items-center gap-2 shrink-0">
                            <i class="fas fa-list-check text-[#0071BC]"></i> Kegiatan & Libur
                        </h3>
                        <div id="event-list" class="flex flex-col gap-4 text-sm overflow-y-auto pr-3 custom-scrollbar flex-1">
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<dialog id="modal_tambah_kegiatan" class="modal backdrop:bg-black/40 backdrop:backdrop-blur-sm">
    <div class="modal-box bg-white w-11/12 max-w-md rounded-2xl p-7 shadow-2xl">
        <h3 class="font-bold text-xl text-gray-800 mb-6 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-50 text-[#0071BC] rounded-full flex items-center justify-center">
                <i class="fas fa-calendar-plus"></i>
            </div>
            Tambah Kegiatan
        </h3>
        <form id="form-tambah-kegiatan" class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Judul Kegiatan</label>
                <input type="text" id="event-title" required placeholder="Contoh: Ujian Tengah Semester" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Mulai</label>
                    <input type="date" id="event-start-date" required class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all text-gray-600">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Selesai</label>
                    <input type="date" id="event-end-date" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all text-gray-600">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Kategori</label>
                <select id="event-type" required class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all">
                    <option value="exam">Hari Ujian (Hijau)</option>
                    <option value="school_event">Kegiatan Sekolah (Kuning)</option>
                    <option value="wfa">Kegiatan WFA (Biru)</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-100">
                <button type="button" class="px-5 py-2.5 text-gray-600 font-semibold bg-gray-100 hover:bg-gray-200 rounded-xl cursor-pointer transition-colors" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="px-6 py-2.5 bg-[#0071BC] hover:bg-blue-800 font-semibold text-white rounded-xl cursor-pointer transition-colors shadow-md shadow-[#0071BC]/20">Simpan Agenda</button>
            </div>
        </form>
    </div>
</dialog>

<style>
    /* Styling Scrollbar Estetik */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    let userEvents = @json($savedEvents ?? []);
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    let currentDate = new Date(2026, 2, 1); 

    const nationalHolidays = {
        "2026-01-01": "Tahun Baru 2026 Masehi", "2026-01-16": "Isra Mikraj Nabi Muhammad SAW",
        "2026-02-17": "Tahun Baru Imlek 2577 Kongzili", "2026-03-19": "Hari Suci Nyepi (Tahun Baru Saka 1948)",
        "2026-03-21": "Idul Fitri 1447 Hijriah", "2026-03-22": "Idul Fitri 1447 Hijriah",
        "2026-04-03": "Wafat Yesus Kristus", "2026-04-05": "Hari Paskah",
        "2026-05-01": "Hari Buruh Internasional", "2026-05-14": "Kenaikan Yesus Kristus",
        "2026-05-27": "Idul Adha 1447 Hijriah", "2026-05-31": "Hari Raya Waisak 2570 BE",
        "2026-06-01": "Hari Lahir Pancasila", "2026-06-16": "Tahun Baru Islam 1448 Hijriah",
        "2026-08-17": "Proklamasi Kemerdekaan RI", "2026-08-25": "Maulid Nabi Muhammad SAW",
        "2026-12-25": "Hari Raya Natal"
    };

    const cutiBersama = {
        "2026-02-16": "Cuti Bersama Imlek", "2026-03-18": "Cuti Bersama Nyepi",
        "2026-03-20": "Cuti Bersama Idul Fitri", "2026-03-23": "Cuti Bersama Idul Fitri",
        "2026-03-24": "Cuti Bersama Idul Fitri", "2026-05-15": "Cuti Bersama Kenaikan Yesus Kristus",
        "2026-05-28": "Cuti Bersama Idul Adha", "2026-12-24": "Cuti Bersama Natal"
    };

    function renderCalendar(date) {
        const daysEl = document.getElementById('calendar-days');
        const year = date.getFullYear();
        const month = date.getMonth();
        document.getElementById('calendar-month-year').innerText = `${monthNames[month]} ${year}`;
        daysEl.innerHTML = "";

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        for (let i = 0; i < firstDay; i++) daysEl.innerHTML += `<div></div>`;

        for (let i = 1; i <= daysInMonth; i++) {
            let dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
            let isSunday = new Date(year, month, i).getDay() === 0;
            let css = "";
            let baseClasses = "w-11 h-11 lg:w-12 lg:h-12 mx-auto flex items-center justify-center rounded-xl cursor-pointer transition-all duration-300 text-gray-700 font-bold border border-transparent hover:scale-110 hover:shadow-md hover:border-blue-200 ";

            if (nationalHolidays[dateStr]) {
                css = "background-color: #B91C1C; color: white; border: none;"; 
                baseClasses += " hover:bg-red-800";
            } else if (cutiBersama[dateStr] || isSunday) {
                css = "border: 2px solid #B91C1C; color: #B91C1C; background: white;"; 
                baseClasses += " hover:bg-red-50";
            } else {
                let ev = userEvents.find(e => e.date === dateStr);
                if(ev) {
                    css = `border: 2px solid ${ev.color}; color: ${ev.color}; background: white;`;
                    baseClasses += " hover:bg-gray-50";
                } else {
                    baseClasses += " hover:bg-blue-50 hover:text-[#0071BC]";
                }
            }

            daysEl.innerHTML += `<div class="py-1"><div class="${baseClasses}" style="${css}" onclick="selectDate('${dateStr}')">${i}</div></div>`;
        }
        renderSideList(year, month);
    }

    function renderSideList(year, month) {
        const listEl = document.getElementById('event-list');
        listEl.innerHTML = "";
        let prefix = `${year}-${String(month + 1).padStart(2, '0')}`;
        let all = [];

        Object.entries(nationalHolidays).forEach(([d, t]) => { if(d.startsWith(prefix)) all.push({d, t, c: '#B91C1C'}); });
        Object.entries(cutiBersama).forEach(([d, t]) => { if(d.startsWith(prefix)) all.push({d, t, c: '#B91C1C'}); });
        userEvents.forEach(e => { if(e.date.startsWith(prefix)) all.push({d: e.date, t: e.title, c: e.color}); });

        all.sort((a,b) => a.d.localeCompare(b.d));

        if (all.length === 0) {
            listEl.innerHTML = `<div class="text-center py-12 flex flex-col items-center justify-center text-gray-400"><i class="fas fa-calendar-xmark text-4xl mb-3 opacity-50"></i><span>Tidak ada agenda di bulan ini.</span></div>`;
            return;
        }

        all.forEach(item => {
            listEl.innerHTML += `
                <div class="group border border-gray-100 bg-gray-50 hover:bg-white hover:shadow-md transition-all rounded-xl p-3.5 flex items-start gap-3">
                    <div class="w-3 h-3 rounded-full mt-1.5 shrink-0" style="background-color: ${item.c}; box-shadow: 0 0 0 3px ${item.c}20;"></div>
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-800 text-[13px] group-hover:text-[#0071BC] transition-colors">${item.d.split('-')[2]} ${monthNames[month]} ${year}</span>
                        <span class="text-gray-500 text-[12px] mt-0.5 leading-snug">${item.t}</span>
                    </div>
                </div>`;
        });
    }

    window.selectDate = function(dateStr) {
        if (nationalHolidays[dateStr] || cutiBersama[dateStr] || new Date(dateStr).getDay() === 0) {
            alert(`Tanggal ${dateStr} adalah hari libur.`); return;
        }
        document.getElementById('event-start-date').value = dateStr;
        document.getElementById('modal_tambah_kegiatan').showModal();
    };

    document.getElementById('form-tambah-kegiatan').onsubmit = function(e) {
        e.preventDefault();
        const start = new Date(document.getElementById('event-start-date').value);
        const end = document.getElementById('event-end-date').value ? new Date(document.getElementById('event-end-date').value) : start;
        const type = document.getElementById('event-type').value;
        const colors = { exam: '#10B981', school_event: '#F59E0B', wfa: '#1E3A8A' };

        for(let d = new Date(start); d <= end; d.setDate(d.getDate()+1)) {
            // Pengamanan zona waktu agar tanggal tidak mundur 1 hari
            let localYear = d.getFullYear();
            let localMonth = String(d.getMonth() + 1).padStart(2, '0');
            let localDay = String(d.getDate()).padStart(2, '0');
            let s = `${localYear}-${localMonth}-${localDay}`;
            
            if (!nationalHolidays[s] && !cutiBersama[s] && d.getDay() !== 0) {
                userEvents = userEvents.filter(x => x.date !== s);
                userEvents.push({ date: s, title: document.getElementById('event-title').value, type: type, color: colors[type] });
            }
        }
        
        document.getElementById('modal_tambah_kegiatan').close();
        renderCalendar(currentDate);
        
        // Auto Save ke database
        saveCalendarData('published');
    };

    document.getElementById('prev-month').onclick = () => { currentDate.setMonth(currentDate.getMonth()-1); renderCalendar(currentDate); };
    document.getElementById('next-month').onclick = () => { currentDate.setMonth(currentDate.getMonth()+1); renderCalendar(currentDate); };

    async function saveCalendarData(statusType) {
        // Cegah konfirmasi jika ini adalah Auto-Save (tidak diklik langsung oleh user)
        if (event && event.type === 'click' && !confirm(statusType === 'draft' ? "Simpan sebagai draft?" : "Publish agenda ke siswa?")) return;
        
        const btnId = statusType === 'draft' ? 'btn-draft' : 'btn-upload';
        const btn = document.getElementById(btnId);
        let originalText = "Simpan";
        
        if (btn) {
            originalText = btn.innerHTML;
            btn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Menyimpan...`;
            btn.disabled = true;
        }

        try {
            const url = `{{ route('lms.teacherCalendar.save', ['role' => $role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}`;
            
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ status: statusType, events: userEvents })
            });

            // RADAR ERROR AKTIF: Jika terjadi error 500 dari Controller/Database
            if (!response.ok) {
                throw new Error(`HTTP Error Status: ${response.status}`);
            }

            const result = await response.json();
            
            // JIKA BERHASIL (Mencegah alert muncul berulang kali jika sukses auto-save)
            if(event && event.type === 'click'){
                alert("INFO: " + (result.message || "Berhasil disimpan."));
            }
            
        } catch (error) {
            console.error("Fetch Error Details:", error);
            // INI ADALAH PESAN JUJUR JIKA TERJADI KEGAGALAN DI BACKEND/DATABASE
            alert("⚠️ GAGAL MENYIMPAN KE DATABASE!\n\nPastikan route 'web.php' Anda sudah memanggil fungsi 'saveCalendarData' (bukan saveCalendar). Hubungi teknisi jika masalah berlanjut.");
        } finally {
            if (btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }
    }

    document.addEventListener("DOMContentLoaded", () => renderCalendar(currentDate));
</script>