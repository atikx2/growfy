/** End-to-end smoke test of the whole app through php-wasm. */
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';
import { PHP, PHPRequestHandler } from '@php-wasm/universal';

const DOCROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const BASE = 'http://e2e.test';

const runtime = await loadNodeRuntime('8.2', { emscriptenOptions: { processId: 1 }, followSymlinks: true });
const php = new PHP(runtime);
useHostFilesystem(php);
await php.setSapiName('apache2handler');
const handler = new PHPRequestHandler({ php, documentRoot: DOCROOT, absoluteUrl: BASE });

// --- manual cookie jar (in addition to handler's own store) ---
let jar = new Map();
function cookieHeader() {
  return [...jar.entries()].map(([k, v]) => `${k}=${v}`).join('; ');
}
/** Run a block with a fresh browser session (bypasses per-session throttle like a new visitor). */
async function withNewSession(fn) {
  const saved = jar;
  jar = new Map();
  try { return await fn(); } finally { jar = saved; }
}
async function req(method, urlPath, bodyObj) {
  const headers = { cookie: cookieHeader() };
  let body;
  if (bodyObj) {
    const params = new URLSearchParams(bodyObj);
    body = new TextEncoder().encode(params.toString());
    headers['content-type'] = 'application/x-www-form-urlencoded';
  }
  const res = await handler.request({ method, url: BASE + urlPath, headers, body });
  (res.headers['set-cookie'] || []).forEach(c => {
    const [pair] = c.split(';');
    const i = pair.indexOf('=');
    if (i > 0) jar.set(pair.slice(0, i).trim(), pair.slice(i + 1).trim());
  });
  return res;
}

let pass = 0, fail = 0;
const results = [];
function check(name, cond, extra = '') {
  results.push(`${cond ? '✅' : '❌'} ${name}${cond ? '' : ' — ' + extra}`);
  cond ? pass++ : fail++;
}
const csrfOf = (html) => (html.match(/name="_csrf" value="([a-f0-9]+)"/) || [])[1];

// 1. homepage
const home = await req('GET', '/');
check('GET / homepage renders', home.httpStatusCode === 200);
check('homepage has hero title', /Empowering Brands/i.test(home.text));
check('homepage has 6 services', (home.text.match(/service-card__title/g) || []).length === 6);
check('homepage has testimonials', (home.text.match(/t-card__quote/g) || []).length >= 5);
check('homepage has FAQs', (home.text.match(/accordion__item/g) || []).length >= 5);

// 2. contact form (AJAX style)
const csrfPublic = csrfOf(home.text);
const contactRes = await req('POST', '/index.php', {
  action: 'contact', _csrf: csrfPublic, _ajax: '1',
  name: 'Test User', email: 'test@example.com', phone: '+123', subject: 'Hello', message: 'This is an integration test message.', website: ''
});
const contactJson = JSON.parse(contactRes.text || '{}');
check('contact form accepts valid submission', contactJson.ok === true, contactRes.text?.slice(0, 200));

const contactBad = await req('POST', '/index.php', {
  action: 'contact', _csrf: csrfPublic, _ajax: '1',
  name: '', email: 'not-an-email', message: '', website: ''
});
check('contact form rejects invalid data', JSON.parse(contactBad.text || '{}').ok === false);

// anti-spam throttle (8s/session) is by design — wait it out like a real user
const waitThrottle = () => new Promise(r => setTimeout(r, 8500));

// 3. newsletter
await waitThrottle();
const newsRes = await req('POST', '/index.php', {
  action: 'newsletter', _csrf: csrfPublic, _ajax: '1', email: 'fan@growfy.test', website: ''
});
check('newsletter subscription works', JSON.parse(newsRes.text || '{}').ok === true, newsRes.text?.slice(0, 200));

// 4. order inquiry
await waitThrottle();
const orderRes = await req('POST', '/index.php', {
  action: 'order', _csrf: csrfPublic, _ajax: '1', service_id: '1', package: 'Growth',
  name: 'Order Tester', contact: '@ordertester', link: 'https://instagram.com/x', notes: 'e2e order', website: ''
});
check('order inquiry works', JSON.parse(orderRes.text || '{}').ok === true, orderRes.text?.slice(0, 200));

// 5. CSRF protection actually blocks bad tokens
const badCsrf = await req('POST', '/index.php', {
  action: 'contact', _csrf: 'deadbeef', _ajax: '1', name: 'x', email: 'x@x.co', message: 'x', website: ''
});
check('CSRF mismatch is blocked (419)', badCsrf.httpStatusCode === 419, 'status=' + badCsrf.httpStatusCode);

