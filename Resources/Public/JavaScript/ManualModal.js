import Modal from "@typo3/backend/modal.js";

class ManualModal {
  constructor() {
    document.querySelectorAll('a[data-manual-modal]').forEach(item => {
      item.addEventListener('click', e => {
        e.preventDefault()
        const url = e.currentTarget.getAttribute('href')
        Modal.advanced({
          type: Modal.types.iframe,
          title: 'Manual',
          content: url,
          size: Modal.sizes.large,
          staticBackdrop: true,
          buttons: [
            {
              text: TYPO3.lang['wizard.mbox.description'],
              name: 'open',
              icon: 'actions-window-open',
              active: true,
              btnClass: 'btn-primary',
              trigger: function() {
                window.open(url, '_blank').focus();
              }
            },
            {
              text: 'Close',
              name: 'close',
              icon: 'actions-close',
              btnClass: 'btn-secondary',
              trigger: function(event, modal) {
                modal.hideModal();
              }
            }
          ]
        });
      })
    })
  }
}

export default new ManualModal()
