import * as React from 'react';
import { MainLayout } from '@/Components/Layouts/MainLayout';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/UI';
import { Button } from '@/Components/UI';
import { Badge } from '@/Components/UI';
import { Input } from '@/Components/UI';
import { Label } from '@/Components/UI';
import { Link, useForm } from '@inertiajs/react';
import { useState, useEffect, useRef, useCallback } from 'react';
import { cn } from '@/Lib/utils';
import {
  ArrowLeft,
  CheckCircle,
  Loader2,
  Camera,
  Image,
  Clock,
  AlertCircle,
  CheckCircle2,
  XCircle,
  Trash2,
  RotateCcw,
  Download,
  WifiOff,
  Wifi,
  Battery,
  Settings,
  User,
  Car,
  Truck,
  Bike,
  MapPin,
  Phone,
  MessageSquare,
  Bell,
  Menu,
  X,
} from 'lucide-react';

// Vehicle type configuration
const vehicleTypeConfig = {
  motor: { label: 'Motor', icon: Bike, color: 'bg-purple-100 text-purple-700 border-purple-200', iconColor: 'text-purple-600' },
  mobil: { label: 'Mobil', icon: Car, color: 'bg-blue-100 text-blue-700 border-blue-200', iconColor: 'text-blue-600' },
  suv: { label: 'SUV', icon: Car, color: 'bg-green-100 text-green-700 border-green-200', iconColor: 'text-green-600' },
  truck: { label: 'Truk', icon: Truck, color: 'bg-orange-100 text-orange-700 border-orange-200', iconColor: 'text-orange-600' },
};

const statusConfig = {
  ASSIGNED: { label: 'DITUGASKAN', color: 'bg-blue-100 text-blue-700 border-blue-200', borderColor: 'border-blue-400', icon: MapPin, iconColor: 'text-blue-600' },
  WASHING: { label: 'SEDANG DICUCI', color: 'bg-orange-100 text-orange-700 border-orange-200', borderColor: 'border-orange-500', icon: Loader2, iconColor: 'text-orange-600' },
  DRYING: { label: 'PENGERINGAN', color: 'bg-purple-100 text-purple-700 border-purple-200', borderColor: 'border-purple-500', icon: Loader2, iconColor: 'text-purple-600' },
  READY: { label: 'SIAP DIAMBIL', color: 'bg-green-100 text-green-700 border-green-200', borderColor: 'border-green-500', icon: CheckCircle2, iconColor: 'text-green-600' },
  COMPLETED: { label: 'SELESAI', color: 'bg-emerald-100 text-emerald-700 border-emerald-200', borderColor: 'border-emerald-500', icon: CheckCircle2, iconColor: 'text-emerald-600' },
};

