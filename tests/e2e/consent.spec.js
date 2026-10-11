const { test, expect } = require('@playwright/test');
const TRACKERS = /googletagmanager|google-analytics|connect\.facebook|facebook\.net|doubleclick|hotjar|clarity\.ms/;

test('no marketing/analytics requests before consent; Reject keeps them off', async ({ page }) => {
  const hits = [];
  page.on('request', (r) => { if (TRACKERS.test(r.url())) hits.push(r.url()); });
  await page.goto('/', { waitUntil: 'networkidle' });
  await expect(page.locator('.ew-consent')).toBeVisible();
  expect(hits).toEqual([]);
  await page.getByRole('button', { name: /Odrzuć/ }).first().click();
  await page.reload({ waitUntil: 'networkidle' });
  await expect(page.locator('.ew-consent')).toBeHidden();
  expect(hits).toEqual([]);
});

test('consent buttons have equal prominence and settings can be reopened from the footer', async ({ page }) => {
  await page.goto('/');
  const accept = page.getByRole('button', { name: /Akceptuj/ }).first();
  const reject = page.getByRole('button', { name: /Odrzuć/ }).first();
  await expect(accept).toBeVisible();
  await expect(reject).toBeVisible();
  await reject.click();
  await page.click('[data-ew-consent-open]');
  await expect(page.locator('[data-ew-consent-dialog]')).toBeVisible();
});
