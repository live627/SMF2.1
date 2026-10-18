// Local fixture server for the SMF tests (replaces live internet requests).
// Usage: node server.js [port]   (default 8125, or the PORT env var)
//
// GET  /<path>  serves files from tests/packages (e.g. /images/logo.gif)
// POST /html    echoes the posted body inside an HTML page (for fetch_web_data POST tests)
const http = require('http');
const fs = require('fs');
const path = require('path');

const port = parseInt(process.argv[2] || process.env.PORT || '8125', 10);
const root = path.resolve('tests/packages');

const mimeTypes = {
  '.ico': 'image/x-icon',
  '.html': 'text/html',
  '.js': 'text/javascript',
  '.json': 'application/json',
  '.css': 'text/css',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.gif': 'image/gif',
  '.wav': 'audio/wav',
  '.mp3': 'audio/mpeg',
  '.svg': 'image/svg+xml',
  '.pdf': 'application/pdf',
  '.doc': 'application/msword',
  '.txt': 'text/plain',
};

function send(res, status, type, body) {
  res.statusCode = status;
  res.setHeader('Content-Type', type);
  res.end(body);
}

function escapeHtml(s) {
  return s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

async function serveStatic(pathname, res) {
  // Resolve inside the root and refuse anything that escapes it (e.g. ../../etc/passwd)
  let file = path.join(root, decodeURIComponent(pathname));
  if (file !== root && !file.startsWith(root + path.sep)) {
    return send(res, 403, 'text/plain', 'Forbidden');
  }

  try {
    if ((await fs.promises.stat(file)).isDirectory()) file = path.join(file, 'index.html');
    const data = await fs.promises.readFile(file);
    send(res, 200, mimeTypes[path.extname(file).toLowerCase()] || 'application/octet-stream', data);
  } catch (err) {
    if (err.code === 'ENOENT') send(res, 404, 'text/plain', `File ${pathname} not found!`);
    else send(res, 500, 'text/plain', `Error getting the file: ${err.message}`);
  }
}

const server = http.createServer((req, res) => {
  console.log(`${req.method} ${req.url}`);
  const { pathname } = new URL(req.url, 'http://localhost');

  if (req.method === 'POST' && pathname === '/html') {
    let body = '';
    req.on('data', (chunk) => { body += chunk; });
    req.on('end', () => {
      const text = decodeURIComponent(body.replace(/\+/g, ' '));
      send(res, 200, 'text/html', `<!doctype html><title>Results</title><pre>${escapeHtml(text)}</pre>`);
    });
    return;
  }

  if (req.method !== 'GET' && req.method !== 'HEAD') {
    return send(res, 405, 'text/plain', 'Method not allowed');
  }

  serveStatic(pathname, res);
});

server.listen(port, () => console.log(`Server listening on port ${port}`));
