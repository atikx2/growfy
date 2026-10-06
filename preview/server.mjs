/**
 * Growfy — local preview server.
 * Runs the REAL PHP application (the exact code you upload to cPanel)
 * through PHP-WASM on Node.js. No PHP installation needed.
 *
 *   node preview/server.mjs           → http://localhost:8080
 *   PORT=9000 node preview/server.mjs
 */
import http from 'node:http';
import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { loadNodeRuntime, useHostFilesystem } from '@php-wasm/node';
import { PHP, PHPRequestHandler } from '@php-wasm/universal';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DOCROOT = path.resolve(__dirname, '..');
const PORT = Number(process.env.PORT || 8080);

console.log('⏳ Loading PHP 8.2 (WASM)…');
const runtime = await loadNodeRuntime('8.2', {
  emscriptenOptions: { processId: 1 },
  followSymlinks: true,
});
const php = new PHP(runtime);
useHostFilesystem(php);
await php.setSapiName('apache2handler');

const handler = new PHPRequestHandler({
  php,
  documentRoot: DOCROOT,
  absoluteUrl: `http://localhost:${PORT}`,
});

const server = http.createServer(async (req, res) => {
  try {
    // buffer request body (forms / uploads)
    const chunks = [];
    let size = 0;
    for await (const chunk of req) {
      size += chunk.length;
      if (size > 25 * 1024 * 1024) break;
      chunks.push(chunk);
    }
    const body = Buffer.concat(chunks);

    const proto = (req.headers['x-forwarded-proto'] || 'http').split(',')[0].trim();
    const host = (req.headers['x-forwarded-host'] || req.headers.host || `localhost:${PORT}`).split(',')[0].trim();
    const url = `${proto}://${host}${req.url}`;

    const headers = {};
    for (const [k, v] of Object.entries(req.headers)) {
      if (typeof v === 'string') headers[k] = v;
    }
    delete headers['content-length'];

    const response = await handler.request({
      method: req.method,
      url,
      headers,
      body: body.length ? new Uint8Array(body) : undefined,
    });

    for (const [k, v] of Object.entries(response.headers || {})) {
      if (['content-length', 'transfer-encoding', 'connection'].includes(k.toLowerCase())) continue;
      try { res.setHeader(k, Array.isArray(v) ? v : [v]); } catch { /* invalid header */ }
    }
    const bytes = response.bytes ? Buffer.from(response.bytes) : Buffer.from(response.text || '', 'utf-8');
    res.setHeader('content-length', String(bytes.length));
    res.writeHead(response.httpStatusCode || 200);
    res.end(bytes);
  } catch (err) {
    console.error('Request error:', err);
    if (!res.headersSent) res.writeHead(500, { 'content-type': 'text/plain' });
    res.end('Preview server error: ' + (err && err.message ? err.message : String(err)));
  }
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`✅ Growfy preview running:`);
  console.log(`   → http://localhost:${PORT}        (website)`);
  console.log(`   → http://localhost:${PORT}/admin (admin panel)`);
});
