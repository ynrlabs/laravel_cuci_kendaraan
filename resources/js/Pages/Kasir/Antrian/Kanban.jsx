import * as React from 'react';
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
  Circle,
  Clock,
  Loader2,
  CheckCircle,
  CreditCard,
  QrCode,
  Car as CarIcon,
  Truck as TruckIcon,
  Bike as BikeIcon,
  Users,
  DollarSign,
  CheckCircle2,
  Minus,
} from 'lucide-react';
import { useState, useEffect, useCallback } from 'react';

// Vehicle type badges
const vehicleTypeConfig = {
  motor: { label: 'Motor', icon: BikeIcon, color: 'bg-purple-100 text-purple-700 border-purple-200', iconColor: 'text-purple-600' },
  mobil: { label: 'Mobil', icon: CarIcon, color: 'bg-blue-100 text-blue-700 border-blue-200', iconColor: 'text-blue-600' },
  suv: { label: 'SUV', icon: CarIcon, color: 'bg-green-100 text-green-700 border-green-200', iconColor: 'text-green-600' },
  truck: { label: 'Truk', icon: TruckIcon, color: 'bg-orange-100 text-orange-700 border-orange-200', iconColor: 'text-orange-600' },
};

const statusConfig = {
  PENDING: { label: 'PENDING', color: 'bg-yellow-100 text-yellow-700 border-yellow-200', borderColor: 'border-yellow-400' },
  WASHING: { label: 'WASHING', color: 'bg-blue-100 text-blue-700 border-blue-200', borderColor: 'border-blue-500' },
  DRYING: { label: 'DRYING', color: 'bg-orange-100 text-orange-700 border-orange-200', borderColor: 'border-orange-500' },
  READY: { label: 'READY', color: 'bg-green-100 text-green-700 border-green-200', borderColor: 'border-green-500' },
  PAID: { label: 'PAID', color: 'bg-emerald-100 text-emerald-700 border-emerald-200', borderColor: 'border-emerald-500' },
};

// Mock data
const mockBookings = [
  {
    id: 'BK-001',
    bookingCode: 'BK-001',
    vehicleType: 'mobil',
    plateNumber: 'B 1234 ABC',
    serviceName: 'Cuci Lengkap + Wax',
    status: 'PENDING',
    waitTime: '15 menit',
    operator: { name: 'Budi', avatar: null },
    bay: 'Bay 1',
    progress: 0,
    timeLeft: '15:00',
    createdAt: '08:30',
  },
  {
    id: 'BK-002',
    bookingCode: 'BK-002',
    vehicleType: 'motor',
    plateNumber: 'B 2345 DEF',
    serviceName: 'Cuci Biasa',
    status: 'PENDING',
    waitTime: '25 menit',
    operator: { name: 'Siti', avatar: null },
    bay: 'Bay 2',
    progress: 0,
    timeLeft: '25:00',
    createdAt: '08:45',
  },
  {
    id: 'BK-003',
    bookingCode: 'BK-003',
    vehicleType: 'suv',
    plateNumber: 'B 3456 GHI',
    serviceName: 'Cuci Interior + Exterior',
    status: 'PENDING',
    waitTime: '35 menit',
    operator: { name: 'Ahmad', avatar: null },
    bay: 'Bay 3',
    progress: 0,
    timeLeft: '35:00',
    createdAt: '09:00',
  },
  {
    id: 'BK-004',
    bookingCode: 'BK-004',
    vehicleType: 'mobil',
    plateNumber: 'B 4567 JKL',
    serviceName: 'Cuci Lengkap + Poles',
    status: 'WASHING',
    waitTime: '0 menit',
    operator: { name: 'Rizki', avatar: null },
    bay: 'Bay 1',
    progress: 65,
    timeLeft: '12:30',
    createdAt: '09:30',
  },
  {
    id: 'BK-005',
    bookingCode: 'BK-005',
    vehicleType: 'motor',
    plateNumber: 'B 5678 MNO',
    serviceName: 'Cuci Biasa',
    status: 'DRYING',
    waitTime: '0 menit',
    operator: { name: 'Dewi', avatar: null },
    bay: 'Bay 2',
    progress: 85,
    timeLeft: '05:15',
    createdAt: '10:00',
  },
  {
    id: 'BK-006',
    bookingCode: 'BK-006',
    vehicleType: 'truck',
    plateNumber: 'B 6789 PQR',
    serviceName: 'Cuci Besar',
    status: 'WASHING',
    waitTime: '0 menit',
    operator: { name: 'Budi', avatar: null },
    bay: 'Bay 4',
    progress: 30,
    timeLeft: '25:00',
    createdAt: '10:15',
  },
  {
    id: 'BK-007',
    bookingCode: 'BK-007',
    vehicleType: 'mobil',
    plateNumber: 'B 7890 STU',
    serviceName: 'Cuci Lengkap',
    status: 'READY',
    waitTime: 'Selesai',
    operator: { name: 'Siti', avatar: null },
    bay: 'Bay 3',
    progress: 100,
    timeLeft: '00:00',
    createdAt: '10:30',
    isPaid: false,
  },
  {
    id: 'BK-008',
    bookingCode: 'BK-008',
    vehicleType: 'suv',
    plateNumber: 'B 8901 VWX',
    serviceName: 'Cuci Interior + Exterior',
    status: 'PAID',
    waitTime: 'Lunas',
    operator: { name: 'Ahmad', avatar: null },
    bay: 'Bay 1',
    progress: 100,
    timeLeft: '00:00',
    createdAt: '11:00',
    isPaid: true,
  },
];

