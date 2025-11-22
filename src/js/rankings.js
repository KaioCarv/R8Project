
(function () {
  const track = document.querySelector('[data-carousel="week"]');
  if (!track) return;

  const btnLeft  = document.querySelector('[data-carousel-arrow="week-left"]');
  const btnRight = document.querySelector('[data-carousel-arrow="week-right"]');


  function getStep() {
    const card = track.querySelector('.poster-card');
    if (!card) return 200;
    const cardWidth = card.getBoundingClientRect().width;
    
    return cardWidth + 16;
  }

  function scrollByStep(direction) {
    const step = getStep();
    track.scrollBy({
      left: direction * step,
      behavior: 'smooth'
    });
  }

  if (btnLeft) {
    btnLeft.addEventListener('click', function () {
      scrollByStep(-1);
    });
  }

  if (btnRight) {
    btnRight.addEventListener('click', function () {
      scrollByStep(1);
    });
  }
})();
