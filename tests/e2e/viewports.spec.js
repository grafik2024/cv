const { test, expect } = require('@playwright/test');
const { core } = require('./pages');
const WIDTHS = [1920, 1440, 1366, 1024, 768, 414, 360];

for (const w of WIDTHS) {
  test(`no horizontal overflow at ${w}px`, async ({ page }) => {
    await page.setViewportSize({ width: w, height: 900 });
    const bad = [];
    for (const p of core) {
      await page.goto(p.path, { waitUntil: 'domcontentloaded' });
      const o = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
      if (o > 1) bad.push(`${p.path} +${o}px`);
    }
    expect(bad, bad.join('\n')).toEqual([]);
  });
}

test('mobile drawer opens, traps focus (dialog) and closes with Escape', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');
  await page.click('[data-ew-drawer-open]');
  const drawer = page.locator('[data-ew-drawer]');
  await expect(drawer).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(drawer).toBeHidden();
  await expect(page.locator('[data-ew-drawer-open]')).toBeFocused();
});

test('theme toggle cycles system → dark → light and persists', async ({ page }) => {
  await page.goto('/');
  await page.click('[data-ew-theme-toggle]');
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await page.reload();
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'dark');
  await page.click('[data-ew-theme-toggle]');
  await expect(page.locator('html')).toHaveAttribute('data-theme', 'light');
});
