<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MyKubur - Perkuburan Islam Kampung Rantau Panjang</title>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col antialiased">

    <!-- AUTHENTICATION SCREEN (LOGIN & REGISTER) -->
    <div id="auth-screen" class="fixed inset-0 z-[100] bg-slate-900 flex items-center justify-center p-4 overflow-y-auto">
        <!-- Background decoration -->
        <div class="absolute inset-0 bg-slate-950"></div>
        
        <div class="app-card max-w-md w-full overflow-hidden relative z-10 p-6 md:p-8 space-y-6">
            <div class="text-center space-y-2">
                <div class="mx-auto inline-flex items-center justify-center w-16 h-16 bg-emerald-700/20 border border-emerald-600/30 rounded-xl p-1">
    <img src="{{ asset('assets/images/logo.png') }}"
         alt="MyKubur Logo"
         class="w-full h-full object-contain">
</div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sistem MyKubur</h1>
                <p class="app-muted font-semibold">Tanah Perkuburan Islam (Baru), Kg Rantau Panjang</p>
            </div>

            <!-- Login / Register Toggle Tabs -->
            <div class="grid grid-cols-2 p-1 bg-slate-100 rounded-xl">
                <button onclick="toggleAuthMode('login')" id="auth-tab-login" class="py-2.5 text-xs font-bold rounded-lg bg-white text-emerald-900 shadow transition-all">Log Masuk</button>
                <button onclick="toggleAuthMode('register')" id="auth-tab-register" class="py-2.5 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-900 transition-all">Daftar Waris</button>
            </div>

            <!-- LOGIN FORM -->
            <form id="login-form" onsubmit="handleLogin(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Alamat Emel</label>
                    <div class="relative">
                        <input type="email" id="login-email" required placeholder="nama@emel.com" class="app-input px-4 py-3 pl-11 text-xs">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Kata Laluan</label>
                    <div class="relative">
                        <input type="password" id="login-password" required placeholder="••••••••" class="app-input px-4 py-3 pl-11 text-xs">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                    </div>
                </div>

                <button type="submit" class="app-button-primary w-full py-3 text-xs flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i> Masuk Ke Sistem
                </button>
            </form>

            <!-- REGISTER FORM (Warises only, Admins pre-seeded) -->
            <form id="register-form" onsubmit="handleRegister(event)" class="space-y-4 hidden">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Nama Penuh Waris</label>
                    <div class="relative">
                        <input type="text" id="reg-name" required placeholder="Contoh: Mohd bin Musa" class="app-input px-4 py-3 pl-11 text-xs">
                        <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Alamat Emel</label>
                    <div class="relative">
                        <input type="email" id="reg-email" required placeholder="waris@emel.com" class="app-input px-4 py-3 pl-11 text-xs">
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5 uppercase">Kata Laluan Baru</label>
                    <div class="relative">
                        <input type="password" id="reg-password" required placeholder="Minimum 6 aksara" minlength="6" class="app-input px-4 py-3 pl-11 text-xs">
                        <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                    </div>
                </div>

                <button type="submit" class="app-button-primary w-full py-3 text-xs flex items-center justify-center gap-2">
                    <i data-lucide="user-plus" class="w-4 h-4"></i> Daftar Akaun Waris
                </button>
            </form>
        </div>
    </div>

    <!-- MAIN SYSTEM INTERFACE (Hidden before successful login) -->
    <div id="main-system-layout" class="hidden flex-grow flex flex-col">
        <!-- TOP NAVBAR -->
        <nav class="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center space-x-3">
                        <div class="inline-flex items-center justify-center w-8 h-8">
    <img src="{{ asset('assets/images/logo.png') }}"
         alt="MyKubur Logo"
         class="w-full h-full object-contain">
