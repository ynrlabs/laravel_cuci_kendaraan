import * as React from 'react';
import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/Lib/utils';
import {
  LayoutDashboard,
  Calendar,
  Users,
  Car,
  Settings,
  CreditCard,
  Package,
  Bell,
  BarChart3,
  LogOut,
  Menu,
  X,
  Kanban,
} from 'lucide-react';

const navigation = [
  { name: 'Dashboard', href: '/kasir/dashboard', icon: LayoutDashboard },
  { name: 'Antrian Kanban', href: '/kasir/antrian', icon: Kanban },
  { name: 'Booking', href: '/kasir/bookings', icon: Calendar },
  { name: 'Pelanggan', href: '/kasir/customers', icon: Users },
  { name: 'Kendaraan', href: '/kasir/vehicles', icon: Car },
  { name: 'Layanan', href: '/kasir/services', icon: Package },
  { name: 'Pembayaran', href: '/kasir/payments', icon: CreditCard },
  { name: 'Notifikasi', href: '/kasir/notifications', icon: Bell },
  { name: 'Laporan', href: '/kasir/reports', icon: BarChart3 },
  { name: 'Pengaturan', href: '/kasir/settings', icon: Settings },
];

export function Sidebar({ isOpen, onClose }) {
  const { url } = usePage();

  return (
    <>
      <button
        className={cn(
          'fixed top-4 left-4 z-50 lg:hidden p-2 rounded-md bg-background border',
          isOpen ? 'left-64' : 'left-4'
        )}
        onClick={onClose}
        aria-label={isOpen ? 'Close sidebar' : 'Open sidebar'}
      >
        {isOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
      </button>

      <aside
        className={cn(
          'fixed inset-y-0 left-0 z-40 w-64 bg-background border-r transition-transform duration-200 ease-in-out lg:translate-x-0',
          isOpen ? 'translate-x-0' : '-translate-x-full'
        )}
        aria-label="Sidebar"
      >
        <div className="flex flex-col h-full">
          <div className="flex h-16 items-center justify-between px-4 border-b">
            <Link href="/kasir/dashboard" className="flex items-center gap-2">
              <div className="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                <Car className="h-5 w-5 text-primary-foreground" />
              </div>
              <span className="font-montserrat font-bold text-xl text-primary">
                CuciKuy
              </span>
            </Link>
            <button
              className="lg:hidden p-2 rounded-md hover:bg-accent"
              onClick={onClose}
              aria-label="Close sidebar"
            >
              <X className="h-5 w-5" />
            </button>
          </div>

          <nav className="flex-1 px-3 py-4 space-y-1 overflow-y-auto" aria-label="Main navigation">
            {navigation.map((item) => {
              const isActive = url === item.href || url.startsWith(item.href + '/');
              return (
                <Link
                  key={item.name}
                  href={item.href}
                  className={cn(
                    'flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors',
                    isActive
                      ? 'bg-primary text-primary-foreground'
                      : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                  )}
                  onClick={onClose}
                >
                  <item.icon className="h-5 w-5 shrink-0" aria-hidden="true" />
                  {item.name}
                  {isActive && (
                    <span className="ml-auto h-1.5 w-1.5 rounded-full bg-primary-foreground" />
                  )}
                </Link>
              );
            })}
          </nav>

          <div className="p-3 border-t">
            <Link
              href="/logout"
              method="post"
              as="button"
              className={cn(
                'flex w-full items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-lg transition-colors',
                'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
              )}
            >
              <LogOut className="h-5 w-5 shrink-0" aria-hidden="true" />
              Keluar
            </Link>
          </div>
        </div>
      </aside>

      {isOpen && (
        <div
          className="fixed inset-0 z-30 bg-black/50 lg:hidden"
          onClick={onClose}
          aria-hidden="true"
        />
      )}
    </>
  );
}