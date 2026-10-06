/** Take full-page screenshots of the live preview for visual QA. */
import puppeteer from 'puppeteer-core';
import chromium from '@sparticuz/chromium';

const BASE = process.env.BASE || 'http://localhost:8080';
const out = process.argv[2] || 'shots';
import { mkdirSync } from 'node:fs';
mkdirSync(out, { recursive: true });

const browser = await puppeteer.launch({
  args: chromium.args,
  executablePath: await chromium.executablePath(),
  headless: 'shell',
});
const page = await browser.newPage();
await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1 });

// wait for webfonts & images
const go = async (url, name, opts = {}) => {
  await page.goto(url, { waitUntil: 'networkidle0', timeout: 60000 });
  await new Promise(r => setTimeout(r, opts.delay ?? 2500));
  await page.screenshot({ path: `${out}/${name}.png`, fullPage: !!opts.full });
  console.log('shot:', name);
};

await go(BASE + '/', 'home-fold');
console.log('home full…');
await page.screenshot({ path: `${out}/home-full.png`, fullPage: true });
console.log('shot: home-full');

// admin login
await go(BASE + '/admin/login.php', 'admin-login', { delay: 1200 });

// login → dashboard (default password flow will redirect to password change first)
await page.type('#username', 'admin');
await page.type('#password', 'Growfy@2026');
await Promise.all([
  page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 60000 }),
  page.click('button[type=submit]'),
]);
await new Promise(r => setTimeout(r, 1500));
await page.screenshot({ path: `${out}/admin-after-login.png` });
console.log('shot: admin-after-login @', page.url());

// if force-change screen, change password to continue QA
if (page.url().includes('tab=password')) {
  await page.type('input[name=current]', 'Growfy@2026');
  await page.type('input[name=new]', 'Preview@2026');
  await page.type('input[name=confirm]', 'Preview@2026');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 60000 }),
    page.click('button[type=submit]'),
  ]);
  await new Promise(r => setTimeout(r, 1200));
  console.log('password changed for preview session');
}
await go(BASE + '/admin/index.php', 'admin-dashboard', { delay: 1500 });
await go(BASE + '/admin/services.php', 'admin-services', { delay: 1000 });
await go(BASE + '/admin/orders.php', 'admin-orders', { delay: 1000 });

// mobile viewport
await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2 });
await go(BASE + '/', 'home-mobile', { delay: 1500 });

await browser.close();
console.log('done');
process.exit(0);
