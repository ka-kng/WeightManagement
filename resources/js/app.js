import './bootstrap';
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import Swiper from 'swiper/bundle';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import './chart/weightWeek';
import './chart/bmiWeek';


// DOM が完全に読み込まれてから初期化
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.mySwiper').forEach(el => {
        const slides = el.querySelectorAll('.swiper-slide');
        if(slides.length === 0) return; // スライドがなければ初期化しない

        new Swiper(el, {
            loop: slides.length > 1, // 1枚だけならループ無効
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
