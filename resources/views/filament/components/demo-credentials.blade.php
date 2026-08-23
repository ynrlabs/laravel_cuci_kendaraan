<div class="mt-6 p-4 rounded-xl bg-slate-900 border border-slate-700/80 text-white text-xs shadow-lg font-sans">
    <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-800">
        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="font-bold text-slate-200 tracking-wide text-xs uppercase font-mono">Akun Demo Testing Admin</span>
        </div>
        <span class="px-2 py-0.5 text-[10px] font-mono bg-blue-500/20 text-blue-300 rounded border border-blue-500/30">
            Pass: password123
        </span>
    </div>

    <div class="space-y-2 text-xs">
        <div class="p-2.5 rounded-lg bg-slate-950/70 border border-slate-800 flex items-center justify-between">
            <div>
                <div class="font-bold text-white text-[11px]">Super Admin (Owner)</div>
                <div class="font-mono text-slate-300 text-[11px]">superadmin@cucikuy.com</div>
            </div>
            <button 
                type="button"
                onclick="
                    const emailInput = document.querySelector('input[type=email], input[name=email]');
                    const passInput = document.querySelector('input[type=password], input[name=password]');
                    if (emailInput) { emailInput.value = 'superadmin@cucikuy.com'; emailInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (passInput) { passInput.value = 'password123'; passInput.dispatchEvent(new Event('input', { bubbles: true })); }
                "
                class="px-2.5 py-1 text-[11px] font-semibold bg-blue-600 hover:bg-blue-500 text-white rounded-md transition-colors"
            >
                Gunakan
            </button>
        </div>

        <div class="p-2.5 rounded-lg bg-slate-950/70 border border-slate-800 flex items-center justify-between">
            <div>
                <div class="font-bold text-white text-[11px]">Admin CuciKuy Pusat</div>
                <div class="font-mono text-slate-300 text-[11px]">admin.cucikuy-pusat@cucikuy.com</div>
            </div>
            <button 
                type="button"
                onclick="
                    const emailInput = document.querySelector('input[type=email], input[name=email]');
                    const passInput = document.querySelector('input[type=password], input[name=password]');
                    if (emailInput) { emailInput.value = 'admin.cucikuy-pusat@cucikuy.com'; emailInput.dispatchEvent(new Event('input', { bubbles: true })); }
                    if (passInput) { passInput.value = 'password123'; passInput.dispatchEvent(new Event('input', { bubbles: true })); }
                "
                class="px-2.5 py-1 text-[11px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-md transition-colors"
            >
                Gunakan
            </button>
        </div>

    </div>

    <div class="mt-3 pt-2 text-center text-[10px] text-slate-400 border-t border-slate-800/80">
        💡 Password default untuk semua akun pengujian: <strong class="text-emerald-400 font-mono">password123</strong>
    </div>
</div>
