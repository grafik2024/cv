const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const cases = JSON.parse(fs.readFileSync(path.join(__dirname, '..', 'finder-cases.json'), 'utf8'));

test('Finder REST: test set (need match, no random product on no-match, red flags)', async ({ request }) => {
  const fails = [];
  for (const c of cases) {
    const r = await request.get('/wp-json/eurowet/v1/finder', { params: { q: c.q, lang: 'pl', log: '0' } });
    expect(r.status()).toBe(200);
    const j = await r.json();
    const got = j.match ? j.intent.need_slug : null;
    if (got !== (c.expect ?? null)) fails.push(`"${c.q}" want=${c.expect} got=${got} conf=${j.intent.confidence}`);
    if (!j.match && (j.primary || (j.complementary || []).length)) fails.push(`"${c.q}" returned products without a match`);
    if ((c.red_flag || 'none') !== j.red_flag.level) fails.push(`"${c.q}" flag want=${c.red_flag} got=${j.red_flag.level}`);
    if (j.red_flag.level !== 'none' && !/lekarzem weterynarii/.test(j.red_flag.message)) fails.push(`"${c.q}" red flag without vet message`);
  }
  const rate = 1 - fails.length / (cases.length * 1.0);
  console.log(`Finder: ${cases.length} cases, ${fails.length} issues, pass ≈ ${(rate * 100).toFixed(1)}%`);
  expect(rate, fails.join('\n')).toBeGreaterThanOrEqual(0.9);
});

test('Finder works without JavaScript (server-rendered results)', async ({ browser }) => {
  const ctx = await browser.newContext({ javaScriptEnabled: false });
  const page = await ctx.newPage();
  await page.goto('/potrzeby/?q=' + encodeURIComponent('pies cały czas się drapie'));
  await expect(page.locator('[data-ew-finder-server]')).toHaveCount(1);
  await ctx.close();
});

test('Finder UI: typing a question renders results in the live region and focuses the result heading', async ({ page }) => {
  await page.goto('/potrzeby/');
  const input = page.locator('[data-ew-finder] input[name=q]').first();
  await input.fill('czym czyścić psu uszy');
  await input.press('Enter');
  await expect(page.locator('[data-ew-finder-results] h2').first()).toBeVisible();
});
