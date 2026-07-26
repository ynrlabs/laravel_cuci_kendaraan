@props([
    'variant' => 'spinner', // spinner, pulse, dots, bars, wave
    'color' => 'primary',
    'size' => 'md', // sm, md, lg
    'text' => 'Memuat...',
    'overlay' => true,
])

<?php
$variants = [
    'spinner' => 'loading-spinner',
    'pulse' => 'loading-pulse',
    'dots' => 'loading-dots',
    'bars' => 'loading-bars',
    'wave' => 'loading-wave',
];

$sizes = [
    'sm' => 'loading-sm',
    'md' => 'loading-md',
    'lg' => 'loading-lg',
];

$colors = [
    'primary' => 'loading-primary',
    'secondary' => 'loading-secondary',
    'success' => 'loading-success',
    'warning' => 'loading-warning',
    'danger' => 'loading-danger',
];

$variantClass = $variants[$variant] ?? 'loading-spinner';
$sizeClass = $sizes[$size] ?? 'loading-md';
$colorClass = $colors[$color] ?? 'loading-primary';
?>

<div 
    id="filament-loading-screen" 
    class="filament-loading-screen fixed inset-0 z-[9999] flex items-center justify-center transition-opacity duration-300 {{ $overlay ? 'bg-black/30 backdrop-blur-sm' : '' }}"
    x-data="filamentLoadingScreen()"
    x-init="
        // Listen for Livewire navigation events
        document.addEventListener('livewire:navigating', () => show())
        document.addEventListener('livewire:navigated', () => hide())
        document.addEventListener('livewire:load', () => hide())
        
        // Listen for form submissions
        document.addEventListener('livewire:submit', () => show())
        
        // Also listen for Turbo/Inertia-like events if present
        document.addEventListener('turbo:visit', () => show())
        document.addEventListener('turbo:load', () => hide())
    "
    style="display: none;"
>
    <div class="{{ $variantClass }} {{ $sizeClass }} {{ $colorClass }} relative">
        @switch($variant)
            @case('spinner')
                <div class="loading-spinner-inner">
                    <svg class="animate-spin" viewBox="0 0 50 50">
                        <circle 
                            cx="25" 
                            cy="25" 
                            r="20" 
                            fill="none" 
                            stroke="currentColor" 
                            stroke-width="4" 
                            stroke-linecap="round"
                            stroke-dasharray="90 150"
                        ></circle>
                    </svg>
                </div>
                @break
            @case('pulse')
                <div class="loading-pulse-inner">
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring"></div>
                    <div class="pulse-ring"></div>
                    <div class="pulse-center"></div>
                </div>
                @break
            @case('dots')
                <div class="loading-dots-inner">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
                @break
            @case('bars')
                <div class="loading-bars-inner">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                @break
            @case('wave')
                <div class="loading-wave-inner">
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                    <div class="wave-bar"></div>
                </div>
                @break
        @endswitch
        
        @if($text)
        <div class="loading-text mt-4 text-center">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $text }}</span>
        </div>
        @endif
    </div>
</div>

<script>
    // Alpine.js component for loading screen
    document.addEventListener('alpine:init', () => {
        Alpine.data('filamentLoadingScreen', () => ({
            show() {
                const el = document.getElementById('filament-loading-screen')
                if (el) {
                    el.style.display = 'flex'
                    // Force reflow for animation
                    el.offsetHeight
                    el.classList.add('opacity-100')
                    el.classList.remove('opacity-0')
                }
            },
            hide() {
                const el = document.getElementById('filament-loading-screen')
                if (el) {
                    el.classList.add('opacity-0')
                    el.classList.remove('opacity-100')
                    setTimeout(() => {
                        if (el.classList.contains('opacity-0')) {
                            el.style.display = 'none'
                        }
                    }, 300)
                }
            }
        }))
    })
</script>

