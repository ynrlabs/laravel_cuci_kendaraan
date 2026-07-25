import * as React from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Input } from '@/Components/UI';
import { Label } from '@/Components/UI';
import { Badge } from '@/Components/UI';
import { Link, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { cn } from '@/Lib/utils';
import {
  ArrowLeft,
  CreditCard,
  QrCode,
  Building,
  User,
  Calendar,
  Clock,
  Printer,
  CheckCircle,
  XCircle,
  Loader2,
  Truck,
  Bike,
  Car,
  Minus,
  Plus,
  Banknote,
  Receipt,
  Smartphone,
} from 'lucide-react';

// Vehicle type configuration
const vehicleTypeConfig = {
  motor: { label: 'Motor', icon: Bike, color: 'bg-purple-100 text-purple-700 border-purple-200', iconColor: 'text-purple-600' },
  mobil: { label: 'Mobil', icon: Car, color: 'bg-blue-100 text-blue-700 border-blue-200', iconColor: 'text-blue-600' },
  suv: { label: 'SUV', icon: Car, color: 'bg-green-100 text-green-700 border-green-200', iconColor: 'text-green-600' },
  truck: { label: 'Truk', icon: Truck, color: 'bg-orange-100 text-orange-700 border-orange-200', iconColor: 'text-orange-600' },
};

// Mock booking data
const mockBooking = {
  id: 'BK-20241201-001',
  bookingCode: 'BK-20241201-001',
  customer: {
    name: 'Budi Santoso',
    phone: '0812-3456-7890',
    loyaltyTier: 'Gold',
    totalSpent: 2500000,
  },
  vehicle: {
    type: 'suv',
    brand: 'Toyota',
    model: 'Fortuner',
    year: 2023,
    color: 'Hitam',
    plate: 'B 1234 ABC',
  },
  services: [
    { id: 'full-wash', name: 'Cuci Lengkap + Wax', price: 250000, duration: '90 menit' },
    { id: 'interior-only', name: 'Cuci Interior Saja', price: 120000, duration: '40 menit' },
  ],
  branch: {
    name: 'Cabang Utama - Sudirman',
    address: 'Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan',
    phone: '021-1234567',
  },
  cashier: 'Siti Rahayu',
  createdAt: '2024-12-01 09:30:00',
  status: 'READY',
  paymentStatus: 'UNPAID',
};

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

function PaymentMethodCard({ method, isSelected, onSelect, disabled = false }) {
  const icons = {
    CASH: CreditCard,
    QRIS: QrCode,
    TRANSFER: Building,
  };
  const labels = {
    CASH: { title: 'Tunai', desc: 'Bayar langsung di kasir', color: 'text-green-600', bg: 'bg-green-50', border: 'border-green-200' },
    QRIS: { title: 'QRIS', desc: 'Scan QR dari e-wallet', color: 'text-blue-600', bg: 'bg-blue-50', border: 'border-blue-200' },
    TRANSFER: { title: 'Transfer Bank', desc: 'Virtual Account / Transfer manual', color: 'text-purple-600', bg: 'bg-purple-50', border: 'border-purple-200' },
  };
  
  const Icon = icons[method];
  const label = labels[method];

  return (
    <button
      type="button"
      onClick={() => !disabled && onSelect(method)}
      disabled={disabled}
      className={cn(
        'relative p-4 border-2 rounded-xl transition-all cursor-pointer',
        isSelected
          ? `border-2 ${label.border.replace('200', '500')} bg-white shadow-md`
          : `border-border ${label.bg} hover:border-primary/50`,
        disabled && 'opacity-50 cursor-not-allowed'
      )}
    >
      {isSelected && (
        <div className="absolute -top-2 -right-2">
          <CheckCircle className="h-5 w-5 text-primary" />
        </div>
      )}
      <div className="flex items-start gap-4">
        <div className={cn('p-3 rounded-xl', label.bg.replace('50', '100'), label.color)}>
          <Icon className="h-6 w-6" />
        </div>
        <div className="flex-1">
          <p className="font-medium text-foreground">{label.title}</p>
          <p className="text-sm text-muted-foreground">{label.desc}</p>
        </div>
      </div>
    </button>
  );
}

function LicensePlateChip({ plate, vehicleType }) {
  return (
    <div className="flex items-center gap-2 px-3 py-1.5 bg-muted rounded-lg">
      <VehicleBadge type={vehicleType} />
      <span className="font-jetbrains-mono font-medium text-sm tracking-wider">{plate}</span>
    </div>
  );
}

function ReceiptPreview({ booking, paymentMethod, amounts, splitPayment }) {
  const subtotal = booking.services.reduce((sum, s) => sum + s.price, 0);
  const tax = Math.round(subtotal * 0.11);
  const total = subtotal + tax;
  const cashAmount = splitPayment ? (amounts.cash || 0) : (paymentMethod === 'CASH' ? total : 0);
  const qrisAmount = splitPayment ? (amounts.qris || 0) : (paymentMethod === 'QRIS' ? total : 0);
  const transferAmount = splitPayment ? (amounts.transfer || 0) : (paymentMethod === 'TRANSFER' ? total : 0);
  const paidTotal = cashAmount + qrisAmount + transferAmount;
  const change = Math.max(0, paidTotal - total);

  const formatDate = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + 
           date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
  };

  const paymentLabels = { CASH: 'TUNAI', QRIS: 'QRIS', TRANSFER: 'TRANSFER' };

  return (
    <div className="bg-white p-4 rounded-lg border" style={{ maxWidth: '320px', width: '100%' }}>
      {/* Thermal receipt style */}
      <div className="font-mono text-sm leading-relaxed" style={{ fontFamily: '"JetBrains Mono", monospace' }}>
        {/* Header */}
        <div className="text-center border-b border-dashed pb-3 mb-3">
          <p className="font-bold text-lg tracking-wide">{booking.branch.name}</p>
          <p className="text-xs mt-1">{booking.branch.address}</p>
          <p className="text-xs">Telp: {booking.branch.phone}</p>
        </div>

        {/* Order Info */}
        <div className="space-y-1 mb-3">
          <div className="flex justify-between">
            <span>No. Order</span>
            <span className="font-bold">{booking.bookingCode}</span>
          </div>
          <div className="flex justify-between">
            <span>Tanggal</span>
            <span>{formatDate(booking.createdAt)}</span>
          </div>
          <div className="flex justify-between">
            <span>Kasir</span>
            <span>{booking.cashier}</span>
          </div>
          <div className="flex justify-between">
            <span>Pelanggan</span>
            <span className="font-medium">{booking.customer.name}</span>
          </div>
          <div className="flex justify-between">
            <span>Kendaraan</span>
            <span className="font-jetbrains-mono">{booking.vehicle.plate}</span>
          </div>
        </div>

        <div className="border-t border-b border-dashed py-2 my-2" />

        {/* Services */}
        <p className="font-medium mb-2">DETAIL LAYANAN</p>
        {booking.services.map((service) => (
          <div key={service.id} className="mb-2">
            <p className="text-xs">{service.name}</p>
            <div className="flex justify-between text-xs">
              <span>Rp {service.price.toLocaleString('id-ID')}</span>
              <span>x 1</span>
            </div>
          </div>
        ))}

        <div className="border-t border-dashed py-2 my-2" />

        {/* Price Breakdown */}
        <div className="space-y-1 mb-2">
          <div className="flex justify-between text-xs">
            <span>Subtotal</span>
            <span>Rp {subtotal.toLocaleString('id-ID')}</span>
          </div>
          <div className="flex justify-between text-xs">
            <span>PPN (11%)</span>
            <span>Rp {tax.toLocaleString('id-ID')}</span>
          </div>
          <div className="flex justify-between font-bold text-base border-t pt-2">
            <span>TOTAL</span>
            <span>Rp {total.toLocaleString('id-ID')}</span>
          </div>
        </div>

        {/* Payment Breakdown */}
        <div className="border-t border-dashed pt-2 mb-2">
          <p className="font-medium text-xs mb-1">PEMBAYARAN</p>
          {splitPayment ? (
            <>
              {cashAmount > 0 && (
                <div className="flex justify-between text-xs">
                  <span>{paymentLabels.CASH}</span>
                  <span>Rp {cashAmount.toLocaleString('id-ID')}</span>
                </div>
              )}
              {qrisAmount > 0 && (
                <div className="flex justify-between text-xs">
                  <span>{paymentLabels.QRIS}</span>
                  <span>Rp {qrisAmount.toLocaleString('id-ID')}</span>
                </div>
              )}
              {transferAmount > 0 && (
                <div className="flex justify-between text-xs">
                  <span>{paymentLabels.TRANSFER}</span>
                  <span>Rp {transferAmount.toLocaleString('id-ID')}</span>
                </div>
              )}
            </>
          ) : (
            <div className="flex justify-between text-xs">
              <span>{paymentLabels[paymentMethod]}</span>
              <span className="font-bold">Rp {total.toLocaleString('id-ID')}</span>
            </div>
          )}
          {splitPayment && paidTotal > total && (
            <div className="flex justify-between text-xs text-green-600 font-medium">
              <span>KEMBALI</span>
              <span>Rp {change.toLocaleString('id-ID')}</span>
            </div>
          )}
        </div>

        <div className="border-t border-dashed pt-2" />
        
        {/* Footer */}
        <div className="text-center text-xs space-y-1">
          <p>Terima kasih telah menggunakan layanan kami</p>
          <p className="font-medium">CuciKuy - Mobil Bersih, Hati Senang</p>
          <div className="flex justify-center my-2">
            <div className="w-20 h-20 bg-muted rounded flex items-center justify-center">
              <QrCode className="h-12 w-12 text-muted-foreground/50" />
            </div>
          </div>
          <p className="text-xs">Scan untuk feedback</p>
          <p className="text-xs">www.cucikuy.com/feedback/{booking.bookingCode}</p>
        </div>
      </div>
    </div>
  );
}