function VehicleBadge({ type }) {
  const config = vehicleTypeConfig[type] || vehicleTypeConfig.mobil;
  const Icon = config.icon;
  return (
    <Badge variant="outline" className={cn(config.color, 'gap-1.5 font-medium')}>
      <Icon className={cn('h-3 w-3', config.iconColor)} />
      {config.label}
    </Badge>
  );
}

function StatusBadge({ status }) {
  const config = statusConfig[status] || statusConfig.PENDING;
  return (
    <Badge variant="outline" className={cn(config.color, 'font-montserrat font-semibold text-xs')}>
      {config.label}
    </Badge>
  );
}

function OperatorAvatar({ operator }) {
  return (
    <Avatar className="h-8 w-8">
      <AvatarImage src={operator.avatar} alt={operator.name} />
      <AvatarFallback className="text-xs font-medium bg-primary/10 text-primary">
        {operator.name.charAt(0).toUpperCase()}
      </AvatarFallback>
    </Avatar>
  );
}

function formatTimeLeft(seconds) {
  if (seconds <= 0) return '00:00';
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
}

function parseTimeLeft(timeStr) {
  const parts = timeStr.split(':').map(Number);
  if (parts.length === 2) {
    return parts[0] * 60 + parts[1];
  }
  return 0;
}

function PendingCard({ booking, onMoveToProgress }) {
  const config = vehicleTypeConfig[booking.vehicleType] || vehicleTypeConfig.mobil;
  
  return (
    <Card 
      className={cn(
        'border-l-4 transition-all hover:shadow-md',
        `border-${config.color.split(' ')[0].replace('bg-', '').replace('-100', '-400', '-400')}`
      )}
    >
      <CardContent className="p-4">
        <div className="flex items-start justify-between gap-3 mb-3">
          <VehicleBadge type={booking.vehicleType} />
          <StatusBadge status={booking.status} />
        </div>
        
        <div className="mb-3">
          <p className="font-montserrat font-bold text-lg text-foreground">
            {booking.bookingCode}
          </p>
          <p className="font-jetbrains-mono text-sm text-foreground font-medium">
            {booking.plateNumber}
          </p>
        </div>
        
        <div className="mb-3">
          <p className="text-sm font-medium text-foreground">{booking.serviceName}</p>
          <p className="text-xs text-muted-foreground flex items-center gap-1">
            <Clock className="h-3 w-3" />
            Est. tunggu: {booking.waitTime}
          </p>
        </div>
        
        <div className="flex items-center justify-between pt-3 border-t">
          <div className="flex items-center gap-2">
            <OperatorAvatar operator={booking.operator} />
            <span className="text-xs text-muted-foreground">{booking.operator.name}</span>
          </div>
          <Badge variant="outline" className="text-xs bg-primary/10 text-primary border-primary/20">
            {booking.bay}
          </Badge>
        </div>
        
        <Button 
          className="w-full mt-3 gap-2 bg-primary hover:bg-primary/90"
          onClick={() => onMoveToProgress(booking.id)}
          size="sm"
        >
          <Circle className="h-4 w-4" />
          Mulai Cuci
        </Button>
      </CardContent>
    </Card>
  );
}

