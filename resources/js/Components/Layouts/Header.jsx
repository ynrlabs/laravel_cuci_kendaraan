import * as React from 'react';
import { Link } from '@inertiajs/react';
import { useAuth } from '@/Hooks/useAuth';
import { cn } from '@/Lib/utils';
import { Button } from '@/Components/UI';
import { Bell, User, Menu, LogOut, ChevronDown, Settings } from 'lucide-react';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
  DropdownMenuLabel,
} from '@/Components/UI/DropdownMenu';

export function Header({ onMenuClick }) {
  const { user, logout } = useAuth();

  return (
    <header className="sticky top-0 z-30 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 lg:static lg:z-20">
      <div className="flex h-16 items-center justify-between px-4 lg:px-6">
        <div className="flex items-center gap-4 lg:hidden">
          <Button
            variant="ghost"
            size="icon"
            onClick={onMenuClick}
            aria-label="Toggle menu"
            className="lg:hidden"
          >
            <Menu className="h-5 w-5" />
          </Button>
        </div>

        <div className="flex-1 lg:flex-none" />

        <div className="flex items-center gap-2 lg:gap-4">
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" size="icon" className="relative h-10 w-10 rounded-full">
                <Bell className="h-5 w-5" />
                <span className="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80">
              <DropdownMenuLabel className="font-medium">Notifikasi</DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem className="text-sm text-muted-foreground">
                Belum ada notifikasi baru
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>

          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" className="relative h-10 w-10 rounded-full gap-2">
                <User className="h-5 w-5" />
                <span className="hidden sm:block text-sm font-medium">{user?.name || 'User'}</span>
                <ChevronDown className="h-4 w-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
              <DropdownMenuLabel className="font-normal">
                <div className="flex flex-col space-y-1">
                  <p className="text-sm font-medium leading-none">{user?.name || 'User'}</p>
                  <p className="text-xs leading-none text-muted-foreground">
                    {user?.email || 'user@example.com'}
                  </p>
                </div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem onClick={() => console.log('Profile clicked')}>
                <User className="mr-2 h-4 w-4" />
                Profil
              </DropdownMenuItem>
              <DropdownMenuItem onClick={() => console.log('Settings clicked')}>
                <Settings className="mr-2 h-4 w-4" />
                Pengaturan
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem
                onClick={() => logout()}
                className="text-destructive focus:text-destructive"
              >
                <LogOut className="mr-2 h-4 w-4" />
                Keluar
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </header>
  );
}
