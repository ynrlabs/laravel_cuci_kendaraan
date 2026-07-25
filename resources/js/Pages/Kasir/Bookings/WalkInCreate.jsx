import * as React from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Input } from '@/Components/UI';
import { Label } from '@/Components/UI';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/UI';
import { Badge } from '@/Components/UI';
import { ArrowLeft, Plus, Car, Calendar, Clock, User, Phone, MapPin, Loader2, CreditCard, QrCode, Minus, Truck, Bike, CheckCircle2 } from 'lucide-react';
import { Link, useForm } from '@inertiajs/react';
import { useState, useEffect, useCallback } from 'react';
import { cn } from '@/Lib/utils';

// Vehicle type configuration
const vehicleTypeConfig = {
  motor: { label: 'Motor', icon: Bike, color: 'bg-purple-100 text-purple-700 border-purple-200', iconColor: 'text-purple-600' },
  mobil: { label: 'Mobil', icon: Car, color: 'bg-blue-100 text-blue-700 border-blue-200', iconColor: 'text-blue-600' },
  suv: { label: 'SUV', icon: Car, color: 'bg-green-100 text-green-700 border-green-200', iconColor: 'text-green-600' },
  truck: { label: 'Truk', icon: Truck, color: 'bg-orange-100 text-orange-700 border-orange-200', iconColor: 'text-orange-600' },
};

const statusConfig = {
  PENDING: { label: 'PENDING', color: 'bg-yellow-100 text-yellow-700 border-yellow-200', borderColor: 'border-yellow-400' },
  WASHING: { label: 'WASHING', color: 'bg-blue-100 text-blue-700 border-blue-200', borderColor: 'border-blue-500' },
  DRYING: { label: 'DRYING', color: 'bg-orange-100 text-orange-700 border-orange-200', borderColor: 'border-orange-500' },
  READY: { label: 'READY', color: 'bg-green-100 text-green-700 border-green-200', borderColor: 'border-green-500' },
  PAID: { label: 'PAID', color: 'bg-emerald-100 text-emerald-700 border-emerald-200', borderColor: 'border-emerald-500' },
};

// Services data
const services = [
  { id: 'full-wash', name: 'Cuci Lengkap + Wax', price: 250000, duration: '90 menit', icon: Car, type: 'premium' },
  { id: 'interior-exterior', name: 'Cuci Interior + Exterior', price: 180000, duration: '60 menit', icon: Car, type: 'standard' },
  { id: 'basic-wash', name: 'Cuci Biasa', price: 100000, duration: '45 menit', icon: Bike, type: 'basic' },
  { id: 'interior-only', name: 'Cuci Interior Saja', price: 120000, duration: '40 menit', icon: Car, type: 'standard' },
  { id: 'body-polish', name: 'Poles Body', price: 300000, duration: '120 menit', icon: Car, type: 'premium' },
];

// Vehicle type options
const vehicleTypes = [
  { value: 'mobil', label: 'Mobil (Sedan/MPV/Hatchback)', types: ['sedan', 'mpv', 'hatchback'] },
  { value: 'suv', label: 'SUV', types: ['suv'] },
  { value: 'motor', label: 'Motor', types: ['motor'] },
  { value: 'truck', label: 'Truk/Pickup', types: ['truck', 'pickup'] },
];