export default function CheckoutPage({ booking = mockBooking }) {
  const [paymentMethod, setPaymentMethod] = useState('CASH');
  const [splitPayment, setSplitPayment] = useState(false);
  const [amounts, setAmounts] = useState({ cash: 0, qris: 0, transfer: 0 });
  const [isProcessing, setIsProcessing] = useState(false);
  const [showPrintDialog, setShowPrintDialog] = useState(false);

  const { data, setData, post, processing, reset } = useForm({
    payment_method: 'CASH',
    split_payment: false,
    cash_amount: 0,
    qris_amount: 0,
    transfer_amount: 0,
    booking_id: booking.id,
  });

  const subtotal = booking.services.reduce((sum, s) => sum + s.price, 0);
  const tax = Math.round(subtotal * 0.11);
  const total = subtotal + tax;

  // Update form data
  useEffect(() => {
    setData('payment_method', paymentMethod);
    setData('split_payment', splitPayment);
    if (splitPayment) {
      setData('cash_amount', amounts.cash);
      setData('qris_amount', amounts.qris);
      setData('transfer_amount', amounts.transfer);
    } else {
      setData('cash_amount', paymentMethod === 'CASH' ? total : 0);
      setData('qris_amount', paymentMethod === 'QRIS' ? total : 0);
      setData('transfer_amount', paymentMethod === 'TRANSFER' ? total : 0);
    }
  }, [paymentMethod, splitPayment, amounts, total]);

  const handlePaymentMethodChange = (method) => {
    if (!splitPayment) {
      setPaymentMethod(method);
    }
  };

  const handleAmountChange = (method, value) => {
    const numValue = parseInt(value) || 0;
    setAmounts(prev => ({ ...prev, [method.toLowerCase()]: numValue }));
  };

  const getTotalPaid = () => {
    if (splitPayment) {
      return amounts.cash + amounts.qris + amounts.transfer;
    }
    return total;
  };

  const getChange = () => {
    const paid = getTotalPaid();
    return Math.max(0, paid - total);
  };

  const canSubmit = () => {
    if (splitPayment) {
      return getTotalPaid() >= total;
    }
    return true;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!canSubmit()) {
      alert('Total pembayaran kurang dari total tagihan');
      return;
    }
    setIsProcessing(true);
    post('/kasir/payments/checkout', {
      onSuccess: () => {
        setShowPrintDialog(true);
      },
      onFinish: () => setIsProcessing(false),
    });
  };

  const handlePrint = () => {
    window.print();
  };

  return (
    <MainLayout pageTitle="Checkout & Struk" pageDescription="Proses pembayaran dan cetak struk">
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Link href="/kasir/antrian">
            <Button variant="ghost" size="icon">
              <ArrowLeft className="h-5 w-5" />
            </Button>
          </Link>
          <div>
            <h1 className="font-montserrat text-2xl font-bold">Checkout & Struk</h1>
            <p className="text-sm text-muted-foreground">Proses pembayaran booking {booking.bookingCode}</p>
          </div>
        </div>

        <div className="grid lg:grid-cols-12 gap-6">
          {/* Left Column - Payment Methods & Receipt Preview */}
          <div className="lg:col-span-7 space-y-6">
            {/* Payment Methods */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <CreditCard className="h-5 w-5" />
                  Metode Pembayaran
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid gap-4 md:grid-cols-3">
                  {['CASH', 'QRIS', 'TRANSFER'].map((method) => (
                    <PaymentMethodCard
                      key={method}
                      method={method}
                      isSelected={paymentMethod === method && !splitPayment}
                      onSelect={handlePaymentMethodChange}
                    />
                  ))}
                </div>

                {/* Split Payment Toggle */}
                <div className="border-t pt-4">
                  <Label className="flex items-center gap-3 cursor-pointer">
                    <input
                      type="checkbox"
                      checked={splitPayment}
                      onChange={(e) => setSplitPayment(e.target.checked)}
                      className="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <div className="flex-1">
                      <p className="font-medium">Split Payment (Pembayaran Campuran)</p>
                      <p className="text-sm text-muted-foreground">Gabungkan multiple metode pembayaran</p>
                    </div>
                  </Label>
                </div>

                {/* Split Payment Inputs */}
                {splitPayment && (
                  <div className="space-y-3 bg-muted/50 p-4 rounded-lg border">
                    <p className="text-sm font-medium mb-3">Masukkan nominal per metode:</p>
                    <div className="grid gap-3 md:grid-cols-3">
                      <div className="space-y-1">
                        <Label className="text-xs text-muted-foreground">Tunai</Label>
                        <div className="relative">
                          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">Rp</span>
                          <Input
                            type="number"
                            placeholder="0"
                            value={amounts.cash}
                            onChange={(e) => handleAmountChange('CASH', e.target.value)}
                            className="pl-6 text-right"
                            min={0}
                            step={1000}
                          />
                        </div>
                      </div>
                      <div className="space-y-1">
                        <Label className="text-xs text-muted-foreground">QRIS</Label>
                        <div className="relative">
                          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">Rp</span>
                          <Input
                            type="number"
                            placeholder="0"
                            value={amounts.qris}
                            onChange={(e) => handleAmountChange('QRIS', e.target.value)}
                            className="pl-6 text-right"
                            min={0}
                            step={1000}
                          />
                        </div>
                      </div>
                      <div className="space-y-1">
                        <Label className="text-xs text-muted-foreground">Transfer</Label>
                        <div className="relative">
                          <span className="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">Rp</span>
                          <Input
                            type="number"
                            placeholder="0"
                            value={amounts.transfer}
                            onChange={(e) => handleAmountChange('TRANSFER', e.target.value)}
                            className="pl-6 text-right"
                            min={0}
                            step={1000}
                          />
                        </div>
                      </div>
                    </div>
                    <div className="flex justify-between text-sm mt-2 pt-2 border-t">
                      <span>Total Dibayar</span>
                      <span className="font-bold font-montserrat text-primary">Rp {getTotalPaid().toLocaleString('id-ID')}</span>
                    </div>
                    {getTotalPaid() > total && (
                      <div className="flex justify-between text-sm text-green-600 font-medium">
                        <span>Kembali</span>
                        <span>Rp {getChange().toLocaleString('id-ID')}</span>
                      </div>
                    )}
                    {getTotalPaid() < total && (
                      <div className="flex justify-between text-sm text-red-600 font-medium">
                        <span>Kekurangan</span>
                        <span>Rp {(total - getTotalPaid()).toLocaleString('id-ID')}</span>
                      </div>
                    )}
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Receipt Preview */}
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle className="flex items-center gap-2">
                  <Receipt className="h-5 w-5" />
                  Preview Struk Thermal (80mm)
                </CardTitle>
                <Button variant="outline" size="icon" onClick={handlePrint} className="gap-1">
                  <Printer className="h-4 w-4" />
                  <span className="hidden sm:inline">Print</span>
                </Button>
              </CardHeader>
              <CardContent>
                <div className="flex justify-center">
                  <ReceiptPreview
                    booking={booking}
                    paymentMethod={paymentMethod}
                    amounts={amounts}
                    splitPayment={splitPayment}
                  />
                </div>
                <p className="text-center text-xs text-muted-foreground mt-2">
                  Preview struk thermal 80mm - Klik icon printer untuk mencetak
                </p>
              </CardContent>
            </Card>
          </div>

          {/* Right Column - Order Summary */}
          <div className="lg:col-span-5">
            <Card className="sticky top-24 lg:sticky lg:top-6 h-fit">
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  Ringkasan Pesanan
                  <Badge variant="outline" className="text-xs">{booking.paymentStatus}</Badge>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {/* Customer & Vehicle Info */}
                <div className="space-y-3 p-3 bg-muted/50 rounded-lg">
                  <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                      <User className="h-4 w-4 text-muted-foreground" />
                      <span className="font-medium">{booking.customer.name}</span>
                    </div>
                    {booking.customer.loyaltyTier !== 'Bronze' && (
                      <Badge variant="outline" className="bg-yellow-50 border-yellow-200 text-yellow-700 text-xs">
                        {booking.customer.loyaltyTier}
                      </Badge>
                    )}
                  </div>
                  <div className="flex items-center gap-2">
                    <LicensePlateChip plate={booking.vehicle.plate} vehicleType={booking.vehicle.type} />
                  </div>
                </div>

                {/* Services Breakdown */}
                <div className="space-y-2 border-t pt-4">
                  {booking.services.map((service) => (
                    <div key={service.id} className="flex justify-between text-sm">
                      <span className="text-muted-foreground">{service.name}</span>
                      <span className="font-medium">Rp {service.price.toLocaleString('id-ID')}</span>
                    </div>
                  ))}
                </div>

                {/* Price Breakdown */}
                <div className="space-y-2 border-t pt-4">
                  <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">Subtotal</span>
                    <span className="font-medium">Rp {subtotal.toLocaleString('id-ID')}</span>
                  </div>
                  <div className="flex justify-between text-sm">
                    <span className="text-muted-foreground">PPN (11%)</span>
                    <span className="font-medium">Rp {tax.toLocaleString('id-ID')}</span>
                  </div>
                  <div className="flex justify-between text-xl font-bold border-t pt-2">
                    <span>GRAND TOTAL</span>
                    <span className="text-primary font-montserrat text-2xl">Rp {total.toLocaleString('id-ID')}</span>
                  </div>
                </div>

                {/* Payment Summary */}
                <div className="space-y-2 border-t pt-4">
                  <Label className="text-sm font-medium">Metode: {paymentMethod} {splitPayment && '(Split)'}</Label>
                  {splitPayment ? (
                    <>
                      {amounts.cash > 0 && (
                        <div className="flex items-center justify-between text-sm">
                          <span className="flex items-center gap-1 text-muted-foreground">
                            <CreditCard className="h-3 w-3" /> Tunai
                          </span>
                          <span className="font-medium">Rp {amounts.cash.toLocaleString('id-ID')}</span>
                        </div>
                      )}
                      {amounts.qris > 0 && (
                        <div className="flex items-center justify-between text-sm">
                          <span className="flex items-center gap-1 text-muted-foreground">
                            <QrCode className="h-3 w-3" /> QRIS
                          </span>
                          <span className="font-medium">Rp {amounts.qris.toLocaleString('id-ID')}</span>
                        </div>
                      )}
                      {amounts.transfer > 0 && (
                        <div className="flex items-center justify-between text-sm">
                          <span className="flex items-center gap-1 text-muted-foreground">
                            <Building className="h-3 w-3" /> Transfer
                          </span>
                          <span className="font-medium">Rp {amounts.transfer.toLocaleString('id-ID')}</span>
                        </div>
                      )}
                    </>
                  ) : (
                    <div className="flex items-center justify-between text-sm">
                      <span className="flex items-center gap-1 text-muted-foreground">
                        {paymentMethod === 'CASH' && <CreditCard className="h-3 w-3" />}
                        {paymentMethod === 'QRIS' && <QrCode className="h-3 w-3" />}
                        {paymentMethod === 'TRANSFER' && <Building className="h-3 w-3" />}
                        {paymentMethod}
                      </span>
                      <span className="font-bold font-montserrat text-primary">Rp {total.toLocaleString('id-ID')}</span>
                    </div>
                  )}
                </div>

                {/* Actions */}
                <div className="space-y-2 border-t pt-4">
                  <Button
                    className="w-full gap-2 bg-primary hover:bg-primary/90 text-primary-foreground py-3 text-lg"
                    size="lg"
                    onClick={handleSubmit}
                    disabled={isProcessing || !canSubmit()}
                  >
                    {isProcessing ? (
                      <>
                        <Loader2 className="h-5 w-5 animate-spin" />
                        <span className="font-montserrat font-semibold">Memproses...</span>
                      </>
                    ) : (
                      <>
                        <CheckCircle className="h-5 w-5" />
                        <span className="font-montserrat font-semibold">Cetak Struk & Selesai</span>
                      </>
                    )}
                  </Button>
                  <Link href="/kasir/antrian">
                    <Button variant="outline" className="w-full gap-2 py-2 border-destructive text-destructive hover:bg-destructive/10">
                      <XCircle className="h-4 w-4" />
                      Batal
                    </Button>
                  </Link>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* Print Dialog */}
        {showPrintDialog && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <Card className="w-full max-w-md mx-4">
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  <Printer className="h-5 w-5 text-primary" />
                  Struk Berhasil Dicetak
                  <button onClick={() => setShowPrintDialog(false)} className="text-muted-foreground hover:text-foreground">✕</button>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4 text-center">
                <div className="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center">
                  <CheckCircle className="h-10 w-10 text-green-600" />
                </div>
                <h3 className="font-montserrat text-lg font-bold">Pembayaran Berhasil!</h3>
                <p className="text-sm text-muted-foreground">
                  Struk untuk booking <span className="font-montserrat font-bold">{booking.bookingCode}</span> telah dicetak.
                </p>
                <div className="flex gap-2">
                  <Button variant="outline" className="flex-1" onClick={() => setShowPrintDialog(false)}>
                    Tutup
                  </Button>
                  <Button className="flex-1" onClick={handlePrint}>
                    <Printer className="h-4 w-4 mr-1" />
                    Cetak Lagi
                  </Button>
                </div>
                <Button variant="ghost" className="w-full" onClick={() => {
                  setShowPrintDialog(false);
                  // Navigate back to kanban
                }}>
                  Kembali ke Antrian
                </Button>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </MainLayout>
  );
}