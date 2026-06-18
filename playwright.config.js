import { defineConfig, devices } from '@playwright/test';

/**
 * Config E2E Playwright.
 * Le webServer reconstruit une base déterministe (migrate:fresh --seed) puis
 * démarre le serveur Laravel. Les assets doivent être buildés au préalable
 * (`npm run build`) ou servis via Vite.
 */
export default defineConfig({
    testDir: './tests/e2e',
    timeout: 30_000,
    fullyParallel: false,
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    reporter: process.env.CI ? [['github'], ['html', { open: 'never' }]] : 'list',
    use: {
        baseURL: process.env.E2E_BASE_URL || 'http://127.0.0.1:8090',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
    webServer: process.env.E2E_BASE_URL
        ? undefined
        : {
              command:
                  'php artisan migrate:fresh --seed --force && php artisan serve --port=8090',
              url: 'http://127.0.0.1:8090/up',
              reuseExistingServer: !process.env.CI,
              timeout: 120_000,
          },
});
