function createAllVanillaCarousels() {
    const carousels = document.querySelectorAll('.owl-carousel');

    if (carousels.length === 0) {
        setTimeout(createAllVanillaCarousels, 500);
        return;
    }

    carousels.forEach((owlCarousel, idx) => {
        // Evitar duplicación si ya fue inicializado
        if (owlCarousel.classList.contains('custom-carousel')) return;

        const slides = [];
        const owlItems = owlCarousel.querySelectorAll('.owl-item');

        owlItems.forEach(item => {
            slides.push(item.innerHTML);
        });

        if (slides.length === 0) {
            Array.from(owlCarousel.children).forEach(child => {
                slides.push(child.outerHTML);
            });
        }

        owlCarousel.innerHTML = '';
        owlCarousel.className = 'custom-carousel custom-carousel-' + idx;

        const wrapper = document.createElement('div');
        wrapper.className = 'carousel-wrapper';
        wrapper.style.cssText = `
      display: flex;
      transition: transform 0.3s ease;
      width: ${slides.length * 100}%;
    `;

        slides.forEach(slideContent => {
            const slide = document.createElement('div');
            slide.className = 'carousel-slide';
            slide.style.cssText = `
        flex: 0 0 ${100 / slides.length}%;
        box-sizing: border-box;
        padding: 0 5px;
      `;
            slide.innerHTML = slideContent;
            wrapper.appendChild(slide);
        });

        owlCarousel.appendChild(wrapper);

        let currentSlide = 0;

        function goToSlide(index) {
            currentSlide = Math.max(0, Math.min(slides.length - 1, index));
            wrapper.style.transform = translateX(-${ currentSlide * (100 / slides.length)
        }%);
}

const prevBtn = document.createElement('button');
prevBtn.innerHTML = '‹';
prevBtn.style.cssText = `
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 50%;
      padding: 5px;
      cursor: pointer;
    `;
prevBtn.onclick = () => goToSlide(currentSlide - 1);

const nextBtn = document.createElement('button');
nextBtn.innerHTML = '›';
nextBtn.style.cssText = `
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      z-index: 10;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 50%;
      padding: 5px;
      cursor: pointer;
    `;
nextBtn.onclick = () => goToSlide(currentSlide + 1);

owlCarousel.style.position = 'relative';
owlCarousel.style.overflow = 'hidden';
owlCarousel.appendChild(prevBtn);
owlCarousel.appendChild(nextBtn);
  });

console.log(Carruseles vanilla creados: ${ document.querySelectorAll('.custom-carousel').length });
}

createAllVanillaCarousels();