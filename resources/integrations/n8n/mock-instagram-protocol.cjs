// Private container-only simulator. No real credential or external request.
const http=require('node:http'),fs=require('node:fs');
let scenario='success',calls=[],polls=0;
const hash='a'.repeat(64),lease='b'.repeat(64),id='123456789';
const payload={content_kind:'news',content_id:833,title:'Fixture',copy:'Fixture de prueba',content_hash:hash};
http.createServer(async(req,res)=>{
  let raw='';for await(const c of req)raw+=c;
  const body=raw?JSON.parse(raw):{}, u=new URL(req.url,'http://localhost');
  const reply=(data,status=200)=>{res.writeHead(status,{'Content-Type':'application/json'});res.end(JSON.stringify(data));};
  if(u.pathname==='/reset'){scenario=u.searchParams.get('scenario')||'success';calls=[];polls=0;return reply({ok:true});}
  if(u.pathname==='/evidence')return reply({scenario,calls,polls});
  calls.push({method:req.method,path:u.pathname,...(u.pathname.endsWith('/result')?{outcome:body.outcome}:{})});
  if(u.pathname.endsWith('/candidates'))return reply({data:scenario==='empty'?[]:[{...payload,provider:'instagram',destination_key:'instagram:account:17841400312117345'}]});
  if(u.pathname.endsWith('/claims'))return reply({target_id:2,attempt_id:3,lease,payload,replayed:false});
  if(u.pathname.endsWith('/render'))return reply({asset_reference:'sha256:'+hash,asset_sha256:hash,mime_type:'image/jpeg',width:1080,height:1350,asset_url:'https://mifolkloreargentino.com/api/v1/social-publication-assets/'+hash+'.jpg'});
  if(u.pathname.endsWith('/begin'))return reply({may_send:true});
  if(u.pathname.endsWith('/container'))return reply({may_publish:true,container_id:id});
  if(u.pathname.endsWith('/result'))return reply({status:body.outcome});
  if(u.pathname.endsWith('/media'))return scenario==='create_reject'?reply({error:{code:100}},400):reply({id});
  if(u.pathname.endsWith('/media_publish'))return scenario==='publish_error'?reply({error:{code:2}},500):reply({id:'987654321'});
  if(u.searchParams.has('fields') && u.searchParams.get('fields').includes('status_code')){
    polls++;return reply({id,status_code:scenario==='wait_exhausted'?'IN_PROGRESS':scenario==='wait_once'&&polls===1?'IN_PROGRESS':'FINISHED'});
  }
  if(u.searchParams.has('fields'))return scenario==='permalink_error'?reply({error:{code:2}},500):reply({id:'987654321',permalink:'https://www.instagram.com/p/Fixture123/'});
  reply({error:'unexpected'},404);
}).listen(18791,'127.0.0.1',()=>fs.writeFileSync('/tmp/mfa-ig-mock-ready','ready'));
