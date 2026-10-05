/**
* The chapter tree of the sidebar: a leaf count per branch, the +/− sign of the design, and the expand/collapse all
* controls. On the single-page manual the open state survives a reload, so an editor keeps the branch they were
* working in. With one page per chapter the server opens the branches leading to the current page instead.
*/
class Tree {
  #storageKey = 'ximaTypo3Manual.openChapters'

  constructor() {
    this.details = [...document.querySelectorAll('.manual-nav details')]
    if (this.details.length === 0) {
      return
    }

    const rememberState = document.querySelector('.manual-nav')?.dataset.fullManual === '1'

    this.details.forEach((details, index) => {
      this.decorate(details, index)
      details.querySelector(':scope > summary')?.addEventListener('click', event => this.restrictToggle(event))
      details.addEventListener('toggle', () => {
        this.updateSign(details)
        if (rememberState) {
          this.persist()
        }
      })
    })

    if (rememberState) {
      this.restore()
    }
    this.bindControls()
  }

  decorate(details, index) {
    details.dataset.treeId = details.querySelector(':scope > summary > a')?.getAttribute('href') || 'branch-' + index

    const leaves = details.querySelectorAll(':scope > ol > li > a, :scope > ol > li > details').length
    if (leaves > 0) {
      const count = document.createElement('span')
      count.className = 'manual-nav_count'
      count.textContent = String(this.countLeaves(details))
      details.querySelector(':scope > summary')?.append(count)
    }

    this.updateSign(details)
  }

  // Only the sign and the keyboard toggle a branch, the browser would otherwise toggle on a click anywhere in the row,
  // the link included. A link click therefore cancels the toggle and navigates itself; modified clicks (new tab,
  // new window) are left to the browser.
  restrictToggle(event) {
    const link = event.target.closest('a')
    if (link) {
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return
      }
      event.preventDefault()
      window.location.assign(link.href)
      return
    }

    const keyboard = event.detail === 0
    if (keyboard || event.target.closest('.manual-nav_sign')) {
      return
    }
    event.preventDefault()
  }

  countLeaves(details) {
    return details.querySelectorAll(':scope > ol > li').length
  }

  updateSign(details) {
    const sign = details.querySelector(':scope > summary > .manual-nav_sign')
    if (sign) {
      sign.textContent = details.open ? '−' : '+'
    }
  }

  bindControls() {
    document.querySelectorAll('[data-manual-tree]').forEach(button => {
      button.addEventListener('click', () => {
        const open = button.dataset.manualTree === 'expand'
        this.details.forEach(details => {
          details.open = open
        })
      })
    })
  }

  persist() {
    try {
      const open = this.details.filter(d => d.open).map(d => d.dataset.treeId)
      window.localStorage.setItem(this.#storageKey, JSON.stringify(open))
    } catch {
      // Storage can be unavailable, the tree simply starts collapsed next time
    }
  }

  restore() {
    let open = []
    try {
      open = JSON.parse(window.localStorage.getItem(this.#storageKey) || '[]')
    } catch {
      open = []
    }

    if (!Array.isArray(open) || open.length === 0) {
      // Without a stored state the first level is open, so the manual never looks empty
      this.details.filter(d => d.closest('.manual-nav') === d.parentElement?.parentElement).forEach(d => {
        d.open = true
      })
      this.details.forEach(d => this.updateSign(d))
      return
    }

    this.details.forEach(details => {
      details.open = open.includes(details.dataset.treeId)
      this.updateSign(details)
    })
  }
}

export default new Tree()
