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
await page.setViewport({ width: 1440, height: 900 });
await page.goto('http://localhost:8080/team.php', { waitUntil: 'load', timeout: 45000 });
await page.addStyleTag({ content: '.reveal{opacity:1!important;transform:none!important}.team-card:nth-child(2){transform:translateY(-8px);border-color:rgba(34,197,94,.5);box-shadow:0 26px 54px -22px rgba(34,197,94,.28)}.team-card:nth-child(2) .team-card__socials{opacity:1!important;transform:none!important}.team-card:nth-child(2) .team-card__media img{transform:scale(1.07)}' });
await page.evaluate(() => document.querySelector('.team-grid').scrollIntoView({ block: 'start' }));
await new Promise(r => setTimeout(r, 1800));
await page.screenshot({ path: '/home/user/growfy/preview/shots/team-hover-grid.png' });
console.log('ok');
await browser.close();
process.exit(0);
