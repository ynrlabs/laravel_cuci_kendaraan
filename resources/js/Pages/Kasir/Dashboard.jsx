import * as React from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Badge } from '@/Components/UI';
import { Link } from '@inertiajs/react';
import { cn } from '@/Lib/utils';
import {
  Car,
  Calendar,
  DollarSign,
  Users,
  TrendingUp,
  Clock,
  CheckCircle,
  XCircle,
  AlertCircle,
  Search,
  Bell,
  BarChart3,
  Zap,
  Circle,
  ArrowUpRight,
  ArrowDownRight,
  QrCode,
  UserPlus,
  ChevronDown,
  Building2,
} from 'lucide-react';

const stats = [
  {
    title: 'Total Booking Hari Ini',
    value: '24',
    trend: '+12%',
    trendUp: true,
    icon: Calendar,
    color: 'text-blue-600',
    bgColor: 'bg-blue-100',
  },
  {
    title: 'Kendaraan Dicuci',
    value: '18',
    trend: '+8%',
    trendUp: true,
    icon: Car,
    color: 'text-green-600',
    bgColor: 'bg-green-100',
  },
  {
    title: 'Pendapatan Hari Ini',
    value: 'Rp 4.850.000',
    trend: '+15%',
    trendUp: true,
    icon: DollarSign,
    color: 'text-emerald-600',
    bgColor: 'bg-emerald-100',
  },
  {
    title: 'Pelanggan Baru',
    value: '5',
    trend: '-3%',
    trendUp: false,
    icon: Users,
    color: 'text-purple-600',
    bgColor: 'bg-purple-100',
  },
];

const recentBookings = [
  {
    id: 'BK-001',
    customer: 'Budi Santoso',
    plate: 'B 1234 ABC',
    vehicle: 'Toyota Avanza',
    service: 'Cuci Lengkap + Wax',
    time: '09:00',
    bay: 'Bay 1',
    status: 'completed',
    price: 'Rp 250.000',
  },
  {
    id: 'BK-002',
    customer: 'Siti Rahayu',
    plate: 'B 2345 DEF',
    vehicle: 'Honda CR-V',
    service: 'Cuci Interior + Exterior',
    time: '10:30',
    bay: 'Bay 2',
    status: 'in_progress',
    price: 'Rp 180.000',
  },
  {
    id: 'BK-003',
    customer: 'Ahmad Wijaya',
    plate: 'B 3456 GHI',
    vehicle: 'Mitsubishi Xpander',
    service: 'Cuci Biasa',
    time: '11:00',
    bay: 'Bay 3',
    status: 'pending',
    price: 'Rp 100.000',
  },
  {
    id: 'BK-004',
    customer: 'Dewi Lestari',
    plate: 'B 4567 JKL',
    vehicle: 'Suzuki Ertiga',
    service: 'Cuci Lengkap + Poles',
    time: '13:00',
    bay: 'Bay 1',
    status: 'pending',
    price: 'Rp 300.000',
  },
  {
    id: 'BK-005',
    customer: 'Rizki Pratama',
    plate: 'B 5678 MNO',
    vehicle: 'Daihatsu Xenia',
    service: 'Cuci Interior',
    time: '14:30',
    bay: 'Bay 2',
    status: 'cancelled',
    price: 'Rp 120.000',
  },
];

const statusConfig = {
  completed: { label: 'Selesai', color: 'bg-green-100 text-green-700', borderColor: 'border-green-200', icon: CheckCircle, dotColor: 'bg-green-500' },
  in_progress: { label: 'Proses', color: 'bg-blue-100 text-blue-700', borderColor: 'border-blue-200', icon: Clock, dotColor: 'bg-blue-500' },
  pending: { label: 'Menunggu', color: 'bg-yellow-100 text-yellow-700', borderColor: 'border-yellow-200', icon: AlertCircle, dotColor: 'bg-yellow-500' },
  cancelled: { label: 'Batal', color: 'bg-red-100 text-red-700', borderColor: 'border-red-200', icon: XCircle, dotColor: 'bg-red-500' },
};

const bayUtilization = [
  { bay: 'Bay 1', status: 'active', service: 'Cuci Lengkap + Wax', progress: 65, vehicle: 'Toyota Avanza', plate: 'B 1234 ABC', timeLeft: '25 min' },
  { bay: 'Bay 2', status: 'active', service: 'Interior + Exterior', progress: 40, vehicle: 'Honda CR-V', plate: 'B 2345 DEF', timeLeft: '35 min' },
  { bay: 'Bay 3', status: 'waiting', service: 'Cuci Biasa', progress: 0, vehicle: 'Mitsubishi Xpander', plate: 'B 3456 GHI', timeLeft: 'Menunggu' },
  { bay: 'Bay 4', status: 'free', service: '-', progress: 0, vehicle: '-', plate: '-', timeLeft: 'Tersedia' },
];

