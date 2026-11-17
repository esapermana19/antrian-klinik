import { ref } from 'vue';

export function useQueueAnnouncer() {
    const isSpeaking = ref(false);
    const isMuted = ref(false);
    const isSupported =
        typeof window !== 'undefined' && 'speechSynthesis' in window;

    // Load setting mute dari localStorage bila ada
    if (typeof window !== 'undefined') {
        const savedMute = localStorage.getItem('queue_display_muted');
        if (savedMute !== null) {
            isMuted.value = savedMute === 'true';
        }
    }

    const toggleMute = () => {
        isMuted.value = !isMuted.value;
        if (typeof window !== 'undefined') {
            localStorage.setItem('queue_display_muted', String(isMuted.value));
        }
        if (isMuted.value && isSupported) {
            window.speechSynthesis.cancel();
            isSpeaking.value = false;
        }
    };

    /**
     * Membunyikan bel ting-tong pembuka menggunakan Web Audio API
     */
    const playBellChime = (): Promise<void> => {
        return new Promise((resolve) => {
            try {
                const AudioCtx =
                    window.AudioContext || (window as any).webkitAudioContext;
                if (!AudioCtx) {
                    resolve();
                    return;
                }

                const ctx = new AudioCtx();
                const now = ctx.currentTime;

                // Nada Pertama (Ding: ~659.25 Hz - E5)
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(659.25, now);
                gain1.gain.setValueAtTime(0.25, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.5);

                // Nada Kedua (Dong: ~523.25 Hz - C5)
                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(523.25, now + 0.35);
                gain2.gain.setValueAtTime(0.3, now + 0.35);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.9);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.35);
                osc2.stop(now + 0.9);

                setTimeout(() => {
                    resolve();
                }, 900);
            } catch {
                resolve();
            }
        });
    };

    /**
     * Memanggil antrean menggunakan Web Speech Synthesis API Bahasa Indonesia
     */
    const announceQueue = async (
        queueNumber: string,
        patientName?: string,
        doctorName?: string,
    ) => {
        if (!isSupported || isMuted.value || !queueNumber) return;

        window.speechSynthesis.cancel();
        await playBellChime();

        if (isMuted.value) return;

        // Pisahkan format huruf dan angka agar dieja dengan jelas (Contoh: "P 0 1" atau "01")
        const formattedNumber = queueNumber.split('').join(' ');
        let textToSpeak = `Nomor antrean... ${formattedNumber}.`;
        if (patientName) {
            textToSpeak += ` Atas nama ${patientName}.`;
        }
        if (doctorName) {
            textToSpeak += ` Silakan menuju ke ruangan ${doctorName}.`;
        } else {
            textToSpeak += ` Silakan menuju ke ruang periksa.`;
        }

        const utterance = new SpeechSynthesisUtterance(textToSpeak);
        utterance.lang = 'id-ID';
        utterance.rate = 0.92; // Sedikit lebih santai dan jelas
        utterance.pitch = 1.05;

        // Pilih voice Bahasa Indonesia bila tersedia di browser
        const voices = window.speechSynthesis.getVoices();
        const idVoice = voices.find(
            (v) => v.lang.startsWith('id') || v.lang.includes('ID'),
        );
        if (idVoice) {
            utterance.voice = idVoice;
        }

        utterance.onstart = () => {
            isSpeaking.value = true;
        };

        utterance.onend = () => {
            isSpeaking.value = false;
        };

        utterance.onerror = () => {
            isSpeaking.value = false;
        };

        window.speechSynthesis.speak(utterance);
    };

    return {
        isSpeaking,
        isMuted,
        isSupported,
        toggleMute,
        announceQueue,
        playBellChime,
    };
}