function InProgressCard({ booking, onComplete, onPayNow }) {
  const config = statusConfig[booking.status] || statusConfig.WASHING;
  const isWashing = booking.status === 'WASHING';
  const isDrying = booking.status === 'DRYING';
  const [timeLeft, setTimeLeft] = useState(parseTimeLeft(booking.timeLeft));
  
  useEffect(() => {
    if (timeLeft > 0) {
      const timer = setInterval(() => {
        setTimeLeft(prev => Math.max(0, prev - 1));
      }, 1000);
      return () => clearInterval(timer);
    }
  }, [timeLeft]);
  
  const borderColor = isWashing ? 'border-blue-500' : 'border-orange-500';
  const bgColor = isWashing ? 'bg-blue-50' : 'bg-orange-50';
  const progressColor = isWashing ? 'bg-blue-500' : 'bg-orange-500';
  const statusIcon = isWashing ? (
    <Loader2 className="h-4 w-4 animate-spin text-blue-500" />
  ) : (
    <Circle className="h-4 w-4 text-orange-500" />
  );
  
  return (
    <Card className={cn('border-l-4', borderColor, bgColor, 'relative overflow-hidden')}>
      <div className={`absolute top-0 left-0 right-0 h-1 ${progressColor}`} 
           style={{ width: `${booking.progress}%` }} />
      
      <CardContent className="p-4 relative">
        <div className="flex items-start justify-between gap-3 mb-3">
          <VehicleBadge type={booking.vehicleType} />
          <StatusBadge status={booking.status} />
        </div>
        
        <div className="mb-3">
          <p className="font-montserrat font-bold text-lg text-foreground">
            {booking.bookingCode}
          </p>
          <p className="font-jetbrains-mono text-sm text-foreground font-medium">
            {booking.plateNumber}
          </p>
        </div>
        
        <div className="mb-3">
          <p className="text-sm font-medium text-foreground">{booking.serviceName}</p>
          <p className="text-xs text-muted-foreground flex items-center gap-1">
            {statusIcon}
            Bay: {booking.bay}
          </p>
        </div>
        
        {/* Progress Bar */}
        <div className="mb-3">
          <div className="flex items-center justify-between text-xs mb-1">
            <span className="text-muted-foreground">Progress</span>
            <span className="font-medium text-foreground">{booking.progress}%</span>
          </div>
          <div className="h-2 bg-muted rounded-full overflow-hidden">
            <div 
              className={cn('h-full rounded-full transition-all duration-300', progressColor)}
              style={{ width: `${booking.progress}%` }}
            />
          </div>
        </div>
        
        {/* Countdown Timer */}
        <div className="mb-3 p-3 bg-background/50 rounded-lg border">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <Clock className={cn('h-4 w-4', isWashing ? 'text-blue-500' : 'text-orange-500')} />
              <span className="text-xs font-medium text-muted-foreground">
                {isWashing ? 'Sisa Washing' : 'Sisa Drying'}
              </span>
            </div>
            <span className="font-jetbrains-mono text-lg font-bold tabular-nums 
              {timeLeft > 60 ? 'text-foreground' : 'text-red-500'}">
              {formatTimeLeft(timeLeft)}
            </span>
          </div>
        </div>
        
        <div className="flex items-center justify-between pt-3 border-t">
          <div className="flex items-center gap-2">
            <OperatorAvatar operator={booking.operator} />
            <span className="text-xs text-muted-foreground">{booking.operator.name}</span>
          </div>
          
          <div className="flex gap-2">
            {booking.progress >= 100 && (
              <>
                <Button 
                  variant="outline" 
                  size="sm" 
                  className="text-green-700 border-green-300 bg-green-50 hover:bg-green-100"
                  onClick={() => onComplete(booking.id)}
                >
                  <CheckCircle2 className="h-3 w-3 mr-1" />
                  Selesai
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  className="text-emerald-700 border-emerald-300 bg-emerald-50 hover:bg-emerald-100"
                  onClick={() => onPayNow(booking.id)}
                >
                  <CreditCard className="h-3 w-3 mr-1" />
                  Bayar
                </Button>
              </>
            )}
          </div>
        </div>
      </CardContent>
    </Card>
  );
}

