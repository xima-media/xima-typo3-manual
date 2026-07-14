import {defineConfig, devices} from "@playwright/test";

const baseURL = process.env.PLAYWRIGHT_BASE_URL || `https://${process.env.VIRTUAL_HOST.split(',')[0]}`;
console.log(`Testing against: ${baseURL}`);

export default defineConfig({
  globalSetup: "./Tests/Playwright/global-setup.ts",
  timeout: 30 * 1000,
  forbidOnly: !!process.env.CI,
  fullyParallel: false,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: process.env.CI
    ? [["list"], ["html", { open: "never", outputFolder: "Tests/Playwright/playwright-report" }], ["junit", { outputFile: "Tests/Playwright/test-results/junit.xml" }]]
    : [["list"], ["html", { open: "never", outputFolder: "Tests/Playwright/playwright-report" }]],
  use: {
    baseURL,
    trace: "on-first-retry",
    screenshot: "only-on-failure",
    ignoreHTTPSErrors: true
  },

  expect: {
    toHaveScreenshot: {
      animations: "disabled",
      fullPage: true,
      maxDiffPixels: 100,
      maxDiffPixelRatio: 0.01,
      threshold: 0.2
    }
  },

  projects: [
    {
      name: "chromium",
      use: {...devices["Desktop Chrome"]}
    }
  ]
});
