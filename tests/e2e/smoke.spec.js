const { test, expect } = require('@playwright/test');
const { core } = require('./pages');

for (const p of core) {
  test(`smoke ${p.name}: 200, one H1, landmarks, canonical, no JS errors`, async ({ page }) => {
    const errors = [];
    page.on('pageerror', (e) => errors.push(e.message));
    page.on('console', (m) => { if (m.type() === 'error' && !/favicon|net::ERR_/.test(m.text())) errors.push(m.text()); });
    const res = await page.goto(p.path, { waitUntil: 'networkidle' });
    expect(res.status(), p.path).toBe(200);
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('main#ew-main')).toHaveCount(1);
    await expect(page.locator('header').first()).toBeVisible();
    await expect(page.locator('footer').first()).toBeAttached();
    expect(await page.getAttribute('html', 'lang')).toMatch(/^pl/);
    const canonical = await page.locator('link[rel=canonical]').getAttribute('href');
    expect(canonical).toBeTruthy();
    const body = await page.locator('body').innerText();
    expect(body).not.toMatch(/lorem ipsum|example product|coming soon|test@test|dummy/i);
    expect(errors, errors.join('\n')).toEqual([]);
  });
}

test('skip link moves focus to main', async ({ page }) => {
  await page.goto('/');
  await page.keyboard.press('Tab');
  const skip = page.locator('.ew-skip-link');
  await expect(skip).toBeFocused();
  await page.keyboard.press('Enter');
  await expect(page).toHaveURL(/#ew-main$/);
});

test('robots.txt and llms.txt are served', async ({ request }) => {
  const robots = await request.get('/robots.txt');
  expect(robots.status()).toBe(200);
  const r = await robots.text();
  expect(r).toMatch(/User-agent: \*/);
  expect((r.match(/User-agent: \*/g) || []).length).toBe(1);
  expect(r).toMatch(/Sitemap:/);
  const llms = await request.get('/llms.txt');
  expect(llms.status()).toBe(200);
  expect(await llms.text()).toMatch(/^# /);
});

test('404 page returns 404 with a finder', async ({ page }) => {
  const res = await page.goto('/to-nie-istnieje-xyz/');
  expect(res.status()).toBe(404);
  await expect(page.locator('main [data-ew-finder]')).toHaveCount(1);
});