function CompletedCard({ booking, onPayNow }) {
  const config = statusConfig[booking.status] || statusConfig.READY;
  const isPaid = booking.status === 'PAID';
  
  return (
    <Card className={cn('border-l-4', config.borderColor)}>
      <CardContent className="p-4">
        <div className="flex items-start justify-between gap-3 mb-3">
          <VehicleBadge type={booking.vehicleType} />
          <StatusBadge status={booking.status} />
        </div>
        
        <div className="mb-3">
          <p className="font-montserrat font-bold text-lg text-foreground">
            {booking.bookingCode}
          </p>
          <p className="font-jetbrains-mono text-sm text-foreground font-medium">
            {booking.plateNumber}
          </p>
        </div>
        
        <div className="mb-3">
          <p className="text-sm font-medium text-foreground">{booking.serviceName}</p>
          <p className="text-xs text-muted-foreground flex items-center gap-1">
            <CheckCircle2 className="h-3 w-3 text-green-500" />
            Selesai pada {booking.createdAt}
          </p>
        </div>
        
        <div className="flex items-center justify-between pt-3 border-t">
          <div className="flex items-center gap-2">
            <OperatorAvatar operator={booking.operator} />
            <span className="text-xs text-muted-foreground">{booking.operator.name}</span>
          </div>
          
          {!isPaid && (
            <Button 
              className="gap-2 bg-emerald-600 hover:bg-emerald-700 text-emerald-50"
              onClick={() => onPayNow(booking.id)}
              size="sm"
            >
              <CreditCard className="h-4 w-4" />
              <span className="font-montserrat font-semibold">BAYAR SEKARANG</span>
            </Button>
          )}
          
          {isPaid && (
            <Badge variant="outline" className="bg-emerald-50 border-emerald-200 text-emerald-700">
              <CheckCircle2 className="h-3 w-3" />
              LUNAS
            </Badge>
          )}
        </div>
      </CardContent>
    </Card>
  );
}

