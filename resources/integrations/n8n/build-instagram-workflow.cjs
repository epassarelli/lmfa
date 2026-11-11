const fs = require('node:fs');
const path = require('node:path');
const nodes = [], connections = {};
const portal = {httpHeaderAuth:{id:'MfaSocialPortal1',name:'MFA - Publicaciones Portal'}};
const meta = {facebookGraphApi:{id:'qaekI4MYP9zPCJe7',name:'MFA - Instagram Publicaciones'}};
const code = (name,jsCode) => nodes.push({id:name,name,type:'n8n-nodes-base.code',typeVersion:2,position:[nodes.length*210,0],parameters:{jsCode}});
const link = (a,b,index=0) => {connections[a] ??= {main:[]}; connections[a].main[index]=[{node:b,type:'main',index:0}];};
const api = "$('Configuración y bloqueo').first().json.api_base_url";
const claim = "$('Reservar IG').first().json";
const lease = `{attempt_id:${claim}.attempt_id,lease:${claim}.lease}`;
const url = suffix => `={{ ${api} + '/social-publications/instagram' + ${suffix} }}`;
const auth = (name,endpoint,body) => {
  nodes.push({id:name,name,type:'n8n-nodes-base.httpRequest',typeVersion:4.2,position:[nodes.length*210,0],credentials:portal,
    retryOnFail:name==='Registrar resultado',maxTries:3,waitBetweenTries:2000,
    parameters:{method:body?'POST':'GET',url:endpoint,authentication:'genericCredentialType',genericAuthType:'httpHeaderAuth',
      ...(body?{sendBody:true,specifyBody:'json',jsonBody:`={{ ${body} }}`} : {}),options:{timeout:30000}}});
};
const remote = (name,endpoint,body) => nodes.push({id:name,name,type:'n8n-nodes-base.httpRequest',typeVersion:4.2,position:[nodes.length*210,0],credentials:meta,
  retryOnFail:false,onError:'continueRegularOutput',parameters:{method:body?'POST':'GET',url:endpoint,authentication:'predefinedCredentialType',nodeCredentialType:'facebookGraphApi',
    ...(body?{sendBody:true,specifyBody:'json',jsonBody:`={{ ${body} }}`} : {}),options:{timeout:30000,response:{response:{fullResponse:true,neverError:true}}}}});
