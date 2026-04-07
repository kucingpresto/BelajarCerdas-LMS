<x-script></x-script>

@if (Auth::user()->role === 'Siswa')
    <aside class="sidebar-beranda-student hidden md:flex flex-col w-72">

        <!-- HEADER PUTIH (MAIN LOGO) -->
        <div class="h-40 bg-white flex flex-col items-center justify-center border-b text-gray-500">
            <i class="fa-solid fa-school text-4xl mb-2"></i>
            <span class="text-sm font-medium">Logo sekolah tidak tersedia.</span>
        </div>

        <div class="flex-1 bg-[#0071BC] text-white flex flex-col">

            <!-- MENU -->
            <ul class="mt-14 space-y-4 px-2">
                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 text-md hover:bg-[#FFFFFF26] rounded-lg transition">
                        <i class="fa-solid fa-gauge"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.view', [
                        'role' => Auth::user()->role,
                        'schoolName' => Auth::user()->StudentProfile->SchoolPartner->nama_sekolah,
                        'schoolId' => Auth::user()->StudentProfile->SchoolPartner->id
                    ]) }}"
                    class="flex items-center gap-3 px-4 py-3 text-md hover:bg-[#FFFFFF26] rounded-lg transition">
                        <i class="fa-solid fa-school-flag"></i>
                        <span>LMS</span>
                    </a>
                </li>
            </ul>

            <!-- FOOTER -->
            <div class="mt-auto">
                <hr class="border-white border opacity-60 mb-10 mx-6">

                <div class="pb-16 -ml-2 flex justify-center">
                    <div class="flex flex-col w-max">
                        <span class="text-[13px] mb-3">
                            Powered By:
                        </span>
                        <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-12 object-contain">
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="relative left-72.5 w-[calc(100%-290px)] transition-all duration-500 ease-in-out hidden md:block">
        <div class="content">
            <!-- Navbar for PC -->
            <div class="w-full h-24 bg-[#0071BC] shadow-lg flex items-center justify-between px-12.5">
                <header class="text-[20px] font-bold flex items-center gap-3.5">
                    @if (isset($linkBackButton))
                        <a href="{{ $linkBackButton }}">
                            @if (isset($backButton))
                                <div class="flex items-center gap-2">
                                    <button class="font-bold text-xl cursor-pointer text-white">{!! $backButton !!}</button>
                                    <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                                </div>
                            @endif
                        </a>
                    @else
                        @if (isset($backButton))
                            <div class="flex items-center gap-2">
                                <button class="font-bold text-xl cursor-pointer">{!! $backButton !!}</button>
                                <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                            </div>
                        @else
                            <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                        @endif
                    @endif
                </header>

                <div class="list-item-button-profile m-2 z-40">
                    <div class="dropdown-menu hidden lg:block">
                        <div class="flex items-center gap-3.5 relative">
                            <div class="flex flex-col">
                                <span class="text-[12px] text-white font-semibold leading-6">
                                    {{ Str::limit(Auth::user()->StudentProfile->nama_lengkap ?? '', 20) }}
                                </span>
                                <span class="text-[11px] text-white font-semibold leading-6">
                                    {{ Str::limit(Auth::user()->role ?? '', 20) }}
                                </span>
                            </div>
                            <i class="fas fa-circle-user text-4xl text-white opacity-85 toggle-menu-button-profile cursor-pointer"></i>

                            <!-- DROPDOWN -->
                            <div
                                class="content-dropdown-button-profile absolute right-0 top-full mt-2 bg-white border border-gray-200 shadow-lg w-55 rounded-lg">
                                <a href="{{ route('beranda') }}">
                                    <div class="flex items-center pl-3 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                        <i class="fa-solid fa-house"></i>
                                        Beranda
                                    </div>
                                </a>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button
                                        class="w-full flex items-center pl-3 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- profile button rounded mobile -->
                <div class="list-item-button-profile relative lg:hidden z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile cursor-pointer">
                            <i class="fas fa-circle-user text-4xl text-white"></i>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                            <a href="{{ route('beranda') }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house"></i>
                                    Beranda
                                </div>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button
                                    class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
    </div>

    <!--- Sidebar Beranda for mobile ---->
    <nav class="navbar-beranda-phone w-full h-20 flex justify-between items-center md:hidden bg-white shadow-lg px-6">
        <div class="flex items-center h-full">
            <label for="my-drawer-1">
                <i class="fas fa-bars text-2xl relative top-1 cursor-pointer"></i>
            </label>
            <a href="{{ route('beranda') }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="" class="w-30 ml-4">
            </a>
        </div>
        <div class="flex items-center gap-8 text-2xl relative top-1 z-40">
            <!-- profile button rounded -->
            <div class="list-item-button-profile relative md:hidden">
                <div class="dropdown-menu">
                    <div class="toggle-menu-button-profile cursor-pointer">
                        <i class="fas fa-circle-user text-4xl text-[#0071BC] font-bold"></i>
                    </div>
                    <div
                        class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                        <a href="{{ route('beranda') }}">
                            <div
                                class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                <i class="fa-solid fa-house"></i>
                                Beranda
                            </div>
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

