import * as React from 'react';
import { useState, useEffect, useCallback, useMemo } from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Badge } from '@/Components/UI';
import { Avatar, AvatarFallback, AvatarImage } from '@/Components/UI';
import { cn } from '@/Lib/utils';
import {
  Car,
  Truck,
  Bike,
  Clock,
  CheckCircle2,
  CreditCard,
  QrCode,
  Users,
  DollarSign,
  Search,
  Sparkles,
  Zap,
  Volume2,
  VolumeX,
  RefreshCw,
  Plus,
  Radio,
  ArrowRight,
  GripVertical,
} from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';

// Vehicle type badges
const vehicleTypeConfig = {
  motor: { label: 'Motor', icon: Bike, color: 'bg-purple-500/10 text-purple-400 border-purple-500/20', iconColor: 'text-purple-400' },
  mobil: { label: 'Mobil', icon: Car, color: 'bg-blue-500/10 text-blue-400 border-blue-500/20', iconColor: 'text-blue-400' },
  suv: { label: 'SUV', icon: Car, color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20', iconColor: 'text-emerald-400' },
  truck: { label: 'Truk', icon: Truck, color: 'bg-amber-500/10 text-amber-400 border-amber-500/20', iconColor: 'text-amber-400' },
};

const statusConfig = {
  PENDING: { label: 'MENUNGGU', color: 'bg-amber-500/10 text-amber-400 border-amber-500/30', borderGlow: 'border-amber-500/60 shadow-amber-500/10' },
  WASHING: { label: 'DICUCI', color: 'bg-blue-500/10 text-blue-400 border-blue-500/30', borderGlow: 'border-blue-500/60 shadow-blue-500/10' },
  DRYING: { label: 'PENGERINGAN', color: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30', borderGlow: 'border-indigo-500/60 shadow-indigo-500/10' },
  READY: { label: 'QC & SELESAI', color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30', borderGlow: 'border-emerald-500/60 shadow-emerald-500/10' },
  PAID: { label: 'LUNAS / KELUAR', color: 'bg-teal-500/10 text-teal-400 border-teal-500/30', borderGlow: 'border-teal-500/60 shadow-teal-500/10' },
};

// Initial data
const initialBookings = [
  {
    id: 'BK-001',
    bookingCode: 'BK-001',
    vehicleType: 'mobil',
    vehicleName: 'Toyota Avanza',
    plateNumber: 'B 1234 ABC',
    serviceName: 'Cuci Lengkap + Wax',
    status: 'WASHING',
    operator: { name: 'Budi Santoso', avatar: null },
    bay: 'Bay 1',
    progress: 45,
    timeLeftSeconds: 900,
    price: 'Rp 250.000',
    isPaid: false,
    createdAt: '08:30',
  },
  {
    id: 'BK-002',
    bookingCode: 'BK-002',
    vehicleType: 'motor',
    vehicleName: 'Honda Vario 160',
    plateNumber: 'B 2345 DEF',
    serviceName: 'Cuci Biasa + Semir',
    status: 'DRYING',
    operator: { name: 'Siti Rahayu', avatar: null },
    bay: 'Bay 2',
    progress: 85,
    timeLeftSeconds: 300,
    price: 'Rp 45.000',
    isPaid: true,
    createdAt: '08:45',
  },
  {
    id: 'BK-003',
    bookingCode: 'BK-003',
    vehicleType: 'suv',
    vehicleName: 'Mitsubishi Pajero',
    plateNumber: 'B 3456 GHI',
    serviceName: 'Cuci Interior + Exterior',
    status: 'PENDING',
    operator: { name: 'Ahmad Wijaya', avatar: null },
    bay: 'Bay 3',
    progress: 0,
    timeLeftSeconds: 2100,
    price: 'Rp 180.000',
    isPaid: false,
    createdAt: '09:00',
  },
  {
    id: 'BK-004',
    bookingCode: 'BK-004',
    vehicleType: 'mobil',
    vehicleName: 'Honda Civic',
    plateNumber: 'B 4567 JKL',
    serviceName: 'Cuci Lengkap + Poles Glass',
    status: 'PENDING',
    operator: { name: 'Rizki Pratama', avatar: null },
    bay: 'Antrian',
    progress: 0,
    timeLeftSeconds: 2400,
    price: 'Rp 300.000',
    isPaid: false,
    createdAt: '09:15',
  },
  {
    id: 'BK-005',
    bookingCode: 'BK-005',
    vehicleType: 'truck',
    vehicleName: 'Isuzu Elf Pickup',
    plateNumber: 'B 6789 PQR',
    serviceName: 'Cuci Truk / Box',
    status: 'READY',
    operator: { name: 'Budi Santoso', avatar: null },
    bay: 'Bay 4',
    progress: 100,
    timeLeftSeconds: 0,
    price: 'Rp 150.000',
    isPaid: false,
    createdAt: '09:30',
  },
  {
    id: 'BK-006',
    bookingCode: 'BK-006',
    vehicleType: 'mobil',
    vehicleName: 'Hyundai Ioniq 5',
    plateNumber: 'B 8901 VWX',
    serviceName: 'Detailing Hydrophobic',
    status: 'PAID',
    operator: { name: 'Rizki Pratama', avatar: null },
    bay: 'Bay 1',
    progress: 100,
    timeLeftSeconds: 0,
    price: 'Rp 450.000',
    isPaid: true,
    createdAt: '08:00',
  },
];

// Modern Web Audio synthesizer for tactile UI sound effects
function playSound(type) {
  try {
    const ctx = new (window.AudioContext || window.webkitAudioContext)();
    const osc = ctx.createOscillator();
    const gain = ctx.createGain();
    osc.connect(gain);
    gain.connect(ctx.destination);

    if (type === 'drop') {
      osc.frequency.setValueAtTime(440, ctx.currentTime);
      osc.frequency.exponentialRampToValueAtTime(880, ctx.currentTime + 0.1);
      gain.gain.setValueAtTime(0.15, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.1);
    } else if (type === 'success') {
      osc.frequency.setValueAtTime(523.25, ctx.currentTime); // C5
      osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.08); // E5
      osc.frequency.setValueAtTime(783.99, ctx.currentTime + 0.16); // G5
      gain.gain.setValueAtTime(0.2, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.3);
    } else if (type === 'bell') {
      osc.frequency.setValueAtTime(800, ctx.currentTime);
      gain.gain.setValueAtTime(0.25, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.4);
    }
  } catch (e) {
    // AudioContext blocked by browser autoplay policy
  }
}

function formatTimer(seconds) {
  if (seconds <= 0) return '00:00';
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
}

export default function Kanban() {
  const { auth } = usePage().props;
  const [bookings, setBookings] = useState(initialBookings);
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedType, setSelectedType] = useState('all');
  const [soundEnabled, setSoundEnabled] = useState(true);
  const [draggedId, setDraggedId] = useState(null);
  const [dragOverColumn, setDragOverColumn] = useState(null);
  const [isLiveConnected, setIsLiveConnected] = useState(false);

  // Live Timer effect
  useEffect(() => {
    const timer = setInterval(() => {
      setBookings(prev =>
        prev.map(b => {
          if (['WASHING', 'DRYING'].includes(b.status) && b.timeLeftSeconds > 0) {
            const nextTime = b.timeLeftSeconds - 1;
            const progress = Math.min(100, Math.round(((2400 - nextTime) / 2400) * 100));
            return { ...b, timeLeftSeconds: nextTime, progress };
          }
          return b;
        })
      );
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  // WebSocket Live Sync (Laravel Reverb)
  useEffect(() => {
    if (window.Echo) {
      const branchId = auth?.user?.branch_id || 'main';
      const channel = window.Echo.channel(`branch.${branchId}.queue`);

      channel.subscribed(() => {
        setIsLiveConnected(true);
      });

      channel.listen('.BookingStatusUpdated', (event) => {
        if (soundEnabled) playSound('bell');
        if (event.booking) {
          setBookings(prev => {
            const idx = prev.findIndex(b => b.id === event.booking.id);
            if (idx >= 0) {
              const updated = [...prev];
              updated[idx] = { ...updated[idx], ...event.booking };
              return updated;
            }
            return [event.booking, ...prev];
          });
        }
      });

      return () => {
        window.Echo.leaveChannel(`branch.${branchId}.queue`);
      };
    }
  }, [auth, soundEnabled]);

  // Filtered Bookings
  const filteredBookings = useMemo(() => {
    return bookings.filter(b => {
      const matchesSearch =
        b.plateNumber.toLowerCase().includes(searchQuery.toLowerCase()) ||
        b.bookingCode.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (b.vehicleName && b.vehicleName.toLowerCase().includes(searchQuery.toLowerCase()));
      const matchesType = selectedType === 'all' || b.vehicleType === selectedType;
      return matchesSearch && matchesType;
    });
  }, [bookings, searchQuery, selectedType]);

  // Drag and drop handlers
  const handleDragStart = (e, bookingId) => {
    setDraggedId(bookingId);
    e.dataTransfer.setData('text/plain', bookingId);
    e.dataTransfer.effectAllowed = 'move';
  };

  const handleDragOver = (e, columnStatus) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    if (dragOverColumn !== columnStatus) {
      setDragOverColumn(columnStatus);
    }
  };

  const handleDragLeave = (e) => {
    e.preventDefault();
    setDragOverColumn(null);
  };

  const handleDrop = (e, targetStatus) => {
    e.preventDefault();
    setDragOverColumn(null);
    const bookingId = e.dataTransfer.getData('text/plain') || draggedId;
    if (!bookingId) return;

    setBookings(prev =>
      prev.map(b => {
        if (b.id === bookingId) {
          let progress = b.progress;
          let isPaid = b.isPaid;
          if (targetStatus === 'WASHING') progress = 35;
          if (targetStatus === 'DRYING') progress = 75;
          if (targetStatus === 'READY') progress = 100;
          if (targetStatus === 'PAID') {
            progress = 100;
            isPaid = true;
          }
          return { ...b, status: targetStatus, progress, isPaid };
        }
        return b;
      })
    );

    if (soundEnabled) {
      if (['READY', 'PAID'].includes(targetStatus)) {
        playSound('success');
      } else {
        playSound('drop');
      }
    }
    setDraggedId(null);
  };

  // Quick Action Buttons
  const handleAdvanceStatus = (bookingId, currentStatus) => {
    const sequence = ['PENDING', 'WASHING', 'DRYING', 'READY', 'PAID'];
    const nextIdx = sequence.indexOf(currentStatus) + 1;
    if (nextIdx < sequence.length) {
      const nextStatus = sequence[nextIdx];
      handleDrop({ preventDefault: () => {}, dataTransfer: { getData: () => bookingId } }, nextStatus);
    }
  };

  // Live Bay calculations
  const bays = [
    { name: 'Bay 1 (Mobil)', type: 'mobil', booking: bookings.find(b => b.bay === 'Bay 1' && ['WASHING', 'DRYING'].includes(b.status)) },
    { name: 'Bay 2 (Motor)', type: 'motor', booking: bookings.find(b => b.bay === 'Bay 2' && ['WASHING', 'DRYING'].includes(b.status)) },
    { name: 'Bay 3 (SUV/Mobil)', type: 'suv', booking: bookings.find(b => b.bay === 'Bay 3' && ['WASHING', 'DRYING'].includes(b.status)) },
    { name: 'Bay 4 (Truk/Besar)', type: 'truck', booking: bookings.find(b => b.bay === 'Bay 4' && ['WASHING', 'DRYING'].includes(b.status)) },
  ];

  const columns = [
    { status: 'PENDING', title: 'Antrian Masuk', color: 'border-amber-500', bg: 'bg-amber-500/5' },
    { status: 'WASHING', title: 'Sedang Dicuci', color: 'border-blue-500', bg: 'bg-blue-500/5' },
    { status: 'DRYING', title: 'Pengeringan & Detailing', color: 'border-indigo-500', bg: 'bg-indigo-500/5' },
    { status: 'READY', title: 'QC & Siap Diambil', color: 'border-emerald-500', bg: 'bg-emerald-500/5' },
    { status: 'PAID', title: 'Selesai & Lunas', color: 'border-teal-500', bg: 'bg-teal-500/5' },
  ];

  return (
    <MainLayout pageTitle="Antrian Kanban" pageDescription="Kelola alur cuci kendaraan real-time dengan Drag & Drop">
      <div className="flex flex-col h-[calc(100vh-5.5rem)] space-y-4">
        
        {/* Top Control Bar & Live Bay Monitor */}
        <div className="bg-slate-900/80 border border-slate-800/90 backdrop-blur-xl rounded-2xl p-4 shadow-xl">
          <div className="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            {/* Search & Filters */}
            <div className="flex flex-wrap items-center gap-2 flex-1">
              <div className="relative flex-1 min-w-[200px] max-w-md">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500" />
                <input
                  type="text"
                  placeholder="Cari Plat (B 1234), Kode, atau Mobil..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-full pl-9 pr-4 py-2 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-all font-mono"
                />
              </div>

              {/* Vehicle Type Filter */}
              <div className="flex items-center gap-1 bg-slate-950/60 p-1 rounded-xl border border-slate-800">
                {['all', 'mobil', 'motor', 'suv', 'truck'].map((type) => (
                  <button
                    key={type}
                    onClick={() => setSelectedType(type)}
                    className={cn(
                      'px-2.5 py-1 text-xs font-semibold rounded-lg capitalize transition-all',
                      selectedType === type
                        ? 'bg-blue-600 text-white shadow-sm'
                        : 'text-slate-400 hover:text-white'
                    )}
                  >
                    {type === 'all' ? 'Semua' : type}
                  </button>
                ))}
              </div>
            </div>

            {/* Quick Actions & Live Indicator */}
            <div className="flex items-center gap-2">
              <div className="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-950/60 border border-slate-800 text-xs">
                <span className={cn('w-2 h-2 rounded-full animate-pulse', isLiveConnected ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : 'bg-blue-500')} />
                <span className="text-slate-300 font-mono text-[11px]">{isLiveConnected ? 'Reverb Live' : 'Real-Time Sync'}</span>
              </div>

              <button
                onClick={() => setSoundEnabled(!soundEnabled)}
                className="p-2 rounded-xl bg-slate-950/60 border border-slate-800 text-slate-400 hover:text-white transition-colors"
                title={soundEnabled ? 'Matikan Suara Chime' : 'Aktifkan Suara Chime'}
              >
                {soundEnabled ? <Volume2 className="w-4 h-4 text-blue-400" /> : <VolumeX className="w-4 h-4" />}
              </button>

              <Link
                href="/kasir/bookings/walkin"
                className="px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs rounded-xl shadow-lg shadow-blue-500/20 flex items-center gap-1.5 transition-all active:scale-95"
              >
                <Plus className="w-4 h-4" />
                <span>+ Walk-in Baru</span>
              </Link>
            </div>
          </div>

          {/* Live Bay Stall Visualizer */}
          <div className="mt-4 pt-3 border-t border-slate-800/80 grid grid-cols-2 md:grid-cols-4 gap-3">
            {bays.map((bay, idx) => (
              <div
                key={idx}
                className={cn(
                  'p-2.5 rounded-xl border transition-all flex items-center justify-between',
                  bay.booking
                    ? 'bg-blue-950/20 border-blue-500/30 shadow-sm'
                    : 'bg-slate-950/40 border-slate-800/60'
                )}
              >
                <div>
                  <div className="flex items-center gap-1.5">
                    <span className={cn('w-2 h-2 rounded-full', bay.booking ? 'bg-blue-400 animate-ping' : 'bg-emerald-500')} />
                    <span className="text-xs font-bold text-slate-200">{bay.name}</span>
                  </div>
                  {bay.booking ? (
                    <p className="text-[11px] font-mono text-blue-400 mt-0.5 font-bold">
                      {bay.booking.plateNumber} • <span className="text-slate-400 font-sans">{bay.booking.operator?.name?.split(' ')[0]}</span>
                    </p>
                  ) : (
                    <p className="text-[11px] text-emerald-400/80 font-medium mt-0.5">Siap Digunakan</p>
                  )}
                </div>

                {bay.booking && (
                  <div className="text-right">
                    <span className="text-xs font-mono font-bold text-white bg-slate-900 px-2 py-0.5 rounded border border-slate-800">
                      {formatTimer(bay.booking.timeLeftSeconds)}
                    </span>
                  </div>
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Drag & Drop Kanban Columns Area */}
        <div className="flex-1 overflow-x-auto overflow-y-hidden pb-4">
          <div className="flex gap-4 h-full min-w-max">
            {columns.map(col => {
              const colBookings = filteredBookings.filter(b => b.status === col.status);
              const isOver = dragOverColumn === col.status;

              return (
                <div
                  key={col.status}
                  onDragOver={(e) => handleDragOver(e, col.status)}
                  onDragLeave={handleDragLeave}
                  onDrop={(e) => handleDrop(e, col.status)}
                  className={cn(
                    'w-80 flex flex-col rounded-2xl border bg-slate-900/60 backdrop-blur-xl transition-all duration-200 overflow-hidden',
                    col.color,
                    isOver ? 'ring-2 ring-blue-500 scale-[1.01] bg-blue-950/20' : 'border-slate-800/80'
                  )}
                >
                  {/* Column Header */}
                  <div className="p-3.5 border-b border-slate-800/80 flex items-center justify-between bg-slate-950/40">
                    <div className="flex items-center gap-2">
                      <span className="w-2.5 h-2.5 rounded-full bg-current" />
                      <h3 className="font-bold text-xs tracking-wider uppercase text-slate-200">{col.title}</h3>
                    </div>
                    <span className="text-xs font-mono font-bold px-2 py-0.5 rounded-full bg-slate-800 text-slate-300">
                      {colBookings.length}
                    </span>
                  </div>

                  {/* Column Cards Container */}
                  <div className="flex-1 p-3 overflow-y-auto space-y-3 custom-scrollbar">
                    {colBookings.length === 0 ? (
                      <div className="h-32 border-2 border-dashed border-slate-800/80 rounded-xl flex items-center justify-center text-xs text-slate-500">
                        {isOver ? 'Lepas untuk memindahkan di sini' : 'Tidak ada antrian'}
                      </div>
                    ) : (
                      colBookings.map(booking => {
                        const vConfig = vehicleTypeConfig[booking.vehicleType] || vehicleTypeConfig.mobil;
                        const VIcon = vConfig.icon;

                        return (
                          <div
                            key={booking.id}
                            draggable
                            onDragStart={(e) => handleDragStart(e, booking.id)}
                            className={cn(
                              'group bg-slate-950/80 border border-slate-800 rounded-xl p-3.5 shadow-lg hover:border-blue-500/50 hover:shadow-blue-500/5 transition-all duration-200 cursor-grab active:cursor-grabbing relative',
                              draggedId === booking.id && 'opacity-40 border-blue-400'
                            )}
                          >
                            {/* Card Top */}
                            <div className="flex items-start justify-between gap-2 mb-2">
                              <div>
                                <span className="font-mono text-[11px] font-bold text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded border border-blue-500/20">
                                  {booking.bookingCode}
                                </span>
                                <h4 className="font-mono font-bold text-base text-white mt-1 tracking-wide">
                                  {booking.plateNumber}
                                </h4>
                                <p className="text-xs text-slate-400 font-medium">{booking.vehicleName}</p>
                              </div>

                              <div className="flex flex-col items-end gap-1">
                                <Badge variant="outline" className={cn(vConfig.color, 'text-[10px] gap-1 py-0 px-2')}>
                                  <VIcon className="w-3 h-3" />
                                  {vConfig.label}
                                </Badge>
                                <span className="text-[10px] font-mono text-slate-500">{booking.bay}</span>
                              </div>
                            </div>

                            {/* Service and Price */}
                            <div className="p-2 rounded-lg bg-slate-900/80 border border-slate-800/60 mb-2.5">
                              <p className="text-xs text-slate-300 font-medium flex items-center justify-between">
                                <span>{booking.serviceName}</span>
                                <span className="font-mono font-bold text-emerald-400">{booking.price}</span>
                              </p>
                            </div>

                            {/* Progress bar if washing/drying */}
                            {['WASHING', 'DRYING'].includes(booking.status) && (
                              <div className="mb-2.5">
                                <div className="flex items-center justify-between text-[10px] font-mono text-slate-400 mb-1">
                                  <span>Progress Cuci</span>
                                  <span className="text-blue-400 font-bold">{booking.progress}%</span>
                                </div>
                                <div className="h-1.5 bg-slate-900 rounded-full overflow-hidden">
                                  <div
                                    className="h-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-300"
                                    style={{ width: `${booking.progress}%` }}
                                  />
                                </div>
                              </div>
                            )}

                            {/* Card Footer: Operator & Advance Button */}
                            <div className="flex items-center justify-between pt-2 border-t border-slate-800/60">
                              <div className="flex items-center gap-1.5 text-xs text-slate-400">
                                <Avatar className="w-5 h-5">
                                  <AvatarFallback className="text-[9px] bg-blue-600 text-white font-bold">
                                    {booking.operator?.name?.charAt(0) || 'O'}
                                  </AvatarFallback>
                                </Avatar>
                                <span className="text-[11px] truncate max-w-[90px]">{booking.operator?.name}</span>
                              </div>

                              <div className="flex items-center gap-1.5">
                                {booking.status !== 'PAID' && (
                                  <button
                                    onClick={() => handleAdvanceStatus(booking.id, booking.status)}
                                    className="px-2 py-1 bg-slate-800 hover:bg-blue-600 text-white rounded-lg text-[11px] font-semibold flex items-center gap-1 transition-all"
                                    title="Majukan ke tahap selanjutnya"
                                  >
                                    <span>Lanjut</span>
                                    <ArrowRight className="w-3 h-3" />
                                  </button>
                                )}

                                {booking.status === 'READY' && !booking.isPaid && (
                                  <Link
                                    href={`/kasir/payments/checkout/${booking.id}`}
                                    className="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-[11px] font-bold flex items-center gap-1 transition-all shadow-sm"
                                  >
                                    <CreditCard className="w-3 h-3" />
                                    <span>Bayar</span>
                                  </Link>
                                )}
                              </div>
                            </div>
                          </div>
                        );
                      })
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </MainLayout>
  );
}