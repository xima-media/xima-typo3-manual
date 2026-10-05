/**
* Builds the "on this page" column from the headings of the chapter currently in view and keeps the active entry in
* sync while scrolling.
*/
class Toc {
  #list
  #links = new Map()

  constructor() {
    this.#list = document.querySelector('[data-manual-toc]')
    if (!this.#list) {
      return
    }

    this.build()
    if (this.#links.size < 2) {
      this.#list.closest('.manual-aside')?.setAttribute('hidden', '')
      return
    }
    this.observe()
  }

  build() {
    let headings = [...document.querySelectorAll('.manual-chapter [data-level][id]')]

    if (this.#list.dataset.fullManual !== '1') {
      headings = headings.filter(heading => heading.closest('.manual-chapter').querySelector('[data-level][id]') !== heading)
    }

    const minLevel = Math.min(...headings.map(heading => Number(heading.dataset.level)))

    headings.forEach(heading => {
      const item = document.createElement('li')
      item.style.setProperty('--manual-toc-level', Number(heading.dataset.level) - minLevel)

      const link = document.createElement('a')
      link.href = '#' + heading.id
      link.textContent = heading.firstChild?.textContent.trim() || heading.textContent.trim()

      item.append(link)
      this.#list.append(item)
      this.#links.set(heading.id, link)
    })
  }

  observe() {
    const observer = new IntersectionObserver(entries => {
      const visible = entries.find(entry => entry.isIntersecting)
      if (!visible) {
        return
      }
      this.#links.forEach(link => link.classList.remove('active'))
      this.#links.get(visible.target.id)?.classList.add('active')
    }, { rootMargin: '-10% 0px -70%', threshold: 0 })

    this.#links.forEach((_, id) => {
      const heading = document.getElementById(id)
      if (heading) {
        observer.observe(heading)
      }
    })
  }
}

export default new Toc()