@push('styles')
<style>
    /* Loading Screen Base Styles */
    .filament-loading-screen {
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }
    
    .filament-loading-screen.opacity-100 {
        opacity: 1;
        pointer-events: auto;
    }
    
    .filament-loading-screen.opacity-0 {
        opacity: 0;
        pointer-events: none;
    }
    
    /* Size Variants */
    .loading-sm .loading-spinner-inner svg { width: 32px; height: 32px; }
    .loading-sm .pulse-ring { width: 32px; height: 32px; }
    .loading-sm .pulse-center { width: 16px; height: 16px; }
    .loading-sm .dot { width: 6px; height: 6px; }
    .loading-sm .bar { width: 3px; height: 16px; }
    .loading-sm .wave-bar { width: 3px; height: 16px; }
    
    .loading-md .loading-spinner-inner svg { width: 48px; height: 48px; }
    .loading-md .pulse-ring { width: 48px; height: 48px; }
    .loading-md .pulse-center { width: 24px; height: 24px; }
    .loading-md .dot { width: 10px; height: 10px; }
    .loading-md .bar { width: 5px; height: 24px; }
    .loading-md .wave-bar { width: 5px; height: 24px; }
    
    .loading-lg .loading-spinner-inner svg { width: 64px; height: 64px; }
    .loading-lg .pulse-ring { width: 64px; height: 64px; }
    .loading-lg .pulse-center { width: 32px; height: 32px; }
    .loading-lg .dot { width: 14px; height: 14px; }
    .loading-lg .bar { width: 7px; height: 32px; }
    .loading-lg .wave-bar { width: 7px; height: 32px; }
    
    /* Color Variants - using CSS custom properties for theme integration */
    .loading-primary { --loading-color: var(--primary-500, #3b82f6); color: var(--loading-color); }
    .loading-secondary { --loading-color: var(--secondary-500, #64748b); color: var(--loading-color); }
    .loading-success { --loading-color: var(--success-500, #22c55e); color: var(--loading-color); }
    .loading-warning { --loading-color: var(--warning-500, #f59e0b); color: var(--loading-color); }
    .loading-danger { --loading-color: var(--danger-500, #ef4444); color: var(--loading-color); }
    
    /* Spinner Variant */
    .loading-spinner-inner {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .loading-spinner-inner svg {
        animation: spin 1s linear infinite;
    }
    
    .loading-spinner-inner circle {
        stroke: currentColor;
    }
    
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    
    /* Pulse Variant */
    .loading-pulse-inner {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .pulse-ring {
        position: absolute;
        border: 3px solid currentColor;
        border-radius: 50%;
        opacity: 0;
        animation: pulse-ring 2s ease-out infinite;
    }
    
    .pulse-ring:nth-child(1) { animation-delay: 0s; }
    .pulse-ring:nth-child(2) { animation-delay: 0.4s; }
    .pulse-ring:nth-child(3) { animation-delay: 0.8s; }
    
    .pulse-center {
        position: relative;
        background: currentColor;
        border-radius: 50%;
        animation: pulse-center 1.5s ease-in-out infinite;
    }
    
    @keyframes pulse-ring {
        0% { transform: scale(0.5); opacity: 0.6; }
        100% { transform: scale(2); opacity: 0; }
    }
    
    @keyframes pulse-center {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(0.8); }
    }
    
    /* Dots Variant */
    .loading-dots-inner {
        display: flex;
        gap: 6px;
        justify-content: center;
        align-items: center;
    }
    
    .dot {
        background: currentColor;
        border-radius: 50%;
        animation: dot-bounce 1.4s ease-in-out infinite both;
    }
    
    .dot:nth-child(1) { animation-delay: -0.32s; }
    .dot:nth-child(2) { animation-delay: -0.16s; }
    
    @keyframes dot-bounce {
        0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
        40% { transform: scale(1); opacity: 1; }
    }
    
    /* Bars Variant */
    .loading-bars-inner {
        display: flex;
        gap: 4px;
        justify-content: center;
        align-items: flex-end;
        height: 40px;
    }
    
    .bar {
        background: currentColor;
        border-radius: 2px;
        animation: bar-scale 1.2s ease-in-out infinite;
    }
    
    .bar:nth-child(1) { animation-delay: -1.1s; }
    .bar:nth-child(2) { animation-delay: -0.9s; }
    .bar:nth-child(3) { animation-delay: -0.7s; }
    .bar:nth-child(4) { animation-delay: -0.5s; }
    .bar:nth-child(5) { animation-delay: -0.3s; }
    
    @keyframes bar-scale {
        0%, 40%, 100% { transform: scaleY(0.2); opacity: 0.5; }
        20% { transform: scaleY(1); opacity: 1; }
    }
    
    /* Wave Variant */
    .loading-wave-inner {
        display: flex;
        gap: 4px;
        justify-content: center;
        align-items: flex-end;
        height: 40px;
    }
    
    .wave-bar {
        background: currentColor;
        border-radius: 2px;
        animation: wave-scale 1.2s ease-in-out infinite;
    }
    
    .wave-bar:nth-child(1) { animation-delay: 0s; }
    .wave-bar:nth-child(2) { animation-delay: 0.1s; }
    .wave-bar:nth-child(3) { animation-delay: 0.2s; }
    .wave-bar:nth-child(4) { animation-delay: 0.3s; }
    .wave-bar:nth-child(5) { animation-delay: 0.4s; }
    
    @keyframes wave-scale {
        0%, 100% { transform: scaleY(0.2); opacity: 0.5; }
        50% { transform: scaleY(1); opacity: 1; }
    }
    
    /* Progress bar style for page transitions */
    .filament-loading-progress {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-500), var(--primary-600));
        transform-origin: 0% 50%;
        transform: scaleX(0);
        z-index: 9999;
        transition: transform 0.2s ease-out;
    }
    
    .filament-loading-progress.active {
        transform: scaleX(1);
        transition: transform 0.4s ease-out;
    }
    
    .filament-loading-progress.complete {
        transform: scaleX(1);
        transition: transform 0.1s ease-out;
    }
    
    /* Dark mode support */
    .dark .filament-loading-screen {
        --loading-text-color: #e5e7eb;
    }
    
    .filament-loading-screen .loading-text span {
        color: var(--loading-text-color, #374151);
    }
    
    .dark .filament-loading-screen .loading-text span {
        color: #e5e7eb;
    }
</style>
@endpush

@push('scripts')
<script>
    // Additional global loading progress bar for page transitions
    document.addEventListener('alpine:init', () => {
        let progressBar = null
        let progressTimeout = null
        let progressValue = 0
        
        function createProgressBar() {
            if (!progressBar) {
                progressBar = document.createElement('div')
                progressBar.className = 'filament-loading-progress'
                progressBar.style.setProperty('--progress', '0%')
                document.body.appendChild(progressBar)
            }
        }
        
        function showProgress() {
            createProgressBar()
            progressValue = 0
            progressBar.classList.remove('complete')
            progressBar.classList.add('active')
            progressBar.style.transform = 'scaleX(0)'
            
            // Simulate progress
            const animate = () => {
                if (progressValue < 0.9) {
                    progressValue += Math.random() * 0.1
                    progressBar.style.transform = `scaleX(${Math.min(progressValue, 0.9)})`
                    progressTimeout = requestAnimationFrame(animate)
                }
            }
            animate()
        }
        
        function completeProgress() {
            if (progressBar) {
                progressBar.classList.remove('active')
                progressBar.classList.add('complete')
                progressBar.style.transform = 'scaleX(1)'
                
                setTimeout(() => {
                    progressBar.style.transform = 'scaleX(0)'
                    progressBar.classList.remove('complete')
                }, 300)
            }
            if (progressTimeout) {
                cancelAnimationFrame(progressTimeout)
            }
        }
        
        // Listen for Livewire navigation events
        document.addEventListener('livewire:navigating', () => {
            // Small delay to avoid flashing for fast transitions
            setTimeout(showProgress, 100)
        })
        
        document.addEventListener('livewire:navigated', () => {
            completeProgress()
        })
        
        document.addEventListener('livewire:load', () => {
            completeProgress()
        })
    })
</script>
@endpush