// Loyalty tiers
const loyaltyTiers = [
  { tier: 'Platinum', minSpend: 5000000, discount: 15, color: 'bg-gradient-to-r from-purple-500 to-pink-500 text-white' },
  { tier: 'Gold', minSpend: 2000000, discount: 10, color: 'bg-gradient-to-r from-yellow-500 to-orange-500 text-white' },
  { tier: 'Silver', minSpend: 500000, discount: 5, color: 'bg-gradient-to-r from-gray-400 to-gray-600 text-white' },
  { tier: 'Bronze', minSpend: 0, discount: 0, color: 'bg-gradient-to-r from-amber-700 to-amber-900 text-white' },
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

function ServiceCard({ service, isSelected, onSelect }) {
  return (
    <button
      type="button"
      onClick={() => onSelect(service.id)}
      className={cn(
        'relative p-4 border-2 rounded-xl transition-all hover:shadow-md',
        isSelected
          ? 'border-primary bg-primary/5 shadow-sm'
          : 'border-border bg-card hover:border-primary/50'
      )}
    >
      <div className="absolute -top-2 -right-2">
        <CheckCircle2 className={cn('h-5 w-5', isSelected ? 'text-primary' : 'text-transparent')} />
      </div>
      <div className="flex items-start gap-3">
        <div className={cn('p-2 rounded-lg', service.type === 'premium' ? 'bg-yellow-100 text-yellow-600' : service.type === 'standard' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600')}>
          <service.icon className="h-5 w-5" />
        </div>
        <div className="flex-1 min-w-0">
          <p className="font-medium text-foreground">{service.name}</p>
          <p className="text-sm text-muted-foreground flex items-center gap-1">
            <Clock className="h-3 w-3" />
            {service.duration}
          </p>
        </div>
        <div className="text-right">
          <p className="font-montserrat font-bold text-lg text-primary">
            Rp {service.price.toLocaleString('id-ID')}
          </p>
        </div>
      </div>
    </button>
  );
}

function VehicleTypeCard({ type, isSelected, onSelect }) {
  const config = vehicleTypeConfig[type.value];
  const Icon = config?.icon || Car;
  
  return (
    <button
      type="button"
      onClick={() => onSelect(type.value)}
      className={cn(
        'flex flex-col items-center gap-3 p-4 border-2 rounded-xl transition-all',
        isSelected
          ? 'border-primary bg-primary/5 shadow-sm'
          : 'border-border bg-card hover:border-primary/50'
      )}
    >
      <div className={cn('p-3 rounded-full', config?.color?.replace('bg-', 'bg-').replace('100', '100') || 'bg-blue-100')}>
        <Icon className={cn('h-6 w-6', config?.iconColor || 'text-blue-600')} />
      </div>
      <span className="text-sm font-medium text-center">{type.label}</span>
    </button>
  );
}

function PaymentToggle({ selected, onChange }) {
  return (
    <div className="flex items-center gap-2 p-1 bg-muted rounded-lg">
      {['CASH', 'QRIS'].map((method) => (
        <button
          key={method}
          type="button"
          onClick={() => onChange(method)}
          className={cn(
            'flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium transition-all',
            selected === method
              ? 'bg-background shadow-sm text-foreground'
              : 'text-muted-foreground hover:text-foreground'
          )}
        >
          {method === 'CASH' ? (
            <CreditCard className="h-4 w-4" />
          ) : (
            <QrCode className="h-4 w-4" />
          )}
          {method}
        </button>
      ))}
    </div>
  );
}

function LoyaltyBadge({ tier, totalSpent }) {
  const tierConfig = loyaltyTiers.find(t => t.tier === tier) || loyaltyTiers[3];
  const discount = tierConfig.discount;
  
  return (
    <div className="flex items-center gap-3 p-3 bg-gradient-to-r from-primary/5 to-primary/10 rounded-xl border border-primary/20">
      <div className={cn('px-3 py-1 rounded-full text-xs font-bold', tierConfig.color)}>
        {tierConfig.tier}
      </div>
      <div className="flex-1">
        <p className="text-sm font-medium">Total Belanja: Rp {totalSpent.toLocaleString('id-ID')}</p>
        {discount > 0 && (
          <p className="text-xs text-green-600 font-medium">Diskon Loyalitas: {discount}%</p>
        )}
      </div>
      <Badge variant="outline" className="bg-green-50 border-green-200 text-green-700">
        <CheckCircle2 className="h-3 w-3 mr-1" />
        Member
      </Badge>
    </div>
  );
}

function OrderSummary({ selectedServices, vehicleType, loyaltyDiscount, paymentMethod, onPaymentChange }) {
  const subtotal = selectedServices.reduce((sum, s) => sum + s.price, 0);
  const discountAmount = Math.round(subtotal * loyaltyDiscount / 100);
  const tax = Math.round((subtotal - discountAmount) * 0.11);
  const total = subtotal - discountAmount + tax;

  return (
    <Card className="sticky top-24 lg:sticky lg:top-6 h-fit">
      <CardHeader className="pb-3">
        <CardTitle className="flex items-center justify-between">
          Ringkasan Pesanan
          <Badge variant="outline" className="text-xs">Walk-in</Badge>
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        {/* Vehicle Type */}
        <div className="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
          <div className="flex items-center gap-2">
            <VehicleBadge type={vehicleType} />
          </div>
          <input
            type="text"
            placeholder="B 1234 ABC"
            className="font-jetbrains-mono text-sm font-medium text-right bg-transparent border-none focus:outline-none w-32 uppercase"
            id="license_plate"
          />
        </div>

        {/* Services Breakdown */}
        <div className="space-y-2 border-t pt-4">
          {selectedServices.map((service) => (
            <div key={service.id} className="flex justify-between text-sm">
              <span className="text-muted-foreground">{service.name}</span>
              <span className="font-medium">Rp {service.price.toLocaleString('id-ID')}</span>
            </div>
          ))}
          {selectedServices.length === 0 && (
            <p className="text-center text-muted-foreground py-4">Belum ada layanan dipilih</p>
          )}
        </div>

        {/* Price Breakdown */}
        <div className="space-y-2 border-t pt-4">
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">Subtotal</span>
            <span className="font-medium">Rp {subtotal.toLocaleString('id-ID')}</span>
          </div>
          {loyaltyDiscount > 0 && (
            <div className="flex justify-between text-sm text-green-600">
              <span>Diskon Loyalitas ({loyaltyDiscount}%)</span>
              <span className="font-medium">- Rp {discountAmount.toLocaleString('id-ID')}</span>
            </div>
          )}
          <div className="flex justify-between text-sm">
            <span className="text-muted-foreground">PPN (11%)</span>
            <span className="font-medium">Rp {tax.toLocaleString('id-ID')}</span>
          </div>
          <div className="flex justify-between text-lg font-bold border-t pt-2">
            <span>TOTAL</span>
            <span className="text-primary font-montserrat text-2xl">Rp {total.toLocaleString('id-ID')}</span>
          </div>
        </div>

        {/* Payment Toggle */}
        <div className="space-y-2 border-t pt-4">
          <Label className="text-sm font-medium">Metode Bayar</Label>
          <PaymentToggle selected={paymentMethod} onChange={onPaymentChange} />
        </div>

        {/* Actions */}
        <div className="space-y-2 border-t pt-4">
          <Button className="w-full gap-2 bg-primary hover:bg-primary/90 text-primary-foreground py-3 text-lg" size="lg">
            <CheckCircle2 className="h-5 w-5" />
            <span className="font-montserrat font-semibold">CONFIRM BOOKING</span>
          </Button>
          <Link href="/kasir/antrian">
            <Button variant="outline" className="w-full gap-2 py-2 border-destructive text-destructive hover:bg-destructive/10">
              <Minus className="h-4 w-4" />
              Discard Draft
            </Button>
          </Link>
        </div>
      </CardContent>
    </Card>
  );
}

export default function WalkInBookingCreate() {
  const [selectedVehicleType, setSelectedVehicleType] = useState('mobil');
  const [selectedServices, setSelectedServices] = useState([]);
  const [loyaltyInfo, setLoyaltyInfo] = useState({ tier: 'Bronze', totalSpent: 0, discount: 0 });
  const [paymentMethod, setPaymentMethod] = useState('CASH');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showCustomerLookup, setShowCustomerLookup] = useState(false);
  const [customerPhone, setCustomerPhone] = useState('');

  const { data, setData, post, processing, errors, reset } = useForm({
    customer_name: '',
    customer_phone: '',
    vehicle_plate: '',
    vehicle_type: selectedVehicleType,
    vehicle_brand: '',
    vehicle_model: '',
    vehicle_year: '',
    vehicle_color: '',
    service_ids: [],
    notes: '',
    payment_method: 'CASH',
    loyalty_discount: 0,
  });

  // Simulate loyalty lookup when phone is entered
  useEffect(() => {
    if (data.customer_phone && data.customer_phone.length >= 10) {
      // Simulate API call to check loyalty
      const mockLoyalty = {
        tier: 'Gold',
        totalSpent: 2500000,
        discount: 10,
      };
      setLoyaltyInfo(mockLoyalty);
      setData('loyalty_discount', mockLoyalty.discount);
    } else {
      setLoyaltyInfo({ tier: 'Bronze', totalSpent: 0, discount: 0 });
      setData('loyalty_discount', 0);
    }
  }, [data.customer_phone]);

  // Update vehicle type in form
  useEffect(() => {
    setData('vehicle_type', selectedVehicleType);
  }, [selectedVehicleType]);

  // Update services in form
  useEffect(() => {
    setData('service_ids', selectedServices.map(s => s.id));
  }, [selectedServices]);

  const toggleService = (serviceId) => {
    const service = services.find(s => s.id === serviceId);
    if (!service) return;
    
    setSelectedServices(prev => {
      const exists = prev.find(s => s.id === serviceId);
      if (exists) {
        return prev.filter(s => s.id !== serviceId);
      }
      return [...prev, service];
    });
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (selectedServices.length === 0) {
      alert('Pilih minimal 1 layanan');
      return;
    }
    if (!data.customer_name) {
      alert('Nama pelanggan wajib diisi');
      return;
    }
    if (!data.vehicle_plate) {
      alert('Nomor plat wajib diisi');
      return;
    }
    
    setIsSubmitting(true);
    post('/kasir/bookings/walkin', {
      onSuccess: () => {
        reset();
        setSelectedServices([]);
        setSelectedVehicleType('mobil');
        setLoyaltyInfo({ tier: 'Bronze', totalSpent: 0, discount: 0 });
        setPaymentMethod('CASH');
      },
      onFinish: () => setIsSubmitting(false),
    });
  };

  return (
    <MainLayout pageTitle="Walk-in Booking Baru" pageDescription="Buat booking walk-in pelanggan datangan langsung">
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Link href="/kasir/antrian">
            <Button variant="ghost" size="icon">
              <ArrowLeft className="h-5 w-5" />
            </Button>
          </Link>
          <div>
            <h1 className="font-montserrat text-2xl font-bold">Walk-in Booking Baru</h1>
            <p className="text-sm text-muted-foreground">Pelanggan datangan langsung - proses cepat</p>
          </div>
        </div>

        <div className="grid lg:grid-cols-12 gap-6">
          {/* Left Column - Forms */}
          <div className="lg:col-span-7 space-y-6">
            {/* Customer Info */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <User className="h-5 w-5" />
                  Informasi Pelanggan
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="flex items-center gap-3">
                  <div className="relative flex-1">
                    <User className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                      id="customer_name"
                      placeholder="Nama Pelanggan *"
                      value={data.customer_name}
                      onChange={(e) => setData('customer_name', e.target.value)}
                      className="pl-10"
                      autoComplete="name"
                    />
                  </div>
                  <Button variant="outline" size="icon" onClick={() => setShowCustomerLookup(true)}>
                    <QrCode className="h-4 w-4" />
                  </Button>
                </div>
                
                <div className="relative">
                  <Phone className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                  <Input
                    id="customer_phone"
                    placeholder="No. HP (untuk cek loyalitas) *"
                    value={data.customer_phone}
                    onChange={(e) => setData('customer_phone', e.target.value.replace(/\D/g, ''))}
                    className="pl-10"
                    type="tel"
                    autoComplete="tel"
                    maxLength={13}
                  />
                </div>

                {loyaltyInfo.discount > 0 && (
                  <LoyaltyBadge tier={loyaltyInfo.tier} totalSpent={loyaltyInfo.totalSpent} />
                )}
              </CardContent>
            </Card>

            {/* Vehicle Specification */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Car className="h-5 w-5" />
                  Spesifikasi Kendaraan
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {/* Vehicle Type Grid */}
                <div className="space-y-2">
                  <Label>Tipe Kendaraan *</Label>
                  <div className="grid grid-cols-2 md:grid-cols-4 gap-3">
                    {vehicleTypes.map((type) => (
                      <VehicleTypeCard
                        key={type.value}
                        type={type}
                        isSelected={selectedVehicleType === type.value}
                        onSelect={setSelectedVehicleType}
                      />
                    ))}
                  </div>
                </div>

                {/* License Plate - JetBrains Mono, Uppercase */}
                <div className="space-y-2">
                  <Label htmlFor="vehicle_plate">Nomor Plat *</Label>
                  <Input
                    id="vehicle_plate"
                    placeholder="B 1234 ABC"
                    value={data.vehicle_plate}
                    onChange={(e) => setData('vehicle_plate', e.target.value.toUpperCase())}
                    className="font-jetbrains-mono text-lg tracking-wider text-center"
                    style={{ fontFamily: '"JetBrains Mono", monospace' }}
                    autoComplete="off"
                  />
                </div>

                {/* Vehicle Details Grid */}
                <div className="grid gap-4 md:grid-cols-2">
                  <div className="space-y-2">
                    <Label htmlFor="vehicle_brand">Merk *</Label>
                    <Select onValueChange={(value) => setData('vehicle_brand', value)}>
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
                    <Input
                      id="vehicle_model"
                      placeholder="Contoh: Avanza, CR-V, Xpander"
                      value={data.vehicle_model}
                      onChange={(e) => setData('vehicle_model', e.target.value)}
                    />
                  </div>
                </div>

                <div className="grid gap-4 md:grid-cols-3">
                  <div className="space-y-2">
                    <Label htmlFor="vehicle_year">Tahun *</Label>
                    <Input
                      id="vehicle_year"
                      type="number"
                      placeholder="2023"
                      value={data.vehicle_year}
                      onChange={(e) => setData('vehicle_year', e.target.value)}
                      min={1990}
                      max={new Date().getFullYear() + 1}
                    />
                  </div>
                  <div className="space-y-2">
                    <Label htmlFor="vehicle_color">Warna *</Label>
                    <Input
                      id="vehicle_color"
                      placeholder="Hitam, Putih, Silver"
                      value={data.vehicle_color}
                      onChange={(e) => setData('vehicle_color', e.target.value)}
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            {/* Service Selection */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <Clock className="h-5 w-5" />
                  Pilih Layanan
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-3">
                  {services.map((service) => (
                    <ServiceCard
                      key={service.id}
                      service={service}
                      isSelected={selectedServices.some(s => s.id === service.id)}
                      onSelect={toggleService}
                    />
                  ))}
                </div>
              </CardContent>
            </Card>

            {/* Notes */}
            <Card>
              <CardHeader>
                <CardTitle>Catatan</CardTitle>
              </CardHeader>
              <CardContent>
                <Label htmlFor="notes" className="block mb-2">Catatan Khusus (Opsional)</Label>
                <textarea
                  id="notes"
                  placeholder="Catatan khusus untuk booking ini..."
                  rows={3}
                  className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                  value={data.notes}
                  onChange={(e) => setData('notes', e.target.value)}
                />
              </CardContent>
            </Card>
          </div>

          {/* Right Column - Order Summary (Sticky) */}
          <div className="lg:col-span-5">
            <OrderSummary
              selectedServices={selectedServices}
              vehicleType={selectedVehicleType}
              loyaltyDiscount={loyaltyInfo.discount}
              paymentMethod={paymentMethod}
              onPaymentChange={setPaymentMethod}
            />
          </div>
        </div>

        {/* Customer Lookup Modal */}
        {showCustomerLookup && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <Card className="w-full max-w-md mx-4">
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  <QrCode className="h-5 w-5 text-yellow-500" />
                  Cari Pelanggan (QR/Phone)
                  <button onClick={() => setShowCustomerLookup(false)} className="text-muted-foreground hover:text-foreground">✕</button>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-2">
                  <Label>Nomor HP / Scan QR</Label>
                  <Input
                    placeholder="Masukkan nomor HP atau scan QR"
                    value={customerPhone}
                    onChange={(e) => setCustomerPhone(e.target.value)}
                  />
                </div>
                <div className="flex gap-2">
                  <Button variant="outline" className="flex-1" onClick={() => setShowCustomerLookup(false)}>
                    Batal
                  </Button>
                  <Button className="flex-1" onClick={() => {
                    // Simulate finding customer
                    setData('customer_name', 'Budi Santoso');
                    setData('customer_phone', '081234567890');
                    setLoyaltyInfo({ tier: 'Gold', totalSpent: 2500000, discount: 10 });
                    setData('loyalty_discount', 10);
                    setShowCustomerLookup(false);
                  }}>
                    Cari & Isi Otomatis
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </MainLayout>
  );
}