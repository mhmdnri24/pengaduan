<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Set page-specific variables for template
$content_only = true; // This view only contains content, not full HTML structure
?>

<!-- Skeleton Loading Styles -->
<style>
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

.skeleton-text {
    height: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
}

.skeleton-title {
    height: 2rem;
    margin-bottom: 1rem;
    border-radius: 0.5rem;
}

.skeleton-card {
    height: 200px;
    border-radius: 1rem;
    margin-bottom: 1rem;
}

.skeleton-avatar {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.skeleton-shimmer {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* Skeleton states */
.skeleton-loaded {
    animation: fadeOut 0.3s ease-out forwards;
}

@keyframes fadeOut {
    from {
        opacity: 1;
    }
    to {
        opacity: 0;
        visibility: hidden;
    }
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Enhanced Slider Animations */
@keyframes parallax-zoom {
    0% {
        transform: scale(1) translateY(0);
    }
    50% {
        transform: scale(1.15) translateY(-20px);
    }
    100% {
        transform: scale(1.2) translateY(-30px);
    }
}

@keyframes static-position {
    0%, 100% {
        transform: scale(1) translateY(0);
    }
}

.animate-parallax-zoom {
    animation: parallax-zoom 7s ease-out forwards;
    -webkit-animation: parallax-zoom 7s ease-out forwards;
    -moz-animation: parallax-zoom 7s ease-out forwards;
    -o-animation: parallax-zoom 7s ease-out forwards;
}

.animate-static {
    animation: static-position 7s ease-out forwards;
    -webkit-animation: static-position 7s ease-out forwards;
    -moz-animation: static-position 7s ease-out forwards;
    -o-animation: static-position 7s ease-out forwards;
}

@keyframes slide-up {
    0% {
        transform: translateY(50px) scale(0.9);
        opacity: 0;
    }
    50% {
        transform: translateY(10px) scale(0.95);
        opacity: 0.7;
    }
    100% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

@keyframes slide-down {
    0% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    100% {
        transform: translateY(-50px) scale(0.9);
        opacity: 0;
    }
}

.animate-slide-up {
    animation: slide-up 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    -webkit-animation: slide-up 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    -moz-animation: slide-up 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    -o-animation: slide-up 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
}

.animate-slide-down {
    animation: slide-down 0.8s cubic-bezier(0.55, 0.085, 0.68, 0.53) forwards;
    -webkit-animation: slide-down 0.8s cubic-bezier(0.55, 0.085, 0.68, 0.53) forwards;
    -moz-animation: slide-down 0.8s cubic-bezier(0.55, 0.085, 0.68, 0.53) forwards;
    -o-animation: slide-down 0.8s cubic-bezier(0.55, 0.085, 0.68, 0.53) forwards;
}

@keyframes title-glow {
    0% {
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        transform: scale(0.95);
    }
    50% {
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 30px rgba(255, 255, 255, 0.2);
        transform: scale(1.02);
    }
    100% {
        text-shadow: 0 8px 30px rgba(0, 0, 0, 0.7), 0 0 50px rgba(255, 255, 255, 0.3);
        transform: scale(1);
    }
}

.animate-title-glow {
    animation: title-glow 2s ease-out forwards;
    -webkit-animation: title-glow 2s ease-out forwards;
    -moz-animation: title-glow 2s ease-out forwards;
    -o-animation: title-glow 2s ease-out forwards;
}

@keyframes text-fade {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    50% {
        opacity: 0.5;
        transform: translateY(5px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-text-fade {
    animation: text-fade 1.5s ease-out 0.3s forwards;
    -webkit-animation: text-fade 1.5s ease-out 0.3s forwards;
    -moz-animation: text-fade 1.5s ease-out 0.3s forwards;
    -o-animation: text-fade 1.5s ease-out 0.3s forwards;
}

/* Enhanced slider transitions */
.slider-container {
    perspective: 1000px;
}

.slider-image {
    will-change: transform;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
    -moz-backface-visibility: hidden;
    transform-style: preserve-3d;
    -webkit-transform-style: preserve-3d;
    -moz-transform-style: preserve-3d;
}

/* Enhanced slide indicators */
.slider-indicators button {
    position: relative;
    overflow: hidden;
}

.slider-indicators button::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
}

.slider-indicators button:hover::before {
    left: 100%;
}

/* Enhanced navigation arrows */
.slider-nav button {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.slider-nav button:hover {
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
}

/* Enhanced progress bar */
.slider-progress {
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.8), transparent);
    background-size: 200% 100%;
    animation: progress-shimmer 2s infinite;
}

@keyframes progress-shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}
</style>


<!-- Hero Slider Section -->
<section class="relative w-full overflow-hidden" x-data="sliderData()">
    <!-- Skeleton Loading State -->
    <div class="skeleton-slider" x-show="!slidesLoaded" style="padding-bottom: 39.0625%;">
        <div class="absolute inset-0 skeleton bg-gray-300"></div>
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center">
                <div class="skeleton skeleton-title w-64 h-8 mx-auto mb-4"></div>
                <div class="skeleton skeleton-text w-96 h-6 mx-auto"></div>
            </div>
        </div>
    </div>

    <!-- Slider Container with Fixed Aspect Ratio -->
    <div class="slider-container relative w-full overflow-hidden" style="padding-bottom: 39.0625%; /* 500/1280 * 100 for 1280x500 aspect ratio */" x-show="slidesLoaded">
        <!-- Slides with Enhanced Parallax Animation -->
        <template x-for="(slide, index) in slides" :key="index">
            <div class="absolute inset-0 transition-all duration-1500 ease-out"
                 :class="currentSlide === index ? 'opacity-100 z-10' : 'opacity-0 z-0'">
                <picture>
                    <img :src="slide.image" :alt="slide.title"
                         class="slider-image slider-bg absolute inset-0 w-full h-full object-cover"
                         :class="currentSlide === index ? 'animate-parallax-zoom' : 'animate-static'"
                         loading="lazy">
                </picture>
                <div class="absolute inset-0 bg-gradient-to-r from-black/10 via-black/30 to-black/10"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center text-white px-4"
                         :class="currentSlide === index ? 'animate-slide-up' : 'animate-slide-down'">
                        <!-- <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold mb-2 sm:mb-4 drop-shadow-2xl"
                            :class="currentSlide === index ? 'animate-title-glow' : ''"
                            x-text="slide.title"></h1>
                        <p class="text-sm sm:text-base md:text-lg lg:text-xl xl:text-2xl mb-4 sm:mb-6 md:mb-8 max-w-2xl lg:max-w-3xl mx-auto opacity-95 drop-shadow-lg"
                            :class="currentSlide === index ? 'animate-text-fade' : ''"
                            x-text="slide.subtitle"></p> -->
                    </div>
                </div>
            </div>
        </template>
    </div>
    
    <!-- Enhanced Slide Indicators -->
    <div class="slider-indicators absolute bottom-4 sm:bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-2 z-10" x-show="slidesLoaded">
        <template x-for="(slide, index) in slides" :key="index">
            <button @click="goToSlide(index)"
                    class="h-2 rounded-full transition-all duration-500 ease-out relative overflow-hidden"
                    :class="currentSlide === index ? 'bg-white w-8 shadow-lg slider-progress' : 'bg-white/50 hover:bg-white/70 w-2'">
                <span class="sr-only" x-text="'Slide ' + (index + 1)"></span>
            </button>
        </template>
    </div>
    
    <!-- Enhanced Navigation Arrows -->
    <div class="slider-nav">
        <button @click="previousSlide()"
                class="absolute left-2 sm:left-4 top-1/2 transform -translate-y-1/2 bg-white/20 backdrop-blur-md text-white p-3 sm:p-4 rounded-full hover:bg-white/30 transition-all duration-300 hover:scale-110 z-10 group" x-show="slidesLoaded">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <button @click="nextSlide()"
                class="absolute right-2 sm:right-4 top-1/2 transform -translate-y-1/2 bg-white/20 backdrop-blur-md text-white p-3 sm:p-4 rounded-full hover:bg-white/30 transition-all duration-300 hover:scale-110 z-10 group" x-show="slidesLoaded">
            <svg class="w-5 h-5 sm:w-6 sm:h-6 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>
    </div>
    
    <!-- Progress Bar -->
    <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/20 z-10" x-show="slidesLoaded">
        <div class="slider-progress h-full bg-white transition-all duration-5000 ease-linear"
             :style="`width: ${(currentSlide + 1) / slides.length * 100}%`"></div>
    </div>
</section>



<!-- Mengapa Yok Lapor Section -->
<section class="py-16 bg-gradient-to-br from-indigo-50 to-blue-50 relative overflow-hidden" x-data="{ featuresLoaded: false }" x-init="setTimeout(() => featuresLoaded = true, 500)">
    <!-- SVG Background -->
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-12">
            <!-- Skeleton Loading State -->
            <template x-if="!featuresLoaded">
                <div>
                    <div class="skeleton skeleton-title w-96 h-12 mx-auto mb-4"></div>
                    <div class="skeleton skeleton-text w-2/3 h-6 mx-auto"></div>
                </div>
            </template>
            
            <!-- Actual Content -->
            <template x-if="featuresLoaded">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Mengapa <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-600">Yok Lapor</span>?
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Platform pelaporan masyarakat yang transparan, akuntabel, dan responsif untuk kemajuan bersama
                    </p>
                </div>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Transparansi -->
            <template x-if="!featuresLoaded">
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="skeleton skeleton-avatar w-16 h-16 mx-auto mb-4"></div>
                    <div class="skeleton skeleton-title w-32 h-6 mx-auto mb-2"></div>
                    <div class="skeleton skeleton-text w-full h-4 mb-2"></div>
                    <div class="skeleton skeleton-text w-3/4 h-4"></div>
                </div>
            </template>
            <template x-if="featuresLoaded">
                <div class="bg-gradient-to-br from-blue-50 to-sky-50 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-blue-100">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-400 to-sky-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Transparansi</h3>
                    <p class="text-gray-600 text-center">Setiap laporan dapat dipantau prosesnya secara transparan dari awal hingga selesai</p>
                </div>
            </template>

            <!-- Akuntabilitas -->
            <template x-if="!featuresLoaded">
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="skeleton skeleton-avatar w-16 h-16 mx-auto mb-4"></div>
                    <div class="skeleton skeleton-title w-32 h-6 mx-auto mb-2"></div>
                    <div class="skeleton skeleton-text w-full h-4 mb-2"></div>
                    <div class="skeleton skeleton-text w-3/4 h-4"></div>
                </div>
            </template>
            <template x-if="featuresLoaded">
                <div class="bg-gradient-to-br from-sky-50 to-blue-50 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-sky-100">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-sky-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Akuntabilitas</h3>
                    <p class="text-gray-600 text-center">Setiap laporan ditangani secara profesional dengan tanggung jawab penuh</p>
                </div>
            </template>

            <!-- Responsif -->
            <template x-if="!featuresLoaded">
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="skeleton skeleton-avatar w-16 h-16 mx-auto mb-4"></div>
                    <div class="skeleton skeleton-title w-32 h-6 mx-auto mb-2"></div>
                    <div class="skeleton skeleton-text w-full h-4 mb-2"></div>
                    <div class="skeleton skeleton-text w-3/4 h-4"></div>
                </div>
            </template>
            <template x-if="featuresLoaded">
                <div class="bg-gradient-to-br from-cyan-50 to-sky-50 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-cyan-100">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-cyan-400 to-sky-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Responsif</h3>
                    <p class="text-gray-600 text-center">Respon cepat terhadap setiap laporan dengan update status berkala</p>
                </div>
            </template>

            <!-- Kemudahan -->
            <template x-if="!featuresLoaded">
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="skeleton skeleton-avatar w-16 h-16 mx-auto mb-4"></div>
                    <div class="skeleton skeleton-title w-32 h-6 mx-auto mb-2"></div>
                    <div class="skeleton skeleton-text w-full h-4 mb-2"></div>
                    <div class="skeleton skeleton-text w-3/4 h-4"></div>
                </div>
            </template>
            <template x-if="featuresLoaded">
                <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-2xl shadow-xl p-6 transform hover:scale-105 transition-all duration-300 border border-blue-100">
                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-blue-400 to-cyan-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Kemudahan</h3>
                    <p class="text-gray-600 text-center">Mudah digunakan oleh siapa saja, kapan saja, dan di mana saja</p>
                </div>
            </template>
        </div>
    </div>
</section>

<!-- Cara Kerja Yok Lapor Section -->
<section class="py-16 bg-gradient-to-br from-white via-blue-50 to-sky-50 relative overflow-hidden" x-data="{ workflowLoaded: false }" x-init="setTimeout(() => workflowLoaded = true, 800)">
    <!-- SVG Background -->
    <div class="absolute inset-0 opacity-3">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="workflow-pattern" x="0" y="0" width="150" height="150" patternUnits="userSpaceOnUse">
                    <circle cx="75" cy="75" r="2" fill="#3b82f6"/>
                    <path d="M0 75 L150 75 M75 0 L75 150" stroke="#3b82f6" stroke-width="0.3"/>
                    <polygon points="75,20 85,40 65,40" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <polygon points="75,130 85,110 65,110" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <polygon points="20,75 40,85 40,65" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <polygon points="130,75 110,85 110,65" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <circle cx="37" cy="37" r="1.5" fill="#06b6d4" opacity="0.6"/>
                    <circle cx="113" cy="37" r="1.5" fill="#06b6d4" opacity="0.6"/>
                    <circle cx="37" cy="113" r="1.5" fill="#06b6d4" opacity="0.6"/>
                    <circle cx="113" cy="113" r="1.5" fill="#06b6d4" opacity="0.6"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#workflow-pattern)"/>
        </svg>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-12">
            <!-- Skeleton Loading State -->
            <template x-if="!workflowLoaded">
                <div>
                    <div class="skeleton skeleton-title w-96 h-12 mx-auto mb-4"></div>
                    <div class="skeleton skeleton-text w-2/3 h-6 mx-auto"></div>
                </div>
            </template>
            
            <!-- Actual Content -->
            <template x-if="workflowLoaded">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Cara Kerja <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-cyan-600">Yok Lapor</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Proses pelaporan yang sederhana dan mudah diikuti oleh seluruh masyarakat
                    </p>
                </div>
            </template>
        </div>

        <!-- Flow Diagram -->
        <div class="relative">
            <!-- Desktop Version -->
            <div class="hidden lg:block">
                <!-- Skeleton Loading State -->
                <template x-if="!workflowLoaded">
                    <div class="flex items-center justify-between relative">
                        <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 transform -translate-y-1/2 z-0"></div>
                        
                        <!-- Step Skeletons -->
                        <div class="relative z-10 text-center">
                            <div class="skeleton skeleton-avatar w-20 h-20 mx-auto mb-4"></div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <div class="skeleton skeleton-text w-24 h-4 mx-auto mb-2"></div>
                                <div class="skeleton skeleton-text w-32 h-3 mx-auto"></div>
                            </div>
                        </div>
                        
                        <div class="relative z-10 flex items-center justify-center">
                            <div class="skeleton skeleton-avatar w-12 h-12"></div>
                        </div>
                        
                        <div class="relative z-10 text-center">
                            <div class="skeleton skeleton-avatar w-20 h-20 mx-auto mb-4"></div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <div class="skeleton skeleton-text w-24 h-4 mx-auto mb-2"></div>
                                <div class="skeleton skeleton-text w-32 h-3 mx-auto"></div>
                            </div>
                        </div>
                        
                        <div class="relative z-10 flex items-center justify-center">
                            <div class="skeleton skeleton-avatar w-12 h-12"></div>
                        </div>
                        
                        <div class="relative z-10 text-center">
                            <div class="skeleton skeleton-avatar w-20 h-20 mx-auto mb-4"></div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <div class="skeleton skeleton-text w-24 h-4 mx-auto mb-2"></div>
                                <div class="skeleton skeleton-text w-32 h-3 mx-auto"></div>
                            </div>
                        </div>
                        
                        <div class="relative z-10 flex items-center justify-center">
                            <div class="skeleton skeleton-avatar w-12 h-12"></div>
                        </div>
                        
                        <div class="relative z-10 text-center">
                            <div class="skeleton skeleton-avatar w-20 h-20 mx-auto mb-4"></div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <div class="skeleton skeleton-text w-24 h-4 mx-auto mb-2"></div>
                                <div class="skeleton skeleton-text w-32 h-3 mx-auto"></div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Actual Content -->
                <template x-if="workflowLoaded">
                    <div class="flex items-center justify-between relative">
                        <!-- Line Connection -->
                        <div class="absolute top-1/2 left-0 right-0 h-1 bg-gradient-to-r from-indigo-200 to-blue-200 transform -translate-y-1/2 z-0"></div>
                        
                        <!-- Step 1 -->
                        <div class="relative z-10 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-sky-600 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <h3 class="font-bold text-gray-900 mb-2">1. Buat Laporan</h3>
                                <p class="text-sm text-gray-600">Isi form laporan dengan detail masalah</p>
                            </div>
                        </div>

                        <!-- Arrow 1 -->
                        <div class="relative z-10 flex items-center justify-center">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg border-2 border-blue-200">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="relative z-10 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-sky-500 to-cyan-600 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <h3 class="font-bold text-gray-900 mb-2">2. Kirim Laporan</h3>
                                <p class="text-sm text-gray-600">Submit laporan dengan bukti pendukung</p>
                            </div>
                        </div>

                        <!-- Arrow 2 -->
                        <div class="relative z-10 flex items-center justify-center">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg border-2 border-sky-200">
                                <svg class="w-6 h-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="relative z-10 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <h3 class="font-bold text-gray-900 mb-2">3. Proses Verifikasi</h3>
                                <p class="text-sm text-gray-600">Tim kami verifikasi dan proses laporan</p>
                            </div>
                        </div>

                        <!-- Arrow 3 -->
                        <div class="relative z-10 flex items-center justify-center">
                            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center shadow-lg border-2 border-cyan-200">
                                <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="relative z-10 text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="bg-white rounded-xl shadow-lg p-4">
                                <h3 class="font-bold text-gray-900 mb-2">4. Tindak Lanjut</h3>
                                <p class="text-sm text-gray-600">Laporan ditindaklanjuti dan diselesaikan</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Mobile Version -->
            <div class="lg:hidden">
                <!-- Skeleton Loading State -->
                <template x-if="!workflowLoaded">
                    <div class="space-y-8">
                        <div class="flex items-center">
                            <div class="skeleton skeleton-avatar w-16 h-16 flex-shrink-0"></div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <div class="skeleton skeleton-text w-32 h-4 mb-1"></div>
                                <div class="skeleton skeleton-text w-full h-3"></div>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="skeleton skeleton-avatar w-16 h-16 flex-shrink-0"></div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <div class="skeleton skeleton-text w-32 h-4 mb-1"></div>
                                <div class="skeleton skeleton-text w-full h-3"></div>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="skeleton skeleton-avatar w-16 h-16 flex-shrink-0"></div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <div class="skeleton skeleton-text w-32 h-4 mb-1"></div>
                                <div class="skeleton skeleton-text w-full h-3"></div>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="skeleton skeleton-avatar w-16 h-16 flex-shrink-0"></div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <div class="skeleton skeleton-text w-32 h-4 mb-1"></div>
                                <div class="skeleton skeleton-text w-full h-3"></div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Actual Content -->
                <template x-if="workflowLoaded">
                    <div class="space-y-8">
                        <!-- Step 1 -->
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-600 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <h3 class="font-bold text-gray-900 mb-1">1. Buat Laporan</h3>
                                <p class="text-sm text-gray-600">Isi form laporan dengan detail masalah</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-sky-500 to-cyan-600 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                            </div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <h3 class="font-bold text-gray-900 mb-1">2. Kirim Laporan</h3>
                                <p class="text-sm text-gray-600">Submit laporan dengan bukti pendukung</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <h3 class="font-bold text-gray-900 mb-1">3. Proses Verifikasi</h3>
                                <p class="text-sm text-gray-600">Tim kami verifikasi dan proses laporan</p>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="flex items-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-lg flex-shrink-0">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4 bg-white rounded-xl shadow-lg p-4 flex-1">
                                <h3 class="font-bold text-gray-900 mb-1">4. Tindak Lanjut</h3>
                                <p class="text-sm text-gray-600">Laporan ditindaklanjuti dan diselesaikan</p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- CTA Button -->
        <div class="text-center mt-12">
            <!-- Skeleton Loading State -->
            <template x-if="!workflowLoaded">
                <div class="skeleton skeleton-card w-64 h-16 mx-auto rounded-xl"></div>
            </template>
            
            <!-- Actual Content -->
            <!-- <template x-if="workflowLoaded">
                <a href="<?= base_url('frontend/pelaporan') ?>" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Laporan Sekarang
                </a>
            </template> -->
        </div>
    </div>
</section>

<!-- Nomor Darurat Section -->
<section class="py-16 bg-gradient-to-br from-slate-50 via-blue-50 to-sky-50 relative overflow-hidden" x-data="{ emergencyLoaded: false }" x-init="setTimeout(() => emergencyLoaded = true, 1200)">
    <!-- SVG Background -->
    <div class="absolute inset-0 opacity-5">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="emergency-pattern" x="0" y="0" width="100" height="100" patternUnits="userSpaceOnUse">
                    <circle cx="50" cy="50" r="2" fill="#3b82f6"/>
                    <path d="M0 50 L100 50 M50 0 L50 100" stroke="#3b82f6" stroke-width="0.5"/>
                    <polygon points="50,15 60,35 40,35" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <polygon points="50,85 60,65 40,65" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <polygon points="15,50 35,60 35,40" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <polygon points="85,50 65,60 65,40" fill="none" stroke="#0ea5e9" stroke-width="0.5"/>
                    <circle cx="25" cy="25" r="1.5" fill="#06b6d4" opacity="0.6"/>
                    <circle cx="75" cy="25" r="1.5" fill="#06b6d4" opacity="0.6"/>
                    <circle cx="25" cy="75" r="1.5" fill="#06b6d4" opacity="0.6"/>
                    <circle cx="75" cy="75" r="1.5" fill="#06b6d4" opacity="0.6"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#emergency-pattern)"/>
        </svg>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-12">
            <!-- Skeleton Loading State -->
            <template x-if="!emergencyLoaded">
                <div>
                    <div class="skeleton skeleton-title w-80 h-12 mx-auto mb-4"></div>
                    <div class="skeleton skeleton-text w-2/3 h-6 mx-auto"></div>
                </div>
            </template>
            
            <!-- Actual Content -->
            <template x-if="emergencyLoaded">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                        Nomor <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-sky-600">Darurat</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                        Hubungi nomor-nomor darurat untuk situasi yang memerlukan penanganan segera
                    </p>
                </div>
            </template>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (isset($emergency_contacts) && is_array($emergency_contacts) && count($emergency_contacts) > 0): ?>
                <?php foreach ($emergency_contacts as $kontak): ?>
                    <?php if (isset($kontak['nama_kontak']) && isset($kontak['no_kontak'])): ?>
                    <div class="group relative" x-show="emergencyLoaded" x-transition.opacity>
                        <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-blue-600 rounded-2xl opacity-25 group-hover:opacity-75 blur transition duration-300"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                            <div class="flex items-center mb-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300">
                                    <?php
                                    // Determine icon based on contact name
                                    $icon_class = 'fa-phone-alt';
                                    $nama_lower = strtolower($kontak['nama_kontak']);
                                    
                                    if (strpos($nama_lower, 'polisi') !== false || strpos($nama_lower, 'keamanan') !== false) {
                                        $icon_class = 'fa-shield-alt';
                                    } elseif (strpos($nama_lower, 'damkar') !== false || strpos($nama_lower, 'pemadam') !== false || strpos($nama_lower, 'kebakaran') !== false) {
                                        $icon_class = 'fa-fire-extinguisher';
                                    } elseif (strpos($nama_lower, 'ambulan') !== false || strpos($nama_lower, 'medis') !== false || strpos($nama_lower, 'rumah sakit') !== false) {
                                        $icon_class = 'fa-ambulance';
                                    } elseif (strpos($nama_lower, 'sar') !== false || strpos($nama_lower, 'penyelamatan') !== false) {
                                        $icon_class = 'fa-life-ring';
                                    } elseif (strpos($nama_lower, 'bencana') !== false || strpos($nama_lower, 'bpbd') !== false) {
                                        $icon_class = 'fa-house-damage';
                                    } elseif (strpos($nama_lower, 'covid') !== false || strpos($nama_lower, 'kesehatan') !== false) {
                                        $icon_class = 'fa-virus';
                                    }
                                    ?>
                                    <i class="fas <?= $icon_class ?> text-white text-2xl"></i>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-xl font-bold text-gray-900"><?= $kontak['nama_kontak'] ?></h3>
                                    <p class="text-sm text-gray-500"><?= isset($kontak['deskripsi']) ? $kontak['deskripsi'] : 'Layanan Darurat' ?></p>
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-4 text-center group-hover:bg-blue-100 transition-colors duration-300">
                                <a href="tel:<?= $kontak['no_kontak'] ?>" class="group">
                                    <div class="text-3xl font-bold text-blue-600 group-hover:text-blue-700 transition-colors duration-300 mb-2"><?= $kontak['no_kontak'] ?></div>
                                    <div class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-full group-hover:bg-blue-700 transition-colors duration-300">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        Panggil Sekarang
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Default emergency contacts if no data in database -->
                <!-- Polisi -->
                <div class="group relative" x-show="emergencyLoaded" x-transition.opacity>
                    <div class="absolute -inset-1 bg-gradient-to-r from-red-400 to-red-600 rounded-2xl opacity-25 group-hover:opacity-75 blur transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-red-400 to-red-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-shield-alt text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">Polisi</h3>
                                <p class="text-sm text-gray-500">Keamanan & Ketertiban</p>
                            </div>
                        </div>
                        <div class="bg-red-50 rounded-xl p-4 text-center group-hover:bg-red-100 transition-colors duration-300">
                            <a href="tel:110" class="group">
                                <div class="text-3xl font-bold text-red-600 group-hover:text-red-700 transition-colors duration-300 mb-2">110</div>
                                <div class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-full group-hover:bg-red-700 transition-colors duration-300">
                                    <i class="fas fa-phone mr-2"></i>
                                    Panggil Sekarang
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pemadam Kebakaran -->
                <div class="group relative" x-show="emergencyLoaded" x-transition.opacity>
                    <div class="absolute -inset-1 bg-gradient-to-r from-orange-400 to-orange-600 rounded-2xl opacity-25 group-hover:opacity-75 blur transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-fire-extinguisher text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">Pemadam Kebakaran</h3>
                                <p class="text-sm text-gray-500">Kebakaran & Penyelamatan</p>
                            </div>
                        </div>
                        <div class="bg-orange-50 rounded-xl p-4 text-center group-hover:bg-orange-100 transition-colors duration-300">
                            <a href="tel:113" class="group">
                                <div class="text-3xl font-bold text-orange-600 group-hover:text-orange-700 transition-colors duration-300 mb-2">113</div>
                                <div class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-full group-hover:bg-orange-700 transition-colors duration-300">
                                    <i class="fas fa-phone mr-2"></i>
                                    Panggil Sekarang
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Ambulans -->
                <div class="group relative" x-show="emergencyLoaded" x-transition.opacity>
                    <div class="absolute -inset-1 bg-gradient-to-r from-green-400 to-green-600 rounded-2xl opacity-25 group-hover:opacity-75 blur transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-ambulance text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">Ambulans</h3>
                                <p class="text-sm text-gray-500">Kedaruratan Medis</p>
                            </div>
                        </div>
                        <div class="bg-green-50 rounded-xl p-4 text-center group-hover:bg-green-100 transition-colors duration-300">
                            <a href="tel:118" class="group">
                                <div class="text-3xl font-bold text-green-600 group-hover:text-green-700 transition-colors duration-300 mb-2">118</div>
                                <div class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-full group-hover:bg-green-700 transition-colors duration-300">
                                    <i class="fas fa-phone mr-2"></i>
                                    Panggil Sekarang
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- SAR -->
                <div class="group relative" x-show="emergencyLoaded" x-transition.opacity>
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-blue-600 rounded-2xl opacity-25 group-hover:opacity-75 blur transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-life-ring text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">SAR</h3>
                                <p class="text-sm text-gray-500">Penyelamatan & Evakuasi</p>
                            </div>
                        </div>
                        <div class="bg-blue-50 rounded-xl p-4 text-center group-hover:bg-blue-100 transition-colors duration-300">
                            <a href="tel:115" class="group">
                                <div class="text-3xl font-bold text-blue-600 group-hover:text-blue-700 transition-colors duration-300 mb-2">115</div>
                                <div class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-full group-hover:bg-blue-700 transition-colors duration-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    Panggil Sekarang
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Bencana Alam -->
                <div class="group relative" x-show="emergencyLoaded" x-transition.opacity>
                    <div class="absolute -inset-1 bg-gradient-to-r from-purple-400 to-purple-600 rounded-2xl opacity-25 group-hover:opacity-75 blur transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-house-damage text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">Bencana Alam</h3>
                                <p class="text-sm text-gray-500">BPBD & Tanggap Darurat</p>
                            </div>
                        </div>
                        <div class="bg-purple-50 rounded-xl p-4 text-center group-hover:bg-purple-100 transition-colors duration-300">
                            <a href="tel:129" class="group">
                                <div class="text-3xl font-bold text-purple-600 group-hover:text-purple-700 transition-colors duration-300 mb-2">129</div>
                                <div class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-full group-hover:bg-purple-700 transition-colors duration-300">
                                    <i class="fas fa-phone mr-2"></i>
                                    Panggil Sekarang
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Darurat COVID-19 -->
                <div class="group relative" x-show="emergencyLoaded" x-transition.opacity>
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-400 to-indigo-600 rounded-2xl opacity-25 group-hover:opacity-75 blur transition duration-300"></div>
                    <div class="relative bg-white rounded-2xl shadow-2xl p-6 transform hover:scale-105 transition-all duration-300 border border-gray-100">
                        <div class="flex items-center mb-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300">
                                <i class="fas fa-virus text-white text-2xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-xl font-bold text-gray-900">COVID-19</h3>
                                <p class="text-sm text-gray-500">Kesehatan Masyarakat</p>
                            </div>
                        </div>
                        <div class="bg-indigo-50 rounded-xl p-4 text-center group-hover:bg-indigo-100 transition-colors duration-300">
                            <a href="tel:119" class="group">
                                <div class="text-3xl font-bold text-indigo-600 group-hover:text-indigo-700 transition-colors duration-300 mb-2">119</div>
                                <div class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-full group-hover:bg-indigo-700 transition-colors duration-300">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    Panggil Sekarang
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Skeleton Loading State -->
            <template x-if="!emergencyLoaded">
                <!-- Emergency Card Skeletons -->
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="skeleton skeleton-avatar w-16 h-16"></div>
                        <div class="ml-4">
                            <div class="skeleton skeleton-text w-24 h-6 mb-1"></div>
                            <div class="skeleton skeleton-text w-32 h-4"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="skeleton skeleton-text w-16 h-8 mx-auto mb-2"></div>
                        <div class="skeleton skeleton-text w-32 h-8 mx-auto rounded-full"></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="skeleton skeleton-avatar w-16 h-16"></div>
                        <div class="ml-4">
                            <div class="skeleton skeleton-text w-32 h-6 mb-1"></div>
                            <div class="skeleton skeleton-text w-36 h-4"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="skeleton skeleton-text w-16 h-8 mx-auto mb-2"></div>
                        <div class="skeleton skeleton-text w-32 h-8 mx-auto rounded-full"></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="skeleton skeleton-avatar w-16 h-16"></div>
                        <div class="ml-4">
                            <div class="skeleton skeleton-text w-24 h-6 mb-1"></div>
                            <div class="skeleton skeleton-text w-32 h-4"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="skeleton skeleton-text w-16 h-8 mx-auto mb-2"></div>
                        <div class="skeleton skeleton-text w-32 h-8 mx-auto rounded-full"></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="skeleton skeleton-avatar w-16 h-16"></div>
                        <div class="ml-4">
                            <div class="skeleton skeleton-text w-16 h-6 mb-1"></div>
                            <div class="skeleton skeleton-text w-40 h-4"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="skeleton skeleton-text w-16 h-8 mx-auto mb-2"></div>
                        <div class="skeleton skeleton-text w-32 h-8 mx-auto rounded-full"></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="skeleton skeleton-avatar w-16 h-16"></div>
                        <div class="ml-4">
                            <div class="skeleton skeleton-text w-32 h-6 mb-1"></div>
                            <div class="skeleton skeleton-text w-36 h-4"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="skeleton skeleton-text w-16 h-8 mx-auto mb-2"></div>
                        <div class="skeleton skeleton-text w-32 h-8 mx-auto rounded-full"></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="skeleton skeleton-avatar w-16 h-16"></div>
                        <div class="ml-4">
                            <div class="skeleton skeleton-text w-24 h-6 mb-1"></div>
                            <div class="skeleton skeleton-text w-32 h-4"></div>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="skeleton skeleton-text w-16 h-8 mx-auto mb-2"></div>
                        <div class="skeleton skeleton-text w-32 h-8 mx-auto rounded-full"></div>
                    </div>
                </div>
            </template>
        </div>

        <div class="text-center mt-12">
            <!-- Skeleton Loading State -->
            <template x-if="!emergencyLoaded">
                <div class="skeleton skeleton-text w-96 h-6 mx-auto"></div>
            </template>
            
            <!-- Actual Content -->
            <template x-if="emergencyLoaded">
                <p class="text-gray-600">
                    <i class="fas fa-info-circle mr-2"></i>
                    Nomor-nomor di atas dapat dihubungi 24 jam untuk situasi darurat
                </p>
            </template>
        </div>
    </div>
</section>



<!-- Enhanced Slider Data Function -->
<script>
function sliderData() {
    return {
        currentSlide: 0,
        slidesLoaded: false,
        isPlaying: true,
        slideInterval: null,
        progressInterval: null,
        slideProgress: 0,
        slides: <?php
            // Prepare slides data from database
            $slides = [];
            if (isset($slider_data) && !empty($slider_data)) {
                foreach ($slider_data as $slider) {
                    $slide_image = $slider->slider_file ? base_url('uploads/slider/' . $slider->slider_file) : base_url('assets/images/hero-1.svg');
                    $slides[] = [
                        'image' => $slide_image,
                        'title' => $slider->slider_judul,
                        'subtitle' => $slider->slider_deskripsi ?: 'Temukan informasi terbaik untuk Anda'
                    ];
                }
            } else {
                // Fallback to default slides if no data in database
                $slides = [
                    [
                        'image' => '<?= base_url("assets/images/hero-1.svg") ?>',
                        'title' => '<?= isset($site_name) ? $site_name : "Dashboard Masyarakat" ?>',
                        'subtitle' => 'Sistem Informasi Publik untuk Transparansi Data Fasilitas Umum'
                    ],
                    [
                        'image' => '<?= base_url("assets/images/hero-2.svg") ?>',
                        'title' => 'Akses Informasi Mudah',
                        'subtitle' => 'Temukan berbagai fasilitas umum dengan cepat dan transparan'
                    ],
                    [
                        'image' => '<?= base_url("assets/images/hero-3.svg") ?>',
                        'title' => 'Data Terupdate',
                        'subtitle' => 'Informasi fasilitas umum selalu diperbarui secara berkala'
                    ]
                ];
            }
            echo json_encode($slides, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        ?>,
        init() {
            // Simulate loading delay for demo purposes
            setTimeout(() => {
                this.slidesLoaded = true;
                this.startAutoPlay();
            }, 1000);
            
            // Pause on hover
            this.$el.addEventListener('mouseenter', () => {
                this.pauseAutoPlay();
            });
            
            // Resume on mouse leave
            this.$el.addEventListener('mouseleave', () => {
                if (this.isPlaying) {
                    this.startAutoPlay();
                }
            });
        },
        startAutoPlay() {
            this.slideInterval = setInterval(() => {
                this.nextSlide();
            }, 5000);
        },
        pauseAutoPlay() {
            clearInterval(this.slideInterval);
        },
        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        },
        previousSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        },
        goToSlide(index) {
            this.currentSlide = index;
            // Restart autoplay when manually changing slide
            if (this.isPlaying) {
                this.pauseAutoPlay();
                this.startAutoPlay();
            }
        },
        togglePlayPause() {
            this.isPlaying = !this.isPlaying;
            if (this.isPlaying) {
                this.startAutoPlay();
            } else {
                this.pauseAutoPlay();
            }
        }
    }
}

// Hide loading overlay when page is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
    }, 1500);
});
</script>



<!-- Include centralized JavaScript -->
<?php $this->load->view('frontend/js_frontend'); ?>
