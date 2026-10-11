const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const rows = fs.readFileSync(path.join(__dirname, '..', '..', 'content', 'redirects.csv'), 'utf8').trim().split('\n').slice(1)
  .map((l) => l.split(',')).filter((r) => r[2] === '301');

test(`301 map: every OLD path redirects once to NEW (${rows.length} rows)`, async ({ request }) => {
  const bad = [];
  for (const [oldPath, newPath] of rows) {
    const r = await request.get(oldPath, { maxRedirects: 0 });
    const loc = (r.headers().location || '').replace(/^https?:\/\/[^/]+/, '');
    if (r.status() !== 301 || loc !== newPath) bad.push(`${oldPath} → ${r.status()} ${loc} (want ${newPath})`);
  }
  expect(bad, bad.slice(0, 40).join('\n')).toEqual([]);
});
