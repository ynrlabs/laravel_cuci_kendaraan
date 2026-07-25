import * as React from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Badge } from '@/Components/UI';
import { Link, useForm } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import { cn } from '@/Lib/utils';
import {
  ArrowLeft,
  QrCode,
  CheckCircle,
  XCircle,
  Loader2,
  Truck,
  Bike,
  Car,
  User,
  Calendar,
  Clock,
  CreditCard,
  AlertCircle,
  CheckCircle2,
  Camera,
  ScanLine,
  Eye,
  EyeOff,
} from 'lucide-react';

// Vehicle type configuration
const vehicleTypeConfig = {
  motor: { label: 'Motor', icon: Bike, color: 'bg-purple-100 text-purple-700 border-purple-200', iconColor: 'text-purple-600' },
  mobil: { label: 'Mobil', icon: Car, color: 'bg-blue-100 text-blue-700 border-blue-200', iconColor: 'text-blue-600' },
  suv: { label: 'SUV', icon: Car, color: 'bg-green-100 text-green-700 border-green-200', iconColor: 'text-green-600' },
  truck: { label: 'Truk', icon: Truck, color: 'bg-orange-100 text-orange-700 border-orange-200', iconColor: 'text-orange-600' },
};

const statusConfig = {
  PENDING: { label: 'MENUNGGU PEMBAYARAN', color: 'bg-yellow-100 text-yellow-700 border-yellow-200', icon: Clock, iconColor: 'text-yellow-600' },
  CONFIRMED: { label: 'DIKONFIRMASI', color: 'bg-blue-100 text-blue-700 border-blue-200', icon: CheckCircle, iconColor: 'text-blue-600' },
  WASHING: { label: 'SEDANG DICUCI', color: 'bg-orange-100 text-orange-700 border-orange-200', icon: Loader2, iconColor: 'text-orange-600' },
  DRYING: { label: 'PENGERINGAN', color: 'bg-purple-100 text-purple-700 border-purple-200', icon: Loader2, iconColor: 'text-purple-600' },
  READY: { label: 'SIAP DIAMBIL', color: 'bg-green-100 text-green-700 border-green-200', icon: CheckCircle2, iconColor: 'text-green-600' },
  PAID: { label: 'LUNAS', color: 'bg-emerald-100 text-emerald-700 border-emerald-200', icon: CheckCircle2, iconColor: 'text-emerald-600' },
  CANCELLED: { label: 'DIBATALKAN', color: 'bg-red-100 text-red-700 border-red-200', icon: XCircle, iconColor: 'text-red-600' },
};

const paymentStatusConfig = {
  UNPAID: { label: 'Belum Bayar', color: 'bg-red-100 text-red-700', icon: XCircle },
  PAID: { label: 'Lunas', color: 'bg-green-100 text-green-700', icon: CheckCircle2 },
  PARTIAL: { label: 'Cicilan', color: 'bg-yellow-100 text-yellow-700', icon: AlertCircle },
};

