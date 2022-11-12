import * as http from 'http'
import { URL } from 'url'
import { stat, readFile } from 'fs/promises';
import { join, extname } from 'path';


// you can pass the parameter in the command line. e.g. node http_server.js 3000
const port = process.argv[2] || 8125;

// maps file extention to MIME types
// full list can be found here: https://www.freeformatter.com/mime-types-list.html
const mimeType = {
  '.ico': 'image/x-icon',
  '.html': 'text/html',
  '.js': 'text/javascript',
  '.json': 'application/json',
  '.css': 'text/css',
  '.png': 'image/png',
  '.jpg': 'image/jpeg',
  '.wav': 'audio/wav',
  '.mp3': 'audio/mpeg',
  '.svg': 'image/svg+xml',
  '.pdf': 'application/pdf',
  '.zip': 'application/zip',
  '.doc': 'application/msword',
  '.eot': 'application/vnd.ms-fontobject',
  '.ttf': 'application/x-font-ttf',
};

let webserver = http.createServer( async (req, res) => {
  console.log(`${req.method} ${req.url}`);
  //console.log("Request Headers: " + JSON.stringify(req.headers, null, '\t'));

  // parse URL
  const baseURL = 'http://' + req.headers.host + '/';
  const parsedUrl = new URL(req.url, baseURL);
  let pathname = decodeURIComponent(join('packages', parsedUrl.pathname));

  try {
    let stats = await stat(pathname)
    
    if(stats.isDirectory()) {
      pathname += '/index.html';
    }

    // if the file is found, read it and its extension
    const data = await readFile(pathname)
    const ext = extname(pathname)

    // set Content-type and send data
    res.setHeader('Content-type', mimeType[ext] || 'text/plain' );
    res.end(data);
  }
  catch (err) {
    // if the file is not found, return 404
    if(err.code == 'ENOENT') { 
      console.error(`file ${pathname} does not exist / stats undefined`)
      res.statusCode = 404;
      res.end(`File ${pathname} not found!`);
    } 
    else {
      console.error("Error: " + err.message)
      res.statusCode = 500;
      res.end(`Error getting the file: ${err}.`);
    }
  }
})

webserver.listen(port);
console.log(`Server listening on port ${port}`);

//export { webserver };