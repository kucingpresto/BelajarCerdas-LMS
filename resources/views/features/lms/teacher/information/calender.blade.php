@include('components/sidebar-beranda', [
    'headerSideNav' => 'Kalender Akademik'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative left-0 md:left-72.5 w-full md:w-[calc(100%-290px)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen">
        <div class="my-10 mx-7.5">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-[#0071BC]">Kalender Akademik 2026</h1>
                    <p class="text-gray-500 mt-1 text-sm">Libur nasional otomatis disinkronkan dengan kalender Indonesia.</p>
                </div>
                <div class="flex gap-3">
                    <button id="btn-draft" onclick="saveCalendarData('draft')" class="px-6 py-2 rounded-lg border border-gray-300 bg-blue-50 text-blue-600 hover:bg-blue-100 transition text-sm font-semibold cursor-pointer">Simpan Draft</button>
                    <button id="btn-upload" onclick="saveCalendarData('published')" class="px-6 py-2 rounded-lg bg-[#0071BC] text-white hover:bg-blue-800 transition text-sm font-semibold cursor-pointer">Upload & Publish</button>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                <div class="xl:col-span-2 bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
                    <div class="flex justify-between items-center mb-8">
                        <h2 id="calendar-month-year" class="text-3xl font-semibold text-gray-800"></h2>
                        <div class="flex gap-2">
                            <button id="prev-month" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition cursor-pointer"><i class="fas fa-chevron-left text-gray-600"></i></button>
                            <button id="next-month" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-gray-100 transition cursor-pointer"><i class="fas fa-chevron-right text-gray-600"></i></button>
                        </div>
                    </div>
                    <div class="grid grid-cols-7 gap-2 text-center text-sm font-bold text-gray-800 mb-6">
                        <div class="text-red-600">Min</div><div>Sen</div><div>Sel</div><div>Rab</div><div>Kam</div><div>Jum</div><div>Sab</div>
                    </div>
                    <div id="calendar-days" class="grid grid-cols-7 gap-y-6 gap-x-2 text-center text-md font-bold"></div>
                </div>

                <div class="flex flex-col gap-6">
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-8">
                        <h3 class="font-bold text-gray-800 mb-5 text-lg">Panduan Warna</h3>
                        <div class="flex flex-col gap-4 text-sm font-bold text-gray-700">
                            <div class="flex items-center gap-4"><div class="w-7 h-7 rounded-full bg-[#B91C1C]"></div> <span>Libur Nasional</span></div>
                            <div class="flex items-center gap-4"><div class="w-7 h-7 rounded-full border-[3px] border-[#B91C1C]"></div> <span>Cuti / Libur Sekolah</span></div>
                            <div class="flex items-center gap-4"><div class="w-7 h-7 rounded-full border-[3px] border-[#10B981]"></div> <span>Hari Ujian</span></div>
                            <div class="flex items-center gap-4"><div class="w-7 h-7 rounded-full border-[3px] border-[#F59E0B]"></div> <span>Kegiatan Sekolah</span></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-gray-200 p-8 flex flex-col max-h-[450px]">
                        <h3 class="font-bold text-gray-800 mb-5 text-lg shrink-0">Kegiatan & Libur</h3>
                        <div id="event-list" class="flex flex-col gap-5 text-sm overflow-y-auto pr-2 custom-scrollbar flex-1">
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<dialog id="modal_tambah_kegiatan" class="modal">
    <div class="modal-box bg-white w-11/12 max-w-lg rounded-2xl p-6">
        <h3 class="font-bold text-xl text-[#0071BC] mb-6 flex items-center gap-2">
            <i class="fas fa-calendar-plus"></i> Tambah Kegiatan Baru
        </h3>
        <form id="form-tambah-kegiatan" class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Judul Kegiatan</label>
                <input type="text" id="event-title" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:border-[#0071BC] transition">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Mulai</label>
                    <input type="date" id="event-start-date" required class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Selesai</label>
                    <input type="date" id="event-end-date" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Kategori</label>
                <select id="event-type" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm">
                    <option value="exam">Hari Ujian (Hijau)</option>
                    <option value="school_event">Kegiatan Sekolah (Kuning)</option>
                    <option value="wfa">Kegiatan WFA (Biru)</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-8">
                <button type="button" class="px-5 py-2 text-gray-600 bg-gray-100 rounded-lg cursor-pointer" onclick="this.closest('dialog').close()">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#0071BC] text-white rounded-lg cursor-pointer">Simpan</button>
            </div>
        </form>
    </div>
</dialog>

<style>
    /* Styling Scrollbar Estetik */
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
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

            if (nationalHolidays[dateStr]) {
                css = "background-color: #B91C1C; color: white;"; 
            } else if (cutiBersama[dateStr] || isSunday) {
                css = "border: 3px solid #B91C1C; color: #B91C1C; background: white;"; 
            } else {
                let ev = userEvents.find(e => e.date === dateStr);
                if(ev) css = `border: 3px solid ${ev.color}; color: ${ev.color}; background: white;`;
            }

            daysEl.innerHTML += `<div class="py-1"><div class="w-11 h-11 mx-auto flex items-center justify-center rounded-full cursor-pointer transition text-gray-800 font-bold" style="${css}" onclick="selectDate('${dateStr}')">${i}</div></div>`;
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
            listEl.innerHTML = `<div class="text-center py-10 text-gray-400">Tidak ada agenda.</div>`;
            return;
        }

        all.forEach(item => {
            listEl.innerHTML += `
                <div class="border-b border-gray-50 pb-3 last:border-0">
                    <div class="flex items-center gap-2 font-bold text-gray-800">
                        <div class="w-2 h-2 rounded-full" style="background-color: ${item.c}"></div>
                        ${item.d.split('-')[2]} ${monthNames[month]}
                    </div>
                    <div class="ml-4 text-gray-500 text-xs mt-1">${item.t}</div>
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
            let s = d.toISOString().split('T')[0];
            if (!nationalHolidays[s] && !cutiBersama[s] && d.getDay() !== 0) {
                userEvents = userEvents.filter(x => x.date !== s);
                userEvents.push({ date: s, title: document.getElementById('event-title').value, type: type, color: colors[type] });
            }
        }
        document.getElementById('modal_tambah_kegiatan').close();
        renderCalendar(currentDate);
    };

    document.getElementById('prev-month').onclick = () => { currentDate.setMonth(currentDate.getMonth()-1); renderCalendar(currentDate); };
    document.getElementById('next-month').onclick = () => { currentDate.setMonth(currentDate.getMonth()+1); renderCalendar(currentDate); };

    async function saveCalendarData(statusType) {
        if (!confirm(statusType === 'draft' ? "Simpan draft?" : "Publish ke siswa?")) return;
        const btnId = statusType === 'draft' ? 'btn-draft' : 'btn-upload';
        const btn = document.getElementById(btnId);
        btn.innerText = "Menyimpan...";
        btn.disabled = true;

        try {
            const url = `{{ route('lms.teacherCalendar.save', ['role' => $role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ status: statusType, events: userEvents })
            });
            const result = await response.json();
            alert(result.success ? result.message : "Gagal simpan.");
        } catch (error) {
            alert("Terjadi kesalahan.");
        } finally {
            btn.innerText = statusType === 'draft' ? "Simpan Draft" : "Upload & Publish";
            btn.disabled = false;
        }
    }

    document.addEventListener("DOMContentLoaded", () => renderCalendar(currentDate));
</script>