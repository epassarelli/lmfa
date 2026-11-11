const fs=require('node:fs'),path=require('node:path');
const runtime=process.argv[2];
const raw=JSON.parse(fs.readFileSync(runtime||path.join(__dirname,'daily/news.json'),'utf8'));
const fb=structuredClone(Array.isArray(raw)?raw[0]:raw);
const ig=JSON.parse(fs.readFileSync(path.join(__dirname,'daily/instagram-news.json'),'utf8'));
const rename=n=>'IG · '+n;
const original=fb.nodes.map(n=>({id:n.id,name:n.name,parameters:n.parameters}));
for(const node of ig.nodes.filter(n=>n.type!=='n8n-nodes-base.manualTrigger')){
  const n=structuredClone(node);n.id='ig-'+n.id;n.name=rename(n.name);n.position=[n.position[0]+2600,n.position[1]+500];
  let params=JSON.stringify(n.parameters);
  for(const old of ig.nodes.map(x=>x.name))params=params.replaceAll(`$('${old}')`,`$('${rename(old)}')`);
  n.parameters=JSON.parse(params);
  if(node.name==='Configuración y bloqueo')n.parameters.jsCode=`const fb=$json;if(fb.status!=='success')return [];const c=$('Reservar').first().json;if(fb.ledger_content_kind!=='news'||fb.ledger_content_id!==c.payload.content_id||fb.destination_key!=='facebook:page:145931542128207')throw new Error('Facebook no confirmó esta noticia');const config={api_base_url:$('Configuración y bloqueo').first().json.api_base_url,allow_publish:${!!runtime},test_news_id:null,content_id:fb.ledger_content_id};if(!config.allow_publish)throw new Error('Encadenamiento Instagram bloqueado en export fuente');return [{json:config}];`;
  if(node.name==='Candidatos IG'){
    n.type='n8n-nodes-base.code';n.typeVersion=2;delete n.credentials;delete n.retryOnFail;delete n.maxTries;delete n.waitBetweenTries;
    n.parameters={jsCode:`const c=$('${rename('Configuración y bloqueo')}').first().json;return [{json:{data:[{content_kind:'news',content_id:c.content_id,provider:'instagram',destination_key:'instagram:account:17841400312117345'}]}}];`};
  }
  fb.nodes.push(n);
}
for(const [from,edges]of Object.entries(ig.connections)){
  if(from==='Inicio manual IG')continue;
  fb.connections[rename(from)]={main:edges.main.map(out=>out.map(e=>({...e,node:rename(e.node)})))};
}
const callback=fb.nodes.find(n=>n.id==='result').name;
if(fb.connections[callback]?.main?.some(x=>x.length))throw new Error('Callback Facebook tiene conexiones no previstas');
fb.connections[callback]={main:[[{node:rename('Configuración y bloqueo'),type:'main',index:0}]]};
for(const old of original){const n=fb.nodes.find(n=>n.id===old.id);if(JSON.stringify(n.parameters)!==JSON.stringify(old.parameters))throw new Error('Se modificó un nodo Facebook');}
fb.name='MFA - Noticias - Facebook + Instagram diario 11:00 Argentina';fb.active=false;
delete fb.activeVersion;delete fb.activeVersionId;delete fb.versionId;delete fb.createdAt;delete fb.updatedAt;delete fb.shared;
const output=runtime?path.join(__dirname,'../../../project/artifacts/news-chain-runtime.json'):path.join(__dirname,'daily/news-facebook-instagram.json');
fs.writeFileSync(output,JSON.stringify(fb,null,2)+'\n');
console.log(JSON.stringify({output,facebook_nodes_unchanged:original.length,total_nodes:fb.nodes.length,cron:fb.nodes.find(n=>n.id==='schedule').parameters.rule.interval[0].expression,active:fb.active}));