<div class="drawer md:hidden z-9999">
    <input id="my-drawer-1" type="checkbox" class="drawer-toggle"/>

    <div class="drawer-side">
        <label for="my-drawer-1" class="drawer-overlay"></label>

        <div class="bg-gray-50 min-h-full min-w-[65vw] flex flex-col">

            <!-- HEADER -->
            <header class="h-20 px-4 bg-[#0071BC] flex items-center justify-between shadow-sm">
                <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" class="h-9 object-contain" alt="Belajar Cerdas">

                <label for="my-drawer-1">
                    <i class="fas fa-xmark text-2xl text-white cursor-pointer"></i>
                </label>
            </header>

            <!-- CARD SEKOLAH -->
            <div class="mx-4 mt-4 p-4 flex flex-col items-center">

                @if(!empty($school?->logo))
                    <img src="{{ asset($school->logo) }}" class="w-20 h-20 rounded-full object-contain border bg-white" alt="Logo Sekolah">
                @else
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200">
                        <i class="fa-solid fa-school text-3xl text-gray-400"></i>
                    </div>
                    <span class="text-sm font-bold opacity-70">Logo sekolah tidak terdaftar.</span>
                @endif
            </div>

            <div class="border border-gray-200"></div>

            <!-- PROFILE SISWA -->
            <div class="flex items-center gap-3 px-4 py-4 mt-2">
                <i class="fas fa-circle-user text-3xl text-gray-400"></i>
                <div>
                    <p class="text-sm font-semibold leading-tight">
                        {{ Str::limit(Auth::user()->StudentProfile->nama_lengkap ?? '', 20) }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ Auth::user()->role }}
                    </p>
                </div>
            </div>

            <div class="border border-gray-200"></div>

            <!-- MENU -->
            <ul class="flex-1 px-3 py-2 space-y-1 text-sm mt-6 flex flex-col gap-4">
                <li class="list-menu-sidebar-mobile-student">
                    <a href="{{ route('beranda') }}"
                    class="flex items-center gap-3 px-4 py-3 text-md hover:bg-gray-200 transition">
                        <i class="fa-solid fa-home"></i>
                        <span>Beranda</span>
                    </a>
                </li>
                <li class="list-menu-sidebar-dekstop-student">
                    <a href="{{ route('lms.student.view', [
                        'role' => Auth::user()->role,
                        'schoolName' => Auth::user()->StudentProfile->SchoolPartner->nama_sekolah,
                        'schoolId' => Auth::user()->StudentProfile->SchoolPartner->id
                    ]) }}"
                    class="flex items-center gap-3 px-4 py-3 text-md hover:bg-[#FFFFFF26] rounded-lg transition">
                        <i class="fa-solid fa-school-flag"></i>
                        <span>LMS</span>
                    </a>
                </li>
            </ul>

            <!-- LOGOUT -->
            <div class="p-4 border-t border-gray-300">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        class="w-full flex items-center justify-center py-3 bg-red-300 rounded-full gap-2 cursor-pointer transition-all duration-300 hover:bg-red-400 focus:ring-2 focus:ring-red-400 active:scale-95">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@elseif(Auth::user()->role === 'Administrator')
    <aside class="sidebar-beranda-administrator hidden md:block">
        <a href="{{ route('beranda') }}">
            <div class="logo_details flex items-center justify-center">
                <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="" class="w-50 h-32">
            </div>
        </a>
        <ul class="max-h-screen overflow-y-auto pb-3">
            <div class="dropdown-menu">
                <li class="list-item pb-2">
                    <div class="content-menu flex items-center gap-3 px-3 py-2">
                        <i class="fa-solid fa-house text-[15px] w-5 text-center"></i>
                        <a href="{{ route('beranda') }}" class="link-href text-[14px]">
                            Beranda
                        </a>
                    </div>
                </li>

                <li class="list-item pb-4">
                    <div class="dropdown-menu">
                        <div class="content-menu text-sm flex items-center gap-3 px-3.75">
                            <div class="">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <a href="{{ route('kurikulum.view') }}" class="link-href flex flex-col text-[14px]">Management Curriculum</a>
                        </div>
                    </div>
                </li>

                <li class="list-item pb-4 px-4">
                    <div class="dropdown-menu w-full flex flex-col items-start">

                        <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                            <i class="fa-solid fa-book-bookmark text-[14px]"></i>
                            <span class="text-[14px]">Belajar Cerdas LMS</span>
                            <i class="fas fa-chevron-down absolute right-0 text-[14px]" id="rotate-icon"></i>
                        </div>

                        <div class="content-dropdown pl-6 w-full">
                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Content</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.contentManagement.view.noSchoolPartner') }}" class="link-href block py-2 text-[12px]">
                                        Manage Content
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Question</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.questionBankManagement.view.noSchoolPartner') }}" class="link-href block py-2 text-[12px]">
                                        Manage Question
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="list-item pb-4 px-4">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                            <i class="fa-solid fa-school-flag text-[12px]"></i>
                            <span class="text-[14px]">School Partner</span>
                            <i class="fas fa-chevron-down absolute right-0 text-[14px]" id="rotate-icon"></i>
                        </div>

                        <div class="content-dropdown pl-6">
                            <a href="{{ route('lms.schoolSubscription.view') }}"
                                class="link-href flex py-2 text-[14px]">
                                LMS
                            </a>
                        </div>
                    </div>
                </li>
            </div>
        </ul>
    </aside>

    <div class="relative left-62.5 w-[calc(100%-250px)] transition-all duration-500 ease-in-out hidden md:block">
        <div class="content">
            <!-- Navbar for PC -->
            <div class="w-full h-24 bg-white shadow-lg flex items-center justify-between px-12.5">
                <header class="text-[20px] font-bold opacity-70 flex items-center gap-3.5">
                    @if (isset($linkBackButton))
                        <a href="{{ $linkBackButton }}">
                            @if (isset($backButton))
                                <div class="flex items-center gap-2">
                                    <button class="font-bold text-xl cursor-pointer">{!! $backButton !!}</button>
                                    <span class="font-bold text-xl cursor-pointer">{{ $headerSideNav ?? '' }}</span>
                                </div>
                            @endif
                        </a>
                    @else
                        @if (isset($backButton))
                            <div class="flex items-center gap-2">
                                <button class="font-bold text-xl cursor-pointer">{!! $backButton !!}</button>
                                <span class="font-bold text-xl cursor-pointer">{{ $headerSideNav ?? '' }}</span>
                            </div>
                        @else
                            <span class="font-bold text-xl cursor-pointer">{{ $headerSideNav ?? '' }}</span>
                        @endif
                    @endif
                </header>

                <div class="list-item-button-profile m-2 z-40">
                    <div class="dropdown-menu hidden lg:block">
                        <div class="toggle-menu-button-profile flex items-center gap-3.5 relative cursor-pointer">
                            <div class="flex items-center justify-between gap-2.5 w-55 h-14 rounded-[20px] p-2.5 bg-[#005B94]">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-circle-user text-2xl text-white opacity-85"></i>
                                    <div class="flex flex-col">
                                        <span class="text-[12px] text-white font-semibold leading-6">{{ Str::limit(Auth::user()->OfficeProfile->nama_lengkap ?? '', 20) }}</span>
                                        <span class="text-[11px] text-white font-semibold leading-6">{{ Str::limit(Auth::user()->role ?? '', 20) }}</span>
                                    </div>
                                </div>
                                <i id="rotate-icon" class="fas fa-chevron-down text-white opacity-85 transition-all duration-400"></i>
                            </div>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-55 rounded-lg mt-2">
                            <a href="{{ route('beranda') }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house text-md"></i>
                                    Beranda
                                </div>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button
                                    class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- profile button rounded mobile -->
                <div class="list-item-button-profile relative lg:hidden z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile cursor-pointer">
                            <i class="fas fa-circle-user text-4xl text-[#005B94]"></i>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                            <a href="{{ route('beranda') }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house"></i>
                                    Beranda
                                </div>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button
                                    class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--- Sidebar Beranda for mobile ---->
    <nav class="navbar-beranda-phone w-full h-20 flex justify-between items-center md:hidden bg-white shadow-lg px-6">
        <div class="flex items-center h-full">
            <label for="my-drawer-1">
                <i class="fas fa-bars text-2xl relative top-1 cursor-pointer"></i>
            </label>
            <a href="{{ route('beranda') }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="" class="w-30 ml-4">
            </a>
        </div>
        <div class="flex items-center gap-8 text-2xl relative top-1 z-40">
            <!-- profile button rounded -->
            <div class="list-item-button-profile relative md:hidden">
                <div class="dropdown-menu">
                    <div class="toggle-menu-button-profile cursor-pointer">
                        <i class="fas fa-circle-user text-4xl text-[#005B94] font-bold"></i>
                    </div>
                    <div
                        class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                        <a href="{{ route('beranda') }}">
                            <div
                                class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                <i class="fa-solid fa-house"></i>
                                Beranda
                            </div>
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="drawer md:hidden z-9999">
        <input id="my-drawer-1" type="checkbox" class="drawer-toggle"/>
        <div class="drawer-side">
            <label for="my-drawer-1" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="bg-base-200 min-h-full min-w-[60vw] p-0">
                <header class="w-full h-20 px-4 bg-[#005B94] flex items-center justify-between">
                    <a href="{{ route('beranda') }}">
                        <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="" class="w-26">
                    </a>

                    <label for="my-drawer-1" aria-label="close sidebar">
                        <i class="fas fa-xmark text-2xl text-white cursor-pointer"></i>
                    </label>
                </header>

                <div class="profile-account flex flex-col items-center px-2 my-6">
                    <i class="fas fa-circle-user text-5xl text-gray-500"></i>
                    <span>{{ Str::limit(Auth::user()->OfficeProfile->nama_lengkap ?? '', 20) }}</span>
                    <span class="text-xs">{{ Auth::user()->role ?? '' }}</span>
                </div>

                <div class="border-b border-gray-300 mb-6"></div>

                <!-- Sidebar content here -->
                <ul class="w-full max-h-screen overflow-y-auto">
                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu">
                            <div class="content-menu text-sm flex items-center gap-3">
                                <i class="fas fa-house"></i>
                                <a href="{{ route('beranda') }}" class="link-href flex flex-col text-[13px]">Beranda</a>
                            </div>
                        </div>
                    </li>

                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu">
                            <div class="content-menu text-sm flex items-center gap-3">
                                <div class="">
                                    <i class="fa-solid fa-layer-group"></i>
                                </div>
                                <a href="{{ route('kurikulum.view') }}" class="link-href flex flex-col text-[13px]">Management Curriculum</a>
                            </div>
                        </div>
                    </li>

                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu w-full flex flex-col items-start">

                            <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                                <i class="fa-solid fa-book-bookmark text-[14px]"></i>
                                <span class="text-[14px]">Belajar Cerdas LMS</span>
                                <i class="fas fa-chevron-down absolute right-0 text-[14px]" id="rotate-icon"></i>
                            </div>

                            <div class="content-dropdown px-2 w-full">
                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                        <span>Content</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.contentManagement.view.noSchoolPartner') }}" class="link-href block py-2 text-[12px]">
                                            Manage Content
                                        </a>
                                    </div>
                                </div>

                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[13px]">
                                        <span>Question</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.questionBankManagement.view.noSchoolPartner') }}" class="link-href block py-2 text-[12px]">
                                            Manage Question
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="list-item m-2 pb-3">
                        <div class="dropdown-menu w-full flex flex-col items-start">
                            <div class="toggle-menu-sidebar w-full flex items-center gap-3.5 relative cursor-pointer">
                                <i class="fa-solid fa-school-flag text-[12px]"></i>
                                <span class="text-[14px]">School Partner</span>
                                <i class="fas fa-chevron-down absolute right-0 text-[14px]" id="rotate-icon"></i>
                            </div>
                            <div class="content-dropdown">
                                <a href="{{ route('lms.schoolSubscription.view') }}" class="link-href flex flex-col px-2 py-2 text-[13px]">LMS</a>
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="border-b border-gray-300 mb-6"></div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button
                        class="flex items-center justify-center w-full max-w-62.5 px-4 py-2 mt-6 mx-auto font-bold bg-red-300 rounded-full gap-2 cursor-pointer transition-all duration-300 hover:bg-red-400 focus:ring-2 focus:ring-red-400 active:scale-95">
                            <i class="fas fa-right-from-bracket transform"></i>
                            <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@elseif (Auth::user()->role === 'Guru')
    <aside class="sidebar-beranda-student hidden md:flex flex-col w-72">

        <div class="h-40 bg-white flex flex-col items-center justify-center border-b text-gray-500">
            <i class="fa-solid fa-school text-4xl mb-2"></i>
            <span class="text-sm font-medium">Logo sekolah tidak tersedia.</span>
        </div>

        <div class="flex-1 bg-[#0071BC] text-white flex flex-col">

            <ul class="max-h-112.5 overflow-y-auto pb-6 pt-6 space-y-3">

                <li class="list-item">
                    <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                        <i class="fa-solid fa-house text-[15px] w-5 text-center"></i>
                        <a href="{{ route('beranda') }}" class="link-href text-[14px]">
                            Beranda
                        </a>
                    </div>
                </li>

                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">

                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-person-chalkboard text-[15px] w-5 text-center"></i>
                            <span class="text-[14px]">Aktivitas Guru</span>
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                        </div>

                        <div class="content-dropdown pl-6 pr-3.5 w-full">
                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Assessment</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.teacherAssessmentManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Assessment Mangement
                                    </a>
                                    <a href="{{ route('lms.assessmentGradingManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Assessment Belum Dinilai
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Content</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.teacherContentManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Content Mangement
                                    </a>
                                    <a href="{{ route('lms.teacherContentForRelease.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Content For Release
                                    </a>
                                </div>
                            </div>

                            <div class="flex flex-col">
                                <div
                                    class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                    <span>Question</span>
                                    <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                </div>

                                <div class="list-content-dropdown pl-4">
                                    <a href="{{ route('lms.teacherQuestionBankManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Question Bank Management
                                    </a>
                                    <a href="{{ route('lms.teacherQuestionBankForRelease.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Question Bank For Release
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                <!-- Meja Guru -->
                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">

                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-folder-open text-[15px] w-5 text-center"></i>
                            <span class="text-[14px]">Meja Guru</span>
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                        </div>

                        <div class="content-dropdown pl-6 pr-3.5 w-full">
                            <div class="flex flex-col">
                                <a href="{{ route('lms.teacherClassList.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                        'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                    ]) }}" class="link-href block py-2 text-[12px]">
                                    Buku Nilai
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="list-item mt-1">
                    <div class="dropdown-menu w-full flex flex-col items-start">
                        
                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-circle-info text-[15px] w-5 text-center"></i>
                            <span class="text-[14px]">Informasi</span>
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                        </div>

                        <div class="content-dropdown pl-6 pr-3.5 w-full">
                            <div class="flex flex-col py-2 mt-1">
                                <a href="{{ route('lms.teacherCalendar.view', ['role' => Auth::user()->role, 'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah, 'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                    Kalender Akademik
                                </a>
                                <a href="{{ route('lms.teacherSchedule.view', ['role' => Auth::user()->role, 'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah, 'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                    Jadwal Pelajaran
                                </a>
                                <a href="{{ route('lms.teacherPolling.view', ['role' => Auth::user()->role, 'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah, 'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                    Polling
                                </a>
                            </div>
                        </div>
                        
                    </div>
                </li>
            </ul>

            <div class="mt-auto">
                <hr class="border-white border opacity-60 mb-10 mx-6">

                <div class="pb-16 -ml-2 flex justify-center">
                    <div class="flex flex-col w-max">
                        <span class="text-[13px] mb-3">
                            Powered By:
                        </span>
                        <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" alt="Belajar Cerdas" class="h-12 object-contain">
                    </div>
                </div>
            </div>
        </div>
    </aside>

    <div class="relative left-72.5 w-[calc(100%-290px)] transition-all duration-500 ease-in-out hidden md:block">
        <div class="content">
            <div class="w-full h-24 bg-[#0071BC] shadow-lg flex items-center justify-between px-12.5">
                <header class="text-[20px] font-bold flex items-center gap-3.5">
                    @if (isset($linkBackButton))
                        <a href="{{ $linkBackButton }}">
                            @if (isset($backButton))
                                <div class="flex items-center gap-2">
                                    <button class="font-bold text-xl cursor-pointer text-white">{!! $backButton !!}</button>
                                    <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                                </div>
                            @endif
                        </a>
                    @else
                        @if (isset($backButton))
                            <div class="flex items-center gap-2">
                                <button class="font-bold text-xl cursor-pointer">{!! $backButton !!}</button>
                                <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                            </div>
                        @else
                            <span class="font-bold text-xl cursor-pointer text-white">{{ $headerSideNav ?? '' }}</span>
                        @endif
                    @endif
                </header>

                <div class="list-item-button-profile m-2 z-40">
                    <div class="dropdown-menu hidden lg:block">
                        <div class="flex items-center gap-3.5 relative">
                            <div class="flex flex-col">
                                <span class="text-[12px] text-white font-semibold leading-6">
                                    {{ Str::limit(Auth::user()->SchoolStaffProfile->nama_lengkap ?? '', 20) }}
                                </span>
                                <span class="text-[11px] text-white font-semibold leading-6">
                                    {{ Str::limit(Auth::user()->role ?? '', 20) }}
                                </span>
                            </div>
                            <i class="fas fa-circle-user text-4xl text-white opacity-85 toggle-menu-button-profile cursor-pointer"></i>

                            <div
                                class="content-dropdown-button-profile absolute right-0 top-full mt-2 bg-white border border-gray-200 shadow-lg w-55 rounded-lg">
                                <a href="{{ route('beranda') }}">
                                    <div class="flex items-center pl-3 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                        <i class="fa-solid fa-house"></i>
                                        Beranda
                                    </div>
                                </a>

                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button
                                        class="w-full flex items-center pl-3 py-3 gap-2 text-[13px] hover:bg-gray-100">
                                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                        Keluar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-item-button-profile relative lg:hidden z-40">
                    <div class="dropdown-menu">
                        <div class="toggle-menu-button-profile cursor-pointer">
                            <i class="fas fa-circle-user text-4xl text-white"></i>
                        </div>
                        <div
                            class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                            <a href="{{ route('beranda') }}">
                                <div
                                    class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                    <i class="fa-solid fa-house"></i>
                                    Beranda
                                </div>
                            </a>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button
                                    class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>            
        </div>
    </div>

    <nav class="navbar-beranda-phone w-full h-20 flex justify-between items-center md:hidden bg-white shadow-lg px-6">
        <div class="flex items-center h-full">
            <label for="my-drawer-1">
                <i class="fas fa-bars text-2xl relative top-1 cursor-pointer"></i>
            </label>
            <a href="{{ route('beranda') }}">
                <img src="{{ asset('assets/images/logo-bc/main-logo-bc.svg') }}" alt="" class="w-30 ml-4">
            </a>
        </div>
        <div class="flex items-center gap-8 text-2xl relative top-1 z-40">
            <div class="list-item-button-profile relative md:hidden">
                <div class="dropdown-menu">
                    <div class="toggle-menu-button-profile cursor-pointer">
                        <i class="fas fa-circle-user text-4xl text-[#0071BC] font-bold"></i>
                    </div>
                    <div
                        class="content-dropdown-button-profile absolute bg-white border border-gray-200 shadow-lg w-35 rounded-lg mt-2 right-0">
                        <a href="{{ route('beranda') }}">
                            <div
                                class="flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black">
                                <i class="fa-solid fa-house"></i>
                                Beranda
                            </div>
                        </a>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button
                                class="w-full flex items-center pl-2 py-3.75 gap-1.5 text-[13px] hover:bg-gray-100 hover:text-black cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-lg ml-0.75"></i>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="drawer md:hidden z-9999">
        <input id="my-drawer-1" type="checkbox" class="drawer-toggle"/>

        <div class="drawer-side">
            <label for="my-drawer-1" class="drawer-overlay"></label>

            <div class="bg-gray-50 min-h-full min-w-[65vw] flex flex-col">

                <header class="h-20 px-4 bg-[#0071BC] flex items-center justify-between shadow-sm">
                    <img src="{{ asset('assets/images/logo-bc/white-logo-bc.svg') }}" class="h-9 object-contain" alt="Belajar Cerdas">

                    <label for="my-drawer-1">
                        <i class="fas fa-xmark text-2xl text-white cursor-pointer"></i>
                    </label>
                </header>

                <div class="mx-4 mt-4 p-4 flex flex-col items-center">

                    @if(!empty($school?->logo))
                        <img src="{{ asset($school->logo) }}" class="w-20 h-20 rounded-full object-contain border bg-white" alt="Logo Sekolah">
                    @else
                        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center border border-gray-200">
                            <i class="fa-solid fa-school text-3xl text-gray-400"></i>
                        </div>
                        <span class="text-sm font-bold opacity-70">Logo sekolah tidak terdaftar.</span>
                    @endif
                </div>

                <div class="border border-gray-200"></div>

                <div class="flex items-center gap-3 px-4 py-4 mt-2">
                    <i class="fas fa-circle-user text-3xl text-gray-400"></i>
                    <div>
                        <p class="text-sm font-semibold leading-tight">
                            {{ Str::limit(Auth::user()->SchoolStaffProfile->nama_lengkap ?? '', 20) }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ Auth::user()->role }}
                        </p>
                    </div>
                </div>

                <div class="border border-gray-200"></div>

                <ul class="max-h-112.5 overflow-y-auto pb-6 pt-6 space-y-3">
                    
                    <li class="list-item">
                        <div class="content-menu flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-house text-[15px] w-5 text-center"></i>
                            <a href="{{ route('beranda') }}" class="link-href text-[14px]">
                                Beranda
                            </a>
                        </div>
                    </li>

                    <li class="list-item">
                        <div class="dropdown-menu w-full flex flex-col items-start">

                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                                <i class="fa-solid fa-person-chalkboard text-[15px] w-5 text-center"></i>
                                <span class="text-[14px]">Aktivitas Guru</span>
                                <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                            </div>

                            <div class="content-dropdown pl-6 pr-3.5 w-full">
                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                        <span>Assessment</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.teacherAssessmentManagement.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Assessment Mangement
                                        </a>
                                    <a href="{{ route('lms.assessmentGradingManagement.view', [
                                            'role' => Auth::user()->role,
                                            'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                            'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                        ]) }}" class="link-href block py-2 text-[12px]">
                                        Assessment Belum Dinilai
                                    </a>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                        <span>Content</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.teacherContentManagement.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Content Mangement
                                        </a>
                                        <a href="{{ route('lms.teacherContentForRelease.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Content For Release
                                        </a>
                                    </div>
                                </div>

                                <div class="flex flex-col">
                                    <div
                                        class="toggle-sub-menu-sidebar flex items-center justify-between cursor-pointer py-2 text-[14px]">
                                        <span>Question</span>
                                        <i class="fas fa-chevron-down text-[12px]" id="rotate-icon-2"></i>
                                    </div>

                                    <div class="list-content-dropdown pl-4">
                                        <a href="{{ route('lms.teacherQuestionBankManagement.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Question Bank Management
                                        </a>
                                        <a href="{{ route('lms.teacherQuestionBankForRelease.view', [
                                                'role' => Auth::user()->role,
                                                'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                                'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                            ]) }}" class="link-href block py-2 text-[12px]">
                                            Question Bank For Release
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                <!-- Meja Guru -->
                <li class="list-item">
                    <div class="dropdown-menu w-full flex flex-col items-start">

                        <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                            <i class="fa-solid fa-folder-open text-[15px] w-5 text-center"></i>
                            <span class="text-[14px]">Meja Guru</span>
                            <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                        </div>

                        <div class="content-dropdown pl-6 pr-3.5 w-full">
                            <div class="flex flex-col">
                                <a href="{{ route('lms.teacherClassList.view', [
                                        'role' => Auth::user()->role,
                                        'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah,
                                        'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id
                                    ]) }}" class="link-href block py-2 text-[12px]">
                                    Buku Nilai
                                </a>
                            </div>
                        </div>
                    </div>
                </li>
                    <li class="list-item mt-1">
                        <div class="dropdown-menu w-full flex flex-col items-start">
                            
                            <div class="toggle-menu-sidebar w-full flex items-center gap-3 relative cursor-pointer px-3 py-2 rounded-lg hover:bg-[#FFFFFF26] transition">
                                <i class="fa-solid fa-circle-info text-[15px] w-5 text-center"></i>
                                <span class="text-[14px]">Informasi</span>
                                <i class="fas fa-chevron-down absolute right-3 text-[13px]"></i>
                            </div>

                            <div class="content-dropdown pl-6 pr-3.5 w-full">
                                <div class="flex flex-col py-2 mt-1">
                                    <a href="{{ route('lms.teacherCalendar.view', ['role' => Auth::user()->role, 'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah, 'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                        Kalender Akademik
                                    </a>
                                    <a href="{{ route('lms.teacherSchedule.view', ['role' => Auth::user()->role, 'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah, 'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                        Jadwal Pelajaran
                                    </a>
                                    <a href="{{ route('lms.teacherPolling.view', ['role' => Auth::user()->role, 'schoolName' => Auth::user()->SchoolStaffProfile->SchoolPartner->nama_sekolah, 'schoolId' => Auth::user()->SchoolStaffProfile->SchoolPartner->id]) }}" class="link-href block py-2 text-[13px] hover:text-gray-300 cursor-pointer">
                                        Polling
                                    </a>
                                </div>
                            </div>
                            
                        </div>
                    </li>
                </ul>

                <div class="p-4 border-t border-gray-300">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button
                            class="w-full flex items-center justify-center py-3 bg-red-300 rounded-full gap-2 cursor-pointer transition-all duration-300 hover:bg-red-400 focus:ring-2 focus:ring-red-400 active:scale-95">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@else
    <p>You do not have access to this dashboard.</p>
@endif

<!-- COMPONENTS -->
<script src="{{ asset('assets/js/components/sidebar-administrator.js') }}"></script> <!-- sidebar administrator -->
<script src="{{ asset('assets/js/components/sidebar-student.js') }}"></script> <!-- sidebar student -->
<script src="{{ asset('assets/js/components/navbar-button-profile.js') }}"></script> <!-- button profile user in navbar -->
