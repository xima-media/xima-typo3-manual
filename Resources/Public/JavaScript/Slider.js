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
    this.containers[containerIndex].style.setProperty('--slider-current', index);
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