function KanbanColumn({ title, status, bookings, children, count, color }) {
  const statusColor = statusConfig[status]?.color || 'bg-gray-100 text-gray-700';
  
  return (
    <div className="flex flex-col h-full min-w-[320px] max-w-[340px] flex-shrink-0">
      <Card className="h-full flex flex-col border-l-4" style={{ borderLeftColor: color }}>
        <CardHeader className="pb-3 flex-shrink-0">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2">
              <div className={cn('h-2 w-2 rounded-full', statusColor.replace('100', '500'))} />
              <CardTitle className="text-base font-montserrat font-bold">{title}</CardTitle>
            </div>
            <Badge variant="outline" className={cn('font-montserrat font-semibold', statusColor)}>
              {count}
            </Badge>
          </div>
        </CardHeader>
        <CardContent className="flex-1 p-0 pt-4 overflow-y-auto">
          <div className="p-3 space-y-3">
            {children}
            {bookings.length === 0 && (
              <div className="text-center py-8 text-muted-foreground">
                <div className="h-12 w-12 mx-auto mb-2 rounded-full bg-muted/50 flex items-center justify-center">
                  <Minus className="h-6 w-6 text-muted-foreground/50" />
                </div>
                <p className="text-sm">Tidak ada antrian</p>
              </div>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

function StatsFooter({ stats }) {
  return (
    <div className="fixed bottom-0 left-0 right-0 lg:left-64 bg-background border-t z-40">
      <div className="grid grid-cols-4 gap-4 p-4 lg:p-6 max-w-full mx-auto">
        <StatItem 
          icon={Car} 
          label="Total Antrian" 
          value={stats.totalQueue} 
          color="text-blue-600 bg-blue-100" 
          trend="+2 dari jam lalu"
        />
        <StatItem 
          icon={Loader2} 
          label="Sedang Proses" 
          value={stats.inProgress} 
          color="text-orange-600 bg-orange-100" 
          trend="3 washing, 1 drying"
        />
        <StatItem 
          icon={CheckCircle2} 
          label="Selesai Hari Ini" 
          value={stats.completedToday} 
          color="text-green-600 bg-green-100" 
          trend="+5 dari kemarin"
        />
        <StatItem 
          icon={DollarSign} 
          label="Pendapatan" 
          value={stats.revenue} 
          color="text-emerald-600 bg-emerald-100" 
          trend="+12% vs kemarin"
        />
      </div>
    </div>
  );
}

function StatItem({ icon: Icon, label, value, color, trend }) {
  return (
    <div className="bg-card rounded-xl border p-4">
      <div className="flex items-center justify-between mb-2">
        <div className={cn('p-2 rounded-lg', color)}>
          <Icon className="h-5 w-5" />
        </div>
        <span className="text-xs text-muted-foreground">{trend}</span>
      </div>
      <p className="font-montserrat text-2xl font-bold text-foreground">{value}</p>
      <p className="text-xs text-muted-foreground">{label}</p>
    </div>
  );
}

function ScanQRFab({ onClick }) {
  return (
    <button
      onClick={onClick}
      className="fixed bottom-24 right-6 z-50 h-14 w-14 rounded-full bg-yellow-500 hover:bg-yellow-600 text-yellow-900 shadow-lg transition-all hover:shadow-xl flex items-center justify-center"
      aria-label="Scan QR Code"
    >
      <QrCode className="h-7 w-7" />
    </button>
  );
}

// WebSocket hook for real-time updates
function useWebSocket(onBookingUpdate) {
  useEffect(() => {
    // In production, this would connect to Laravel Reverb
    // const echo = new Echo({
    //   broadcaster: 'reverb',
    //   key: import.meta.env.VITE_REVERB_APP_KEY,
    //   wsHost: import.meta.env.VITE_REVERB_HOST,
    //   wsPort: import.meta.env.VITE_REVERB_PORT,
    //   wssPort: import.meta.env.VITE_REVERB_PORT,
    //   forceTLS: false,
    //   disableStats: true,
    // });
    //
    // echo.channel('antrian-kanban')
    //   .listen('BookingStatusUpdated', (e) => {
    //     onBookingUpdate(e.booking);
    //   });
    
    // Mock real-time updates for demo
    const interval = setInterval(() => {
      // Simulate random updates
      if (Math.random() > 0.7) {
        // In a real app, this would come from WebSocket
        console.log('[WebSocket] Mock update received');
      }
    }, 30000);
    
    return () => clearInterval(interval);
  }, [onBookingUpdate]);
}

export default function Kanban() {
  const [bookings, setBookings] = useState(mockBookings);
  const [showQRScanner, setShowQRScanner] = useState(false);
  
  const pendingBookings = bookings.filter(b => b.status === 'PENDING');
  const inProgressBookings = bookings.filter(b => ['WASHING', 'DRYING'].includes(b.status));
  const completedBookings = bookings.filter(b => ['READY', 'PAID'].includes(b.status));
  
  const stats = {
    totalQueue: pendingBookings.length + inProgressBookings.length,
    inProgress: inProgressBookings.length,
    completedToday: completedBookings.length,
    revenue: 'Rp 12.500.000',
  };
  
  const handleMoveToProgress = useCallback((bookingId) => {
    setBookings(prev => prev.map(b => 
      b.id === bookingId 
        ? { ...b, status: 'WASHING', progress: 0, timeLeft: '20:00' }
        : b
    ));
  }, []);
  
  const handleComplete = useCallback((bookingId) => {
    setBookings(prev => prev.map(b => 
      b.id === bookingId 
        ? { ...b, status: 'READY', progress: 100, timeLeft: '00:00', isPaid: false }
        : b
    ));
  }, []);
  
  const handlePayNow = useCallback((bookingId) => {
    setBookings(prev => prev.map(b => 
      b.id === bookingId 
        ? { ...b, status: 'PAID', isPaid: true }
        : b
    ));
  }, []);
  
  // Setup WebSocket for real-time updates
  useWebSocket(useCallback((updatedBooking) => {
    setBookings(prev => prev.map(b => b.id === updatedBooking.id ? updatedBooking : b));
  }, []));
  
  return (
    <MainLayout pageTitle="Antrian Kanban" pageDescription="Kelola antrian cuci kendaraan mode Kanban">
      <div className="h-[calc(100vh-8rem)] lg:h-[calc(100vh-6rem)] flex flex-col overflow-hidden">
        {/* Kanban Board */}
        <div className="flex-1 overflow-x-auto overflow-y-hidden pb-20 lg:pb-24">
          <div className="flex gap-4 min-w-max h-full px-4 lg:px-6 py-4">
            {/* Pending Column */}
            <KanbanColumn
              title="Pending"
              status="PENDING"
              bookings={pendingBookings}
              count={pendingBookings.length}
              color="#f59e0b"
            >
              {pendingBookings.map(booking => (
                <PendingCard
                  key={booking.id}
                  booking={booking}
                  onMoveToProgress={handleMoveToProgress}
                />
              ))}
            </KanbanColumn>
            
            {/* In Progress Column */}
            <KanbanColumn
              title="In Progress"
              status="WASHING"
              bookings={inProgressBookings}
              count={inProgressBookings.length}
              color="#3b82f6"
            >
              {inProgressBookings.map(booking => (
                <InProgressCard
                  key={booking.id}
                  booking={booking}
                  onComplete={handleComplete}
                  onPayNow={handlePayNow}
                />
              ))}
            </KanbanColumn>
            
            {/* Completed Column */}
            <KanbanColumn
              title="Completed"
              status="READY"
              bookings={completedBookings}
              count={completedBookings.length}
              color="#10b981"
            >
              {completedBookings.map(booking => (
                <CompletedCard
                  key={booking.id}
                  booking={booking}
                  onPayNow={handlePayNow}
                />
              ))}
            </KanbanColumn>
          </div>
        </div>
        
        {/* FAB Scan QR */}
        <ScanQRFab onClick={() => setShowQRScanner(true)} />
        
        {/* Stats Footer */}
        <StatsFooter stats={stats} />
        
        {/* QR Scanner Modal */}
        {showQRScanner && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 lg:hidden">
            <Card className="w-full max-w-md mx-4">
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  <QrCode className="h-5 w-5 text-yellow-500" />
                  Scan QR Code
                  <button onClick={() => setShowQRScanner(false)} className="text-muted-foreground hover:text-foreground">
                    ✕
                  </button>
                </CardTitle>
              </CardHeader>
              <CardContent className="py-6 text-center">
                <div className="w-64 h-64 mx-auto mb-4 bg-muted rounded-lg flex items-center justify-center">
                  <QrCode className="h-32 w-32 text-muted-foreground/50" />
                </div>
                <p className="text-sm text-muted-foreground">
                  Arahkan kamera ke QR code pada tiket booking pelanggan
                </p>
                <Button className="w-full mt-4" onClick={() => setShowQRScanner(false)}>
                  Tutup
                </Button>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </MainLayout>
  );
}