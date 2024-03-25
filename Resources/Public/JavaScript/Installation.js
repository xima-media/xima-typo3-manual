import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

class Installation {
  constructor() {
    this.bindEvents()
  }

  bindEvents() {
    document.querySelectorAll('a.install[data-preset]').forEach(btn => {
      btn.addEventListener('click', this.onInstallClick.bind(this))
    })
  }

  navigateToStep(stepNr) {
    document.querySelectorAll('.manual-installation-step').forEach(step => step.classList.add('hidden'))
    document.querySelector(`.manual-installation-step[data-step="${stepNr}"]`).classList.remove('hidden')
  }

  onInstallClick(e) {
    e.preventDefault()
    e.currentTarget.classList.add('disabled')
    this.navigateToStep(2)
    const preset = e.currentTarget.getAttribute('data-preset')

    const request = new AjaxRequest(TYPO3.settings.ajaxUrls.manual_installation_create)
    request.post({
      preset: preset
    }).then(async data => {
      const html = await data.resolve()
      document.querySelector('.manual-installation-step[data-step="3"]').innerHTML = html
      this.navigateToStep(3)
    })
  }
}

export default new Installation()