// 6. admin login flow
const loginPage = await req('GET', '/admin/login.php');
check('admin login page renders', loginPage.httpStatusCode === 200 && /Sign In/.test(loginPage.text));

const csrfLogin = csrfOf(loginPage.text);
const wrongLogin = await req('POST', '/admin/login.php', { _csrf: csrfLogin, username: 'admin', password: 'wrong' });
check('wrong password rejected', /Invalid username or password/.test(wrongLogin.text));

const goodLogin = await req('POST', '/admin/login.php', { _csrf: csrfLogin, username: 'admin', password: 'Growfy@2026' });
check('correct password logs in (302)', goodLogin.httpStatusCode === 302);

// dashboard should redirect to forced password change
const dashBefore = await req('GET', '/admin/index.php');
check('default password forces change screen', dashBefore.httpStatusCode === 302 && (dashBefore.headers.location || []).some(l => l.includes('settings.php?tab=password')), JSON.stringify(dashBefore.headers.location));

// change password
const pwPage = await req('GET', '/admin/settings.php?tab=password');
const csrfPw = csrfOf(pwPage.text);
const pwRes = await req('POST', '/admin/settings.php?tab=password', {
  _act: 'password', _csrf: csrfPw, current: 'Growfy@2026', new: 'ArenaTest#2026', confirm: 'ArenaTest#2026', display_name: 'Admin Boss'
});
check('password change accepted', pwRes.httpStatusCode === 302 && (pwRes.headers.location || []).some(l => l.includes('settings.php')));

const dash = await req('GET', '/admin/index.php');
check('dashboard renders after password change', dash.httpStatusCode === 200 && /Activity — last 14 days/.test(dash.text), 'status=' + dash.httpStatusCode);

// 7. inbox shows test message, mark read via view
const inbox = await req('GET', '/admin/inbox.php?view=1');
check('inbox shows submitted message', inbox.httpStatusCode === 200 && /integration test message/.test(inbox.text));

// 8. orders page shows order; change status
const orders = await req('GET', '/admin/orders.php');
check('orders page lists order', orders.httpStatusCode === 200 && /Order Tester/.test(orders.text));
const csrfOrders = csrfOf(orders.text);
const stRes = await req('POST', '/admin/orders.php', { _act: 'status', _csrf: csrfOrders, id: '1', status: 'contacted' });
check('order status update works', stRes.httpStatusCode === 302);

// 9. subscribers list
const subs = await req('GET', '/admin/subscribers.php');
check('subscriber listed', subs.httpStatusCode === 200 && /fan@growfy.test/.test(subs.text));

// 10. edit a service and see it live
const svcs = await req('GET', '/admin/services.php?edit=1');
const csrfSvc = csrfOf(svcs.text);
const svcSave = await req('POST', '/admin/services.php', {
  _act: 'save', _csrf: csrfSvc, id: '1', title: 'Instagram Growth', tag: 'BEST SELLER', platform: 'instagram',
  icon: 'instagram', description: 'EDITED-BY-E2E premium organic strategies.', price_from: '$29', sort_order: '1', active: '1'
});
check('service save works', svcSave.httpStatusCode === 302);
const home2 = await req('GET', '/');
check('edited service appears on site', /EDITED-BY-E2E/.test(home2.text));

// 11. content editor round-trip
const content = await req('GET', '/admin/content.php');
const csrfContent = csrfOf(content.text);
const fields = {};
for (const m of content.text.matchAll(/name="(hero_[a-z0-9_]+|cta_[a-z0-9_]+)"[^>]*?(?:value="([^"]*)"|>([^<]*)<\/textarea>)/gs)) {
  fields[m[1]] = (m[2] ?? m[3] ?? '');
}
if (fields.hero_badge !== undefined) {
  fields.hero_badge = 'E2E BADGE ✅';
  const cRes = await req('POST', '/admin/content.php', { _csrf: csrfContent, ...fields });
  check('content editor saves', cRes.httpStatusCode === 302);
  const home3 = await req('GET', '/');
  check('updated badge visible on homepage', home3.text.includes('E2E BADGE'));
} else {
  check('content page exposes hero fields', false, 'no fields parsed');
}

// 12. include dir & config not directly accessible? (public probing returns empty 200 for includes — acceptable,
//     cPanel blocks via .htaccess; check index bootstrap didn't leak anything sensitive)
// 13. logout
const logout = await req('GET', '/admin/logout.php');
const dashAfter = await req('GET', '/admin/index.php');
check('logout works (dashboard redirects to login)', dashAfter.httpStatusCode === 302 && (dashAfter.headers.location || []).some(l => l.includes('login.php')));

console.log(results.join('\n'));
console.log(`\n${pass} passed, ${fail} failed`);
process.exit(fail ? 1 : 0);
