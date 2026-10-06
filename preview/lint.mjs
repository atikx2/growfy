/**
 * Exercises every PHP file through the WASM request handler —
 * catches parse errors AND runtime errors (missing constants, etc).
 */
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { readdirSync, statSync } from 'node:fs';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';
import { PHP, PHPRequestHandler } from '@php-wasm/universal';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DOCROOT = path.resolve(__dirname, '..');

function* walk(dir) {
  for (const name of readdirSync(dir)) {
    if (['node_modules', '.git', 'data', 'uploads', 'preview'].includes(name)) continue;
    const p = path.join(dir, name);
    if (statSync(p).isDirectory()) yield* walk(p);
    else if (name.endsWith('.php')) yield p;
  }
}

const runtime = await loadNodeRuntime('8.2', { emscriptenOptions: { processId: 1 }, followSymlinks: true });
const php = new PHP(runtime);
useHostFilesystem(php);
await php.setSapiName('apache2handler');
const handler = new PHPRequestHandler({ php, documentRoot: DOCROOT, absoluteUrl: 'http://lint.local' });

// Template partials are rendered through index.php, not standalone —
// requiring them directly always fails by design, so skip them here.
const SKIP = [/^\/includes\/sections\//, /^\/includes\/site_header\.php$/, /^\/includes\/site_footer\.php$/];

let bad = 0;
for (const file of walk(DOCROOT)) {
  const rel = '/' + path.relative(DOCROOT, file).split(path.sep).join('/');
  if (SKIP.some(rx => rx.test(rel))) {
    console.log(`skip ${rel} (template partial — covered by /index.php)`);
    continue;
  }
  try {
    const res = await handler.request({ method: 'GET', url: 'http://lint.local' + rel });
    const text = res.text || '';
    const failed = res.httpStatusCode >= 500 ||
      /(Fatal error|Parse error|Uncaught|Warning|Notice):/.test(text);
    if (failed) {
      bad++;
      console.log(`FAIL ${rel} [HTTP ${res.httpStatusCode}]`);
      const m = text.match(/(?:Fatal error|Parse error|Uncaught|Warning|Notice)[^\n]*(\n\s*[^\n]*){0,2}/);
      if (m) console.log('   ' + m[0].slice(0, 500));
    } else {
      console.log(`ok   ${rel} [HTTP ${res.httpStatusCode}]`);
    }
  } catch (e) {
    bad++;
    console.log(`FAIL ${rel} — ${e.message}`);
  }
}
console.log(bad ? `\n❌ ${bad} file(s) failed` : '\n✅ All PHP files OK');
process.exit(bad ? 1 : 0);
