class Coverage {
  constructor() {
    const container = document.querySelector('[data-manual-coverage]')
    if (!container) {
      return
    }

    container.querySelectorAll('[data-coverage-filter]').forEach(button => {
      button.addEventListener('click', () => {
        const filter = button.dataset.coverageFilter
        container.querySelectorAll('[data-coverage-filter]').forEach(b => b.classList.toggle('active', b === button))
        container.querySelectorAll('tbody tr').forEach(row => {
          row.hidden = filter === 'undocumented' && row.dataset.documented === '1'
        })
      })
    })
  }
}

export default new Coverage()