</div>
                        <div>
                            <span class="font-bold text-lg tracking-normal block leading-none">MyKubur</span>
                            <span class="text-[10px] block text-slate-300 mt-1 font-semibold">Tanah Perkuburan Islam (Baru), Kg Rantau Panjang</span>
                        </div>
                    </div>
                    
                    <!-- Navigation Tabs (Desktop) -->
                    <div class="hidden md:flex items-center space-x-2">
                        <!-- Portal Waris button (Visible strictly to Warises) -->
                        <button onclick="switchTab('waris')" id="nav-waris" class="nav-btn px-4 py-2 rounded-md text-sm font-medium bg-emerald-800 text-white transition">
                            <span class="flex items-center gap-2"><i data-lucide="users" class="w-4 h-4"></i> Portal Waris</span>
                        </button>
                        <!-- Panel Admin button (Visible strictly to Admin) -->
                        <button onclick="switchTab('admin')" id="nav-admin" class="nav-btn px-4 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition hidden">
                            <span class="flex items-center gap-2"><i data-lucide="lock" class="w-4 h-4"></i> Panel Admin</span>
                        </button>
                        
                        <!-- Divider -->
                        <div class="h-6 w-[1px] bg-slate-700 mx-2"></div>
                        
                        <div class="flex items-center gap-2">
                            <div class="text-right">
                                <span id="nav-user-name" class="text-xs font-bold block">Mohd Waris</span>
                                <span id="nav-user-role" class="text-[10px] text-slate-300 block">Waris Terdaftar</span>
                            </div>
                            <button onclick="handleLogout()" class="bg-slate-800 hover:bg-slate-700 text-white p-2 rounded-lg transition" title="Log Keluar">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mobile Right elements -->
                    <div class="flex md:hidden items-center gap-3">
                        <div class="text-right">
                            <span id="nav-user-name-mobile" class="text-xs font-bold block">Waris</span>
                            <button onclick="handleLogout()" class="text-[10px] text-rose-300 font-bold underline">Log Keluar</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Navigation bar (Hidden since roles are strictly separate) -->
            <div id="mobile-nav-bar" class="hidden flex md:hidden bg-slate-950 justify-around py-2 border-t border-slate-800">
                <button onclick="switchTab('waris')" id="nav-waris-mobile" class="flex flex-col items-center text-slate-300 hover:text-white text-xs py-1">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Portal Waris</span>
                </button>
                <button onclick="switchTab('admin')" id="nav-admin-mobile" class="flex flex-col items-center text-slate-300 hover:text-white text-xs py-1 hidden">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                    <span>Admin</span>
                </button>
            </div>
        </nav>

        <!-- MAIN CONTENT CONTAINER -->
        <main class="flex-grow max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8">

            <!-- Banner Info -->
            <div class="app-card cemetery-hero p-5 md:p-7 mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
                <div class="cemetery-hero-watermark" aria-hidden="true">
                    <span class="cemetery-hero-crescent"></span>
                </div>
                <div class="cemetery-hero-content space-y-4">
                    <span class="app-badge cemetery-hero-date">
                        <i data-lucide="calendar-days" class="w-4 h-4"></i>
                        Mulai 1 Jun 2026
                    </span>
                    <h1 class="cemetery-hero-title text-xl md:text-3xl font-extrabold tracking-normal">Tanah Perkuburan Islam (Baru) Kg Rantau Panjang</h1>
                    <p class="cemetery-hero-meta app-muted max-w-xl flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span class="inline-flex items-center gap-1.5">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                            42100 Klang, Selangor.
                        </span>
                        <span class="hidden sm:inline text-slate-300">|</span>
                        <span>Jumlah kapasiti: <strong id="total-capacity">0 lot</strong>.</span>
                    </p>
                </div>
                <div class="cemetery-hero-stats grid grid-cols-3 gap-3 w-full md:w-auto text-center">
                    <div class="cemetery-stat-card p-3 md:p-4 min-w-24">
                        <span class="cemetery-stat-icon cemetery-stat-icon-a">
                            <i data-lucide="box" class="w-5 h-5"></i>
                        </span>
                        <span class="text-xs text-slate-500 block font-semibold">Block A</span>
                        <span id="stats-a" class="cemetery-stat-value-a text-lg md:text-xl font-extrabold">0 / 570</span>
                    </div>
                    <div class="cemetery-stat-card p-3 md:p-4 min-w-24">
                        <span class="cemetery-stat-icon cemetery-stat-icon-b">
                            <i data-lucide="gem" class="w-5 h-5"></i>
                        </span>
                        <span class="text-xs text-slate-500 block font-semibold">Block B</span>
                        <span id="stats-b" class="cemetery-stat-value-b text-lg md:text-xl font-extrabold">0 / 530</span>
                    </div>
                    <div class="cemetery-stat-card p-3 md:p-4 min-w-24">
                        <span class="cemetery-stat-icon cemetery-stat-icon-c">
                            <i data-lucide="box" class="w-5 h-5"></i>
                        </span>
                        <span class="text-xs text-slate-500 block font-semibold">Block C</span>
                        <span id="stats-c" class="cemetery-stat-value-c text-lg md:text-xl font-extrabold">0 / 570</span>
                    </div>
                </div>
            </div>

            <!-- Custom Notification Toast -->
            <div id="toast" class="hidden fixed bottom-5 right-5 z-50 bg-slate-900 text-white px-5 py-3 rounded-lg shadow-xl flex items-center gap-3 transition-opacity duration-300">
                <i data-lucide="check-circle" class="text-emerald-400 w-5 h-5"></i>
                <span id="toast-text" class="text-sm font-medium">Rekod berjaya disimpan.</span>
            </div>

            <!-- TAB 1: PORTAL WARIS -->
            <section id="tab-waris" class="tab-content space-y-6">
                <!-- CARIAN SI MATI -->
                <div class="app-card app-section">
                    <div class="app-card-header">
                        <div class="app-icon">
                            <i data-lucide="search" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="app-title">Carian Rekod & Lokasi Kubur</h2>
                            <p class="app-muted">Cari menggunakan nama, no. IC, nama waris atau nombor lot.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="app-label">Kata Carian</label>
                            <div class="relative">
                                <input type="text" id="waris-search-input" onkeyup="filterSearchWaris()" placeholder="Contoh: Noraini, 650203-08-5431 atau A12-4" class="app-input px-4 py-3 pl-10">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5"></i>
                            </div>
                        </div>
                        <div class="flex items-end">
                            <button onclick="resetWarisSearch()" class="app-button-secondary px-5 py-3 text-sm w-full md:w-auto">
                                Set Semula
                            </button>
                        </div>
                    </div>

                    <!-- Hasil Carian -->
                    <div class="waris-search-scroll mt-6">
                        <div id="search-results-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Carian akan dipaparkan di sini -->
                        </div>
                        <div id="no-search-results" class="hidden text-center py-8 app-panel">
                            <i data-lucide="frown" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-sm font-medium text-slate-500">Tiada padanan rekod dijumpai.</p>
                        </div>
                    </div>
                </div>

                <!-- INTERACTIVE PHYSICAL CEMETERY MAP -->
                <div class="app-card app-section space-y-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="app-card-header mb-0">
                            <div class="app-icon">
                                <i data-lucide="map" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <h2 class="app-title">Pelan Lokasi Fizikal</h2>
                            </div>
                        </div>
                    </div>

                    <!-- PHYSICAL LAYOUT COMPONENT -->
                    <div class="map-shell p-3 md:p-6 overflow-x-auto">
                        <div class="min-w-[640px] max-w-3xl mx-auto flex flex-col items-stretch space-y-0 relative">
                            
                            <!-- Top Blocks Area (A, B, C) and Integrated Vertical Lanes -->
                            <div class="grid grid-cols-11 items-stretch min-h-[280px] md:min-h-[340px]">
                                
                                <!-- BLOCK C (Left) -->
                                <button onclick="changeMapBlock('C')" id="layout-block-c" class="col-span-3 transition duration-200 cursor-pointer text-center relative group shadow-sm">
                                    <!-- Dynamic elements loaded via JS -->
                                </button>

                                <!-- Vertical Lane 1 -->
                                <div class="col-span-1 bg-slate-800 flex flex-col justify-between items-center relative py-4 border-r border-l border-slate-700">
                                    <div class="w-[2px] h-full border-l border-dashed border-slate-500/40"></div>
                                </div>

                                <!-- BLOCK B (Middle + Well Structured Facility Widget) -->
                                <div class="col-span-3 flex flex-col justify-between items-stretch gap-2 md:gap-3">
                                    <button onclick="changeMapBlock('B')" id="layout-block-b" class="flex-grow transition duration-200 cursor-pointer text-center relative group shadow-sm">
                                        <!-- Dynamic elements loaded via JS -->
                                    </button>
                                    
                                    <!-- STORE DAN TANDAS Structure inside Block B -->
                                    <div class="bg-white border border-slate-300 rounded-xl p-2 md:p-3 text-center flex flex-col items-center justify-center space-y-1 relative">
                                        <div class="flex items-center gap-1 bg-slate-50 px-1.5 py-0.5 md:py-1 rounded-lg border border-slate-200">
                                            <i data-lucide="store" class="w-3.5 h-3.5 text-blue-600"></i>
                                            <i data-lucide="toilet" class="w-3.5 h-3.5 text-blue-600"></i>
                                        </div>
                                        <p class="text-[9px] md:text-[10px] font-bold text-slate-800 uppercase tracking-wide leading-none">Store & Tandas</p>
                                    </div>
                                </div>

                                <!-- Vertical Lane 2 -->
                                <div class="col-span-1 bg-slate-800 flex flex-col justify-between items-center relative py-4 border-r border-l border-slate-700">
                                    <div class="w-[2px] h-full border-l border-dashed border-slate-500/40"></div>
                                </div>

                                <!-- BLOCK A (Right) -->
                                <button onclick="changeMapBlock('A')" id="layout-block-a" class="col-span-3 transition duration-200 cursor-pointer text-center relative group shadow-sm">
                                    <!-- Dynamic elements loaded via JS -->
                                </button>

                            </div>

                            <!-- Integrated Horizontal Path connected seamlessly to lanes, aligning text on the right and placing Pintu Pagar Utama perfectly between B & C -->
                            <div class="map-path py-4 px-6 flex items-center justify-end border-t border-b border-slate-500 relative z-20">
                                <!-- Pintu Pagar Utama positioned precisely on top of the path, centered at the vertical line between Block B and C (Column 4 center = 31.81%) -->
                                <div class="absolute left-[31.8%] top-1/2 -translate-x-1/2 -translate-y-1/2 z-30">
                                    <div class="bg-white text-slate-800 font-bold text-[9px] md:text-xs px-2.5 py-1.5 rounded border border-slate-300 text-center whitespace-nowrap">
                                        Pintu Pagar Utama
                                    </div>
                                </div>

                                <!-- Text moved strictly to the right -->
                                <span class="text-[9px] md:text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5 text-slate-200">
                                    <i data-lucide="truck" class="w-3.5 h-3.5 text-emerald-400"></i> Laluan Pejalan Kaki & Kereta Jenazah
                                </span>
                            </div>

                            <!-- Jalan Utama Road at the bottom -->
                            <div class="map-road rounded-b-2xl py-5 text-center border-t border-slate-600 relative overflow-hidden">
                                <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 border-t-2 border-dashed border-zinc-700"></div>
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-200 relative z-10 flex items-center justify-center gap-2">
                                    <i data-lucide="navigation" class="w-4 h-4 text-emerald-400 rotate-45"></i> Jalan Utama (Rantau Panjang)
                                </span>
                            </div>

                        </div>
                    </div>

                    <!-- FILTER BAR UNTUK VIEW LOTS -->
                    <div class="app-panel p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 font-bold uppercase">Blok Terpilih:</span>
                            <span id="selected-block-label" class="app-badge app-badge-primary">BLOCK A</span>
                        </div>

                        <div class="flex items-center gap-2 w-full sm:w-auto">
                            <label class="text-xs text-slate-500 font-bold uppercase whitespace-nowrap">Pilih Baris (1 - 57):</label>
                            <select id="map-row-select" onchange="renderVisualMap()" class="app-input w-full sm:w-32 text-xs font-semibold px-3 py-2.5">
                                <!-- JS auto populate baris 1-57 -->
                            </select>
                        </div>
                    </div>

                    <!-- Penunjuk Warna Status -->
                    <div class="flex flex-wrap items-center gap-4 text-xs font-medium app-panel p-3">
                        <div class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded-md bg-emerald-500 border border-emerald-600 inline-block"></span>
                            <span class="text-slate-600">Lot Kosong (Tersedia)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-3.5 h-3.5 rounded-md bg-rose-500 border border-rose-600 inline-block"></span>
                            <span class="text-slate-600">Lot Berisi (Telah Dihuni)</span>
                        </div>
                    </div>

                    <!-- Grid Visual Lot -->
                    <div class="app-panel p-4 md:p-5">
                        <div class="text-center mb-6">
                            <span id="lots-header-title" class="text-xs font-bold text-slate-400 tracking-widest uppercase block">PAPARAN BARISAN LOTS: BLOK A (BARIS 1)</span>
                            <div class="h-1 bg-slate-200 rounded w-full mt-2"></div>
                        </div>

                        <div id="visual-lots-grid" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-10 gap-3">
                            <!-- Generated Lot cards go here -->
                        </div>
                    </div>

                    <!-- Detail Panel click lot -->
                    <div id="map-detail-card" class="hidden bg-emerald-50 border border-emerald-100 rounded-xl p-5 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <span class="text-lg font-black text-emerald-900 bg-white shadow-sm border border-emerald-200 px-3 py-1.5 rounded-xl" id="detail-lot-id">A1-1</span>
                                <div>
                                    <h3 class="text-sm font-bold text-emerald-950" id="detail-deceased-name">Ahmad bin Musa</h3>
                                    <p class="text-xs text-emerald-800" id="detail-deceased-ic">No. IC: 650203-08-5431</p>
                                </div>
                            </div>
                            <button onclick="closeMapDetail()" class="text-emerald-700 hover:text-emerald-950"><i data-lucide="x" class="w-5 h-5"></i></button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 pt-4 border-t border-emerald-200 text-xs">
                            <div>
                                <span class="text-emerald-700 block mb-0.5">Tarikh & Masa Pengebumian</span>
                                <strong class="text-emerald-950 font-bold" id="detail-dateTime">15/01/2026, 10:30 AM</strong>
                            </div>
                            <div>
                                <span class="text-emerald-700 block mb-0.5">Waris Utama</span>
                                <strong class="text-emerald-950 font-bold" id="detail-waris-list">Mohd Musa (012-3456789)</strong>
                            </div>
                            <div class="flex items-end text-emerald-600 font-medium italic">
                                Dikemaskini oleh Sistem MyKubur
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB 2: PORTAL ADMIN -->
            <section id="tab-admin" class="tab-content hidden space-y-6">
                <!-- ADMIN LOGIN NOTICE -->
                <div class="app-card p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div class="flex items-center gap-2">
                        <span class="status-dot bg-emerald-600"></span>
                        <span class="text-xs font-semibold text-slate-700">Zon Kawalan Admin: Tanah Perkuburan Rantau Panjang</span>
                    </div>
                </div>

                <!-- TABEL REKOD (READ) & BUTTON TAMBAH -->
                <div class="app-card overflow-hidden">
                    <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div>
                            <h2 class="app-title">Senarai Rekod Si Mati</h2>
                            <p class="app-muted">Urus pendaftaran, kemaskini dan padam rekod kubur.</p>
                        </div>
                        <button onclick="openFormModal()" class="app-button-primary text-xs px-4 py-3 flex items-center gap-2 w-full sm:w-auto justify-center">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Rekod Baharu
                        </button>
                    </div>

                    <!-- Admin Search & Filter Bar -->
                    <div class="p-4 bg-slate-50 border-b border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="relative">
                            <input type="text" id="admin-search-input" onkeyup="filterAdminTable()" placeholder="Cari nama atau no. lot" class="app-input px-3 py-2 pl-9 text-xs">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-3"></i>
                        </div>
                        <div>
                            <select id="admin-filter-block" onchange="filterAdminTable()" class="app-input text-xs px-3 py-2">
                                <option value="">Semua Blok (A, B, C)</option>
                                <option value="A">Blok A</option>
                                <option value="B">Blok B</option>
                                <option value="C">Blok C</option>
                            </select>
                        </div>
                        <div class="text-right flex items-center justify-end text-xs text-slate-500 font-medium">
                            Jumlah: <span id="admin-table-count" class="font-bold text-slate-800 ml-1">0</span> rekod dijumpai
                        </div>
                    </div>

                    <!-- TABLE REKOD -->
                    <div class="admin-records-scroll overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                                    <th class="py-3 px-6">No. Slot</th>
                                    <th class="py-3 px-6">Butiran Si Mati</th>
                                    <th class="py-3 px-6">Tarikh & Masa Kebumi</th>
                                    <th class="py-3 px-6">Senarai Waris</th>
                                    <th class="py-3 px-6 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="admin-records-table-body" class="divide-y divide-slate-100 text-xs">
                                <!-- JS will inject table rows here -->
                            </tbody>
                        </table>
                    </div>

                    <div id="no-admin-records" class="hidden text-center py-12">
                        <i data-lucide="folder-open" class="w-10 h-10 text-slate-300 mx-auto mb-3"></i>
                        <p class="text-sm font-semibold text-slate-500">Tiada rekod kubur didaftarkan.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- FORM MODAL (ADD & EDIT GRAVE) -->
    <div id="form-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-2xl w-full shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <!-- Modal Header -->
            <div class="bg-slate-900 text-white px-6 py-4 flex justify-between items-center">
                <h3 id="modal-title" class="text-sm font-bold">Tambah Rekod Si Mati & Waris</h3>
                <button onclick="closeFormModal()" class="text-emerald-200 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            
            <!-- Modal Body Form -->
            <form id="grave-form" onsubmit="saveGraveRecord(event)" class="p-6 space-y-6 max-h-[75vh] overflow-y-auto text-xs">
                <input type="hidden" id="form-id">
                
                <!-- MAKLUMAT SI MATI -->
                <div>
                    <h4 class="font-bold text-slate-800 uppercase tracking-wide mb-3 border-b border-slate-200 pb-1">1. Maklumat Si Mati</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-slate-600 font-semibold mb-1">Nama Si Mati <span class="text-red-500">*</span></label>
                            <input type="text" id="form-nama" required placeholder="Contoh: Ahmad bin Musa" class="app-input px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-slate-600 font-semibold mb-1">No. Kad Pengenalan (IC) <span class="text-red-500">*</span></label>
                            <input type="text" id="form-ic" required placeholder="Contoh: 650203-08-5431" class="app-input px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-slate-600 font-semibold mb-1">Tarikh Dikebumikan <span class="text-red-500">*</span></label>
                            <input type="date" id="form-tarikh" required class="app-input px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label class="block text-slate-600 font-semibold mb-1">Masa Dikebumikan <span class="text-red-500">*</span></label>
                            <input type="time" id="form-masa" required class="app-input px-3 py-2 text-xs">
                        </div>
                    </div>
                </div>

                <!-- LOKASI / SLOT KUBUR -->
                <div>
                    <h4 class="font-bold text-slate-800 uppercase tracking-wide mb-3 border-b border-slate-200 pb-1">2. Pilihan Lokasi Slot Kubur</h4>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-slate-600 font-semibold mb-1">Blok <span class="text-red-500">*</span></label>
                            <select id="form-blok" onchange="updateNextLotForNewRecord()" required class="app-input px-3 py-2 text-xs">
                                <option value="A">Blok A</option>
                                <option value="B">Blok B</option>
                                <option value="C">Blok C</option>
                            </select>
                        </div>
                        <div>
                            <label id="form-baris-label" class="block text-slate-600 font-semibold mb-1">Baris (1-57) <span class="text-red-500">*</span></label>
                            <input type="number" id="form-baris" oninput="updateNextLotForNewRecord()" onchange="updateNextLotForNewRecord()" required min="1" max="57" placeholder="Contoh: 1" class="app-input px-3 py-2 text-xs">
                        </div>
                        <div>
                            <label id="form-lot-label" class="block text-slate-600 font-semibold mb-1">Lot Seterusnya <span class="text-red-500">*</span></label>
                            <input type="number" id="form-lot" oninput="validateSlotAvailability()" onchange="validateSlotAvailability()" required min="1" placeholder="Auto: lot seterusnya" class="app-input px-3 py-2 text-xs">
                        </div>
                    </div>
                    <div id="slot-warning" class="hidden mt-2 text-rose-600 font-semibold flex items-center gap-1">
                        <i data-lucide="alert-triangle" class="w-4 h-4"></i> <span id="slot-warning-text">Ralat: Slot ini telah dihuni oleh si mati lain!</span>
                    </div>
                </div>

                <!-- MAKLUMAT WARIS (1 - 5 ORANG) -->
                <div>
                    <div class="flex justify-between items-center mb-3 border-b border-slate-100 pb-1">
                        <h4 class="font-bold text-slate-800 uppercase tracking-wide">3. Maklumat Waris</h4>
                        <button type="button" onclick="addWarisRow()" class="text-emerald-800 hover:text-emerald-950 font-bold flex items-center gap-1">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Tambah Waris
                        </button>
                    </div>
                    
                    <div id="waris-rows-container" class="space-y-3">
                        <!-- Waris rows will inject here -->
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeFormModal()" class="app-button-secondary px-4 py-2">Batal</button>
                    <button type="submit" id="btn-submit-form" class="app-button-primary px-5 py-2">Simpan Rekod</button>
                </div>
            </form>
        </div>
    </div>

    <!-- FOOTER -->
    <footer id="system-footer" class="bg-slate-900 text-slate-400 py-8 border-t border-slate-800 text-xs hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
            <p class="font-bold text-slate-300">MyKubur &copy; 2026. Hak Cipta Terpelihara.</p>
            <p>Sistem ini merupakan model pengurusan rekod & peta interaktif rasmi Tanah Perkuburan Islam (Baru) Kg Rantau Panjang, 42100 Klang, Selangor.</p>
        </div>
    </footer>

    <!-- INTERACTIVE JAVASCRIPT STATE MANAGEMENT -->
    <script src="{{ asset('assets/js/app.js') }}?v={{ filemtime(public_path('assets/js/app.js')) }}" defer></script>
</body>
</html>
