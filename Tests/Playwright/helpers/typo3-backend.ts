import { Page, FrameLocator } from '@playwright/test';

export async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/typo3');
  await page.waitForSelector('#t3-username', { timeout: 10000 });
  await page.fill('#t3-username', 'admin');
  await page.fill('#t3-password', 'Passw0rd!');
  await page.click('#t3-login-submit-section > button');
  await page.waitForSelector('.scaffold-header', { timeout: 20000 });
}

/**
 * Open the manual module by its module menu identifier.
 * Returns the content frame (list_frame iframe).
 */
export async function openManualModule(page: Page): Promise<FrameLocator> {
  const contentFrame = page.frameLocator('iframe[name="list_frame"]');
  await page.click('a[data-modulemenu-identifier="help_manual"]');
  // The manual module renders its own layout inside the iframe
  await contentFrame.locator('.typo3-module-xima_typo3_manual').waitFor({ state: 'visible', timeout: 10000 });
  return contentFrame;
}

/**
 * Navigate to a manual page by clicking the sidebar navigation item with matching text.
 */
export async function navigateToManualPage(contentFrame: FrameLocator, title: string): Promise<void> {
  await contentFrame.locator('.xima-typo3-manual-nav a').filter({ hasText: title }).click();
  await contentFrame.waitForTimeout(500); // wait for scroll/animation
}

/**
 * Wait for the manual module content area to finish loading.
 */
export async function waitForManualContent(contentFrame: FrameLocator): Promise<void> {
  await contentFrame.locator('.xima-typo3-manual-content').waitFor({ state: 'visible', timeout: 10000 });
}
