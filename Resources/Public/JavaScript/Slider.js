class Slider {
  constructor(sliderContainer) {
    this.sliderContainer = sliderContainer;
    this.slides = Array.from(this.sliderContainer.querySelectorAll('.slide'));
    this.currentSlideIndex = 0;

    this.prevButton = this.sliderContainer.querySelector('.prev-button');
    this.nextButton = this.sliderContainer.querySelector('.next-button');

    this.prevButton.addEventListener('click', this.showPrevSlide.bind(this));
    this.nextButton.addEventListener('click', this.showNextSlide.bind(this));

    this.createIndicators();
    this.showSlide(this.currentSlideIndex);
  }

  createIndicators() {
    const indicatorsContainer = this.sliderContainer.querySelector('.indicators');

    this.slides.forEach((slide, index) => {
      const indicator = document.createElement('span');
      indicator.classList.add('indicator');
      indicator.textContent = `${index+1}`;
      indicator.addEventListener('click', () => this.showSlide(index));
      indicatorsContainer.appendChild(indicator);
    });
    this.indicators = Array.from(this.sliderContainer.querySelectorAll('.indicator'));
  }

  showSlide(index) {
    this.slides.forEach(slide => slide.style.display = 'none');
    this.slides[index].style.display = 'flex';

    this.indicators.forEach((indicator, i) => {
      if (i === index) {
        indicator.classList.add('active');
      } else {
        indicator.classList.remove('active');
      }
    });

    this.currentSlideIndex = index;
  }

  showNextSlide() {
    this.currentSlideIndex = (this.currentSlideIndex + 1) % this.slides.length;
    this.showSlide(this.currentSlideIndex);
  }

  showPrevSlide() {
    this.currentSlideIndex = (this.currentSlideIndex - 1 + this.slides.length) % this.slides.length;
    this.showSlide(this.currentSlideIndex);
  }
}

export default new Slider(document.querySelector('.slider-container'));
