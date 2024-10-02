class Slider {
  constructor(container) {
    this.container = container;

    this.currentSlideIndex = 0;
    this.slideCount = container.querySelectorAll('.slide').length;
    this.addEventListeners();
  }

  addEventListeners() {
    this.container.querySelector('.prev-button').addEventListener('click', this.showPreviousSlide.bind(this));
    this.container.querySelector('.next-button').addEventListener('click', this.showNextSlide.bind(this));

    this.container.querySelectorAll('.indicator').forEach((indicator, index) => {
      indicator.addEventListener('click', () => {
        this.showSlide(index);
      });
    });
  }

  showSlide(index) {
    this.currentSlideIndex = index;
    this.container.style.setProperty('--slider-current', index);
    this.updateIndicators(index);
  }

  updateIndicators(index) {
    this.container.querySelectorAll('.indicator').forEach((indicator, i) => {
      indicator.classList.toggle('active', i === index);
    });
  }

  showPreviousSlide() {
    const prevIndex = (this.currentSlideIndex - 1 + this.slideCount) % this.slideCount;
    this.showSlide(prevIndex);
  }

  showNextSlide() {
    const nextIndex = (this.currentSlideIndex + 1) % this.slideCount;
    this.showSlide(nextIndex);
  }
}

document.querySelectorAll('.slider-container').forEach(container => {
  new Slider(container);
});
