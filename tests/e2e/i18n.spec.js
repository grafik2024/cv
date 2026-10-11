const { test, expect } = require('@playwright/test');
const { languages } = require('./pages');

test('PL home exposes hreflang alternates incl. x-default', async ({ page }) => {
  await page.goto('/');
  const hreflangs = await page.locator('link[rel=alternate][hreflang]').evaluateAll((l) => l.map((x) => x.getAttribute('hreflang')));
  expect(hreflangs).toEqual(expect.arrayContaining(['x-default']));
  expect(hreflangs.some((h) => h.startsWith('pl'))).toBeTruthy();
});

for (const l of languages) {
  test(`language ${l.lang}: 200, html lang, localized bases`, async ({ page }) => {
    const res = await page.goto(l.path);
    expect(res.status()).toBe(200);
    expect(await page.getAttribute('html', 'lang')).toMatch(new RegExp('^' + l.lang));
  });
}

test('browser language does not change the PL home (no Accept-Language redirect)', async ({ browser }) => {
  const ctx = await browser.newContext({ locale: 'en-GB', extraHTTPHeaders: { 'Accept-Language': 'en-GB,en;q=0.9' } });
  const page = await ctx.newPage();
  const res = await page.goto('/');
  expect(res.status()).toBe(200);
  expect(await page.getAttribute('html', 'lang')).toMatch(/^pl/);
  await ctx.close();
});
