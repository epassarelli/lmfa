// Local-only simulator. Bind inside the n8n container: no published ports, no Meta requests.
const http = require('node:http');
const fs = require('node:fs');
const crypto = require('node:crypto');
const png = fs.readFileSync(process.argv[2]);
const sha = crypto.createHash('sha256').update(png).digest('hex');
let state;
const reset = scenario => state={scenario,claims:0,begins:0,meta_posts:0,callbacks:0,status:'pending',png_verified:false};
reset('success');
const payload={content_kind:'news',content_id:1,title:'Prueba privada del protocolo',excerpt:'Simulación sin publicación externa.',url:'https://mifolkloreargentino.com/',copy:'Prueba privada',template_id:'mfa-noticias-v4',template_catalog_version:'4.0.0',content_hash:'b'.repeat(64),destination_key:'facebook:page:145931542128207',provider:'facebook'};
http.createServer(async (req,res)=>{
  const url=new URL(req.url,'http://127.0.0.1:5688');
  const chunks=[]; for await(const part of req) chunks.push(part); const body=Buffer.concat(chunks);
  const send=(code,data)=>{res.writeHead(code,{'Content-Type':'application/json'});res.end(JSON.stringify(data));};
  if(url.pathname==='/control/reset'){reset(url.searchParams.get('scenario')||'success');return send(200,state);}
  if(url.pathname==='/control/state')return send(200,state);
  if(url.pathname.endsWith('/candidates'))return send(200,{data:[payload]});
  if(url.pathname.endsWith('/claims')){state.claims++;state.status='reserved';return send(201,{target_id:1,attempt_id:1,lease:'a'.repeat(64),payload,replayed:false});}
  if(url.pathname.endsWith('/render'))return send(200,{asset_reference:'sha256:'+sha,asset_sha256:sha,asset_base64:png.toString('base64'),mime_type:'image/png',width:1080,height:1350});
  if(url.pathname.endsWith('/begin')){
    const data=JSON.parse(body); if(data.asset_sha256!==sha||data.asset_reference!=='sha256:'+sha||state.begins)return send(409,{error:'Invalid or duplicate begin'});
    state.begins++;state.status='processing';
    if(state.scenario==='lost_begin'){req.socket.destroy();return;}
    return send(200,{target_id:1,attempt_id:1,may_send:true});
  }
  if(url.pathname==='/meta'){
    state.meta_posts++;
    const start=body.indexOf(Buffer.from([137,80,78,71,13,10,26,10]));const end=body.indexOf(Buffer.from('IEND'),start);
    state.png_verified=start>=0&&end>start&&crypto.createHash('sha256').update(body.subarray(start,end+8)).digest('hex')===sha;
    if(!state.png_verified||state.meta_posts!==1)return send(400,{error:{code:100}});
    if(state.scenario==='reject')return send(400,{error:{code:100}});
    if(state.scenario==='server_error')return send(503,{error:{code:1}});
    if(state.scenario==='lost_meta'){req.socket.destroy();return;}
    return send(200,{id:'photo_fixture',post_id:'145931542128207_123456'});
  }
  if(url.pathname.endsWith('/result')){
    state.callbacks++; if(state.scenario==='lost_result'&&state.callbacks===1)return send(503,{error:'Simulated callback failure'});
    const data=JSON.parse(body); state.status={success:'success',definitive_failure:'failed',uncertain:'uncertain'}[data.outcome];
    return send(200,{id:1,status:state.status});
  }
  send(404,{error:'Not found'});
}).listen(5688,'127.0.0.1',()=>console.log('MFA mock listening on container loopback 5688'));
