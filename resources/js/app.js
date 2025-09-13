import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import Swiper from 'swiper/bundle';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

// DOM が完全に読み込まれてから初期化
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.mySwiper').forEach(el => {
        new Swiper(el, {
            loop: true,
            pagination: {
                el: el.querySelector('.swiper-pagination'),
                clickable: true
            },
            navigation: {
                nextEl: el.querySelector('.swiper-button-next'),
                prevEl: el.querySelector('.swiper-button-prev')
            },
            observer: true,
            observeParents: true,
        });
    });
});
