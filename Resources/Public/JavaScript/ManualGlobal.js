class DocumentService {
  constructor() {
    this.bindListener();
  }

  bindListener() {
    const contentIframe = top.document.getElementById('typo3-contentIframe');
    if (contentIframe) {
      contentIframe.addEventListener('load', function () {
        const tree = top.document.querySelector('typo3-backend-navigation-component-pagetree');
        const isManualModule = top.window.location.pathname === '/typo3/module/help/XimaTypo3ManualManual'
        if (!isManualModule && tree && tree.classList.contains('filtered-for-manuals')) {
          tree.classList.remove('filtered-for-manuals');
          tree.refresh();
        }
      });
    }
  }
}

export default new DocumentService();