// Mock operator tasks
const mockTasks = [
  {
    id: 'TASK-001',
    bookingCode: 'BK-20241201-001',
    customer: { name: 'Budi Santoso', phone: '0812-3456-7890' },
    vehicle: { type: 'suv', brand: 'Toyota', model: 'Fortuner', year: 2023, color: 'Hitam', plate: 'B 1234 ABC' },
    services: [
      { name: 'Cuci Lengkap + Wax', price: 250000, duration: 90 },
      { name: 'Cuci Interior Saja', price: 120000, duration: 40 },
    ],
    bay: 'Bay 1',
    assignedAt: '2024-12-01 08:30:00',
    status: 'ASSIGNED',
    estimatedDuration: 130,
    actualDuration: 0,
    progressPhotos: [],
    finalPhotos: [],
    notes: 'Mohon hati-hati dengan cat body',
  },
  {
    id: 'TASK-002',
    bookingCode: 'BK-20241201-002',
    customer: { name: 'Siti Rahayu', phone: '0821-9876-5432' },
    vehicle: { type: 'mobil', brand: 'Honda', model: 'CR-V', year: 2022, color: 'Putih', plate: 'B 2345 DEF' },
    services: [{ name: 'Cuci Interior + Exterior', price: 180000, duration: 60 }],
    bay: 'Bay 2',
    assignedAt: '2024-12-01 09:45:00',
    status: 'WASHING',
    estimatedDuration: 60,
    actualDuration: 25,
    progressPhotos: ['https://picsum.photos/seed/task2-progress/400/300'],
    finalPhotos: [],
    notes: '',
  },
  {
    id: 'TASK-003',
    bookingCode: 'BK-20241201-003',
    customer: { name: 'Ahmad Wijaya', phone: '0813-5555-6666' },
    vehicle: { type: 'motor', brand: 'Honda', model: 'Beat', year: 2021, color: 'Merah', plate: 'B 3456 GHI' },
    services: [{ name: 'Cuci Biasa', price: 100000, duration: 45 }],
    bay: 'Bay 3',
    assignedAt: '2024-12-01 10:30:00',
    status: 'DRYING',
    estimatedDuration: 45,
    actualDuration: 40,
    progressPhotos: ['https://picsum.photos/seed/task3-progress/400/300'],
    finalPhotos: [],
    notes: 'Motor lama, perlahan',
  },
  {
    id: 'TASK-004',
    bookingCode: 'BK-20241201-004',
    customer: { name: 'Dewi Lestari', phone: '0857-1111-2222' },
    vehicle: { type: 'suv', brand: 'Mitsubishi', model: 'Pajero Sport', year: 2023, color: 'Silver', plate: 'B 4567 JKL' },
    services: [{ name: 'Cuci Lengkap + Poles', price: 300000, duration: 120 }],
    bay: 'Bay 1',
    assignedAt: '2024-12-01 11:00:00',
    status: 'READY',
    estimatedDuration: 120,
    actualDuration: 115,
    progressPhotos: ['https://picsum.photos/seed/task4-progress/400/300'],
    finalPhotos: ['https://picsum.photos/seed/task4-final/400/300'],
    notes: '',
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
  const config = statusConfig[status] || statusConfig.ASSIGNED;
  const Icon = config.icon;
  return (
    <Badge variant="outline" className={cn(config.color, config.borderColor, 'gap-1.5 font-medium')}>
      <Icon className={cn('h-3 w-3', config.iconColor)} />
      {config.label}
    </Badge>
  );
}

function formatDuration(minutes) {
  if (minutes < 60) return `${minutes} menit`;
  const hours = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return mins > 0 ? `${hours} jam ${mins} menit` : `${hours} jam`;
}

function formatTimeAgo(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diff = Math.floor((now - date) / 1000 / 60); // minutes
  if (diff < 1) return 'Baru saja';
  if (diff < 60) return `${diff} menit lalu`;
  const hours = Math.floor(diff / 60);
  if (hours < 24) return `${hours} jam lalu`;
  return `${Math.floor(hours / 24)} hari lalu`;
}

function PhotoUpload({ 
  label, 
  photos, 
  onAddPhoto, 
  onRemovePhoto, 
  maxPhotos = 5,
  required = false,
  acceptedTypes = 'image/*'
}) {
  const [previews, setPreviews] = useState([]);
  const fileInputRef = useRef(null);

  const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    files.forEach(file => {
      if (previews.length + photos.length >= maxPhotos) return;
      const reader = new FileReader();
      reader.onload = (e) => {
        setPreviews(prev => [...prev, { file, preview: e.target.result }]);
      };
      reader.readAsDataURL(file);
    });
    e.target.value = '';
  };

  const handleUpload = async () => {
    for (const { file, preview } of previews) {
      // Simulate upload
      await new Promise(resolve => setTimeout(resolve, 500));
      onAddPhoto(preview); // In real app, upload to server and get URL
    }
    setPreviews([]);
  };

  const allPhotos = [...photos, ...previews.map(p => p.preview)];

  return (
    <div className="space-y-3">
      <div className="flex items-center justify-between">
        <Label className="mb-0">{label} {required && <span className="text-red-500">*</span>}</Label>
        <span className="text-xs text-muted-foreground">{allPhotos.length}/{maxPhotos}</span>
      </div>
      
      {/* Existing Photos */}
      {allPhotos.length > 0 && (
        <div className="flex gap-2 overflow-x-auto pb-2">
          {allPhotos.map((photo, index) => (
            <div key={index} className="relative flex-shrink-0 w-24 h-24 rounded-lg overflow-hidden border">
              <img src={photo} alt={`${label} ${index + 1}`} className="w-full h-full object-cover" />
              <button
                type="button"
                onClick={() => index < photos.length ? onRemovePhoto(index) : setPreviews(prev => prev.filter((_, i) => i !== index - photos.length))}
                className="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500/90 text-white flex items-center justify-center hover:bg-red-600"
              >
                <X className="h-3 w-3" />
              </button>
              {index >= photos.length && (
                <div className="absolute inset-0 bg-black/50 flex items-center justify-center">
                  <Loader2 className="h-5 w-5 text-white animate-spin" />
                </div>
              )}
            </div>
          ))}
        </div>
      )}

      {/* Upload Area */}
      {allPhotos.length < maxPhotos && (
        <label className="flex flex-col items-center justify-center p-4 border-2 border-dashed border-border rounded-lg cursor-pointer hover:border-primary/50 transition-colors">
          <input
            ref={fileInputRef}
            type="file"
            accept={acceptedTypes}
            multiple
            onChange={handleFileSelect}
            className="hidden"
            id={`upload-${label.replace(/\s+/g, '-')}`}
          />
          <Camera className="h-8 w-8 text-muted-foreground mb-2" />
          <span className="text-sm text-muted-foreground">Klik atau tarik foto di sini</span>
          <span className="text-xs text-muted-foreground">Maks {maxPhotos - allPhotos.length} foto lagi</span>
        </label>
      )}

      {previews.length > 0 && (
        <Button variant="outline" size="sm" onClick={handleUpload} className="w-full">
          <Loader2 className="h-4 w-4 mr-2" />
          Upload {previews.length} Foto
        </Button>
      )}
    </div>
  );
}