const branch = (name,expression) => nodes.push({id:name,name,type:'n8n-nodes-base.if',typeVersion:2.2,position:[nodes.length*210,0],parameters:{conditions:{options:{caseSensitive:true,leftValue:'',typeValidation:'strict',version:2},conditions:[{id:name,leftValue:`={{ ${expression} }}`,rightValue:true,operator:{type:'boolean',operation:'true',singleValue:true}}],combinator:'and'},options:{}}});
nodes.push({id:'manual',name:'Inicio manual IG',type:'n8n-nodes-base.manualTrigger',typeVersion:1,position:[0,0],parameters:{}});
code('Configuración y bloqueo',"const c={api_base_url:'https://mifolkloreargentino.com/api/v1',allow_publish:false,test_news_id:833}; if(!c.allow_publish) throw new Error('Instagram bloqueado hasta deploy, credencial vigente y prueba autorizada'); return [{json:c}];");
auth('Candidatos IG',url("'/candidates?content_kind=news&provider=instagram&per_page=1'"));
code('Preparar reserva IG',"const c=$json.data?.[0]; if(!c)return []; const test=$('Configuración y bloqueo').first().json.test_news_id; if(test && c.content_id!==test) throw new Error('Prueba limitada a noticia833'); const request_key='xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g,c=>{const r=Math.floor(Math.random()*16);return(c==='x'?r:(r&3)|8).toString(16)});return [{json:{...c,request_key}}];");
auth('Reservar IG',url("'/claims'"),"{content_kind:'news',content_id:$json.content_id,provider:'instagram',destination_key:$json.destination_key,n8n_execution_id:$execution.id}");
const reserve=nodes.at(-1); reserve.parameters.sendHeaders=true;reserve.parameters.headerParameters={parameters:[{name:'Idempotency-Key',value:'={{ $json.request_key }}'}]};
code('Validar reserva IG',"if($json.replayed||!$json.lease)throw new Error('Reserva repetida: no enviar');return $input.all();");
auth('Generar JPEG',url(`'/' + ${claim}.target_id + '/render'`),lease);
code('Validar JPEG',"const a=$json; if(a.mime_type!=='image/jpeg'||a.width!==1080||a.height!==1350||!a.asset_url?.startsWith('https://mifolkloreargentino.com/api/v1/social-publication-assets/')||!a.asset_reference?.startsWith('sha256:'))throw new Error('Placa IG inválida');return [{json:{asset_url:a.asset_url,asset_reference:a.asset_reference,asset_sha256:a.asset_sha256}}];");
auth('Iniciar intento IG',url(`'/' + ${claim}.target_id + '/begin'`),`{...${lease},content_hash:${claim}.payload.content_hash,asset_reference:$json.asset_reference,asset_sha256:$json.asset_sha256}`);
code('Validar inicio IG',"if(!$json.may_send)throw new Error('Begin no autorizó');return $input.all();");
remote('Crear contenedor IG','https://graph.facebook.com/v27.0/17841400312117345/media',`{image_url:$('Validar JPEG').first().json.asset_url,caption:${claim}.payload.copy}`);
branch('Contenedor creado',"$json.statusCode===200 && /^[0-9]{5,32}$/.test($json.body?.id||'')");
remote('Estado contenedor IG',"={{ 'https://graph.facebook.com/v27.0/' + $('Crear contenedor IG').first().json.body.id + '?fields=id,status_code' }}");
branch('Contenedor listo',"$json.statusCode===200 && $json.body?.status_code==='FINISHED'");
code('Control de espera',"const n=$('Estado contenedor IG').all(0,$runIndex)?.[0]?.json||$json;return [{json:{...n,wait_again:$runIndex<5 && n.statusCode===200 && n.body?.status_code==='IN_PROGRESS'}}];");
branch('Esperar procesamiento',"$json.wait_again===true");
nodes.push({id:'wait',name:'Esperar cinco segundos',type:'n8n-nodes-base.wait',typeVersion:1.1,position:[3100,300],parameters:{amount:5,unit:'seconds'}});
auth('Registrar contenedor IG',url(`'/' + ${claim}.target_id + '/container'`),`{...${lease},container_id:$('Crear contenedor IG').first().json.body.id}`);
// A portal refusal stops here. No publish occurs; the processing lease later becomes uncertain.
code('Validar autorización IG',"if(!$json.may_publish)throw new Error('Portal no autorizó publicar');return $input.all();");
remote('Publicar IG UNA vez','https://graph.facebook.com/v27.0/17841400312117345/media_publish',"{creation_id:$('Registrar contenedor IG').first().json.container_id}");
branch('Publicación confirmada',"$json.statusCode===200 && /^[0-9]{5,32}$/.test($json.body?.id||'')");
remote('Permalink IG',"={{ 'https://graph.facebook.com/v27.0/' + $('Publicar IG UNA vez').first().json.body.id + '?fields=id,permalink,media_type,caption' }}");
code('Resultado verificado IG',"const b=$json.body||{};const id=$('Publicar IG UNA vez').first().json.body.id;const ok=$json.statusCode===200&&b.id===id&&new RegExp('^https://www[.]instagram[.]com/(p|reel)/[A-Za-z0-9_-]+/$').test(b.permalink||'');return [{json:{outcome:ok?'success':'uncertain',external_post_id:id,...(ok?{external_url:b.permalink}:{})}}];");
code('Resultado incierto IG',"return [{json:{outcome:'uncertain'}}];");
auth('Registrar resultado',url(`'/' + ${claim}.target_id + '/result'`),`{...${lease},...$json}`);
const linear=['Inicio manual IG','Configuración y bloqueo','Candidatos IG','Preparar reserva IG','Reservar IG','Validar reserva IG','Generar JPEG','Validar JPEG','Iniciar intento IG','Validar inicio IG','Crear contenedor IG','Contenedor creado'];
linear.slice(0,-1).forEach((n,i)=>link(n,linear[i+1]));
link('Contenedor creado','Estado contenedor IG');link('Contenedor creado','Resultado incierto IG',1);
link('Estado contenedor IG','Contenedor listo');link('Contenedor listo','Registrar contenedor IG');link('Contenedor listo','Control de espera',1);
link('Control de espera','Esperar procesamiento');link('Esperar procesamiento','Esperar cinco segundos');link('Esperar procesamiento','Resultado incierto IG',1);link('Esperar cinco segundos','Estado contenedor IG');
link('Registrar contenedor IG','Validar autorización IG');link('Validar autorización IG','Publicar IG UNA vez');link('Publicar IG UNA vez','Publicación confirmada');
link('Publicación confirmada','Permalink IG');link('Publicación confirmada','Resultado incierto IG',1);link('Permalink IG','Resultado verificado IG');
link('Resultado verificado IG','Registrar resultado');link('Resultado incierto IG','Registrar resultado');
const workflow={id:'MfaInstagramNews01',name:'MFA - Noticias - Instagram (bloqueado hasta deploy y prueba)',active:false,nodes,connections,settings:{timezone:'America/Argentina/Buenos_Aires',saveDataSuccessExecution:'none',saveDataErrorExecution:'none',saveManualExecutions:false}};
fs.writeFileSync(path.join(__dirname,'daily','instagram-news.json'),JSON.stringify(workflow,null,2)+'\n');
console.log('Workflow Instagram manual, inactivo y bloqueado generado.');
