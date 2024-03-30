import Modal from "@typo3/backend/modal.js";

class ManualModal {
  constructor() {
    document.querySelectorAll('a[data-manual-modal]').forEach(item => {
      item.addEventListener('click', e => {
        e.preventDefault()
        const url = e.currentTarget.getAttribute('href')
        const backendUrl = e.currentTarget.getAttribute('data-manual-backend-url')
        Modal.advanced({
          type: Modal.types.iframe,
          title: 'Manual',
          content: url,
          size: Modal.sizes.large,
          staticBackdrop: true,
          buttons: [
            {
              text: TYPO3.lang['button.modal.footer.open'],
              name: 'open',
              icon: 'actions-window-open',
              btnClass: 'btn-secondary',
              trigger: function() {
                window.open(backendUrl, '_blank').focus();
              }
            },
            {
              text: TYPO3.lang['button.modal.footer.close'],
              name: 'close',
              icon: 'actions-close',
              active: true,
              btnClass: 'btn-primary',
              trigger: function(event, modal) {
                modal.hideModal();
              }
            }
          ]
        });
      })
    })

    document.querySelectorAll('a[data-manual-preview]').forEach(btn => {
      btn.addEventListener('click', e => {
        const event = new CustomEvent('typo3:pagetree:mountPoint', {
          detail: {
            pageId: parseInt(e.currentTarget.getAttribute('data-manual-preview'))
          },
        })
        top.document.dispatchEvent(event)
      })

    })
  }
}

export default new ManualModal()
