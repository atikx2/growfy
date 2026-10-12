import puppeteer from 'puppeteer-core';
process.env.FONTCONFIG_PATH = '/tmp/fonts';
process.env.HOME = '/tmp';
process.env.LD_LIBRARY_PATH = '/tmp/al2023/lib:/tmp/swiftshader:/tmp';
process.env.VK_ICD_FILENAMES = '/tmp/swiftshader/vk_swiftshader_icd.json';

const browser = await puppeteer.launch({
  executablePath: '/tmp/chromium', headless: 'shell',
  args: ['--no-sandbox','--no-zygote','--single-process','--headless=shell','--disable-dev-shm-usage',
    '--use-gl=angle','--use-angle=swiftshader','--enable-unsafe-swiftshader','--disable-vulkan-surface',
    '--disable-features=AudioServiceOutOfProcess,IsolateOrigins,site-per-process','--no-first-run'],
});
const page = await browser.newPage();
await page.setRequestInterception(true);
page.on('request', (r) => (/fonts\.(googleapis|gstatic)\.com/.test(r.url()) ? r.abort() : r.continue()));
await page.setViewport({ width: 1440, height: 960 });

// login (integration test changed password to ArenaTest#2026)
await page.goto('http://localhost:8080/admin/login.php', { waitUntil: 'load', timeout: 45000 });
await page.type('input[name="username"]', 'admin');
await page.type('input[name="password"]', 'ArenaTest#2026');
await Promise.all([
  page.waitForNavigation({ waitUntil: 'load', timeout: 45000 }).catch(() => {}),
  page.click('button[type="submit"]'),
]);

await page.goto('http://localhost:8080/admin/team.php', { waitUntil: 'load', timeout: 45000 });
await new Promise(r => setTimeout(r, 1400));
await page.screenshot({ path: '/home/user/growfy/preview/shots/admin-team-list.png', fullPage: true });
console.log('shot: admin-team-list');

await page.goto('http://localhost:8080/admin/team.php?edit=1', { waitUntil: 'load', timeout: 45000 });
await new Promise(r => setTimeout(r, 1400));
await page.screenshot({ path: '/home/user/growfy/preview/shots/admin-team-edit.png', fullPage: true });
console.log('shot: admin-team-edit');

await browser.close();
process.exit(0);
