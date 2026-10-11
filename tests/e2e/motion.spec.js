const { test, expect } = require('@playwright/test');

test('reduced motion: hero 3D never starts, static packshot stays', async ({ page }) => {
  await page.emulateMedia({ reducedMotion: 'reduce' });
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto('/', { waitUntil: 'networkidle' });
  await page.waitForTimeout(3500);
  await expect(page.locator('.ew-hero__canvas')).toHaveCount(0);
  await expect(page.locator('.ew-hero__packshot img')).toBeVisible();
});

test('mobile: hero 3D library is not downloaded', async ({ page }) => {
  const three = [];
  page.on('request', (r) => { if (/three\.module/.test(r.url())) three.push(r.url()); });
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/', { waitUntil: 'networkidle' });
  await page.waitForTimeout(3500);
  expect(three).toEqual([]);
});
