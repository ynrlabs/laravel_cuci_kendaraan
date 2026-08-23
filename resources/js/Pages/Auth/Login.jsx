import React, { useState } from 'react';
import { useForm, Head } from '@inertiajs/react';
import { Button } from '@/Components/UI/Button';
import { Input } from '@/Components/UI/Input';
import { Label } from '@/Components/UI/Label';
import { Eye, EyeOff, Lock, Mail, Car } from 'lucide-react';

export default function Login() {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false,
  });

  const [showPassword, setShowPassword] = useState(false);

  const handleSubmit = (e) => {
    e.preventDefault();
    post('/login');
  };

  return (
    <>
      <Head title="Login Kasir & Operator" />
      
      <div className="min-h-screen flex items-center justify-center bg-radial from-slate-900 via-slate-950 to-black p-4 relative overflow-hidden font-sans">
        {/* Animated background decoration */}
        <div className="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-[#003EC7]/10 rounded-full blur-[120px] pointer-events-none"></div>
        <div className="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] bg-[#003EC7]/15 rounded-full blur-[120px] pointer-events-none"></div>

        <div className="w-full max-w-md bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 rounded-2xl p-8 shadow-2xl relative z-10 transition-all duration-300 hover:shadow-[#003EC7]/5">
          {/* Header & Logo */}
          <div className="flex flex-col items-center mb-8">
            <div className="w-16 h-16 bg-gradient-to-tr from-[#003EC7] to-blue-500 rounded-2xl flex items-center justify-center shadow-lg shadow-[#003EC7]/30 mb-4 transform hover:scale-105 transition-transform duration-300">
              <Car className="w-9 h-9 text-white" />
            </div>
            <h1 className="text-3xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-white via-slate-100 to-slate-300 tracking-tight font-montserrat">
              CuciKuy
            </h1>
            <p className="text-sm text-slate-400 mt-2 font-medium">
              Sistem Manajemen Cuci Kendaraan
            </p>
          </div>

          {/* Form */}
          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Email Input */}
            <div className="space-y-2">
              <Label htmlFor="email" className="text-slate-300 text-xs font-semibold uppercase tracking-wider">
                Alamat Email
              </Label>
              <div className="relative">
                <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                  <Mail className="w-4 h-4" />
                </span>
                <Input
                  id="email"
                  type="email"
                  value={data.email}
                  onChange={(e) => setData('email', e.target.value)}
                  placeholder="name@branch.com"
                  className="pl-10 bg-slate-950/50 border-slate-800 text-slate-200 placeholder:text-slate-600 focus:border-[#003EC7] focus:ring-[#003EC7]/20 transition-all"
                  required
                  autoFocus
                />
              </div>
              {errors.email && (
                <p className="text-xs text-red-500 font-medium mt-1 animate-pulse">
                  {errors.email}
                </p>
              )}
            </div>

            {/* Password Input */}
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <Label htmlFor="password" className="text-slate-300 text-xs font-semibold uppercase tracking-wider">
                  Password
                </Label>
              </div>
              <div className="relative">
                <span className="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500">
                  <Lock className="w-4 h-4" />
                </span>
                <Input
                  id="password"
                  type={showPassword ? 'text' : 'password'}
                  value={data.password}
                  onChange={(e) => setData('password', e.target.value)}
                  placeholder="••••••••"
                  className="pl-10 pr-10 bg-slate-950/50 border-slate-800 text-slate-200 placeholder:text-slate-600 focus:border-[#003EC7] focus:ring-[#003EC7]/20 transition-all"
                  required
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-500 hover:text-slate-300 transition-colors"
                >
                  {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                </button>
              </div>
              {errors.password && (
                <p className="text-xs text-red-500 font-medium mt-1 animate-pulse">
                  {errors.password}
                </p>
              )}
            </div>

            {/* Remember Me */}
            <div className="flex items-center">
              <input
                id="remember"
                type="checkbox"
                checked={data.remember}
                onChange={(e) => setData('remember', e.target.checked)}
                className="w-4 h-4 rounded border-slate-800 text-[#003EC7] focus:ring-[#003EC7]/30 bg-slate-950/50 focus:ring-offset-slate-900"
              />
              <label htmlFor="remember" className="ml-2 text-sm text-slate-400 cursor-pointer select-none">
                Ingat saya di perangkat ini
              </label>
            </div>

            {/* Submit Button */}
            <Button
              type="submit"
              disabled={processing}
              className="w-full bg-[#003EC7] hover:bg-[#003EC7]/90 text-white font-semibold py-2.5 rounded-lg shadow-lg shadow-[#003EC7]/20 hover:shadow-[#003EC7]/30 transition-all duration-300 flex justify-center items-center gap-2 group disabled:opacity-50"
            >
              {processing ? (
                <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
              ) : (
                <>
                  <span>Masuk ke Dashboard</span>
                </>
              )}
            </Button>
          </form>

          {/* Default Testing Credentials Helper */}
          <div className="mt-6 pt-5 border-t border-slate-800/90">
            <div className="p-4 rounded-xl bg-slate-950/80 border border-slate-800 shadow-inner">
              <div className="flex items-center justify-between mb-3 pb-2 border-b border-slate-800/80">
                <div className="flex items-center gap-2">
                  <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  <span className="text-xs font-bold text-slate-200 uppercase tracking-wider font-mono">
                    Akun Demo & Testing:
                  </span>
                </div>
                <span className="text-[10px] font-mono bg-emerald-500/10 text-emerald-400 px-2 py-0.5 rounded border border-emerald-500/20 font-bold">
                  Password: password123
                </span>
              </div>

              <div className="space-y-2.5">
                {/* Kasir */}
                <div className="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800/90 flex items-center justify-between hover:border-blue-500/40 transition-colors">
                  <div className="min-w-0 pr-2">
                    <div className="flex items-center gap-1.5">
                      <span className="text-xs font-bold text-white">Kasir Cabang Pusat</span>
                      <span className="text-[9px] px-1.5 py-0.2 rounded bg-blue-500/20 text-blue-300 font-mono">POS / Antrian</span>
                    </div>
                    <div className="text-[11px] font-mono text-slate-300 truncate select-all mt-0.5">
                      kasir.cucikuy-pusat@cucikuy.com
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={() => {
                      setData('email', 'kasir.cucikuy-pusat@cucikuy.com');
                      setData('password', 'password123');
                    }}
                    className="flex-shrink-0 px-2.5 py-1 text-[11px] font-bold bg-blue-600 hover:bg-blue-500 text-white rounded-md transition-all shadow-sm active:scale-95"
                  >
                    Gunakan
                  </button>
                </div>

                {/* Operator */}
                <div className="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800/90 flex items-center justify-between hover:border-blue-500/40 transition-colors">
                  <div className="min-w-0 pr-2">
                    <div className="flex items-center gap-1.5">
                      <span className="text-xs font-bold text-white">Operator Cuci Pusat</span>
                      <span className="text-[9px] px-1.5 py-0.2 rounded bg-purple-500/20 text-purple-300 font-mono">Pengerjaan</span>
                    </div>
                    <div className="text-[11px] font-mono text-slate-300 truncate select-all mt-0.5">
                      supervisor.cucikuy-pusat@cucikuy.com
                    </div>
                  </div>
                  <button
                    type="button"
                    onClick={() => {
                      setData('email', 'supervisor.cucikuy-pusat@cucikuy.com');
                      setData('password', 'password123');
                    }}
                    className="flex-shrink-0 px-2.5 py-1 text-[11px] font-bold bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-md transition-all shadow-sm active:scale-95"
                  >
                    Gunakan
                  </button>
                </div>


                {/* Super Admin link */}
                <div className="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800/90 flex items-center justify-between hover:border-blue-500/40 transition-colors">
                  <div className="min-w-0 pr-2">
                    <div className="flex items-center gap-1.5">
                      <span className="text-xs font-bold text-white">Super Admin (Owner)</span>
                      <span className="text-[9px] px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300 font-mono">Panel Admin</span>
                    </div>
                    <div className="text-[11px] font-mono text-slate-300 truncate select-all mt-0.5">
                      superadmin@cucikuy.com
                    </div>
                  </div>
                  <a
                    href="/admin/login"
                    className="flex-shrink-0 px-2.5 py-1 text-[11px] font-bold bg-amber-600/80 hover:bg-amber-500 text-white rounded-md transition-all shadow-sm active:scale-95"
                  >
                    Ke Admin
                  </a>
                </div>
              </div>

              <div className="mt-3 pt-2 text-center text-[10px] text-slate-400 border-t border-slate-800/80">
                💡 Klik tombol <strong class="text-blue-400">Gunakan</strong> untuk otomatis mengisi form di atas.
              </div>
            </div>
          </div>

          {/* Footer note */}
          <div className="mt-4 text-center text-xs text-slate-500">
            CuciKuy • Sistem Manajemen Cuci Kendaraan Terintegrasi
          </div>
        </div>
      </div>
    </>
  );
}


