<script setup>
import { computed } from 'vue';

const props = defineProps({
    isChecking: {
        type: Boolean,
        default: false,
    },
    patientName: {
        type: String,
        default: 'Pasien',
    },
});
</script>

<template>
    <div class="relative w-full h-full min-h-[300px] flex items-center justify-center overflow-hidden select-none">
        <!-- Background Ambient Glow & Organic Shapes (Style Banjarnegara Vector Art) -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <!-- Warm & Violet Abstract Background Circles (like the reference illustration) -->
            <div class="absolute -top-12 -left-12 w-64 h-64 rounded-full bg-violet-400/20 blur-3xl animate-pulse"></div>
            <div class="absolute top-1/3 right-4 w-72 h-72 rounded-full bg-indigo-500/15 blur-2xl"></div>
            <div class="absolute bottom-0 left-1/4 w-80 h-44 rounded-full bg-purple-300/20 blur-xl"></div>
            
            <!-- Minimalist Decorative Clinic Window / Arch Background -->
            <svg class="absolute inset-0 w-full h-full opacity-15" viewBox="0 0 600 400" fill="none" preserveAspectRatio="none">
                <path d="M 50,400 L 50,160 Q 50,80 130,80 L 220,80 Q 300,80 300,160 L 300,400 Z" fill="#6366f1" />
                <path d="M 320,400 L 320,200 Q 320,130 380,130 L 460,130 Q 520,130 520,200 L 520,400 Z" fill="#8b5cf6" />
                <circle cx="210" cy="180" r="40" fill="#a855f7" opacity="0.3" />
                <circle cx="420" cy="220" r="50" fill="#6366f1" opacity="0.25" />
            </svg>
        </div>

        <!-- Main Vector Illustration Stage (Vector Flat Layered Art in Project Violet/Purple/Slate palette) -->
        <svg
            viewBox="0 0 700 450"
            class="w-full max-w-[620px] max-h-[360px] drop-shadow-md z-10 transition-transform duration-500"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <!-- Gradients -->
                <linearGradient id="wallGlow" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#f8fafc" stop-opacity="0.9" />
                    <stop offset="100%" stop-color="#ede9fe" stop-opacity="0.8" />
                </linearGradient>

                <linearGradient id="deskGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#334155" />
                    <stop offset="100%" stop-color="#1e293b" />
                </linearGradient>

                <linearGradient id="doctorCoat" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#ffffff" />
                    <stop offset="100%" stop-color="#f1f5f9" />
                </linearGradient>

                <linearGradient id="doctorShirt" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#8b5cf6" />
                    <stop offset="100%" stop-color="#6d28d9" />
                </linearGradient>

                <linearGradient id="patientCloth" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#a78bfa" />
                    <stop offset="100%" stop-color="#7c3aed" />
                </linearGradient>

                <linearGradient id="bedGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#6366f1" />
                    <stop offset="100%" stop-color="#4f46e5" />
                </linearGradient>

                <filter id="shadowFilter" x="-10%" y="-10%" width="120%" height="120%">
                    <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#0f172a" flood-opacity="0.12" />
                </filter>
            </defs>

            <!-- FLOOR BASE -->
            <ellipse cx="350" cy="415" rx="310" ry="22" fill="#e2e8f0" opacity="0.6" />
            <ellipse cx="350" cy="415" rx="260" ry="14" fill="#cbd5e1" opacity="0.4" />

            <!-- BACKGROUND CLINIC ELEMENTS -->
            <!-- Medical Screen / ECG Monitor on wall -->
            <g transform="translate(70, 70)">
                <rect x="0" y="0" width="130" height="90" rx="10" fill="#0f172a" filter="url(#shadowFilter)" />
                <rect x="6" y="6" width="118" height="78" rx="6" fill="#1e1b4b" />
                
                <!-- Animated ECG Line inside monitor -->
                <path
                    d="M 12,48 L 35,48 L 45,26 L 55,62 L 65,38 L 75,48 L 115,48"
                    stroke="#10b981"
                    stroke-width="3"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    fill="none"
                    class="animate-pulse"
                />
                <!-- Heartbeat icon badge -->
                <circle cx="106" cy="20" r="5" fill="#ef4444" class="animate-ping" />
                <circle cx="106" cy="20" r="4" fill="#ef4444" />
                <text x="14" y="24" fill="#94a3b8" font-size="10" font-family="monospace" font-weight="bold">BPM: 78</text>
                <text x="14" y="74" fill="#6366f1" font-size="9" font-family="sans-serif">PULSE OK</text>
            </g>

            <!-- Medical Cross Wall Sign -->
            <g transform="translate(325, 45)">
                <circle cx="25" cy="25" r="25" fill="#ede9fe" />
                <rect x="21" y="11" width="8" height="28" rx="2" fill="#7c3aed" />
                <rect x="11" y="21" width="28" height="8" rx="2" fill="#7c3aed" />
            </g>

            <!-- IV Drip Stand (Tiang Infus) -->
            <g transform="translate(580, 110)">
                <!-- Pole -->
                <line x1="20" y1="30" x2="20" y2="290" stroke="#94a3b8" stroke-width="4" stroke-linecap="round" />
                <line x1="5" y1="318" x2="20" y2="290" stroke="#64748b" stroke-width="3" />
                <line x1="35" y1="318" x2="20" y2="290" stroke="#64748b" stroke-width="3" />
                <!-- Hanger -->
                <path d="M 6,36 Q 20,20 20,30 Q 20,20 34,36" stroke="#94a3b8" stroke-width="3" fill="none" />
                <!-- Bottle -->
                <rect x="10" y="42" width="20" height="34" rx="4" fill="#e0e7ff" stroke="#818cf8" stroke-width="1.5" />
                <rect x="13" y="52" width="14" height="22" rx="2" fill="#c7d2fe" />
                <!-- Drip tube -->
                <path d="M 20,76 C 20,130 30,160 5,200" stroke="#c7d2fe" stroke-width="2" fill="none" stroke-dasharray="3 3" />
            </g>

            <!-- EXAMINATION BED (Ranjang Periksa) -->
            <g transform="translate(380, 240)">
                <!-- Mattress & Bed Base -->
                <rect x="0" y="70" width="190" height="20" rx="8" fill="url(#bedGrad)" />
                <path d="M 140,70 L 185,35 L 190,45 L 145,75 Z" fill="#4338ca" />
                <!-- Legs -->
                <rect x="15" y="90" width="8" height="80" rx="3" fill="#64748b" />
                <rect x="165" y="90" width="8" height="80" rx="3" fill="#64748b" />
                <line x1="15" y1="130" x2="173" y2="130" stroke="#94a3b8" stroke-width="3" />
                <!-- Pillow -->
                <rect x="135" y="32" width="45" height="18" rx="8" transform="rotate(-30 135 32)" fill="#ffffff" stroke="#e2e8f0" />
            </g>

            <!-- DOCTOR & PATIENT CHARACTERS -->

            <!-- 1. PATIENT (Duduk santai di kursi samping / ranjang) -->
            <g id="patientCharacter" transform="translate(420, 160)">
                <!-- Shadow -->
                <ellipse cx="45" cy="225" rx="35" ry="8" fill="#94a3b8" opacity="0.3" />

                <!-- Chair Legs -->
                <line x1="25" y1="170" x2="18" y2="225" stroke="#475569" stroke-width="4" stroke-linecap="round" />
                <line x1="65" y1="170" x2="72" y2="225" stroke="#475569" stroke-width="4" stroke-linecap="round" />
                <!-- Chair Seat -->
                <rect x="15" y="155" width="60" height="12" rx="4" fill="#334155" />

                <!-- Patient Body / Torso (Vector flat style like Banjarnegara illustration) -->
                <!-- Legs -->
                <path d="M 28,155 L 28,195 L 38,220" stroke="#334155" stroke-width="12" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                <path d="M 52,155 L 52,195 L 62,220" stroke="#1e293b" stroke-width="12" stroke-linecap="round" stroke-linejoin="round" fill="none" />

                <!-- Torso Cloth -->
                <path d="M 25,95 Q 45,90 65,95 L 68,160 Q 45,165 22,160 Z" fill="url(#patientCloth)" />

                <!-- Head & Hair -->
                <circle cx="45" cy="62" r="22" fill="#fed7aa" />
                <!-- Hair (Modern clean shape) -->
                <path d="M 25,62 C 25,40 40,36 55,38 C 68,40 70,52 68,62 C 60,54 52,54 45,56 C 36,54 30,56 25,62 Z" fill="#451a03" />

                <!-- Face profile -->
                <circle cx="36" cy="62" r="2.5" fill="#451a03" /> <!-- Eye -->
                <path d="M 32,72 Q 38,76 44,72" stroke="#ea580c" stroke-width="2" stroke-linecap="round" fill="none" /> <!-- Smile -->

                <!-- Patient Arm resting -->
                <path d="M 32,105 Q 20,135 25,145" stroke="#fed7aa" stroke-width="9" stroke-linecap="round" fill="none" />
                <!-- Other arm resting on chest -->
                <path d="M 58,105 Q 40,125 36,128" stroke="#fed7aa" stroke-width="9" stroke-linecap="round" fill="none" />
            </g>

            <!-- 2. DOCTOR (Animasi tangan memeriksa dengan Stetoskop & Tubuh Mengayun) -->
            <g id="doctorCharacter" transform="translate(240, 130)">
                <!-- Doctor Shadow -->
                <ellipse cx="60" cy="255" rx="42" ry="10" fill="#94a3b8" opacity="0.35" />

                <!-- Doctor Legs & Shoes -->
                <path d="M 45,210 L 42,365" stroke="#1e293b" stroke-width="14" stroke-linecap="round" />
                <path d="M 75,210 L 78,365" stroke="#0f172a" stroke-width="14" stroke-linecap="round" />
                <!-- Shoes -->
                <ellipse cx="38" cy="372" rx="14" ry="7" fill="#0f172a" />
                <ellipse cx="84" cy="372" rx="14" ry="7" fill="#0f172a" />

                <!-- Doctor Torso / White Lab Coat (Jas Dokter) -->
                <!-- Inner scrub / purple shirt -->
                <path d="M 42,95 L 78,95 L 75,150 L 45,150 Z" fill="url(#doctorShirt)" />

                <!-- White Lab Coat Layers -->
                <path d="M 32,90 Q 60,86 88,90 L 98,245 Q 60,250 22,245 Z" fill="url(#doctorCoat)" filter="url(#shadowFilter)" />
                <path d="M 52,90 L 46,245" stroke="#cbd5e1" stroke-width="2" />
                <path d="M 68,90 L 74,245" stroke="#cbd5e1" stroke-width="2" />

                <!-- Doctor ID Badge -->
                <rect x="74" y="115" width="14" height="18" rx="2" fill="#7c3aed" />
                <rect x="76" y="117" width="10" height="6" rx="1" fill="#ffffff" />
                <line x1="77" y1="126" x2="85" y2="126" stroke="#ffffff" stroke-width="1.5" />

                <!-- Doctor Head & Hair -->
                <!-- Neck -->
                <rect x="52" y="74" width="16" height="18" fill="#fcd34d" />
                <!-- Head -->
                <circle cx="60" cy="56" r="23" fill="#fcd34d" />
                <!-- Doctor Glasses (Kacamata Intelektual Modern) -->
                <circle cx="68" cy="54" r="7" stroke="#6366f1" stroke-width="2" fill="#ffffff" fill-opacity="0.25" />
                <line x1="61" y1="54" x2="63" y2="54" stroke="#6366f1" stroke-width="2" />
                <!-- Hair (Stylized Flat Art) -->
                <path d="M 38,52 C 38,32 50,28 66,28 C 82,28 86,40 85,54 C 76,44 68,44 60,46 C 52,44 44,46 38,52 Z" fill="#1e293b" />
                <circle cx="70" cy="54" r="2.5" fill="#0f172a" /> <!-- Eye -->
                <path d="M 65,66 Q 71,70 76,66" stroke="#b45309" stroke-width="2" stroke-linecap="round" fill="none" /> <!-- Friendly Smile -->

                <!-- STETHOSCOPE (Stetoskop yang terhubung ke telinga dan dipegang ke pasien) -->
                <!-- Earpieces -->
                <path d="M 55,48 Q 50,62 58,78 Q 62,88 60,110" stroke="#64748b" stroke-width="3" fill="none" stroke-linecap="round" />
                <path d="M 68,48 Q 72,62 64,78" stroke="#64748b" stroke-width="3" fill="none" stroke-linecap="round" />

                <!-- Left Arm (Holding clipboard or resting) -->
                <path d="M 32,95 Q 15,130 30,150" stroke="#ffffff" stroke-width="16" stroke-linecap="round" fill="none" />
                <circle cx="30" cy="152" r="8" fill="#fcd34d" />
                <!-- Medical Clipboard -->
                <g transform="translate(18, 140) rotate(-15)">
                    <rect x="0" y="0" width="22" height="32" rx="3" fill="#b45309" />
                    <rect x="2" y="4" width="18" height="26" rx="2" fill="#ffffff" />
                    <line x1="5" y1="10" x2="17" y2="10" stroke="#7c3aed" stroke-width="2" />
                    <line x1="5" y1="15" x2="15" y2="15" stroke="#94a3b8" stroke-width="1.5" />
                    <line x1="5" y1="19" x2="13" y2="19" stroke="#94a3b8" stroke-width="1.5" />
                </g>

                <!-- RIGHT ARM: ANIMATED EXAMINING ACTION (Mengulur ke pasien dengan stetoskop di dada pasien) -->
                <!-- Class conditional animation: when checking or active -->
                <g class="doctor-arm-group origin-[85px_100px]">
                    <!-- Upper & Forearm reaching towards patient -->
                    <path
                        d="M 85,100 Q 115,115 140,125 L 175,130"
                        stroke="#ffffff"
                        stroke-width="16"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        fill="none"
                    />
                    <!-- Hand -->
                    <circle cx="177" cy="130" r="8" fill="#fcd34d" />
                    <!-- Stethoscope Head / Chestpiece held in hand against patient -->
                    <circle cx="186" cy="130" r="7" fill="#64748b" stroke="#334155" stroke-width="2" />
                    <circle cx="186" cy="130" r="4" fill="#94a3b8" />
                    <!-- Stethoscope rubber cord from neck to hand -->
                    <path
                        d="M 60,110 C 65,150 120,165 178,135"
                        stroke="#475569"
                        stroke-width="3"
                        fill="none"
                        stroke-linecap="round"
                    />

                    <!-- Glowing Stethoscope Pulse Wave during checking -->
                    <circle cx="186" cy="130" r="14" fill="#8b5cf6" opacity="0.3" class="animate-ping" />
                </g>
            </g>

            <!-- DOCTOR DESK IN FOREGROUND (Meja Dokter & Aksesoris) -->
            <g transform="translate(110, 260)">
                <!-- Desk Top Surface -->
                <path d="M 0,55 L 170,55 L 160,85 L 0,85 Z" fill="url(#deskGrad)" rx="4" filter="url(#shadowFilter)" />
                <!-- Desk Legs -->
                <rect x="10" y="85" width="10" height="95" fill="#0f172a" rx="2" />
                <rect x="145" y="85" width="10" height="95" fill="#1e293b" rx="2" />
                <!-- Laptop on desk -->
                <rect x="35" y="38" width="45" height="18" rx="2" fill="#64748b" />
                <path d="M 40,40 L 40,24 Q 40,20 44,20 L 71,20 Q 75,20 75,24 L 75,40 Z" fill="#0f172a" />
                <rect x="43" y="23" width="29" height="15" fill="#38bdf8" opacity="0.8" class="animate-pulse" />
                <!-- Prescription Medicine Bottle on desk -->
                <rect x="95" y="34" width="14" height="22" rx="3" fill="#ffffff" stroke="#e2e8f0" stroke-width="1.5" />
                <rect x="97" y="30" width="10" height="5" rx="1" fill="#ef4444" />
                <rect x="98" y="41" width="8" height="8" rx="1" fill="#7c3aed" />
                <!-- Pen holder -->
                <rect x="120" y="36" width="12" height="20" rx="3" fill="#cbd5e1" />
                <line x1="123" y1="26" x2="123" y2="40" stroke="#6366f1" stroke-width="2.5" />
                <line x1="128" y1="28" x2="128" y2="40" stroke="#10b981" stroke-width="2" />
            </g>

            <!-- FLOATING CARTOON ACCENTS & HEARTS / MEDICAL PLUS -->
            <g class="animate-bounce" style="animation-duration: 2.5s;">
                <circle cx="560" cy="180" r="14" fill="#ede9fe" />
                <path d="M 556,180 L 564,180 M 560,176 L 560,184" stroke="#7c3aed" stroke-width="2.5" stroke-linecap="round" />
            </g>
            <g class="animate-pulse" style="animation-duration: 3s;">
                <circle cx="190" cy="130" r="10" fill="#fef2f2" />
                <path d="M 187,130 C 187,126 193,126 193,130 C 193,133 190,135 187,137 C 184,135 181,133 181,130 C 181,126 187,126 187,130 Z" fill="#ef4444" transform="scale(0.8) translate(40, 30)" />
            </g>
        </svg>

        <!-- Status Bubble Floating Over The Scene -->
        <div class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/90 backdrop-blur-md border border-violet-200 shadow-sm text-xs text-slate-700">
            <span class="size-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span class="font-semibold text-violet-700">Pemeriksaan Medis:</span>
            <span class="font-medium text-slate-800 truncate max-w-[200px]">{{ patientName }}</span>
        </div>
    </div>
</template>

<style scoped>
/* Keyframe Animation for Doctor Arm Movement (Examining Patient) */
@keyframes doctorExamining {
    0%, 100% {
        transform: rotate(0deg) translate(0px, 0px);
    }
    50% {
        transform: rotate(-3deg) translate(-2px, -3px);
    }
}

.doctor-arm-group {
    animation: doctorExamining 3s ease-in-out infinite;
    transform-origin: 85px 100px;
}
</style>