// Mock booking data for demo
const mockBookings = {
  'BK-20241201-001': {
    id: 'BK-20241201-001',
    bookingCode: 'BK-20241201-001',
    customer: { name: 'Budi Santoso', phone: '0812-3456-7890' },
    vehicle: { type: 'suv', brand: 'Toyota', model: 'Fortuner', year: 2023, color: 'Hitam', plate: 'B 1234 ABC' },
    services: [
      { name: 'Cuci Lengkap + Wax', price: 250000, duration: '90 menit' },
      { name: 'Cuci Interior Saja', price: 120000, duration: '40 menit' },
    ],
    branch: { name: 'Cabang Utama - Sudirman' },
    slotTime: '09:00',
    bookingDate: '2024-12-01',
    status: 'CONFIRMED',
    paymentStatus: 'UNPAID',
    total: 370000,
    notes: 'Mohon hati-hati dengan cat body',
    createdAt: '2024-12-01 08:30:00',
  },
  'BK-20241201-002': {
    id: 'BK-20241201-002',
    bookingCode: 'BK-20241201-002',
    customer: { name: 'Siti Rahayu', phone: '0821-9876-5432' },
    vehicle: { type: 'mobil', brand: 'Honda', model: 'CR-V', year: 2022, color: 'Putih', plate: 'B 2345 DEF' },
    services: [{ name: 'Cuci Interior + Exterior', price: 180000, duration: '60 menit' }],
    branch: { name: 'Cabang Selatan - Thamrin' },
    slotTime: '10:30',
    bookingDate: '2024-12-01',
    status: 'WASHING',
    paymentStatus: 'PAID',
    total: 180000,
    notes: '',
    createdAt: '2024-12-01 09:45:00',
  },
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

function StatusBadge({ status }) {
  const config = statusConfig[status] || statusConfig.PENDING;
  const Icon = config.icon;
  return (
    <Badge variant="outline" className={cn(config.color, 'gap-1.5 font-medium')}>
      <Icon className={cn('h-3 w-3', config.iconColor)} />
      {config.label}
    </Badge>
  );
}

function PaymentStatusBadge({ status }) {
  const config = paymentStatusConfig[status] || paymentStatusConfig.UNPAID;
  const Icon = config.icon;
  return (
    <Badge variant="outline" className={cn(config.color, 'gap-1.5 font-medium')}>
      <Icon className="h-3 w-3" />
      {config.label}
    </Badge>
  );
}

function ScanQRScanner({ onScan, isActive, onError }) {
  const videoRef = useRef(null);
  const canvasRef = useRef(null);
  const scanIntervalRef = useRef(null);
  const html5QrcodeRef = useRef(null);

  useEffect(() => {
    if (!isActive) return;

    // Try to use html5-qrcode if available, otherwise fallback to native API
    const initScanner = async () => {
      try {
        // Check if html5-qrcode is available
        if (typeof window !== 'undefined' && window.Html5Qrcode) {
          html5QrcodeRef.current = new window.Html5Qrcode('qr-reader');
          
          await html5QrcodeRef.current.start(
            { facingMode: 'environment' },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
              onScan(decodedText);
              stopScanner();
            },
            (error) => {
              // Ignore scan errors (no QR code found)
            }
          );
        } else {
          // Fallback to native BarcodeDetector API
          await startNativeScanner();
        }
      } catch (err) {
        console.error('QR Scanner error:', err);
        onError?.('Gagal memulai kamera. Pastikan izin kamera diberikan.');
      }
    };

    const startNativeScanner = async () => {
      try {
        const stream = await navigator.mediaDevices.getUserMedia({ 
          video: { facingMode: 'environment' } 
        });
        
        if (videoRef.current) {
          videoRef.current.srcObject = stream;
          await videoRef.current.play();
          
          // Use BarcodeDetector API if available
          if ('BarcodeDetector' in window) {
            const detector = new BarcodeDetector({ formats: ['qr_code'] });
            
            scanIntervalRef.current = setInterval(async () => {
              if (videoRef.current && videoRef.current.readyState === videoRef.current.HAVE_ENOUGH_DATA) {
                try {
                  const barcodes = await detector.detect(videoRef.current);
                  if (barcodes.length > 0) {
                    onScan(barcodes[0].rawValue);
                    stopScanner();
                  }
                } catch (err) {
                  // Ignore detection errors
                }
              }
            }, 100);
          } else {
            // Canvas fallback - just show camera feed
            onError?.('Browser tidak mendukung QR scanning otomatis. Gunakan aplikasi kamera bawaan.');
          }
        }
      } catch (err) {
        console.error('Camera access error:', err);
        onError?.('Tidak dapat mengakses kamera. Periksa izin browser.');
      }
    };

    const stopScanner = () => {
      if (html5QrcodeRef.current) {
        html5QrcodeRef.current.stop().catch(() => {});
        html5QrcodeRef.current = null;
      }
      if (scanIntervalRef.current) {
        clearInterval(scanIntervalRef.current);
        scanIntervalRef.current = null;
      }
      if (videoRef.current && videoRef.current.srcObject) {
        videoRef.current.srcObject.getTracks().forEach(track => track.stop());
        videoRef.current.srcObject = null;
      }
    };

    initScanner();

    return () => stopScanner();
  }, [isActive, onScan, onError]);

  if (!isActive) return null;

  return (
    <div className="relative w-full aspect-square bg-black rounded-lg overflow-hidden">
      {/* html5-qrcode will create its own video element inside this div */}
      <div id="qr-reader" className="w-full h-full" />
      
      {/* Fallback video element for native API */}
      <video
        ref={videoRef}
        className="w-full h-full object-cover"
        playsInline
        muted
        style={{ display: 'none' }}
      />
      
      {/* Scanning overlay */}
      <div className="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
        <div className="relative w-64 h-64">
          <div className="absolute inset-0 border-2 border-primary/50 rounded-lg" />
          {/* Corner brackets */}
          <div className="absolute -top-2 -left-2 w-8 h-8 border-t-4 border-l-4 border-primary rounded-tl-lg" />
          <div className="absolute -top-2 -right-2 w-8 h-8 border-t-4 border-r-4 border-primary rounded-tr-lg" />
          <div className="absolute -bottom-2 -left-2 w-8 h-8 border-b-4 border-l-4 border-primary rounded-bl-lg" />
          <div className="absolute -bottom-2 -right-2 w-8 h-8 border-b-4 border-r-4 border-primary rounded-br-lg" />
        </div>
        <p className="mt-4 text-white text-sm bg-black/50 px-3 py-1 rounded">
          Arahkan QR code ke dalam kotak
        </p>
      </div>
    </div>
  );
}

