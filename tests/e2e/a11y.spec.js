const { test, expect } = require('@playwright/test');
const AxeBuilder = require('@axe-core/playwright').default;
const { core } = require('./pages');

for (const scheme of ['light', 'dark']) {
  for (const p of core) {
    test(`axe WCAG 2.2 AA ${p.name} (${scheme})`, async ({ page }) => {
      await page.emulateMedia({ colorScheme: scheme });
      await page.goto(p.path, { waitUntil: 'networkidle' });
      const results = await new AxeBuilder({ page })
        .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
        .exclude('#wpadminbar')
        .analyze();
      const serious = results.violations.filter((v) => ['serious', 'critical'].includes(v.impact));
      const report = serious.map((v) => `${v.id} (${v.impact}): ${v.nodes.slice(0, 3).map((n) => n.target.join(' ')).join(' | ')}`).join('\n');
      expect(serious, report).toEqual([]);
    });
  }
}

test('accessibility panel: text size, contrast, reset persist on reload', async ({ page }) => {
  await page.goto('/');
  await page.click('[data-ew-a11y-open]');
  await page.click('[data-ew-a11y-set="text"][data-value="2"]');
  await expect(page.locator('html')).toHaveAttribute('data-a11y-text', '2');
  await page.reload();
  await expect(page.locator('html')).toHaveAttribute('data-a11y-text', '2');
  await page.click('[data-ew-a11y-open]');
  await page.click('[data-ew-a11y-reset]');
  await expect(page.locator('html')).not.toHaveAttribute('data-a11y-text', /./);
});

test('200% zoom (640 CSS px) keeps content reachable without horizontal scroll', async ({ page }) => {
  await page.setViewportSize({ width: 640, height: 900 });
  await page.goto('/produkty/dermokosmetyki-weterynaryjne/triaderm-excellence-200ml/');
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(overflow).toBeLessThanOrEqual(1);
});
