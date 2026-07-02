let state = {
            records: [],
            currentUser: null,
            currentTab: 'waris',
            selectedMapBlock: 'A',
            selectedMapRow: 1,
            authMode: 'login' // login or register
        };

        const RECORDS_API_URL = '/grave-records';
        const AUTH_API_URL = '/auth';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const MAX_ROW = 57;

        function getBaseLotCount(blok, baris) {
            if (blok === 'B' && Number(baris) <= 7) {
                return 5;
            }

            return 10;
        }

        function getHighestLotInRow(blok, baris, ignoreId = '') {
            return state.records
                .filter(r => r.id !== ignoreId && r.blok === blok && r.baris === Number(baris))
                .reduce((highest, record) => Math.max(highest, Number(record.lot)), 0);
        }

        function getDisplayLotCount(blok, baris) {
            return Math.max(getBaseLotCount(blok, baris), getHighestLotInRow(blok, baris));
        }

        function getBlockCapacity(blok) {
            let total = 0;
            for (let baris = 1; baris <= MAX_ROW; baris++) {
                total += getDisplayLotCount(blok, baris);
            }

            return total;
        }

        function getNextLotNumber(blok, baris, ignoreId = '') {
            return Math.max(getBaseLotCount(blok, baris), getHighestLotInRow(blok, baris, ignoreId)) + 1;
        }

        function getLotCode(record) {
            return `${record.blok}${record.baris}-${record.lot}`;
        }

        function getZoneName(blok) {
            if (blok === 'A') return 'Zon Kanan';
            if (blok === 'B') return 'Zon Tengah';
            return 'Zon Kiri';
        }

        function isValidSlot(blok, baris, lot) {
            const rowNumber = Number(baris);
            const lotNumber = Number(lot);
            return ['A', 'B', 'C'].includes(blok)
                && rowNumber >= 1
                && rowNumber <= MAX_ROW
                && lotNumber >= 1;
        }

        function normalizeRecord(record) {
            return {
                ...record,
                id: String(record.id),
                baris: Number(record.baris),
                lot: Number(record.lot),
                masa_kebumi: String(record.masa_kebumi || '').slice(0, 5),
                waris: record.waris || []
            };
        }

        function getErrorMessage(error) {
            const firstError = error.errors ? Object.values(error.errors)[0]?.[0] : null;

            return firstError || error.message || 'Ralat sambungan database.';
        }

        async function jsonApi(baseUrl, path = '', options = {}) {
            const response = await fetch(`${baseUrl}${path}`, {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    ...(options.headers || {})
                },
                ...options
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({}));
                throw new Error(getErrorMessage(error));
            }

            return response.status === 204 ? null : response.json();
        }

        async function recordsApi(path = '', options = {}) {
            return jsonApi(RECORDS_API_URL, path, options);
        }

        async function authApi(path = '', options = {}) {
            return jsonApi(AUTH_API_URL, path, options);
        }

        function refreshAllViews() {
            localStorage.setItem('mykubur_records', JSON.stringify(state.records));
            renderStats();
            updatePhysicalMapBlocksUI();
            renderVisualMap();
            renderAdminTable();
            filterSearchWaris();
        }

        // Initialize App
        window.addEventListener('DOMContentLoaded', async () => {
            await initData();
            checkSession();
            populateBarisDropdown();
            renderStats();
            renderVisualMap();
            filterSearchWaris(); // Initial state show help
            document.getElementById('login-email')?.addEventListener('input', () => setLoginError(''));
            document.getElementById('login-password')?.addEventListener('input', () => setLoginError(''));
            lucide.createIcons();
        });

        async function initData() {
            // Load Records
            try {
                const records = await recordsApi();
                state.records = records.map(normalizeRecord);

                localStorage.setItem('mykubur_records', JSON.stringify(state.records));
            } catch (error) {
                const savedRecords = localStorage.getItem('mykubur_records');
                if (savedRecords) {
                    state.records = JSON.parse(savedRecords);
                } else {
                    state.records = [];
                    localStorage.removeItem('mykubur_records');
                }
                console.error(error);
            }

            localStorage.removeItem('mykubur_users');
        }

        // ==========================================
        // SESSION & AUTHENTICATION MANAGEMENT
        // ==========================================
        function checkSession() {
            const activeUser = sessionStorage.getItem('mykubur_session');
            if (activeUser) {
                state.currentUser = JSON.parse(activeUser);
                showSystemInterface();
            } else {
                hideSystemInterface();
            }
        }

        function toggleAuthMode(mode) {
            state.authMode = mode;
            setLoginError('');
            const tabLogin = document.getElementById('auth-tab-login');
            const tabRegister = document.getElementById('auth-tab-register');
            const formLogin = document.getElementById('login-form');
            const formRegister = document.getElementById('register-form');

            if (mode === 'login') {
                tabLogin.className = "py-2.5 text-xs font-bold rounded-lg bg-white text-emerald-900 shadow transition-all";
                tabRegister.className = "py-2.5 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-900 transition-all";
                formLogin.classList.remove('hidden');
                formRegister.classList.add('hidden');
            } else {
                tabRegister.className = "py-2.5 text-xs font-bold rounded-lg bg-white text-emerald-900 shadow transition-all";
                tabLogin.className = "py-2.5 text-xs font-bold rounded-lg text-slate-500 hover:text-slate-900 transition-all";
                formRegister.classList.remove('hidden');
                formLogin.classList.add('hidden');
            }
        }

        function setLoginError(message) {
            const errorBox = document.getElementById('login-error');
            if (!errorBox) return;

            errorBox.textContent = message || '';
            errorBox.classList.toggle('hidden', !message);
        }

        async function handleLogin(e) {
            e.preventDefault();
            setLoginError('');
            const email = document.getElementById('login-email').value.trim().toLowerCase();
            const pass = document.getElementById('login-password').value;

            try {
                const result = await authApi('/login', {
                    method: 'POST',
                    body: JSON.stringify({ email, password: pass })
                });
                sessionStorage.setItem('mykubur_session', JSON.stringify(result.user));
                state.currentUser = result.user;
                showSystemInterface();
                showToast(`Selamat kembali, ${result.user.name}!`);
            } catch (error) {
                setLoginError(error.message);
                showToast(error.message);
                console.error(error);
            }
        }

        async function handleRegister(e) {
            e.preventDefault();
            const name = document.getElementById('reg-name').value.trim();
            const email = document.getElementById('reg-email').value.trim().toLowerCase();
            const pass = document.getElementById('reg-password').value;

            try {
                const result = await authApi('/register', {
                    method: 'POST',
                    body: JSON.stringify({ name, email, password: pass })
                });
                sessionStorage.setItem('mykubur_session', JSON.stringify(result.user));
                state.currentUser = result.user;
                showSystemInterface();
                showToast(`Akaun berjaya didaftarkan. Selamat datang, ${result.user.name}!`);
            } catch (error) {
                showToast(error.message);
                console.error(error);
            }
        }

        function handleLogout() {
            sessionStorage.removeItem('mykubur_session');
            state.currentUser = null;
            hideSystemInterface();
        }

        function showSystemInterface() {
            document.getElementById('auth-screen').classList.add('hidden');
            document.getElementById('main-system-layout').classList.remove('hidden');
            document.getElementById('system-footer').classList.remove('hidden');

            // Set navbar names
            document.getElementById('nav-user-name').innerText = state.currentUser.name;
            document.getElementById('nav-user-name-mobile').innerText = state.currentUser.name;
            
            // Strict role-based navigation view control (Admins cannot see waris, Warises cannot see admin)
            if (state.currentUser.role === 'admin') {
                document.getElementById('nav-user-role').innerText = "Administrator Utama";
                document.getElementById('nav-admin').classList.remove('hidden');
                document.getElementById('nav-waris').classList.add('hidden'); // Admin strictly cannot see Waris button
                
                document.getElementById('mobile-nav-bar').classList.add('hidden'); 
                switchTab('admin'); 
            } else {
                document.getElementById('nav-user-role').innerText = "Waris Terdaftar";
                document.getElementById('nav-admin').classList.add('hidden'); // Waris strictly cannot see Admin button
                document.getElementById('nav-waris').classList.remove('hidden');
                
                document.getElementById('mobile-nav-bar').classList.add('hidden'); 
                switchTab('waris'); 
            }
            
            renderStats();
            updatePhysicalMapBlocksUI();
            renderVisualMap();
            lucide.createIcons();
        }

        function hideSystemInterface() {
            document.getElementById('auth-screen').classList.remove('hidden');
            document.getElementById('main-system-layout').classList.add('hidden');
            document.getElementById('system-footer').classList.add('hidden');
            
            // Reset inputs
            document.getElementById('login-email').value = '';
            document.getElementById('login-password').value = '';
            document.getElementById('reg-name').value = '';
            document.getElementById('reg-email').value = '';
            document.getElementById('reg-password').value = '';
        }

        function showToast(text) {
            const toast = document.getElementById('toast');
            document.getElementById('toast-text').innerText = text;
            toast.classList.remove('hidden');
            toast.classList.add('opacity-100');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 3000);
        }

        // Tampilkan statistik isian lot
        function renderStats() {
            let a = state.records.filter(r => r.blok === 'A').length;
            let b = state.records.filter(r => r.blok === 'B').length;
            let c = state.records.filter(r => r.blok === 'C').length;

            const capacityA = getBlockCapacity('A');
            const capacityB = getBlockCapacity('B');
            const capacityC = getBlockCapacity('C');
            const textA = `${a} / ${capacityA}`;
            const textB = `${b} / ${capacityB}`;
            const textC = `${c} / ${capacityC}`;
            const totalCapacityEl = document.getElementById('total-capacity');

            if (totalCapacityEl) {
                totalCapacityEl.innerText = `${capacityA + capacityB + capacityC} lot`;
            }

            // Top banner stats
            document.getElementById('stats-a').innerText = textA;
            document.getElementById('stats-b').innerText = textB;
            document.getElementById('stats-c').innerText = textC;

            // Physical layout stats safely checking existence
            const statsA = document.getElementById('layout-stats-a');
            const statsB = document.getElementById('layout-stats-b');
            const statsC = document.getElementById('layout-stats-c');
            if (statsA) statsA.innerText = textA;
            if (statsB) statsB.innerText = textB;
            if (statsC) statsC.innerText = textC;
        }

        function populateBarisDropdown() {
            const select = document.getElementById('map-row-select');
            select.innerHTML = '';
            for (let i = 1; i <= MAX_ROW; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.innerText = `Baris ${i}`;
                select.appendChild(opt);
            }
            select.value = state.selectedMapRow;
        }

        // TAB SWITCHER WITH STRICT PERMISSIONS
        function switchTab(tabId) {
            // Guard role access strictly
            if (tabId === 'admin' && state.currentUser.role !== 'admin') {
                showToast("Akses dihalang: Anda bukan Pentadbir sistem!");
                return;
            }
            if (tabId === 'waris' && state.currentUser.role !== 'waris') {
                showToast("Akses dihalang: Portal Waris hanya untuk akaun waris sahaja!");
                return;
            }

            state.currentTab = tabId;
            document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
            document.getElementById(`tab-${tabId}`).classList.remove('hidden');

            // Highlighting desktop buttons without overwriting hidden state
            document.querySelectorAll('.nav-btn').forEach(btn => {
                btn.classList.remove('bg-emerald-800', 'text-white');
                btn.classList.add('text-slate-300', 'hover:text-white', 'hover:bg-slate-800');
            });
            const activeBtn = document.getElementById(`nav-${tabId}`);
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-300', 'hover:text-white', 'hover:bg-slate-800');
                activeBtn.classList.add('bg-emerald-800', 'text-white');
            }

            if (tabId === 'admin') renderAdminTable();
            if (tabId === 'waris') {
                filterSearchWaris();
                updatePhysicalMapBlocksUI();
                renderVisualMap();
            }
        }

        // ==========================================
        // WARIS FUNCTIONALITIES
        // ==========================================
        function filterSearchWaris() {
            const query = document.getElementById('waris-search-input').value.toLowerCase().trim();
            const container = document.getElementById('search-results-container');
            const emptyState = document.getElementById('no-search-results');
            
            container.innerHTML = '';
            
            if (query === '') {
                emptyState.classList.add('hidden');
                container.innerHTML = `
                    <div class="col-span-full text-center py-6 app-panel">
                        <i data-lucide="info" class="w-6 h-6 text-emerald-700 mx-auto mb-2"></i>
                        <p class="text-xs text-slate-500 font-medium">Masukkan kata carian untuk melihat rekod kubur.</p>
                    </div>
                `;
                lucide.createIcons();
                return;
            }

            const filtered = state.records.filter(r => {
                const lotCode = getLotCode(r).toLowerCase();
                const warisNames = r.waris.map(w => w.nama.toLowerCase()).join(' ');
                return r.nama_si_mati.toLowerCase().includes(query)
                    || r.no_ic.toLowerCase().includes(query)
                    || lotCode.includes(query)
                    || warisNames.includes(query);
            });

            if (filtered.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
                filtered.forEach(record => {
                    const warisListHtml = record.waris.map((w, index) =>
                        `<li>Waris ${index+1}: <span class="font-bold text-slate-800">${w.nama}</span> (${w.no_tel})</li>`
                    ).join('');
                    const warisNames = record.waris.map(w => w.nama).join(', ') || '-';

                    const card = document.createElement('div');
                    card.className = "bg-white border border-slate-200 hover:border-emerald-600 rounded-xl p-4 transition cursor-pointer";
                    card.onclick = () => locateOnMap(record.blok, record.baris, record.id);
                    card.innerHTML = `
                        <div class="flex justify-between items-start gap-3">
                            <div>
                                <h3 class="font-bold text-slate-900 text-sm md:text-base">${record.nama_si_mati}</h3>
                                <p class="text-xs text-slate-500 font-semibold mb-2">IC Si Mati: ${record.no_ic}</p>
                                <div class="flex items-center gap-1.5 bg-emerald-50 text-emerald-800 px-2 py-1 rounded text-[11px] font-bold w-fit border border-emerald-100">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> Nombor Lot: ${getLotCode(record)}
                                </div>
                            </div>
                            <span class="app-badge app-badge-primary">
                                Blok ${record.blok}
                            </span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                            <p class="flex items-center gap-1"><i data-lucide="users" class="w-3.5 h-3.5 text-slate-400"></i> Nama Waris: <strong class="text-slate-700">${warisNames}</strong></p>
                            <p class="flex items-center gap-1"><i data-lucide="layout-grid" class="w-3.5 h-3.5 text-slate-400"></i> Blok: <strong class="text-slate-700">${record.blok}</strong> | Lokasi: <strong class="text-slate-700">${getZoneName(record.blok)}</strong></p>
                            <p class="flex items-center gap-1"><i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i> Dikebumikan: <strong class="text-slate-700">${formatDate(record.tarikh_kebumi)} @ ${record.masa_kebumi}</strong></p>
                            <ul class="space-y-1 pl-1 text-[11px] text-slate-500 border-l-2 border-slate-200 pl-3">
                                ${warisListHtml}
                            </ul>
                        </div>
                        <div class="mt-3 text-[11px] text-emerald-700 hover:text-emerald-950 font-bold flex items-center gap-1">
                            Klik untuk lihat lokasi fizikal pada peta <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </div>
                    `;
                    container.appendChild(card);
                });
            }
            lucide.createIcons();
        }

        function resetWarisSearch() {
            document.getElementById('waris-search-input').value = '';
            filterSearchWaris();
        }

        function locateOnMap(blok, baris, recordId) {
            state.selectedMapBlock = blok;
            state.selectedMapRow = baris;
            
            // Sync values with inputs
            document.getElementById('map-row-select').value = baris;
            
            updatePhysicalMapBlocksUI();
            renderVisualMap();

            // Highlight & show detail panel
            const rec = state.records.find(r => r.id === recordId);
            if (rec) {
                showMapDetail(rec);
            }

            // Scroll down to the visual grid section smoothly
            document.getElementById('visual-lots-grid').scrollIntoView({ behavior: 'smooth' });
        }

        function changeMapBlock(blok) {
            state.selectedMapBlock = blok;
            if (state.selectedMapRow > MAX_ROW) {
                state.selectedMapRow = MAX_ROW;
                document.getElementById('map-row-select').value = MAX_ROW;
            }
            updatePhysicalMapBlocksUI();
            renderVisualMap();
            showToast(`Menukar paparan ke Blok ${blok}`);
        }

        // Toggles classes dynamically for Block A, B, and C to prevent washed out text colors
        function updatePhysicalMapBlocksUI() {
            const blkA = document.getElementById('layout-block-a');
            const blkB = document.getElementById('layout-block-b');
            const blkC = document.getElementById('layout-block-c');

            const normalClasses = "col-span-3 border rounded-xl p-3 md:p-4 flex flex-col justify-between items-center transition duration-200 cursor-pointer text-center relative group";
            const activeClasses = "col-span-3 border rounded-xl p-3 md:p-4 flex flex-col justify-between items-center transition duration-200 cursor-pointer text-center relative group ring-2 ring-emerald-700/20";

            function styleBlock(blockEl, blockId, isSelected) {
                const isB = blockId === 'B';
                const zonName = getZoneName(blockId);
                const capacity = getBlockCapacity(blockId);
                
                if (isSelected) {
                    blockEl.className = `${isB ? 'flex-grow' : 'col-span-3'} bg-emerald-800 hover:bg-emerald-700 border-emerald-700 text-white ${activeClasses}`;
                    blockEl.innerHTML = `
                        <span class="bg-white/95 text-emerald-900 text-[9px] md:text-xs font-bold px-2.5 py-1 rounded uppercase">Blok ${blockId}</span>
                        <div class="my-3 md:my-5">
                            <p class="text-[10px] md:text-xs text-emerald-50 font-bold uppercase">${zonName}</p>
                        </div>
                        <div class="bg-emerald-950/70 px-1.5 md:px-2 py-1.5 rounded-lg border border-emerald-700 w-full">
                            <span class="text-[8px] md:text-[9px] text-emerald-200 block uppercase font-bold leading-none">Isian Lot</span>
                            <strong id="layout-stats-${blockId.toLowerCase()}" class="text-white text-xs font-black">0 / ${capacity}</strong>
                        </div>
                        <span class="active-badge-span absolute -top-2 -right-2 bg-slate-900 text-white text-[9px] font-bold px-2 py-0.5 rounded border border-white/70">PILIHAN</span>
                    `;
                } else {
                    blockEl.className = `${isB ? 'flex-grow' : 'col-span-3'} bg-white hover:bg-slate-50 border-slate-300 text-slate-800 ${normalClasses}`;
                    blockEl.innerHTML = `
                        <span class="bg-slate-100 text-slate-800 text-[9px] md:text-xs font-bold px-2.5 py-1 rounded uppercase border border-slate-200">Blok ${blockId}</span>
                        <div class="my-3 md:my-5">
                            <p class="text-[10px] md:text-xs text-slate-800 font-bold uppercase">${zonName}</p>
                        </div>
                        <div class="bg-slate-50 px-1.5 md:px-2 py-1.5 rounded-lg border border-slate-200 w-full">
                            <span class="text-[8px] md:text-[9px] text-slate-600 block uppercase font-bold leading-none">Isian Lot</span>
                            <strong id="layout-stats-${blockId.toLowerCase()}" class="text-slate-850 text-xs font-black">0 / ${capacity}</strong>
                        </div>
                    `;
                }
            }

            styleBlock(blkA, 'A', state.selectedMapBlock === 'A');
            styleBlock(blkB, 'B', state.selectedMapBlock === 'B');
            styleBlock(blkC, 'C', state.selectedMapBlock === 'C');

            // Sync top header label text
            document.getElementById('selected-block-label').innerText = `BLOCK ${state.selectedMapBlock}`;
            document.getElementById('lots-header-title').innerText = `PAPARAN BARISAN LOTS: BLOK ${state.selectedMapBlock} (BARIS ${state.selectedMapRow})`;

            // Re-render state stats labels inside the newly dynamic blocks
            renderStats();
            lucide.createIcons();
        }

        function renderVisualMap() {
            let baris = parseInt(document.getElementById('map-row-select').value) || 1;
            if (baris > MAX_ROW) {
                baris = MAX_ROW;
                document.getElementById('map-row-select').value = MAX_ROW;
            }
            state.selectedMapRow = baris;
            
            const container = document.getElementById('visual-lots-grid');
            container.innerHTML = '';

            const blok = state.selectedMapBlock;
            document.getElementById('lots-header-title').innerText = `PAPARAN BARISAN LOTS: BLOK ${blok} (BARIS ${baris})`;

            const lotCount = getDisplayLotCount(blok, baris);

            for (let lotNum = 1; lotNum <= lotCount; lotNum++) {
                const occupiedRecord = state.records.find(r => r.blok === blok && r.baris === baris && r.lot === lotNum);
                
                const lotCard = document.createElement('div');
                const lotCode = `${blok}${baris}-${lotNum}`;

                if (occupiedRecord) {
                    lotCard.className = "bg-rose-50 border border-rose-300 hover:border-rose-500 rounded-lg p-3 text-center cursor-pointer transition";
                    lotCard.onclick = () => showMapDetail(occupiedRecord);
                    lotCard.innerHTML = `
                        <span class="text-[10px] font-bold block text-rose-800">${lotCode}</span>
                        <div class="h-1 bg-rose-200 rounded my-1 w-full"></div>
                        <span class="text-xs font-bold text-rose-950 truncate block">${occupiedRecord.nama_si_mati}</span>
                    `;
                } else {
                    lotCard.className = "bg-white border border-dashed border-slate-300 hover:border-emerald-500 rounded-lg p-3 text-center cursor-pointer transition";
                    lotCard.onclick = () => showEmptyLotAlert(blok, baris, lotNum);
                    lotCard.innerHTML = `
                        <span class="text-[10px] font-bold block text-slate-400">${lotCode}</span>
                        <div class="h-1 bg-emerald-100 rounded my-1 w-full"></div>
                        <span class="text-[10px] font-bold text-emerald-600">KOSONG</span>
                    `;
                }
                container.appendChild(lotCard);
            }
        }

        function showMapDetail(record) {
            const panel = document.getElementById('map-detail-card');
            panel.classList.remove('hidden');

            document.getElementById('detail-lot-id').innerText = getLotCode(record);
            document.getElementById('detail-deceased-name').innerText = record.nama_si_mati;
            document.getElementById('detail-deceased-ic').innerText = `No. IC: ${record.no_ic}`;
            document.getElementById('detail-dateTime').innerText = `${formatDate(record.tarikh_kebumi)} @ ${record.masa_kebumi}`;
            
            const warisStr = record.waris.map(w => `${w.nama} (${w.no_tel})`).join(' | ');
            document.getElementById('detail-waris-list').innerText = warisStr;
            
            panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function closeMapDetail() {
            document.getElementById('map-detail-card').classList.add('hidden');
        }

        function showEmptyLotAlert(blok, baris, lot) {
            closeMapDetail();
            showToast(`Lot ${blok}${baris}-${lot} sedia ada (Kosong). Admin boleh mendaftarkan si mati di slot ini.`);
        }

        // ==========================================
        // ADMIN FUNCTIONALITIES (CRUD)
        // ==========================================
        function renderAdminTable() {
            const tbody = document.getElementById('admin-records-table-body');
            const noRecords = document.getElementById('no-admin-records');
            const searchVal = document.getElementById('admin-search-input').value.toLowerCase().trim();
            const filterBlock = document.getElementById('admin-filter-block').value;

            tbody.innerHTML = '';

            const filtered = state.records.filter(r => {
                const lotCode = getLotCode(r).toLowerCase();
                const warisNames = r.waris.map(w => w.nama.toLowerCase()).join(' ');
                const matchSearch = r.nama_si_mati.toLowerCase().includes(searchVal)
                    || r.no_ic.toLowerCase().includes(searchVal)
                    || lotCode.includes(searchVal)
                    || warisNames.includes(searchVal);
                const matchBlock = filterBlock === "" || r.blok === filterBlock;
                return matchSearch && matchBlock;
            });

            document.getElementById('admin-table-count').innerText = filtered.length;

            if (filtered.length === 0) {
                noRecords.classList.remove('hidden');
                return;
            }
            noRecords.classList.add('hidden');

            filtered.forEach(r => {
                const warisList = r.waris.map((w, index) => 
                    `<div class="pb-1 text-[11px]"><span class="font-bold text-slate-700">${index+1}. ${w.nama}</span> - <span class="text-slate-500">${w.no_tel}</span></div>`
                ).join('');

                const row = document.createElement('tr');
                row.className = "hover:bg-slate-50 border-b border-slate-100 transition";
                row.innerHTML = `
                    <td class="py-4 px-6 font-bold text-emerald-900">
                        <span class="bg-emerald-50 border border-emerald-200 px-2 py-1 rounded text-xs font-bold">
                            ${getLotCode(r)}
                        </span>
                    </td>
                    <td class="py-4 px-6">
                        <div class="font-bold text-slate-800 text-sm">${r.nama_si_mati}</div>
                        <div class="text-slate-500 text-[11px]">No. IC: ${r.no_ic}</div>
                    </td>
                    <td class="py-4 px-6 font-medium text-slate-600">
                        <div>${formatDate(r.tarikh_kebumi)}</div>
                        <div class="text-slate-400 text-[11px]">${r.masa_kebumi}</div>
                    </td>
                    <td class="py-4 px-6">
                        <div class="divide-y divide-slate-50">${warisList}</div>
                    </td>
                    <td class="py-4 px-6 text-right">
                        <div class="flex justify-end gap-1.5">
                            <button onclick="editGraveRecord('${r.id}')" class="p-1.5 bg-white border border-slate-200 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 rounded transition" title="Kemaskini">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </button>
                            <button onclick="deleteGraveRecord('${r.id}')" class="p-1.5 bg-white border border-slate-200 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded transition" title="Padam">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
            lucide.createIcons();
        }

        function filterAdminTable() {
            renderAdminTable();
        }

        function updateNextLotForNewRecord() {
            const id = document.getElementById('form-id').value;
            const blok = document.getElementById('form-blok').value;
            const baris = parseInt(document.getElementById('form-baris').value) || 0;

            if (!id && blok && baris >= 1 && baris <= MAX_ROW) {
                document.getElementById('form-lot').value = getNextLotNumber(blok, baris);
            }

            validateSlotAvailability();
        }

        // Add/Remove Waris fields dynamically inside modal form
        function addWarisRow(nama = '', noTel = '') {
            const container = document.getElementById('waris-rows-container');
            const rows = container.getElementsByClassName('waris-row');
            
            if (rows.length >= 5) {
                showToast("Maksimum 5 orang waris sahaja bagi setiap kubur.");
                return;
            }

            const rowId = `waris-row-${Date.now()}-${Math.random()}`;
            const row = document.createElement('div');
            row.className = "waris-row grid grid-cols-1 sm:grid-cols-2 gap-3 items-end p-3 bg-slate-50 rounded-lg border border-slate-200 relative";
            row.id = rowId;
            row.innerHTML = `
                <div>
                    <label class="block text-slate-500 font-medium mb-1 text-[10px]">Nama Waris</label>
                    <input type="text" value="${nama}" required placeholder="Nama penuh waris" class="waris-nama app-input px-2 py-1.5 text-xs">
                </div>
                <div class="flex gap-2 items-center">
                    <div class="flex-grow">
                        <label class="block text-slate-500 font-medium mb-1 text-[10px]">No. Telefon Waris</label>
                        <input type="text" value="${noTel}" required placeholder="Contoh: 012-3456789" class="waris-tel app-input px-2 py-1.5 text-xs">
                    </div>
                    <button type="button" onclick="removeWarisRow('${rowId}')" class="p-2 bg-white border border-slate-200 hover:bg-rose-50 text-rose-600 rounded-lg mt-5 transition" title="Padam Waris">
                        <i data-lucide="minus-circle" class="w-4 h-4"></i>
                    </button>
                </div>
            `;
            container.appendChild(row);
            lucide.createIcons();
        }

        function removeWarisRow(rowId) {
            const container = document.getElementById('waris-rows-container');
            const rows = container.getElementsByClassName('waris-row');
            if (rows.length <= 1) {
                showToast("Minimum mesti ada sekurang-kurangnya 1 orang waris.");
                return;
            }
            const row = document.getElementById(rowId);
            if (row) container.removeChild(row);
        }

        // Verify slot availability live in frontend simulator
        function validateSlotAvailability() {
            const id = document.getElementById('form-id').value;
            const blok = document.getElementById('form-blok').value;
            const baris = parseInt(document.getElementById('form-baris').value) || 0;
            const lot = parseInt(document.getElementById('form-lot').value) || 0;
            const warning = document.getElementById('slot-warning');
            const warningText = document.getElementById('slot-warning-text');
            const btnSubmit = document.getElementById('btn-submit-form');
            const lotInput = document.getElementById('form-lot');
            const rowInput = document.getElementById('form-baris');
            const lotLabel = document.getElementById('form-lot-label');
            const rowLabel = document.getElementById('form-baris-label');
            const baseLot = getBaseLotCount(blok, baris || 1);
            const displayLot = getDisplayLotCount(blok, baris || 1);

            rowInput.max = MAX_ROW;
            lotInput.removeAttribute('max');
            rowLabel.innerHTML = `Baris (1-${MAX_ROW}) <span class="text-red-500">*</span>`;
            lotLabel.innerHTML = `Lot Seterusnya <span class="text-red-500">*</span>`;
            lotInput.placeholder = `Auto: ${blok}${baris || 1}-${displayLot + 1} (asas ${baseLot} lot)`;

            if (!blok || !baris || !lot) return;

            if (!isValidSlot(blok, baris, lot)) {
                warningText.innerText = `Ralat: ${blok}${baris}-${lot} di luar susun atur blok ini.`;
                warning.classList.remove('hidden');
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
                return;
            }

            // Check if slot is taken by another record
            const occupied = state.records.find(r => r.id !== id && r.blok === blok && r.baris === baris && r.lot === lot);
            
            if (occupied) {
                warningText.innerText = 'Ralat: Slot ini telah dihuni oleh si mati lain!';
                warning.classList.remove('hidden');
                btnSubmit.disabled = true;
                btnSubmit.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                warning.classList.add('hidden');
                btnSubmit.disabled = false;
                btnSubmit.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        // Open modal for Create or Update
        function openFormModal(record = null) {
            const modal = document.getElementById('form-modal');
            const title = document.getElementById('modal-title');
            const warisContainer = document.getElementById('waris-rows-container');
            
            modal.classList.remove('hidden');
            warisContainer.innerHTML = '';

            if (record) {
                title.innerText = "Kemaskini Rekod Si Mati & Waris";
                document.getElementById('form-id').value = record.id;
                document.getElementById('form-nama').value = record.nama_si_mati;
                document.getElementById('form-ic').value = record.no_ic;
                document.getElementById('form-tarikh').value = record.tarikh_kebumi;
                document.getElementById('form-masa').value = record.masa_kebumi;
                document.getElementById('form-blok').value = record.blok;
                document.getElementById('form-baris').value = record.baris;
                document.getElementById('form-lot').value = record.lot;

                record.waris.forEach(w => addWarisRow(w.nama, w.no_tel));
            } else {
                title.innerText = "Tambah Rekod Si Mati Baru";
                document.getElementById('grave-form').reset();
                document.getElementById('form-id').value = '';
                
                document.getElementById('form-blok').value = state.selectedMapBlock;
                document.getElementById('form-baris').value = state.selectedMapRow;
                document.getElementById('form-lot').value = getNextLotNumber(state.selectedMapBlock, state.selectedMapRow);

                addWarisRow();
            }

            validateSlotAvailability();
        }

        // Close form modal
        function closeFormModal() {
            document.getElementById('form-modal').classList.add('hidden');
        }

        // Save Function
        async function saveGraveRecord(e) {
            e.preventDefault();

            const id = document.getElementById('form-id').value;
            const nama = document.getElementById('form-nama').value.trim();
            const ic = document.getElementById('form-ic').value.trim();
            const tarikh = document.getElementById('form-tarikh').value;
            const masa = document.getElementById('form-masa').value;
            const blok = document.getElementById('form-blok').value;
            const baris = parseInt(document.getElementById('form-baris').value);
            const lot = parseInt(document.getElementById('form-lot').value);

            // Read waris inputs
            const warisRows = document.getElementById('waris-rows-container').getElementsByClassName('waris-row');
            const warisList = [];
            for (let r of warisRows) {
                const wNama = r.getElementsByClassName('waris-nama')[0].value.trim();
                const wTel = r.getElementsByClassName('waris-tel')[0].value.trim();
                if (wNama && wTel) {
                    warisList.push({ nama: wNama, no_tel: wTel });
                }
            }

            if (warisList.length === 0) {
                showToast("Sila isi sekurang-kurangnya satu maklumat waris.");
                return;
            }

            if (!isValidSlot(blok, baris, lot)) {
                showToast(`Slot ${blok}${baris}-${lot} tidak wujud dalam susun atur blok ini.`);
                validateSlotAvailability();
                return;
            }

            const payload = { nama_si_mati: nama, no_ic: ic, blok, baris, lot, tarikh_kebumi: tarikh, masa_kebumi: masa, waris: warisList };

            try {
                if (id) {
                    const updatedRecord = await recordsApi(`/${id}`, {
                        method: 'PUT',
                        body: JSON.stringify(payload)
                    });
                    const index = state.records.findIndex(r => r.id === id);
                    if (index !== -1) {
                        state.records[index] = normalizeRecord(updatedRecord);
                    }
                    showToast("Rekod si mati berjaya dikemaskini.");
                } else {
                    const newRecord = await recordsApi('', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    state.records.push(normalizeRecord(newRecord));
                    showToast("Rekod si mati berjaya didaftarkan.");
                }
            } catch (error) {
                showToast(`Ralat database: ${error.message}`);
                console.error(error);
                return;
            }

            closeFormModal();
            refreshAllViews();
        }

        function editGraveRecord(id) {
            const record = state.records.find(r => r.id === id);
            if (record) {
                openFormModal(record);
            }
        }

        async function deleteGraveRecord(id) {
            if (confirm("Adakah anda pasti mahu memadam rekod si mati ini secara kekal?")) {
                try {
                    await recordsApi(`/${id}`, { method: 'DELETE' });
                } catch (error) {
                    showToast(`Ralat database: ${error.message}`);
                    console.error(error);
                    return;
                }
                state.records = state.records.filter(r => r.id !== id);
                showToast("Rekod berjaya dipadam secara kekal.");
                refreshAllViews();
                closeMapDetail();
            }
        }

        // ==========================================
        // HELPERS
        // ==========================================
        function formatDate(dateStr) {
            if (!dateStr) return '';
            const parts = dateStr.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`; // DD/MM/YYYY
            }
            return dateStr;
        }
