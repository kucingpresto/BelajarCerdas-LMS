@include('components/sidebar-beranda', [
    'headerSideNav' => 'Polling'
])

@if (Auth::user()->role === 'Guru')
    <div class="relative lg:left-72 w-full lg:w-[calc(100%-18rem)] transition-all duration-500 ease-in-out z-20 bg-[#F8FAFC] min-h-screen pb-12">
        <div class="my-8 mx-6 lg:mx-10">

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-5 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div>
                    <h1 class="text-2xl font-extrabold text-[#0071BC] tracking-tight">Manajemen Polling</h1>
                    <p class="text-gray-500 mt-1.5 text-sm font-medium">Buat jejak pendapat interaktif untuk mengetahui opini dan tingkat pemahaman siswa.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                
                <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 border-b border-gray-100 pb-4">
                        <i class="fas fa-square-poll-vertical text-[#0071BC] mr-2"></i> Buat Polling Baru
                    </h2>

                    <form id="form-polling" class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pertanyaan Polling <span class="text-red-500">*</span></label>
                            <textarea id="poll-question" required rows="3" placeholder="Contoh: Bagaimana tingkat pemahaman kalian tentang materi Laravel hari ini?" class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilihan Jawaban <span class="text-red-500">*</span></label>
                            <div id="options-container" class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <input type="text" required placeholder="Pilihan 1 (Misal: Sangat Paham)" class="poll-option w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all">
                                </div>
                                <div class="flex items-center gap-3">
                                    <input type="text" required placeholder="Pilihan 2 (Misal: Sedikit Bingung)" class="poll-option w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all">
                                </div>
                            </div>
                            
                            <button type="button" onclick="addOption()" class="mt-4 text-sm font-semibold text-[#0071BC] hover:text-blue-800 flex items-center gap-1.5 transition-colors">
                                <i class="fas fa-plus-circle"></i> Tambah Pilihan Lain
                            </button>
                        </div>

                        <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                            <button type="submit" id="btn-publish-poll" class="px-8 py-3 bg-gradient-to-r from-[#0071BC] to-[#005B94] hover:shadow-lg hover:shadow-[#0071BC]/30 font-bold text-white rounded-xl cursor-pointer transition-all transform hover:-translate-y-0.5">
                                <i class="fas fa-paper-plane mr-2"></i> Publish ke Siswa
                            </button>
                        </div>
                    </form>
                </div>

                <div class="xl:col-span-1 bg-white rounded-2xl shadow-sm border border-gray-100 p-7 flex flex-col max-h-[600px]">
                    <h3 class="font-extrabold text-gray-800 mb-5 text-lg flex items-center gap-2 shrink-0">
                        <i class="fas fa-history text-[#0071BC]"></i> Riwayat Polling
                    </h3>
                    <div class="flex flex-col gap-4 overflow-y-auto custom-scrollbar flex-1 pr-2">
                        @forelse($polls as $poll)
                            <div class="p-4 border border-gray-100 rounded-xl bg-gray-50 hover:bg-white hover:shadow-md transition-all cursor-default">
                                <span class="text-[10px] font-bold text-white bg-green-500 px-2.5 py-0.5 rounded-md uppercase tracking-wider mb-2.5 inline-block shadow-sm">Aktif</span>
                                <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $poll->question }}</p>
                                <p class="text-[11px] text-gray-500 mt-2 font-medium"><i class="far fa-clock mr-1"></i> Dibuat: {{ $poll->created_at->format('d M Y') }}</p>
                            </div>
                        @empty
                            <div class="text-center py-12 flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-box-open text-4xl mb-3 opacity-40"></i>
                                <span class="text-sm font-medium">Belum ada polling.</span>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
@endif

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

<script>
    // Fungsi Menambah Kolom Pilihan Jawaban
    function addOption() {
        const container = document.getElementById('options-container');
        const optionCount = container.children.length + 1;
        
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 animate-fade-in';
        div.innerHTML = `
            <input type="text" required placeholder="Pilihan ${optionCount}" class="poll-option w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-[#0071BC]/20 focus:border-[#0071BC] outline-none transition-all">
            <button type="button" onclick="this.parentElement.remove()" class="w-10 h-10 shrink-0 bg-red-50 text-red-500 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition-colors cursor-pointer">
                <i class="fas fa-trash-alt"></i>
            </button>
        `;
        container.appendChild(div);
    }

    // Fungsi Mengirim Data Polling ke Backend via AJAX
    document.getElementById('form-polling').onsubmit = async function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-publish-poll');
        const originalText = btn.innerHTML;
        btn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...`;
        btn.disabled = true;

        // Kumpulkan pertanyaan dan semua pilihan
        const question = document.getElementById('poll-question').value;
        const optionsInputs = document.querySelectorAll('.poll-option');
        let options = [];
        optionsInputs.forEach(input => {
            if(input.value.trim() !== '') options.push(input.value.trim());
        });

        if(options.length < 2) {
            alert("Minimal harus ada 2 pilihan jawaban!");
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        try {
            const url = `{{ route('lms.teacherPolling.save', ['role' => $role, 'schoolName' => $schoolName, 'schoolId' => $schoolId]) }}`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ question: question, options: options })
            });

            if (!response.ok) throw new Error("Gagal menyimpan ke database");

            const result = await response.json();
            alert(result.message);
            window.location.reload(); // Reload untuk memunculkan polling di riwayat kanan
            
        } catch (error) {
            console.error(error);
            alert("Terjadi kesalahan saat mempublikasikan polling.");
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    };
</script>