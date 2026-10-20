// Renders slides/<lang>/*.html to out/<lang>/*.png at full page height.
const { chromium } = require('playwright');
const fs = require('fs'), path = require('path');
(async () => {
  const base = __dirname;
  const browser = await chromium.launch();
  for (const lang of fs.readdirSync(path.join(base, 'slides'))) {
    const dir = path.join(base, 'slides', lang);
    const out = path.join(base, 'out', lang);
    fs.mkdirSync(out, { recursive: true });
    for (const f of fs.readdirSync(dir).filter(x => x.endsWith('.html')).sort()) {
      const cover = f.startsWith('00_');
      const page = await browser.newPage({ viewport: { width: cover ? 1616 : 1400, height: cover ? 1264 : 900 } });
      await page.goto('file://' + path.join(dir, f));
      await page.evaluate(() => document.fonts.ready);
      await page.waitForTimeout(150);
      const el = await page.$('.slide');
      await el.screenshot({ path: path.join(out, f.replace('.html', '.png')) });
      await page.close();
      console.log(lang, f);
    }
  }
  await browser.close();
})();
