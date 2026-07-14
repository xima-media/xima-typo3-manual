import { Page, expect } from '@playwright/test';

export interface ConsoleErrorTracker {
  assertNoErrors(): void;
}

/**
 * Attach a JS console error tracker to the given Playwright page.
 * Captures console.error() calls from all frames (including iframes).
 * Network-level errors ("Failed to load resource") are excluded.
 */
export function trackConsoleErrors(page: Page, ignorePatterns: RegExp[] = []): ConsoleErrorTracker {
  const errors: string[] = [];

  page.on('console', (msg) => {
    if (msg.type() !== 'error') return;
    const text = msg.text();
    if (text.startsWith('Failed to load resource')) return;
    if (ignorePatterns.some((pattern) => pattern.test(text))) return;
    errors.push(text);
  });

  return {
    assertNoErrors() {
      const summary = errors.map((text, i) => `  ${i + 1}. ${text}`).join('\n');
      expect(errors, `JS console errors detected:\n${summary}`).toHaveLength(0);
    },
  };
}
