document.addEventListener('DOMContentLoaded', function(){
  // Carousel autoplay controls
  const carousel = document.getElementById('icbf-carousel-1');
  const carouselInstance = new bootstrap.Carousel(carousel, {
      interval: 5000
  });
  
  const playButton = document.getElementById('icbf-play-button-1');
  const pauseButton = document.getElementById('icbf-pause-button-1');
  
  playButton.addEventListener('click', function(){
      carouselInstance.cycle();
      playButton.classList.add('d-none');
      pauseButton.classList.remove('d-none');
  });
  
  pauseButton.addEventListener('click', function(){
      carouselInstance.pause();
      pauseButton.classList.add('d-none');
      playButton.classList.remove('d-none');
  });
});