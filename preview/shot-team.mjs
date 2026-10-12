import puppeteer from 'puppeteer-core';
import { mkdirSync } from 'node:fs';

process.env.FONTCONFIG_PATH = '/tmp/fonts';
process.env.HOME = '/tmp';
process.env.LD_LIBRARY_PATH = '/tmp/al2023/lib:/tmp/swiftshader:/tmp';
process.env.VK_ICD_FILENAMES = '/tmp/swiftshader/vk_swiftshader_icd.json';

const OUT = '/home/user/growfy/preview/shots';
mkdirSync(OUT, { recursive: true });

const browser = await puppeteer.launch({
  executablePath: '/tmp/chromium',
  headless: 'shell',
  args: [
    '--ash-no-nudges', '--disable-domain-reliability', '--disable-print-preview',
    '--disk-cache-size=33554432', '--no-default-browser-check', '--no-pings',
    '--single-process', '--font-render-hinting=none',
    '--disable-features=AudioServiceOutOfProcess,IsolateOrigins,site-per-process',
    '--enable-features=SharedArrayBuffer', '--ignore-gpu-blocklist', '--in-process-gpu',
    '--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader',
    '--allow-running-insecure-content', '--disable-setuid-sandbox',
    '--disable-site-isolation-trials', '--disable-web-security', '--headless=shell',
    '--no-sandbox', '--no-zygote', '--disable-dev-shm-usage', '--no-first-run',
    '--disable-vulkan-surface',
  ],
});
const page = await browser.newPage();
await page.setRequestInterception(true);
page.on('request', (r) => (/fonts\.(googleapis|gstatic)\.com/.test(r.url()) ? r.abort() : r.continue()));

async function shoot({ theme = 'dark', width = 1440, height = 900, name, fullPage = true, url = '/team.php' }) {
  await page.evaluateOnNewDocument((t) => { try { localStorage.setItem('growfy-theme', t); } catch (e) {} }, theme);
  await page.setViewport({ width, height, deviceScaleFactor: 1 });
  await page.goto('http://localhost:8080' + url, { waitUntil: 'load', timeout: 45000 });
  await page.evaluate(async () => {
    await new Promise(res => {
      let y = 0;
      const t = setInterval(() => { y += 500; window.scrollTo(0, y);
        if (y >= document.body.scrollHeight) { clearInterval(t); res(); } }, 60);
    });
    const st = document.createElement('style');
    st.textContent = '.reveal{opacity:1!important;transform:none!important}';
    document.head.appendChild(st);
    window.scrollTo(0, 0);
  });
  await new Promise(r => setTimeout(r, 2000));
  await page.screenshot({ path: `${OUT}/${name}.png`, fullPage });
  console.log('shot:', name);
}

await shoot({ theme: 'dark',  name: 'team-dark-full' });
await shoot({ theme: 'light', name: 'team-light-full' });
await shoot({ theme: 'dark',  name: 'team-dark-mobile',  width: 390, height: 844, fullPage: true });
await shoot({ theme: 'light', name: 'team-light-mobile', width: 390, height: 844, fullPage: true });

/* hover state preview — force socials visible on first card, desktop only */
await page.evaluateOnNewDocument(() => {});
await page.setViewport({ width: 1440, height: 900 });
await page.goto('http://localhost:8080/team.php', { waitUntil: 'load', timeout: 45000 });
await page.addStyleTag({ content: '.reveal{opacity:1!important;transform:none!important}.team-card:first-child .team-card__socials{opacity:1!important;transform:none!important}.team-card:first-child{transform:translateY(-8px);border-color:rgba(34,197,94,.5)}' });
await new Promise(r => setTimeout(r, 1600));
await page.screenshot({ path: `${OUT}/team-card-hover.png` });
console.log('shot: team-card-hover');

await browser.close();
console.log('done');
process.exit(0);