function BookingDetail({ booking, onConfirmStart, onRecordCashPayment, onClose }) {
  if (!booking) return null;

  const statusConfigItem = statusConfig[booking.status] || statusConfig.PENDING;
  const paymentConfig = paymentStatusConfig[booking.paymentStatus] || paymentStatusConfig.UNPAID;
  const PaymentIcon = paymentConfig.icon;
  const StatusIcon = statusConfigItem.icon;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 lg:p-0">
      <Card className="w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-slide-up">
        <CardHeader className="border-b">
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <QrCode className="h-5 w-5 text-yellow-500" />
              Detail Booking
            </CardTitle>
            <Button variant="ghost" size="icon" onClick={onClose}>
              <XCircle className="h-5 w-5" />
            </Button>
          </div>
        </CardHeader>
        <CardContent className="space-y-4">
          {/* Booking Code & Status */}
          <div className="flex items-start justify-between gap-4 p-3 bg-muted/50 rounded-lg">
            <div>
              <p className="font-montserrat font-bold text-lg">{booking.bookingCode}</p>
              <p className="text-sm text-muted-foreground">{booking.branch.name}</p>
            </div>
            <div className="flex flex-col items-end gap-1">
              <StatusBadge status={booking.status} />
              <PaymentStatusBadge status={booking.paymentStatus} />
            </div>
          </div>

          {/* Customer Info */}
          <div className="grid gap-3 md:grid-cols-2">
            <div className="space-y-1">
              <p className="text-xs text-muted-foreground">Pelanggan</p>
              <div className="flex items-center gap-2">
                <User className="h-4 w-4 text-muted-foreground" />
                <p className="font-medium">{booking.customer.name}</p>
              </div>
              <p className="text-sm text-muted-foreground ml-6">{booking.customer.phone}</p>
            </div>
            <div className="space-y-1">
              <p className="text-xs text-muted-foreground">Kendaraan</p>
              <div className="flex items-center gap-2">
                <VehicleBadge type={booking.vehicle.type} />
              </div>
              <p className="text-sm font-jetbrains-mono font-medium ml-6">{booking.vehicle.plate}</p>
              <p className="text-xs text-muted-foreground ml-6">
                {booking.vehicle.brand} {booking.vehicle.model} ({booking.vehicle.year}) - {booking.vehicle.color}
              </p>
            </div>
          </div>

          {/* Schedule Info */}
          <div className="grid gap-3 md:grid-cols-3 p-3 bg-muted/50 rounded-lg">
            <div className="flex items-center gap-2">
              <Calendar className="h-4 w-4 text-muted-foreground" />
              <div>
                <p className="text-xs text-muted-foreground">Tanggal</p>
                <p className="font-medium">{new Date(booking.bookingDate).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })}</p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <Clock className="h-4 w-4 text-muted-foreground" />
              <div>
                <p className="text-xs text-muted-foreground">Slot Waktu</p>
                <p className="font-medium">{booking.slotTime}</p>
              </div>
            </div>
            <div className="flex items-center gap-2">
              <CreditCard className="h-4 w-4 text-muted-foreground" />
              <div>
                <p className="text-xs text-muted-foreground">Total</p>
                <p className="font-montserrat font-bold text-primary">Rp {booking.total.toLocaleString('id-ID')}</p>
              </div>
            </div>
          </div>

          {/* Services */}
          <div className="space-y-2">
            <p className="text-xs text-muted-foreground">Layanan</p>
            <div className="space-y-2">
              {booking.services.map((service, index) => (
                <div key={index} className="flex items-center justify-between p-3 border rounded-lg">
                  <div className="flex-1">
                    <p className="font-medium">{service.name}</p>
                    <p className="text-xs text-muted-foreground flex items-center gap-1">
                      <Clock className="h-3 w-3" /> {service.duration}
                    </p>
                  </div>
                  <p className="font-medium text-primary">Rp {service.price.toLocaleString('id-ID')}</p>
                </div>
              ))}
            </div>
          </div>

          {/* Notes */}
          {booking.notes && (
            <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
              <p className="text-xs text-yellow-800 flex items-center gap-1">
                <AlertCircle className="h-3 w-3" /> Catatan: {booking.notes}
              </p>
            </div>
          )}

          {/* Actions */}
          <div className="flex gap-2 pt-2 border-t">
            {booking.paymentStatus === 'UNPAID' && booking.status === 'CONFIRMED' && (
              <Button className="flex-1 gap-2 bg-emerald-600 hover:bg-emerald-700" onClick={onRecordCashPayment}>
                <CreditCard className="h-4 w-4" />
                <span>Catat Bayar Tunai</span>
              </Button>
            )}
            {booking.status === 'CONFIRMED' && booking.paymentStatus === 'PAID' && (
              <Button className="flex-1 gap-2 bg-primary hover:bg-primary/90" onClick={onConfirmStart}>
                <CheckCircle2 className="h-4 w-4" />
                <span>Konfirmasi & Mulai Cuci</span>
              </Button>
            )}
            {booking.status === 'WASHING' && (
              <Button className="flex-1 gap-2 bg-orange-600 hover:bg-orange-700" variant="outline">
                <Loader2 className="h-4 w-4 animate-spin" />
                <span>Sedang Dicuci</span>
              </Button>
            )}
            {booking.status === 'READY' && (
              <Button className="flex-1 gap-2 bg-green-600 hover:bg-green-700" variant="outline">
                <CheckCircle2 className="h-4 w-4" />
                <span>Siap Diambil</span>
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

export default function ScanQRPage() {
  const [isScanning, setIsScanning] = useState(false);
  const [scannedCode, setScannedCode] = useState(null);
  const [booking, setBooking] = useState(null);
  const [error, setError] = useState(null);
  const [showManualInput, setShowManualInput] = useState(false);
  const [manualCode, setManualCode] = useState('');
  const [torchEnabled, setTorchEnabled] = useState(false);

  const { data, setData, post, processing } = useForm({
    booking_code: '',
    action: '', // 'start_wash' | 'record_cash'
  });

  const handleScan = (code) => {
    setScannedCode(code);
    setIsScanning(false);
    
    // Look up booking
    const foundBooking = mockBookings[code];
    if (foundBooking) {
      setBooking(foundBooking);
      setError(null);
    } else {
      setError(`Booking dengan kode "${code}" tidak ditemukan`);
      setBooking(null);
    }
  };

  const handleManualSubmit = (e) => {
    e.preventDefault();
    if (manualCode.trim()) {
      handleScan(manualCode.trim().toUpperCase());
      setShowManualInput(false);
      setManualCode('');
    }
  };

  const handleConfirmStart = () => {
    if (!booking) return;
    setData('booking_code', booking.bookingCode);
    setData('action', 'start_wash');
    post('/kasir/bookings/scan-action', {
      onSuccess: () => {
        alert('Pengerjaan dimulai!');
        setBooking({ ...booking, status: 'WASHING' });
      },
    });
  };

  const handleRecordCashPayment = () => {
    if (!booking) return;
    setData('booking_code', booking.bookingCode);
    setData('action', 'record_cash');
    post('/kasir/bookings/scan-action', {
      onSuccess: () => {
        alert('Pembayaran tunai dicatat!');
        setBooking({ ...booking, paymentStatus: 'PAID', status: 'CONFIRMED' });
      },
    });
  };

  const resetScanner = () => {
    setScannedCode(null);
    setBooking(null);
    setError(null);
    setIsScanning(true);
  };

  return (
    <MainLayout pageTitle="Scan QR Booking" pageDescription="Scan QR code untuk verifikasi booking pelanggan">
      <div className="space-y-6">
        <div className="flex items-center gap-4">
          <Link href="/kasir/antrian">
            <Button variant="ghost" size="icon">
              <ArrowLeft className="h-5 w-5" />
            </Button>
          </Link>
          <div>
            <h1 className="font-montserrat text-2xl font-bold">Scan QR Booking</h1>
            <p className="text-sm text-muted-foreground">Scan QR code pada tiket booking pelanggan</p>
          </div>
        </div>

        {error && !booking && (
          <div className="p-4 bg-red-50 border border-red-200 rounded-lg flex items-center gap-3 text-red-700">
            <AlertCircle className="h-5 w-5 flex-shrink-0" />
            <p className="text-sm">{error}</p>
            <Button variant="ghost" size="sm" className="ml-auto" onClick={resetScanner}>
              Coba Lagi
            </Button>
          </div>
        )}

        {!booking && (
          <div className="grid lg:grid-cols-2 gap-6">
            {/* Scanner Panel */}
            <Card className="lg:col-span-1">
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <QrCode className="h-5 w-5 text-yellow-500" />
                  Scanner QR Code
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                {!isScanning ? (
                  <div className="space-y-4 text-center">
                    <div className="w-48 h-48 mx-auto rounded-full bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center">
                      <QrCode className="h-24 w-24 text-primary/30" />
                    </div>
                    <div>
                      <h3 className="font-medium text-lg">Siap Memindai</h3>
                      <p className="text-sm text-muted-foreground mt-1">
                        Arahkan kamera ke QR code pada tiket booking pelanggan
                      </p>
                    </div>
                    <Button 
                      className="w-full gap-2 bg-primary hover:bg-primary/90 text-primary-foreground py-3" 
                      size="lg"
                      onClick={() => setIsScanning(true)}
                    >
                      <Camera className="h-5 w-5" />
                      <span className="font-montserrat font-semibold">Mulai Scan</span>
                    </Button>
                    <Button 
                      variant="outline" 
                      className="w-full gap-2" 
                      onClick={() => setShowManualInput(true)}
                    >
                      <ScanLine className="h-4 w-4" />
                      <span>Input Manual Kode Booking</span>
                    </Button>
                  </div>
                ) : (
                  <div className="space-y-4">
                    <ScanQRScanner 
                      onScan={handleScan} 
                      isActive={isScanning} 
                      onError={setError}
                    />
                    <div className="flex gap-2">
                      <Button variant="outline" className="flex-1" onClick={() => setIsScanning(false)}>
                        Batal
                      </Button>
                      <Button variant="outline" className="flex-1" onClick={() => setTorchEnabled(!torchEnabled)}>
                        <span className="flex items-center gap-1">
                          {torchEnabled ? '🔦' : '💡'} Torch
                        </span>
                      </Button>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>

            {/* Instructions Panel */}
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center gap-2">
                  <ScanLine className="h-5 w-5 text-blue-500" />
                  Cara Penggunaan
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="space-y-4">
                  <div className="flex gap-3">
                    <div className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">1</div>
                    <div>
                      <p className="font-medium">Klik "Mulai Scan"</p>
                      <p className="text-sm text-muted-foreground">Izinkan akses kamera saat diminta browser</p>
                    </div>
                  </div>
                  <div className="flex gap-3">
                    <div className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">2</div>
                    <div>
                      <p className="font-medium">Arahkan ke QR Code</p>
                      <p className="text-sm text-muted-foreground">Posisikan QR code di dalam kotak panduan di layar</p>
                    </div>
                  </div>
                  <div className="flex gap-3">
                    <div className="flex-shrink-0 w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold">3</div>
                    <div>
                      <p className="font-medium">Tunggu Deteksi</p>
                      <p className="text-sm text-muted-foreground">Sistem akan otomatis mendeteksi dan menampilkan detail booking</p>
                    </div>
                  </div>
                </div>

                <div className="p-3 bg-muted/50 rounded-lg border">
                  <p className="text-sm font-medium mb-2">Tips:</p>
                  <ul className="text-sm text-muted-foreground space-y-1">
                    <li>• Pastikan pencahayaan cukup terang</li>
                    <li>• Jaga jarak 10-20 cm dari QR code</li>
                    <li>• Gunakan tombol Torch untuk cahaya tambahan</li>
                    <li>• Kode booking format: BK-YYYYMMDD-XXX</li>
                  </ul>
                </div>

                <div className="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <p className="text-sm font-medium text-blue-800 mb-1">Input Manual</p>
                  <p className="text-sm text-blue-700">
                    Jika kamera tidak berfungsi, gunakan tombol "Input Manual Kode Booking" 
                    dan masukkan kode seperti: <code className="font-montserrat bg-white px-1 rounded">BK-20241201-001</code>
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        )}

        {/* Booking Detail Modal */}
        <BookingDetail
          booking={booking}
          onConfirmStart={handleConfirmStart}
          onRecordCashPayment={handleRecordCashPayment}
          onClose={() => {
            setBooking(null);
            setScannedCode(null);
          }}
        />

        {/* Manual Input Modal */}
        {showManualInput && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <Card className="w-full max-w-md mx-4">
              <CardHeader>
                <CardTitle className="flex items-center justify-between">
                  <ScanLine className="h-5 w-5 text-blue-500" />
                  Input Manual Kode Booking
                  <button onClick={() => setShowManualInput(false)} className="text-muted-foreground hover:text-foreground">✕</button>
                </CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <form onSubmit={handleManualSubmit}>
                  <div className="space-y-2">
                    <Label>Kode Booking</Label>
                    <Input
                      placeholder="BK-20241201-001"
                      value={manualCode}
                      onChange={(e) => setManualCode(e.target.value.toUpperCase())}
                      className="font-montserrat text-lg tracking-wider text-center"
                      autoFocus
                      autoComplete="off"
                    />
                  </div>
                  <div className="flex gap-2">
                    <Button variant="outline" className="flex-1" type="button" onClick={() => setShowManualInput(false)}>
                      Batal
                    </Button>
                    <Button className="flex-1" type="submit">
                      Cari Booking
                    </Button>
                  </div>
                </form>
              </CardContent>
            </Card>
          </div>
        )}
      </div>
    </MainLayout>
  );
}