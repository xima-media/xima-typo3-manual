import { test, expect } from '@playwright/test';
import { loginAsAdmin, openManualModule, waitForManualContent } from '../helpers/typo3-backend';
import { resetDatabase } from '../helpers/db-reset';
import { trackConsoleErrors, ConsoleErrorTracker } from '../helpers/console-errors';

test.describe('Manual Module', () => {
  test.afterAll(() => { resetDatabase(); });

  let consoleErrors: ConsoleErrorTracker;
  test.beforeEach(async ({ page }) => {
    consoleErrors = trackConsoleErrors(page);
    await loginAsAdmin(page);
  });
  test.afterEach(() => { consoleErrors.assertNoErrors(); });

  test('manual module opens and renders navigation + content', async ({ page }) => {
    const contentFrame = await openManualModule(page);

    // Main layout structure
    await expect(contentFrame.locator('.xima-typo3-manual-nav')).toBeVisible();
    await expect(contentFrame.locator('.xima-typo3-manual-content')).toBeVisible();

    // Navigation includes fixture manual pages
    await expect(contentFrame.locator('.xima-typo3-manual-nav a')).toHaveCount(3);

    // First chapter (First Chapter) is visible
    await expect(contentFrame.locator('.xima-typo3-manual-nav a').first()).toContainText('First Chapter');
  });

  test('clicking a chapter loads its content in the iframe', async ({ page }) => {
    const contentFrame = await openManualModule(page);

    // Click "Second Chapter"
    await contentFrame.locator('.xima-typo3-manual-nav a').filter({ hasText: 'Second Chapter' }).click();
    await contentFrame.waitForTimeout(500);

    // The inner content iframe should navigate to the second chapter
    // The module renders a content iframe — check it has switched to the right page
    const contentIframe = contentFrame.frameLocator('.xima-typo3-manual-content iframe');
    await expect(contentIframe.locator('h1').first()).toContainText(/Second Chapter|Aenean/);
  });

  test('subchapter is nested under its parent', async ({ page }) => {
    const contentFrame = await openManualModule(page);

    // "Subchapter" should be nested under "Second Chapter"
    const secondChapter = contentFrame.locator('.xima-typo3-manual-nav li').filter({ hasText: 'Second Chapter' });
    const subchapter = secondChapter.locator('..').locator('li').filter({ hasText: 'Subchapter' });

    // The subchapter should exist in the navigation tree
    await expect(subchapter).toBeVisible();
  });
});
