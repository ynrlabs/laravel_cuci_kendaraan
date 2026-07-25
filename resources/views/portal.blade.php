<!DOCTYPE html>
<html class="dark" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>CuciKuy Portal - Role Selection</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Inter:wght@300;400;500;600&family=JetBrains+Mono:wght@500&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Montserrat:wght@100..900&display=swap" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-bg": "#F8FAFC",
                        "on-tertiary-fixed": "#351000",
                        "inverse-surface": "#213145",
                        "primary-fixed": "#dde1ff",
                        "surface-container": "#e5eeff",
                        "surface-container-high": "#dce9ff",
                        "surface": "#f8f9ff",
                        "secondary-fixed-dim": "#bcc7de",
                        "on-primary": "#ffffff",
                        "error-red": "#EF4444",
                        "on-secondary-fixed-variant": "#3c475a",
                        "on-secondary": "#ffffff",
                        "secondary": "#545f73",
                        "surface-dim": "#cbdbf5",
                        "surface-container-lowest": "#ffffff",
                        "surface-tint": "#004ced",
                        "on-surface-variant": "#434656",
                        "tertiary": "#863500",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-fixed-variant": "#7a3000",
                        "surface-container-highest": "#d3e4fe",
                        "outline-variant": "#c3c5d9",
                        "on-secondary-fixed": "#111c2d",
                        "on-secondary-container": "#586377",
                        "on-background": "#0b1c30",
                        "error": "#ba1a1a",
                        "on-surface": "#0b1c30",
                        "on-primary-fixed-variant": "#0038b6",
                        "on-primary-container": "#dfe3ff",
                        "secondary-container": "#d5e0f8",
                        "inverse-on-surface": "#eaf1ff",
                        "error-container": "#ffdad6",
                        "outline": "#737688",
                        "tertiary-container": "#ad4600",
                        "primary": "#003ec7",
                        "background": "#f8f9ff",
                        "primary-fixed-dim": "#b7c4ff",
                        "surface-container-low": "#eff4ff",
                        "success-green": "#10B981",
                        "secondary-fixed": "#d8e3fb",
                        "on-primary-fixed": "#001452",
                        "on-error-container": "#93000a",
                        "surface-variant": "#d3e4fe",
                        "action-yellow": "#FFD600",
                        "on-tertiary-container": "#ffded0",
                        "primary-container": "#0052ff",
                        "tertiary-fixed-dim": "#ffb693",
                        "inverse-primary": "#b7c4ff",
                        "on-error": "#ffffff",
                        "warning-yellow": "#FBBF24",
                        "tertiary-fixed": "#ffdbcc",
                        "surface-bright": "#f8f9ff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "stack-lg": "32px",
                        "gutter-grid": "20px",
                        "margin-mobile": "16px",
                        "stack-md": "16px",
                        "unit": "4px",
                        "stack-sm": "8px",
                        "margin-desktop": "32px"
                    },
                    "fontFamily": {
                        "stat-value": ["Montserrat"],
                        "body-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "label-caps": ["Inter"],
                        "headline-lg-mobile": ["Montserrat"],
                        "code-booking": ["JetBrains Mono"],
                        "body-lg": ["Inter"],
                        "headline-lg": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "body-sm": ["Inter"]
                    },
                    "fontSize": {
                        "stat-value": ["36px", {"lineHeight": "44px", "fontWeight": "700"}],
                        "body-md": ["16px", {"lineHeight": "24px", "fontWeight": "400"}],
                        "headline-md": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "label-caps": ["12px", {"lineHeight": "16px", "letterSpacing": "0.05em", "fontWeight": "700"}],
                        "headline-lg-mobile": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "code-booking": ["16px", {"lineHeight": "24px", "fontWeight": "500"}],
                        "body-lg": ["18px", {"lineHeight": "28px", "fontWeight": "400"}],
                        "headline-lg": ["32px", {"lineHeight": "40px", "fontWeight": "700"}],
                        "display-lg": ["48px", {"lineHeight": "56px", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-sm": ["14px", {"lineHeight": "20px", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 300, 'GRAD' 0, 'opsz' 24;
        }
        
        .premium-bg {
            background: radial-gradient(circle at center, #020617 0%, #000000 100%);
        }

        .ambient-glow {
            filter: blur(120px);
            opacity: 0.4;
            pointer-events: none;
        }

        .card-glow:hover {
            border-color: #004ced;
            box-shadow: 0 0 30px rgba(0, 76, 237, 0.25);
            transform: scale(1.02);
        }

        .brand-gradient {
            background: linear-gradient(135deg, #ffffff 0%, #0052ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .kinetic-border {
            position: relative;
            overflow: hidden;
        }

        .kinetic-border::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(transparent, #0052ff, transparent 30%);
            animation: rotate 6s linear infinite;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card-glow:hover.kinetic-border::after {
            opacity: 0.5;
        }

        @keyframes rotate {
            100% { transform: rotate(360deg); }
        }

        body {
            cursor: default;
        }
    </style>
</head>
<body class="premium-bg text-on-primary-container min-h-screen flex flex-col font-body-md overflow-hidden animate-fade-in">
    <!-- Ambient Accents -->
    <div class="fixed top-0 left-0 w-96 h-96 bg-primary ambient-glow -translate-x-1/2 -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-0 w-96 h-96 bg-primary ambient-glow translate-x-1/2 translate-y-1/2"></div>
    <!-- Main Container -->
    <main class="relative z-10 flex-1 flex flex-col items-center justify-center px-margin-mobile md:px-margin-desktop py-stack-lg">
        <!-- Header -->
        <header class="text-center mb-12">
            <div class="flex items-center justify-center mb-6">
                <div class="relative w-20 h-20 flex items-center justify-center bg-on-background rounded-2xl shadow-lg border border-outline/20">
                    <span class="material-symbols-outlined text-5xl text-primary-fixed" style="font-variation-settings: 'FILL' 1;">directions_car</span>
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-primary rounded-full animate-pulse shadow-[0_0_10px_#004ced]"></div>
                </div>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-white mb-2 tracking-tight">
                Selamat Datang di <span class="brand-gradient font-black">Portal CuciKuy</span>
            </h1>
            <p class="font-body-lg text-body-lg text-secondary-fixed-dim/60 max-w-lg mx-auto">
                Pilih portal masuk sesuai dengan peran Anda untuk mengakses ekosistem manajemen CuciKuy.
            </p>
        </header>
        <!-- Role Selection Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-stack-lg max-w-5xl w-full">
            <!-- Admin / Manager Card -->
            <div class="card-glow kinetic-border group relative bg-on-background/40 backdrop-blur-xl p-8 rounded-xl border border-outline/10 transition-all duration-300 flex flex-col h-full">
                <div class="mb-6 w-14 h-14 rounded-lg bg-primary/10 flex items-center justify-center border border-primary/20 transition-colors group-hover:bg-primary/20">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">shield_person</span>
                </div>
                <h2 class="font-headline-md text-headline-md text-white mb-4">Admin & Pemilik</h2>
                <p class="font-body-md text-body-md text-secondary-fixed-dim/70 mb-8 flex-grow">
                    Kelola cabang, laporan keuangan komprehensif, konfigurasi layanan, monitoring karyawan, dan performa bisnis secara real-time.
                </p>
                <a class="inline-flex items-center justify-center px-6 py-4 bg-primary hover:bg-primary-container text-white font-bold rounded-lg transition-all duration-200 active:scale-95 group/btn" href="/admin/login">
                    <span>Login Admin</span>
                    <span class="material-symbols-outlined ml-2 text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
            <!-- Kasir / Operator Card -->
            <div class="card-glow kinetic-border group relative bg-on-background/40 backdrop-blur-xl p-8 rounded-xl border border-outline/10 transition-all duration-300 flex flex-col h-full">
                <div class="mb-6 w-14 h-14 rounded-lg bg-primary/10 flex items-center justify-center border border-primary/20 transition-colors group-hover:bg-primary/20">
                    <span class="material-symbols-outlined text-primary text-3xl" style="font-variation-settings: 'FILL' 1;">badge</span>
                </div>
                <h2 class="font-headline-md text-headline-md text-white mb-4">Kasir & Operator</h2>
                <p class="font-body-md text-body-md text-secondary-fixed-dim/70 mb-8 flex-grow">
                    Kelola antrian cuci kendaraan, catat walk-in booking, scan QR code pelanggan, proses pembayaran kasir, dan update status pengerjaan.
                </p>
                <a class="inline-flex items-center justify-center px-6 py-4 bg-surface-container-highest/10 hover:bg-surface-container-highest/20 border border-outline/20 text-white font-bold rounded-lg transition-all duration-200 active:scale-95 group/btn" href="/login">
                    <span>Login Staff</span>
                    <span class="material-symbols-outlined ml-2 text-xl group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </a>
            </div>
        </div>
    </main>
    <!-- Footer -->
    <footer class="relative z-10 w-full py-stack-lg border-t border-outline/5 text-center">
        <p class="font-label-caps text-label-caps text-secondary/40 tracking-widest uppercase">
            Akses terbatas hanya untuk staf resmi CuciKuy.
        </p>
    </footer>
    <!-- Interactive Background Particles -->
    <canvas class="fixed inset-0 pointer-events-none z-0" id="particleCanvas"></canvas>
    <script>
        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        let particles = [];

        function init() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            particles = [];
            for (let i = 0; i < 50; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    size: Math.random() * 2 + 1,
                    speedX: Math.random() * 0.5 - 0.25,
                    speedY: Math.random() * 0.5 - 0.25,
                    opacity: Math.random() * 0.5
                });
            }
        }

        function animate() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            particles.forEach(p => {
                p.x += p.speedX;
                p.y += p.speedY;
                
                if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
                if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;

                ctx.fillStyle = `rgba(0, 82, 255, ${p.opacity})`;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fill();
            });
            requestAnimationFrame(animate);
        }

        window.addEventListener('resize', init);
        init();
        animate();
    </script>
</body>
</html>
