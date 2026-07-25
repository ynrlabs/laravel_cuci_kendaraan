import * as React from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Input } from '@/Components/UI';
import { Label } from '@/Components/UI';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/UI';
import { Textarea } from '@/Components/UI';
import { ArrowLeft, Plus, Car, Calendar, Clock, User, Phone, MapPin } from 'lucide-react';
import { Link } from '@inertiajs/react';

export default function BookingCreate() {
  return (
    <MainLayout>
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Link href="/kasir/bookings">
            <Button variant="ghost" size="icon">
              <ArrowLeft className="h-5 w-5" />
            </Button>
          </Link>
          <div>
            <h1 className="font-montserrat text-2xl font-bold">Booking Baru</h1>
            <p className="text-sm text-muted-foreground">Buat booking cuci kendaraan baru</p>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <User className="h-5 w-5" />
              Data Pelanggan
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="customer_name">Nama Pelanggan *</Label>
                <Input
                  id="customer_name"
                  placeholder="Masukkan nama pelanggan"
                  className="flex items-center gap-2"
                >
                  <User className="h-4 w-4 text-muted-foreground" />
                </Input>
              </div>
              <div className="space-y-2">
                <Label htmlFor="customer_phone">Nomor Telepon *</Label>
                <Input
                  id="customer_phone"
                  placeholder="08xx-xxxx-xxxx"
                  className="flex items-center gap-2"
                >
                  <Phone className="h-4 w-4 text-muted-foreground" />
                </Input>
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="customer_address">Alamat</Label>
              <Textarea
                id="customer_address"
                placeholder="Alamat lengkap pelanggan (opsional)"
                rows={2}
              />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Car className="h-5 w-5" />
              Data Kendaraan
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-2">
              <div className="space-y-2">
                <Label htmlFor="vehicle_brand">Merk *</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih merk" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="toyota">Toyota</SelectItem>
                    <SelectItem value="honda">Honda</SelectItem>
                    <SelectItem value="mitsubishi">Mitsubishi</SelectItem>
                    <SelectItem value="suzuki">Suzuki</SelectItem>
                    <SelectItem value="daihatsu">Daihatsu</SelectItem>
                    <SelectItem value="other">Lainnya</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label htmlFor="vehicle_model">Model *</Label>
                <Input id="vehicle_model" placeholder="Contoh: Avanza, CR-V, Xpander" />
              </div>
            </div>
            <div className="grid gap-4 md:grid-cols-3">
              <div className="space-y-2">
                <Label htmlFor="vehicle_year">Tahun *</Label>
                <Input id="vehicle_year" type="number" placeholder="2023" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="vehicle_color">Warna *</Label>
                <Input id="vehicle_color" placeholder="Hitam, Putih, Silver" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="vehicle_plate">Nomor Plat *</Label>
                <Input id="vehicle_plate" placeholder="B 1234 ABC" className="text-uppercase" />
              </div>
            </div>
            <div className="space-y-2">
              <Label htmlFor="vehicle_type">Tipe Kendaraan *</Label>
              <Select>
                <SelectTrigger>
                  <SelectValue placeholder="Pilih tipe" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="sedan">Sedan</SelectItem>
                  <SelectItem value="suv">SUV</SelectItem>
                  <SelectItem value="mpv">MPV</SelectItem>
                  <SelectItem value="hatchback">Hatchback</SelectItem>
                  <SelectItem value="pickup">Pickup</SelectItem>
                  <SelectItem value="motor">Motor</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Calendar className="h-5 w-5" />
              Jadwal & Layanan
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="grid gap-4 md:grid-cols-3">
              <div className="space-y-2">
                <Label htmlFor="booking_date">Tanggal *</Label>
                <Input id="booking_date" type="date" />
              </div>
              <div className="space-y-2">
                <Label htmlFor="booking_time">Waktu *</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih waktu" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="08:00">08:00</SelectItem>
                    <SelectItem value="09:00">09:00</SelectItem>
                    <SelectItem value="10:00">10:00</SelectItem>
                    <SelectItem value="11:00">11:00</SelectItem>
                    <SelectItem value="13:00">13:00</SelectItem>
                    <SelectItem value="14:00">14:00</SelectItem>
                    <SelectItem value="15:00">15:00</SelectItem>
                    <SelectItem value="16:00">16:00</SelectItem>
                  </SelectContent>
                </Select>
              </div>
              <div className="space-y-2">
                <Label htmlFor="branch">Cabang *</Label>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="Pilih cabang" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="cabang-utama">Cabang Utama - Jl. Sudirman</SelectItem>
                    <SelectItem value="cabang-selatan">Cabang Selatan - Jl. Thamrin</SelectItem>
                    <SelectItem value="cabang-utara">Cabang Utara - Jl. Gatot Subroto</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>

            <div className="space-y-2">
              <Label>Layanan *</Label>
              <div className="space-y-3">
                {[
                  { id: 'full-wash', name: 'Cuci Lengkap + Wax', price: 250000, duration: '90 menit' },
                  { id: 'interior-exterior', name: 'Cuci Interior + Exterior', price: 180000, duration: '60 menit' },
                  { id: 'basic-wash', name: 'Cuci Biasa', price: 100000, duration: '45 menit' },
                  { id: 'interior-only', name: 'Cuci Interior Saja', price: 120000, duration: '40 menit' },
                  { id: 'body-polish', name: 'Poles Body', price: 300000, duration: '120 menit' },
                ].map((service) => (
                  <label
                    key={service.id}
                    className="flex items-center justify-between p-3 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                  >
                    <div className="flex items-center gap-3">
                      <input type="checkbox" className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary" />
                      <div>
                        <p className="font-medium">{service.name}</p>
                        <p className="text-sm text-muted-foreground">{service.duration}</p>
                      </div>
                    </div>
                    <span className="font-medium text-primary">Rp {service.price.toLocaleString('id-ID')}</span>
                  </label>
                ))}
              </div>
            </div>

            <div className="space-y-2">
              <Label htmlFor="notes">Catatan (Opsional)</Label>
              <Textarea
                id="notes"
                placeholder="Catatan khusus untuk booking ini..."
                rows={3}
              />
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MapPin className="h-5 w-5" />
              Detail & Total
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="bg-muted/50 p-4 rounded-lg space-y-2">
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Subtotal Layanan</span>
                <span className="font-medium">Rp 430.000</span>
              </div>
              <div className="flex justify-between text-sm">
                <span className="text-muted-foreground">Diskon</span>
                <span className="font-medium text-green-600">- Rp 0</span>
              </div>
              <div className="flex justify-between text-sm border-t pt-2">
                <span className="text-muted-foreground">Total Bayar</span>
                <span className="font-bold text-lg">Rp 430.000</span>
              </div>
            </div>

            <div className="flex justify-end gap-4 pt-4 border-t">
              <Link href="/kasir/bookings">
                <Button variant="outline">Batal</Button>
              </Link>
              <Button className="gap-2" size="lg">
                <Plus className="h-5 w-5" />
                <span>Buat Booking</span>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </MainLayout>
  );
}