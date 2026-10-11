// Eurowet 2026 QA suite. BASE_URL defaults to the local WordPress (tools/local-env). Chromium from PW_CHROMIUM or the
// preinstalled /opt/pw-browsers/chromium when present (no browser download needed in CI containers).
const fs = require('fs');
const { defineConfig } = require('@playwright/test');
const exe = process.env.PW_CHROMIUM || (fs.existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined);

module.exports = defineConfig({
  testDir: './e2e',
  timeout: 60_000,
  retries: 0,
  workers: 2,
  reporter: [['list'], ['json', { outputFile: 'output/results.json' }]],
  use: {
    baseURL: process.env.BASE_URL || 'http://localhost:8080',
    launchOptions: exe ? { executablePath: exe } : {},
    locale: 'pl-PL',
    screenshot: 'only-on-failure',
  },
  outputDir: 'output/artifacts',
});