const liveStatusFeed = [
  { time: '09:15', event: 'Booking BK-001 memulai di Bay 1', type: 'start' },
  { time: '09:45', event: 'Walk-in: Honda Jazz (B 9999 XYZ)', type: 'walkin' },
  { time: '10:30', event: 'Booking BK-002 memulai di Bay 2', type: 'start' },
  { time: '10:55', event: 'Bay 3 selesai - Cuci Biasa', type: 'complete' },
  { time: '11:10', event: 'Booking BK-003 ditugaskan ke Bay 3', type: 'assign' },
];

function StatCard({ stat }) {
  const TrendIcon = stat.trendUp ? ArrowUpRight : ArrowDownRight;
  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle className="text-sm font-medium text-muted-foreground">
          {stat.title}
        </CardTitle>
        <stat.icon className={`h-5 w-5 ${stat.color} ${stat.bgColor} p-1 rounded-lg`} />
      </CardHeader>
      <CardContent>
        <div className="text-2xl font-bold">{stat.value}</div>
        <div className="flex items-center gap-1 mt-1">
          <TrendIcon className={cn('h-3 w-3', stat.trendUp ? 'text-green-600' : 'text-red-600')} />
          <span className={cn('text-xs font-medium', stat.trendUp ? 'text-green-600' : 'text-red-600')}>
            {stat.trend}
          </span>
          <span className="text-xs text-muted-foreground">vs kemarin</span>
        </div>
      </CardContent>
    </Card>
  );
}

function StatusBadge({ status }) {
  const config = statusConfig[status];
  const Icon = config.icon;
  return (
    <Badge variant="outline" className={cn(config.color, config.borderColor, 'gap-1.5')}>
      <span className={cn('h-2 w-2 rounded-full', config.dotColor)} />
      <Icon className="h-3 w-3" />
      <span className="font-medium">{config.label}</span>
    </Badge>
  );
}

function BayCard({ bay }) {
  const statusColors = {
    active: 'bg-blue-50 border-blue-200',
    waiting: 'bg-yellow-50 border-yellow-200',
    free: 'bg-green-50 border-green-200',
  };
  const statusLabels = {
    active: 'Aktif',
    waiting: 'Menunggu',
    free: 'Tersedia',
  };
  const statusDots = {
    active: 'bg-blue-500',
    waiting: 'bg-yellow-500',
    free: 'bg-green-500',
  };

  return (
    <div className={cn('p-4 rounded-lg border', statusColors[bay.status])}>
      <div className="flex items-center justify-between mb-3">
        <span className="font-montserrat font-bold text-lg text-foreground">{bay.bay}</span>
        <Badge variant="outline" className={cn('gap-1', statusColors[bay.status].replace('50', '100').replace('200', '700'))}>
          <span className={cn('h-2 w-2 rounded-full', statusDots[bay.status])} />
          {statusLabels[bay.status]}
        </Badge>
      </div>
      
      {bay.status !== 'free' && (
        <div className="space-y-2">
          <div className="flex items-center justify-between text-sm">
            <span className="text-muted-foreground">Layanan</span>
            <span className="font-medium">{bay.service}</span>
          </div>
          <div className="flex items-center justify-between text-sm">
            <span className="text-muted-foreground">Kendaraan</span>
            <span className="font-medium font-jetbrains-mono">{bay.vehicle}</span>
          </div>
          <div className="flex items-center justify-between text-sm">
            <span className="text-muted-foreground">Plat</span>
            <span className="font-medium font-jetbrains-mono">{bay.plate}</span>
          </div>
          <div className="h-2 bg-muted rounded-full overflow-hidden mt-2">
            <div
              className="h-full bg-primary rounded-full transition-all"
              style={{ width: `${bay.progress}%` }}
            />
          </div>
          <div className="flex items-center justify-between text-xs text-muted-foreground">
            <span>Progress: {bay.progress}%</span>
            <span>Sisa: {bay.timeLeft}</span>
          </div>
        </div>
      )}
      
      {bay.status === 'free' && (
        <div className="text-center text-muted-foreground py-4">
          <Car className="h-8 w-8 mx-auto mb-2 opacity-50" />
          <p>Tersedia untuk booking</p>
        </div>
      )}
    </div>
  );
}

function LiveStatusItem({ item }) {
  const typeStyles = {
    start: { icon: Circle, color: 'text-blue-500', bg: 'bg-blue-50' },
    walkin: { icon: UserPlus, color: 'text-purple-500', bg: 'bg-purple-50' },
    complete: { icon: CheckCircle, color: 'text-green-500', bg: 'bg-green-50' },
    assign: { icon: Zap, color: 'text-yellow-500', bg: 'bg-yellow-50' },
  };
  const style = typeStyles[item.type] || typeStyles.start;
  const Icon = style.icon;

  return (
    <div className="flex items-start gap-3 p-3 rounded-lg hover:bg-muted/50 transition-colors">
      <div className={cn('p-2 rounded-full', style.bg)}>
        <Icon className={cn('h-4 w-4', style.color)} />
      </div>
      <div className="flex-1 min-w-0">
        <p className="text-sm font-medium text-foreground">{item.event}</p>
        <p className="text-xs text-muted-foreground">{item.time}</p>
      </div>
    </div>
  );
}

