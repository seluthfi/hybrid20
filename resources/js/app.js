import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

// Deteksi mode perangkat ringan (fx-lite) per PLAN.md Fase 6B
try {
    const isLowMemory = navigator.deviceMemory && navigator.deviceMemory <= 4;
    const isSaveData = navigator.connection && navigator.connection.saveData === true;
    const isReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (isLowMemory || isSaveData || isReducedMotion) {
        document.documentElement.classList.add('fx-lite');
    }
} catch (_) {}

// Pemulihan posisi scroll otomatis setelah submit form (PLAN.md §1 R17)
try {
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-preserve-scroll')) return;

        const keepElements = {};
        document.querySelectorAll('[data-scroll-keep][id]').forEach((el) => {
            keepElements[el.id] = el.scrollLeft;
        });

        const state = {
            url: window.location.pathname + window.location.search,
            y: window.scrollY,
            keep: keepElements,
        };

        try {
            sessionStorage.setItem('hybrid20:scroll', JSON.stringify(state));
        } catch (_) {}
    }, true);

    document.addEventListener('DOMContentLoaded', () => {
        let saved = null;
        try {
            const raw = sessionStorage.getItem('hybrid20:scroll');
            if (raw) {
                saved = JSON.parse(raw);
                sessionStorage.removeItem('hybrid20:scroll');
            }
        } catch (_) {}

        if (saved && saved.url === (window.location.pathname + window.location.search)) {
            window.requestAnimationFrame(() => {
                if (typeof saved.y === 'number') {
                    window.scrollTo(0, saved.y);
                }
                if (saved.keep && typeof saved.keep === 'object') {
                    Object.entries(saved.keep).forEach(([id, scrollLeft]) => {
                        const target = document.getElementById(id);
                        if (target) {
                            target.scrollLeft = scrollLeft;
                        }
                    });
                }
            });
        }
    });
} catch (_) {}

Alpine.start();
