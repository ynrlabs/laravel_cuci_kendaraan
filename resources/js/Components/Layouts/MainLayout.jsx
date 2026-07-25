import * as React from 'react';
import { Head, usePage } from '@inertiajs/react';
import { Sidebar } from './Sidebar';
import { Header } from './Header';
import { Button } from '@/Components/UI';
import { Plus, Car } from 'lucide-react';
import { Link } from '@inertiajs/react';

export function MainLayout({ children }) {
  const [sidebarOpen, setSidebarOpen] = React.useState(false);
  const page = usePage();

  return (
    <>
      <Head>
        <title>{page.props.title || 'CuciKuy - Kasir'}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1" />
      </Head>

      <Sidebar isOpen={sidebarOpen} onClose={() => setSidebarOpen(false)} />

      <div className="lg:pl-64 flex flex-col min-h-screen bg-background">
        <Header onMenuClick={() => setSidebarOpen(true)} />

        <main className="flex-1 p-4 lg:p-6">
          <div className="flex items-center justify-between mb-6">
            <div>
              <h1 className="font-montserrat text-2xl font-bold text-foreground">
                {page.props.pageTitle || 'Dashboard'}
              </h1>
              <p className="text-sm text-muted-foreground mt-1">
                {page.props.pageDescription || 'Kelola booking cuci kendaraan dengan mudah'}
              </p>
            </div>

            <Link href="/kasir/bookings/create">
              <Button className="gap-2" size="lg">
                <Plus className="h-5 w-5" />
                <Car className="h-5 w-5" />
                <span>Booking Baru</span>
              </Button>
            </Link>
          </div>

          {children}
        </main>
      </div>
    </>
  );
}