function TimerDisplay({ seconds, isRunning, warningThreshold = 300 }) {
  const hours = Math.floor(seconds / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  const secs = seconds % 60;
  
  const isWarning = seconds <= warningThreshold && seconds > 0;
  const isDanger = seconds <= 60 && seconds > 0;
  
  return (
    <div className={cn(
      'font-jetbrains-mono text-3xl font-bold tabular-nums font-montserrat',
      isDanger ? 'text-red-500 animate-pulse' : isWarning ? 'text-yellow-500' : 'text-foreground'
    )}>
    {hours > 0 ? `${hours.toString().padStart(2, '0')}:` : ''}
    {minutes.toString().padStart(2, '0')}:{secs.toString().padStart(2, '0')}
  </div>
);
}

function TaskCard({ task, onStart, onProgress, onComplete, onViewDetails }) {
  const config = statusConfig[task.status] || statusConfig.ASSIGNED;
  const Icon = config.icon;
  const [actualDuration, setActualDuration] = useState(task.actualDuration || 0);
  const [timerRunning, setTimerRunning] = useState(task.status === 'WASHING' || task.status === 'DRYING');

  // Timer effect
  useEffect(() => {
    if (!timerRunning) return;
    const interval = setInterval(() => {
      setActualDuration(prev => prev + 1);
    }, 1000);
    return () => clearInterval(interval);
  }, [timerRunning]);

  const estimatedSeconds = task.estimatedDuration * 60;
  const actualSeconds = actualDuration;
  const progress = estimatedSeconds > 0 ? Math.min(100, (actualSeconds / estimatedSeconds) * 100) : 0;
  const remaining = Math.max(0, estimatedSeconds - actualSeconds);
  const isOverdue = actualSeconds > estimatedSeconds;

  return (
    <Card className={cn('border-l-4', config.borderColor)}>
      <div className={`absolute top-0 left-0 right-0 h-1 bg-gradient-to-r ${config.borderColor.replace('border-', 'from-')} ${config.borderColor.replace('border-', 'to-')}`} />
      
      <CardContent className="p-4 relative">
        {/* Header */}
        <div className="flex items-start justify-between gap-3 mb-3">
          <div className="flex items-center gap-2">
            <VehicleBadge type={task.vehicle.type} />
            <StatusBadge status={task.status} />
          </div>
          <div className="flex items-center gap-1">
            <span className="font-montserrat font-bold text-lg text-primary">{task.bay}</span>
          </div>
        </div>

        {/* Booking Code & Customer */}
        <div className="mb-3">
          <p className="font-montserrat font-bold text-lg text-foreground">{task.bookingCode}</p>
          <div className="flex items-center gap-2 text-sm text-muted-foreground mt-1">
            <User className="h-3 w-3" />
            <span>{task.customer.name}</span>
            <span>•</span>
            <Phone className="h-3 w-3" />
            <span>{task.customer.phone}</span>
          </div>
        </div>

        {/* Vehicle Info */}
        <div className="mb-3 p-2 bg-muted/50 rounded-lg">
          <div className="flex items-center gap-2">
            <span className="font-jetbrains-mono font-medium text-sm">{task.vehicle.plate}</span>
            <span className="text-xs text-muted-foreground">
              {task.vehicle.brand} {task.vehicle.model} ({task.vehicle.year}) - {task.vehicle.color}
            </span>
          </div>
        </div>

        {/* Services */}
        <div className="mb-3">
          <p className="text-xs text-muted-foreground mb-1">Layanan:</p>
          <div className="space-y-1">
            {task.services.map((service, index) => (
              <div key={index} className="flex justify-between text-sm">
                <span className="text-muted-foreground">{service.name}</span>
                <span className="font-medium">Rp {service.price.toLocaleString('id-ID')}</span>
              </div>
            ))}
          </div>
        </div>

        {/* Timer & Progress */}
        {['WASHING', 'DRYING'].includes(task.status) && (
          <div className="mb-3 p-3 bg-background border rounded-lg">
            <div className="flex items-center justify-between mb-2">
              <div className="flex items-center gap-2">
                <Clock className={cn('h-4 w-4', isOverdue ? 'text-red-500' : 'text-muted-foreground')} />
                <span className="text-xs font-medium text-muted-foreground">
                  {task.status === 'WASHING' ? 'Waktu Cuci' : 'Waktu Kering'}
                </span>
              </div>
              <TimerDisplay seconds={remaining} isRunning={timerRunning} />
            </div>
            
            {/* Progress Bar */}
            <div className="mb-2">
              <div className="flex justify-between text-xs mb-1">
                <span className="text-muted-foreground">Progress</span>
                <span className="font-medium">{Math.round(progress)}%</span>
              </div>
              <div className="h-2 bg-muted rounded-full overflow-hidden">
                <div 
                  className={cn('h-full rounded-full transition-all duration-300', isOverdue ? 'bg-red-500' : 'bg-primary')}
                  style={{ width: `${Math.min(100, progress)}%` }}
                />
              </div>
            </div>
            
            <div className="flex items-center justify-between text-xs text-muted-foreground">
              <span>Estimasi: {formatDuration(task.estimatedDuration)}</span>
              <span>Aktual: {formatDuration(Math.round(actualSeconds / 60))}</span>
              {isOverdue && <span className="text-red-500 font-medium">LEWAT ESTIMASI</span>}
            </div>
          </div>
        )}

        {/* Actions */}
        <div className="flex gap-2 pt-2 border-t">
          {task.status === 'ASSIGNED' && (
            <Button 
              className="flex-1 gap-2 bg-primary hover:bg-primary/90" 
              onClick={() => onStart(task.id)}
              size="sm"
            >
              <Icon className="h-4 w-4" />
              Mulai Cuci
            </Button>
          )}
          
          {task.status === 'WASHING' && (
            <>
              <Button 
                variant="outline" 
                className="flex-1 gap-1" 
                onClick={() => onProgress(task.id, 'progress')}
                size="sm"
              >
                <Camera className="h-3 w-3" />
                Foto Progres
              </Button>
              <Button 
                className="flex-1 gap-1 bg-orange-600 hover:bg-orange-700" 
                onClick={() => onComplete(task.id, 'DRYING')}
                size="sm"
              >
                <RotateCcw className="h-3 w-3" />
                Selesai Cuci
              </Button>
            </>
          )}
          
          {task.status === 'DRYING' && (
            <>
              <Button 
                variant="outline" 
                className="flex-1 gap-1" 
                onClick={() => onProgress(task.id, 'progress')}
                size="sm"
              >
                <Camera className="h-3 w-3" />
                Foto Progres
              </Button>
              <Button 
                className="flex-1 gap-1 bg-purple-600 hover:bg-purple-700" 
                onClick={() => onComplete(task.id, 'READY')}
                size="sm"
              >
                <CheckCircle2 className="h-3 w-3" />
                Selesai Kering
              </Button>
            </>
          )}
          
          {task.status === 'READY' && (
            <Button 
              className="flex-1 gap-2 bg-green-600 hover:bg-green-700" 
              onClick={() => onComplete(task.id, 'COMPLETED')}
              size="sm"
            >
              <CheckCircle2 className="h-4 w-4" />
              <span className="font-montserrat font-semibold">Tandai Selesai</span>
            </Button>
          )}

          {task.status === 'COMPLETED' && (
            <Badge variant="outline" className="flex-1 flex items-center justify-center bg-emerald-50 border-emerald-200 text-emerald-700">
              <CheckCircle2 className="h-3 w-3 mr-1" />
              SELESAI
            </Badge>
          )}

          <Button 
            variant="ghost" 
            size="icon" 
            className="text-muted-foreground hover:text-foreground"
            onClick={() => onViewDetails(task.id)}
          >
            <MessageSquare className="h-4 w-4" />
          </Button>
        </div>
      </CardContent>
    </Card>
  );
}

function TaskDetailModal({ task, isOpen, onClose, onStart, onUploadProgress, onUploadFinal, onComplete }) {
  if (!isOpen || !task) return null;

  const [activeTab, setActiveTab] = useState('progress');
  const [progressPhotos, setProgressPhotos] = useState(task.progressPhotos || []);
  const [finalPhotos, setFinalPhotos] = useState(task.finalPhotos || []);
  const [notes, setNotes] = useState('');

  const handleAddProgressPhoto = useCallback((photo) => {
    setProgressPhotos(prev => [...prev, photo]);
  }, []);

  const handleRemoveProgressPhoto = useCallback((index) => {
    setProgressPhotos(prev => prev.filter((_, i) => i !== index));
  }, []);

  const handleAddFinalPhoto = useCallback((photo) => {
    setFinalPhotos(prev => [...prev, photo]);
  }, []);

  const handleRemoveFinalPhoto = useCallback((index) => {
    setFinalPhotos(prev => prev.filter((_, i) => i !== index));
  }, []);

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <Card className="w-full max-w-2xl max-h-[90vh] overflow-y-auto animate-slide-up">
        <CardHeader className="border-b sticky top-0 bg-background z-10">
          <div className="flex items-center justify-between">
            <CardTitle className="flex items-center gap-2">
              <MapPin className="h-5 w-5 text-primary" />
              Detail Tugas - {task.bookingCode}
            </CardTitle>
            <Button variant="ghost" size="icon" onClick={onClose}>
              <X className="h-5 w-5" />
            </Button>
          </div>
        </CardHeader>
        
        <CardContent className="space-y-4">
          {/* Tabs */}
          <div className="flex border-b">
            {[
              { id: 'progress', label: 'Progres', icon: Loader2 },
              { id: 'photos', label: 'Foto', icon: Image },
              { id: 'info', label: 'Info', icon: MessageSquare },
            ].map(tab => (
              <Button
                key={tab.id}
                variant="ghost"
                className="flex-1 gap-2 px-3 py-2 text-sm"
                onClick={() => setActiveTab(tab.id)}
              >
                <tab.icon className="h-4 w-4" />
                {tab.label}
              </Button>
            ))}
          </div>

          {/* Progress Tab */}
          {activeTab === 'progress' && (
            <div className="space-y-4">
              <div className="p-3 bg-muted/50 rounded-lg">
                <div className="flex items-center justify-between mb-2">
                  <span className="text-sm text-muted-foreground">Status</span>
                  <StatusBadge status={task.status} />
                </div>
                <div className="flex items-center justify-between mb-2">
                  <span className="text-sm text-muted-foreground">Bay</span>
                  <span className="font-montserrat font-bold text-lg text-primary">{task.bay}</span>
                </div>
              </div>

              {['WASHING', 'DRYING'].includes(task.status) && (
                <div className="p-4 bg-primary/5 border border-primary/20 rounded-lg">
                  <div className="flex items-center justify-between mb-2">
                    <span className="font-medium">Timer {task.status === 'WASHING' ? 'Cuci' : 'Kering'}</span>
                    <TimerDisplay 
                      seconds={Math.max(0, task.estimatedDuration * 60 - (task.actualDuration || 0))} 
                      isRunning={true} 
                    />
                  </div>
                  <div className="h-3 bg-muted rounded-full overflow-hidden">
                    <div 
                      className="h-full bg-primary rounded-full transition-all duration-300"
                      style={{ width: `${Math.min(100, ((task.actualDuration || 0) / (task.estimatedDuration * 60)) * 100)}%` }}
                    />
                  </div>
                </div>
              )}

              <div className="space-y-2">
                <Label className="text-sm font-medium">Catatan Pengerjaan</Label>
                <textarea
                  value={notes}
                  onChange={(e) => setNotes(e.target.value)}
                  placeholder="Catatan tambahan saat pengerjaan..."
                  rows={3}
                  className="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-ring focus:border-transparent"
                />
              </div>

              <div className="flex gap-2 pt-2 border-t">
                {task.status === 'ASSIGNED' && (
                  <Button className="flex-1 gap-2" onClick={() => onStart(task.id)}>
                    <Loader2 className="h-4 w-4" />
                    Mulai Pengerjaan
                  </Button>
                )}
                {task.status === 'WASHING' && (
                  <Button className="flex-1 gap-2 bg-orange-600 hover:bg-orange-700" onClick={() => onComplete(task.id, 'DRYING')}>
                    <RotateCcw className="h-4 w-4" />
                    Selesai Cuci → Kering
                  </Button>
                )}
                {task.status === 'DRYING' && (
                  <Button className="flex-1 gap-2 bg-purple-600 hover:bg-purple-700" onClick={() => onComplete(task.id, 'READY')}>
                    <CheckCircle2 className="h-4 w-4" />
                    Selesai Kering → Siap
                  </Button>
                )}
                {task.status === 'READY' && (
                  <Button className="flex-1 gap-2 bg-green-600 hover:bg-green-700" onClick={() => onComplete(task.id, 'COMPLETED')}>
                    <CheckCircle2 className="h-4 w-4" />
                    Tandai Selesai
                  </Button>
                )}
              </div>
            </div>
          )}

          {/* Photos Tab */}
          {activeTab === 'photos' && (
            <div className="space-y-4">
              <div>
                <Label className="text-sm font-medium">Foto Progres (saat pengerjaan)</Label>
                <PhotoUpload
                  label="Foto Progres"
                  photos={progressPhotos}
                  onAddPhoto={handleAddProgressPhoto}
                  onRemovePhoto={handleRemoveProgressPhoto}
                  maxPhotos={10}
                />
              </div>
              <div>
                <Label className="text-sm font-medium">Foto Final (hasil akhir)</Label>
                <PhotoUpload
                  label="Foto Final"
                  photos={finalPhotos}
                  onAddPhoto={handleAddFinalPhoto}
                  onRemovePhoto={handleRemoveFinalPhoto}
                  maxPhotos={5}
                  required={task.status === 'READY' || task.status === 'COMPLETED'}
                />
              </div>
            </div>
          )}

          {/* Info Tab */}
          {activeTab === 'info' && (
            <div className="space-y-4">
              <div className="grid gap-3 md:grid-cols-2">
                <div className="p-3 bg-muted/50 rounded-lg">
                  <p className="text-xs text-muted-foreground">Pelanggan</p>
                  <p className="font-medium">{task.customer.name}</p>
                  <p className="text-sm text-muted-foreground">{task.customer.phone}</p>
                </div>
                <div className="p-3 bg-muted/50 rounded-lg">
                  <p className="text-xs text-muted-foreground">Kendaraan</p>
                  <div className="flex items-center gap-2">
                    <VehicleBadge type={task.vehicle.type} />
                  </div>
                  <p className="font-jetbrains-mono font-medium mt-1">{task.vehicle.plate}</p>
                  <p className="text-sm text-muted-foreground">
                    {task.vehicle.brand} {task.vehicle.model} ({task.vehicle.year}) - {task.vehicle.color}
                  </p>
                </div>
              </div>

              <div className="p-3 bg-muted/50 rounded-lg">
                <p className="text-xs text-muted-foreground mb-1">Layanan</p>
                <div className="space-y-1">
                  {task.services.map((service, index) => (
                    <div key={index} className="flex justify-between text-sm">
                      <span>{service.name}</span>
                      <span className="font-medium">Rp {service.price.toLocaleString('id-ID')}</span>
                    </div>
                  ))}
                  <div className="flex justify-between text-sm font-bold border-t pt-1 mt-1">
                    <span>Total</span>
                    <span>Rp {task.services.reduce((sum, s) => sum + s.price, 0).toLocaleString('id-ID')}</span>
                  </div>
                </div>
              </div>

              <div className="p-3 bg-muted/50 rounded-lg">
                <p className="text-xs text-muted-foreground mb-1">Jadwal</p>
                <div className="grid gap-2 md:grid-cols-3 text-sm">
                  <div><span className="text-muted-foreground">Ditugaskan: </span>{formatTimeAgo(task.assignedAt)}</div>
                  <div><span className="text-muted-foreground">Estimasi: </span>{formatDuration(task.estimatedDuration)}</div>
                  <div><span className="text-muted-foreground">Aktual: </span>{formatDuration(Math.round((task.actualDuration || 0) / 60))}</div>
                </div>
              </div>

              {task.notes && (
                <div className="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                  <p className="text-xs text-yellow-800 flex items-center gap-1">
                    <AlertCircle className="h-3 w-3" /> Catatan Booking: {task.notes}
                  </p>
                </div>
              )}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}

export default function OperatorDashboard() {
  const [tasks, setTasks] = useState(mockTasks);
  const [selectedTask, setSelectedTask] = useState(null);
  const [isDetailOpen, setIsDetailOpen] = useState(false);
  const [filterStatus, setFilterStatus] = useState('ALL');
  const [isOnline, setIsOnline] = useState(true);
  const [syncStatus, setSyncStatus] = useState('synced'); // 'synced' | 'pending' | 'offline'

  // PWA Install prompt
  const [deferredPrompt, setDeferredPrompt] = useState(null);
  const [showInstallPrompt, setShowInstallPrompt] = useState(false);

  useEffect(() => {
    // Check online status
    const handleOnline = () => { setIsOnline(true); setSyncStatus('synced'); };
    const handleOffline = () => { setIsOnline(false); setSyncStatus('offline'); };
    
    window.addEventListener('online', handleOnline);
    window.addEventListener('offline', handleOffline);
    setIsOnline(navigator.onLine);

    // PWA Install prompt
    const handleBeforeInstallPrompt = (e) => {
      e.preventDefault();
      setDeferredPrompt(e);
      setShowInstallPrompt(true);
    };
    window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);

    return () => {
      window.removeEventListener('online', handleOnline);
      window.removeEventListener('offline', handleOffline);
      window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
    };
  }, []);

  const handleInstall = async () => {
    if (deferredPrompt) {
      deferredPrompt.prompt();
      const { outcome } = await deferredPrompt.userChoice;
      if (outcome === 'accepted') {
        setShowInstallPrompt(false);
        setDeferredPrompt(null);
      }
    }
  };

  const filteredTasks = tasks.filter(task => 
    filterStatus === 'ALL' || task.status === filterStatus
  );

  const statusCounts = {
    ALL: tasks.length,
    ASSIGNED: tasks.filter(t => t.status === 'ASSIGNED').length,
    WASHING: tasks.filter(t => t.status === 'WASHING').length,
    DRYING: tasks.filter(t => t.status === 'DRYING').length,
    READY: tasks.filter(t => t.status === 'READY').length,
    COMPLETED: tasks.filter(t => t.status === 'COMPLETED').length,
  };

  const handleStart = (taskId) => {
    setTasks(prev => prev.map(t => 
      t.id === taskId ? { ...t, status: 'WASHING', actualDuration: 0 } : t
    ));
  };

  const handleComplete = (taskId, newStatus) => {
    setTasks(prev => prev.map(t => 
      t.id === taskId ? { ...t, status: newStatus } : t
    ));
  };

  const handleProgress = (taskId, type) => {
    // Open detail modal for photo upload
    setSelectedTask(tasks.find(t => t.id === taskId));
    setIsDetailOpen(true);
  };

  const handleViewDetails = (taskId) => {
    setSelectedTask(tasks.find(t => t.id === taskId));
    setIsDetailOpen(true);
  };

  const handleCloseDetail = () => {
    setIsDetailOpen(false);
    setSelectedTask(null);
  };

  return (
    <MainLayout pageTitle="Dashboard Operator" pageDescription="Kelola tugas pencucian yang ditugaskan">
      {/* PWA Install Banner */}
      {showInstallPrompt && (
        <div className="fixed bottom-4 left-4 right-4 lg:left-auto lg:right-4 lg:bottom-4 lg:w-80 z-50 animate-slide-up">
          <Card className="border-primary/50 bg-primary/5">
            <CardContent className="p-4">
              <div className="flex items-start gap-3">
                <div className="p-2 rounded-lg bg-primary text-primary-foreground">
                  <Smartphone className="h-5 w-5" />
                </div>
                <div className="flex-1 min-w-0">
                  <p className="font-medium text-sm">Install Aplikasi Operator</p>
                  <p className="text-xs text-muted-foreground mt-1">Akses cepat offline, notifikasi push, dan kamera terintegrasi</p>
                </div>
                <Button variant="ghost" size="icon" onClick={() => setShowInstallPrompt(false)}>
                  <X className="h-4 w-4" />
                </Button>
              </div>
              <Button className="w-full mt-3 gap-2" onClick={handleInstall}>
                <Download className="h-4 w-4" />
                Install Sekarang
              </Button>
            </CardContent>
          </Card>
        </div>
      )}

      {/* Offline Banner */}
      {!isOnline && (
        <div className="fixed top-0 left-0 right-0 z-40 bg-yellow-500 text-yellow-900 px-4 py-2 text-center text-sm font-medium flex items-center justify-center gap-2">
          <WifiOff className="h-4 w-4" />
          Mode Offline - Data akan disinkronkan saat online kembali
        </div>
      )}

      <div className="space-y-4 pt-2">
        {/* Header */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div className="flex items-center gap-3">
            <User className="h-6 w-6 text-primary" />
            <div>
              <h1 className="font-montserrat text-xl font-bold">Dashboard Operator</h1>
              <p className="text-sm text-muted-foreground">Tugas yang ditugaskan ke Anda hari ini</p>
            </div>
          </div>
          <div className="flex items-center gap-3">
            {/* Online Status */}
            <div className={cn('flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium', isOnline ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700')}>
              {isOnline ? <Wifi className="h-3 w-3" /> : <WifiOff className="h-3 w-3" />}
              {isOnline ? 'Online' : 'Offline'}
            </div>
            {/* Sync Status */}
            {syncStatus === 'pending' && (
              <Badge variant="outline" className="bg-blue-50 border-blue-200 text-blue-700 animate-pulse">
                <Loader2 className="h-3 w-3 animate-spin mr-1" />
                Menyinkronkan...
              </Badge>
            )}
          </div>
        </div>

        {/* Stats Tabs */}
        <div className="flex gap-2 overflow-x-auto pb-2">
          {[
            { id: 'ALL', label: 'Semua', icon: null },
            { id: 'ASSIGNED', label: 'Ditugaskan', icon: MapPin, color: 'bg-blue-100 text-blue-700' },
            { id: 'WASHING', label: 'Cuci', icon: Loader2, color: 'bg-orange-100 text-orange-700' },
            { id: 'DRYING', label: 'Kering', icon: Loader2, color: 'bg-purple-100 text-purple-700' },
            { id: 'READY', label: 'Siap', icon: CheckCircle2, color: 'bg-green-100 text-green-700' },
            { id: 'COMPLETED', label: 'Selesai', icon: CheckCircle2, color: 'bg-emerald-100 text-emerald-700' },
          ].map(tab => (
            <Button
              key={tab.id}
              variant={filterStatus === tab.id ? 'default' : 'outline'}
              className={cn('whitespace-nowrap gap-1', tab.id !== 'ALL' && tab.color && filterStatus === tab.id && tab.color)}
              onClick={() => setFilterStatus(tab.id)}
            >
              {tab.icon && <tab.icon className="h-3 w-3" />}
              {tab.label}
              <Badge variant="outline" className={cn('ml-1', tab.color)}>
                {statusCounts[tab.id]}
              </Badge>
            </Button>
          ))}
        </div>

        {/* Task List */}
        <div className="space-y-3">
          {filteredTasks.length === 0 ? (
            <Card>
              <CardContent className="py-12 text-center">
                <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-muted/50 flex items-center justify-center">
                  <CheckCircle2 className="h-8 w-8 text-muted-foreground/50" />
                </div>
                <h3 className="font-medium text-lg">Tidak ada tugas</h3>
                <p className="text-sm text-muted-foreground mt-1">
                  {filterStatus === 'ALL' 
                    ? 'Semua tugas selesai. Luangkan waktu untuk istirahat!' 
                    : `Tidak ada tugas dengan status ${filterStatus}`}
                </p>
              </CardContent>
            </Card>
          ) : (
            filteredTasks.map(task => (
              <TaskCard
                key={task.id}
                task={task}
                onStart={handleStart}
                onProgress={handleProgress}
                onComplete={handleComplete}
                onViewDetails={handleViewDetails}
              />
            ))
          )}
        </div>
      </div>

      {/* Task Detail Modal */}
      <TaskDetailModal
        task={selectedTask}
        isOpen={isDetailOpen}
        onClose={handleCloseDetail}
        onStart={handleStart}
        onUploadProgress={() => {}}
        onUploadFinal={() => {}}
        onComplete={handleComplete}
      />
    </MainLayout>
  );
}



