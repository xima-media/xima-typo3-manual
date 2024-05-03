class Slider {
  constructor() {
    this.initializeSlider();
    this.addEventListeners();
  }

  initializeSlider() {
    this.containers = Array.from(document.querySelectorAll('.slider-container'));
    this.slides = this.containers.map(container => Array.from(container.querySelectorAll('.slide')));
    this.indicators = this.containers.map(container => Array.from(container.querySelectorAll('.indicator')));
    this.currentSlideIndices = this.slides.map(() => 0);

    this.slides.forEach((slides, containerIndex) => {
      slides.forEach((slide, index) => {
        this.setSlideDisplay(slide, index);
        this.setIndicatorActive(containerIndex, index);
      });
    });
  }

  setSlideDisplay(slide, index) {
    slide.style.display = index === 0 ? 'flex' : 'none';
  }

  setIndicatorActive(containerIndex, index) {
    if (index === 0) {
      this.indicators[containerIndex][index].classList.add('active');
    }
  }

  addEventListeners() {
    this.containers.forEach((container, containerIndex) => {
      const prevButton = container.querySelector('.prev-button');
      const nextButton = container.querySelector('.next-button');

      prevButton.addEventListener('click', () => this.showPreviousSlide(containerIndex));
      nextButton.addEventListener('click', () => this.showNextSlide(containerIndex));

      this.indicators[containerIndex].forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
          this.showSlide(containerIndex, index);
        });
      });
    });
  }

  showSlide(containerIndex, index) {
    this.currentSlideIndices[containerIndex] = index;
    this.slides[containerIndex].forEach((slide, i) => {
      slide.style.display = i === index ? 'flex' : 'none';
    });

    this.updateIndicators(containerIndex, index);
  }

  updateIndicators(containerIndex, index) {
    this.indicators[containerIndex].forEach((indicator, i) => {
      indicator.classList.toggle('active', i === index);
    });
  }

  showPreviousSlide(containerIndex) {
    const prevIndex = (this.currentSlideIndices[containerIndex] - 1 + this.slides[containerIndex].length) % this.slides[containerIndex].length;
    this.showSlide(containerIndex, prevIndex);
  }

  showNextSlide(containerIndex) {
    const nextIndex = (this.currentSlideIndices[containerIndex] + 1) % this.slides[containerIndex].length;
    this.showSlide(containerIndex, nextIndex);
  }
}

export default new Slider();
