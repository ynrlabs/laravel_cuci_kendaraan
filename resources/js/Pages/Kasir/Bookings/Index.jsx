import * as React from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Input } from '@/Components/UI';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/UI';
import { Badge } from '@/Components/UI';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/UI';
import { Plus, Search, Filter, Car, Calendar, CheckCircle, Clock, XCircle, AlertCircle, MoreHorizontal } from 'lucide-react';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/Components/UI/DropdownMenu';

const bookings = [
  { id: 'BK-001', customer: 'Budi Santoso', phone: '0812-3456-7890', vehicle: 'Toyota Avanza', plate: 'B 1234 ABC', service: 'Cuci Lengkap + Wax', time: '09:00', status: 'completed', price: 'Rp 250.000' },
  { id: 'BK-002', customer: 'Siti Rahayu', phone: '0813-4567-8901', vehicle: 'Honda CR-V', plate: 'B 2345 DEF', service: 'Cuci Interior + Exterior', time: '10:30', status: 'in_progress', price: 'Rp 180.000' },
  { id: 'BK-003', customer: 'Ahmad Wijaya', phone: '0814-5678-9012', vehicle: 'Mitsubishi Xpander', plate: 'B 3456 GHI', service: 'Cuci Biasa', time: '11:00', status: 'pending', price: 'Rp 100.000' },
  { id: 'BK-004', customer: 'Dewi Lestari', phone: '0815-6789-0123', vehicle: 'Suzuki Ertiga', plate: 'B 4567 JKL', service: 'Cuci Lengkap + Poles', time: '13:00', status: 'pending', price: 'Rp 300.000' },
  { id: 'BK-005', customer: 'Rizki Pratama', phone: '0816-7890-1234', vehicle: 'Daihatsu Xenia', plate: 'B 5678 MNO', service: 'Cuci Interior', time: '14:30', status: 'cancelled', price: 'Rp 120.000' },
  { id: 'BK-006', customer: 'Maya Sari', phone: '0817-8901-2345', vehicle: 'Toyota Fortuner', plate: 'B 6789 PQR', service: 'Cuci Lengkap + Wax', time: '15:00', status: 'pending', price: 'Rp 280.000' },
  { id: 'BK-007', customer: 'Doni Setiawan', phone: '0818-9012-3456', vehicle: 'Honda Brio', plate: 'B 7890 STU', service: 'Cuci Biasa', time: '16:00', status: 'pending', price: 'Rp 80.000' },
];

const statusConfig = {
  completed: { label: 'Selesai', color: 'bg-green-100 text-green-800', icon: CheckCircle },
  in_progress: { label: 'Sedang Proses', color: 'bg-blue-100 text-blue-800', icon: Clock },
  pending: { label: 'Menunggu', color: 'bg-yellow-100 text-yellow-800', icon: AlertCircle },
  cancelled: { label: 'Dibatalkan', color: 'bg-red-100 text-red-800', icon: XCircle },
};

export default function BookingsIndex() {
  const [search, setSearch] = React.useState('');
  const [statusFilter, setStatusFilter] = React.useState('all');

  const filteredBookings = bookings.filter((booking) => {
    const matchesSearch = booking.customer.toLowerCase().includes(search.toLowerCase()) ||
      booking.vehicle.toLowerCase().includes(search.toLowerCase()) ||
      booking.plate.toLowerCase().includes(search.toLowerCase());
    const matchesStatus = statusFilter === 'all' || booking.status === statusFilter;
    return matchesSearch && matchesStatus;
  });

  return (
    <MainLayout>
      <div className="space-y-6">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 className="font-montserrat text-2xl font-bold">Booking</h1>
            <p className="text-sm text-muted-foreground">Kelola booking cuci kendaraan</p>
          </div>
          <Button className="gap-2" size="lg">
            <Plus className="h-5 w-5" />
            <Car className="h-5 w-5" />
            <span>Booking Baru</span>
          </Button>
        </div>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between">
            <CardTitle>Daftar Booking</CardTitle>
            <div className="flex items-center gap-2">
              <div className="relative">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Cari pelanggan, kendaraan, plat..."
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="pl-10 w-64"
                />
              </div>
              <Select value={statusFilter} onValueChange={setStatusFilter}>
                <SelectTrigger className="w-40">
                  <SelectValue placeholder="Semua Status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Semua Status</SelectItem>
                  <SelectItem value="pending">Menunggu</SelectItem>
                  <SelectItem value="in_progress">Sedang Proses</SelectItem>
                  <SelectItem value="completed">Selesai</SelectItem>
                  <SelectItem value="cancelled">Dibatalkan</SelectItem>
                </SelectContent>
              </Select>
              <Button variant="outline" size="icon">
                <Filter className="h-4 w-4" />
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div className="overflow-x-auto">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>ID</TableHead>
                    <TableHead>Pelanggan</TableHead>
                    <TableHead>Kendaraan</TableHead>
                    <TableHead>Layanan</TableHead>
                    <TableHead>Waktu</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Harga</TableHead>
                    <TableHead className="text-right">Aksi</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredBookings.map((booking) => {
                    const status = statusConfig[booking.status];
                    const StatusIcon = status.icon;
                    return (
                      <TableRow key={booking.id} className="hover:bg-muted/50">
                        <TableCell className="font-mono text-muted-foreground">{booking.id}</TableCell>
                        <TableCell>
                          <div>
                            <p className="font-medium">{booking.customer}</p>
                            <p className="text-sm text-muted-foreground">{booking.phone}</p>
                          </div>
                        </TableCell>
                        <TableCell>
                          <div>
                            <p>{booking.vehicle}</p>
                            <p className="text-sm text-muted-foreground">{booking.plate}</p>
                          </div>
                        </TableCell>
                        <TableCell>{booking.service}</TableCell>
                        <TableCell className="text-muted-foreground">
                          <Calendar className="h-4 w-4 inline mr-1" /> {booking.time}
                        </TableCell>
                        <TableCell>
                          <Badge variant="outline" className={status.color}>
                            <StatusIcon className="h-3 w-3 mr-1" />
                            {status.label}
                          </Badge>
                        </TableCell>
                        <TableCell className="font-medium">{booking.price}</TableCell>
                        <TableCell className="text-right">
                          <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                              <Button variant="ghost" size="icon">
                                <MoreHorizontal className="h-4 w-4" />
                              </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                              <DropdownMenuItem>Lihat Detail</DropdownMenuItem>
                              <DropdownMenuItem>Edit</DropdownMenuItem>
                              {booking.status === 'pending' && (
                                <>
                                  <DropdownMenuItem>Mulai Proses</DropdownMenuItem>
                                  <DropdownMenuItem className="text-destructive">Batalkan</DropdownMenuItem>
                                </>
                              )}
                              {booking.status === 'in_progress' && (
                                <DropdownMenuItem>Selesai</DropdownMenuItem>
                              )}
                            </DropdownMenuContent>
                          </DropdownMenu>
                        </TableCell>
                      </TableRow>
                    );
                  })}
                </TableBody>
              </Table>
            </div>
            <div className="flex items-center justify-between mt-4">
              <p className="text-sm text-muted-foreground">
                Menampilkan {filteredBookings.length} dari {bookings.length} booking
              </p>
              <div className="flex gap-2">
                <Button variant="outline" size="sm" disabled>Sebelumnya</Button>
                <Button variant="outline" size="sm" disabled>Selanjutnya</Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}