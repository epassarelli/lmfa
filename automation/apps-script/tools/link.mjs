import fs from 'node:fs';

const scriptId = String(process.argv[2] || '').trim();
if (!scriptId) {
  console.error('Uso: npm run apps-script:link -- <SCRIPT_ID>');
  process.exit(1);
}

fs.writeFileSync('.clasp.json', JSON.stringify({ scriptId, rootDir: 'automation/apps-script' }, null, 2) + '\n', 'utf8');
console.log('Vinculación local creada en .clasp.json. El archivo está ignorado por Git.');