export default function Dashboard() {
  return (
    <MainLayout>
      <div className="space-y-6">
        {/* Branch Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2">
          <div className="flex items-center gap-3">
            <Building2 className="h-6 w-6 text-primary" />
            <div>
              <h1 className="font-montserrat text-xl font-bold text-foreground">Cabang Utama - Sudirman</h1>
              <p className="text-sm text-muted-foreground">Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan</p>
            </div>
          </div>
          <div className="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
              <input
                type="text"
                placeholder="Cari booking, pelanggan, plat nomor..."
                className="w-full pl-10 pr-4 py-2 border border-input bg-background rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
              />
            </div>
            <div className="flex items-center gap-2">
              <Button variant="ghost" size="icon" className="relative">
                <Bell className="h-5 w-5" />
                <span className="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500" />
              </Button>
              <Badge variant="outline" className="bg-green-50 border-green-200 text-green-700 gap-1.5">
                <span className="h-2 w-2 rounded-full bg-green-500" />
                <Building2 className="h-3 w-3" />
                <span className="font-medium">Branch Status: Aktif</span>
              </Badge>
            </div>
          </div>
        </div>

        {/* Stat Cards */}
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          {stats.map((stat) => (
            <StatCard key={stat.title} stat={stat} />
          ))}
        </div>

        {/* Main Grid: Recent Bookings (left) + Bay Utilization + Live Status (right) */}
        <div className="grid gap-4 lg:grid-cols-12">
          {/* Recent Bookings Table - 8 cols */}
          <Card className="lg:col-span-8">
            <CardHeader className="flex flex-row items-center justify-between">
              <CardTitle className="text-lg">Booking Terbaru</CardTitle>
              <Link href="/kasir/bookings">
                <Button variant="ghost" size="sm">Lihat Semua</Button>
              </Link>
            </CardHeader>
            <CardContent className="p-0">
              <div className="overflow-x-auto">
                <table className="w-full">
                  <thead>
                    <tr className="border-b bg-muted/50">
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">ID</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Pelanggan</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Plat Nomor</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Kendaraan</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Layanan</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Bay</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Waktu</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Status</th>
                      <th className="px-4 py-3 text-left text-sm font-medium text-muted-foreground">Harga</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y">
                    {recentBookings.map((booking) => (
                      <tr key={booking.id} className="hover:bg-muted/50">
                        <td className="px-4 py-3 text-sm font-mono text-muted-foreground">{booking.id}</td>
                        <td className="px-4 py-3 text-sm font-medium">{booking.customer}</td>
                        <td className="px-4 py-3 text-sm font-jetbrains-mono text-foreground">{booking.plate}</td>
                        <td className="px-4 py-3 text-sm text-muted-foreground">{booking.vehicle}</td>
                        <td className="px-4 py-3 text-sm text-muted-foreground">{booking.service}</td>
                        <td className="px-4 py-3 text-sm font-medium text-primary">{booking.bay}</td>
                        <td className="px-4 py-3 text-sm text-muted-foreground">
                          <Clock className="h-4 w-4 inline mr-1" /> {booking.time}
                        </td>
                        <td className="px-4 py-3">
                          <StatusBadge status={booking.status} />
                        </td>
                        <td className="px-4 py-3 text-sm font-medium">{booking.price}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </CardContent>
          </Card>

          {/* Right Panel: Bay Utilization + Live Status - 4 cols */}
          <div className="lg:col-span-4 space-y-4">
            {/* Bay Utilization */}
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle className="text-lg flex items-center gap-2">
                  <BarChart3 className="h-5 w-5 text-primary" />
                  Utilisasi Bay
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-3 p-0 pt-4">
                {bayUtilization.map((bay) => (
                  <BayCard key={bay.bay} bay={bay} />
                ))}
              </CardContent>
            </Card>

            {/* Live Status Feed */}
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle className="text-lg flex items-center gap-2">
                  <Zap className="h-5 w-5 text-yellow-500" />
                  Status Live
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-1 p-0 pt-4">
                {liveStatusFeed.map((item, index) => (
                  <LiveStatusItem key={index} item={item} />
                ))}
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Action Buttons: Scan QR + New Walk-in */}
        <div className="flex flex-col sm:flex-row gap-3 justify-end">
          <Link href="/kasir/bookings/scan">
            <Button variant="outline" className="gap-2 bg-yellow-50 border-yellow-200 text-yellow-700 hover:bg-yellow-100 hover:border-yellow-300" size="lg">
              <QrCode className="h-5 w-5" />
              <span>Scan QR</span>
            </Button>
          </Link>
          <Link href="/kasir/bookings/walkin">
            <Button className="gap-2 bg-slate-900 text-white hover:bg-slate-800" size="lg">
              <UserPlus className="h-5 w-5" />
              <span>Walk-in Baru</span>
            </Button>
          </Link>
        </div>
      </div>
    </MainLayout>
  );
}