const http=require('node:http');let scenario='success',calls=[];
const payload={content_kind:'news',content_id:834,title:'Fixture noticia834',copy:'Fixture',content_hash:'a'.repeat(64)};
const lease='b'.repeat(64),hash='a'.repeat(64);
http.createServer(async(req,res)=>{
  let raw='';for await(const c of req)raw+=c;const b=raw&&req.headers['content-type']?.includes('application/json')?JSON.parse(raw):{};
  const u=new URL(req.url,'http://localhost');const reply=(x,status=200)=>{res.writeHead(status,{'Content-Type':'application/json'});res.end(JSON.stringify(x));};
  if(u.pathname==='/reset'){scenario=u.searchParams.get('scenario');calls=[];return reply({ok:true});}
  if(u.pathname==='/evidence')return reply({scenario,calls});
  calls.push({method:req.method,path:u.pathname,...(u.pathname.endsWith('/claims')?{content_id:b.content_id}:{}),...(u.pathname.endsWith('/result')?{outcome:b.outcome}:{})});
  const ig=u.pathname.includes('/instagram/');
  if(u.pathname.endsWith('/candidates'))return reply({data:[{...payload,provider:'facebook',destination_key:'facebook:page:145931542128207'}]});
  if(u.pathname.endsWith('/claims'))return reply({target_id:ig?2:1,attempt_id:ig?2:1,lease,payload,replayed:false});
  if(u.pathname.endsWith('/render'))return reply({asset_reference:'sha256:'+hash,asset_sha256:hash,asset_base64:Buffer.from('fixture').toString('base64'),mime_type:ig?'image/jpeg':'image/png',width:1080,height:1350,asset_url:'https://mifolkloreargentino.com/api/v1/social-publication-assets/'+hash+'.jpg'});
  if(u.pathname.endsWith('/begin'))return reply({may_send:true});
  if(u.pathname.endsWith('/container'))return reply({may_publish:true,container_id:'123456789'});
  if(u.pathname.endsWith('/result'))return reply({id:ig?2:1,status:b.outcome==='success'?'success':b.outcome==='uncertain'?'uncertain':'failed',ledger_content_kind:'news',ledger_content_id:834,destination_key:ig?'instagram:account:17841400312117345':'facebook:page:145931542128207'});
  if(u.pathname.endsWith('/photos'))return scenario==='facebook_reject'?reply({error:{code:100}},400):scenario==='facebook_uncertain'?reply({error:{code:2}},500):reply({post_id:'145931542128207_123456'});
  if(u.pathname.endsWith('/media'))return reply({id:'123456789'});
  if(u.pathname.endsWith('/media_publish'))return scenario==='instagram_error'?reply({error:{code:2}},500):reply({id:'987654321'});
  if(u.searchParams.get('fields')?.includes('status_code'))return reply({id:'123456789',status_code:'FINISHED'});
  if(u.searchParams.has('fields'))return reply({id:'987654321',permalink:'https://www.instagram.com/p/Fixture123/'});
  return reply({error:'unexpected'},404);
}).listen(18792,'127.0.0.1');
