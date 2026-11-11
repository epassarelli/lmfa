const fs = require('node:fs');
const path = require('node:path');
const source = JSON.parse(fs.readFileSync(path.join(__dirname, 'mfa-social-publications.inactive.json'), 'utf8'));
const output = path.join(__dirname, 'daily'); fs.mkdirSync(output, {recursive:true});
for (const [kind,label,hour,id] of [['news','Noticias',11,'MfaNewsDailyV401'],['events','Eventos',18,'MfaEventsDaily01']]) {
  const w = structuredClone(source);
  w.id=id; w.name=`MFA - ${label} - Facebook diario (pendiente activación)`;
  w.settings.timezone='America/Argentina/Buenos_Aires';
  const get=id=>w.nodes.find(n=>n.id===id);
  const gate=get('gate'); gate.name='Configuración y bloqueo';
  gate.parameters.jsCode=`const config={api_base_url:'https://mifolkloreargentino.com/api/v1',content_kind:'${kind}',allow_publish:false}; if(!config.allow_publish) throw new Error('Publicación bloqueada: completar credencial dedicada, despliegue y prueba autorizada antes de habilitar'); return [{json:config}];`;
  const schedule={id:'schedule',name:`Diario ${hour}:00 Argentina`,type:'n8n-nodes-base.scheduleTrigger',typeVersion:1.2,position:[0,240],parameters:{rule:{interval:[{field:'cronExpression',expression:`0 0 ${hour} * * *`}]}}};
  if (kind === 'news') { w.nodes.push(schedule);w.connections[schedule.name]={main:[[{node:gate.name,type:'main',index:0}]]}; }
  else { w.name='MFA - Eventos - Facebook manual (sin programación)'; }
  w.connections['Inicio manual'].main[0][0].node=gate.name;
  delete w.connections['Validar configuración']; w.connections[gate.name]={main:[[{node:'Candidatos',type:'main',index:0}]]};
  for(const node of w.nodes){
    const serialized=JSON.stringify(node.parameters).replaceAll('$vars.MFA_API_BASE_URL',"$('Configuración y bloqueo').first().json.api_base_url");
    node.parameters=JSON.parse(serialized);
  }
  get('candidates').parameters.url=`={{ $('Configuración y bloqueo').first().json.api_base_url + '/social-publications/candidates?provider=facebook&content_kind=${kind}&per_page=1' }}`;
  const render=get('render');render.name='Generar PNG privado';render.type='n8n-nodes-base.httpRequest';render.typeVersion=4.2;
  render.parameters={method:'POST',url:"={{ $('Configuración y bloqueo').first().json.api_base_url + '/social-publications/' + $json.target_id + '/render' }}",authentication:'genericCredentialType',genericAuthType:'httpHeaderAuth',sendBody:true,specifyBody:'json',jsonBody:'={{ {attempt_id:$json.attempt_id,lease:$json.lease} }}',options:{timeout:30000}};
  w.connections.Reservar.main[0][0].node=render.name;
  w.connections[render.name]=w.connections['Render aprobado y alojamiento'];delete w.connections['Render aprobado y alojamiento'];
  get('prepare').parameters.jsCode="const c=$('Reservar').item.json; if(c.replayed||!c.lease) throw new Error('Reserva repetida: no enviar'); if(!$json.asset_base64||!$json.asset_reference?.startsWith('sha256:')||!/^[a-f0-9]{64}$/.test($json.asset_sha256||'')) throw new Error('PNG privado inválido'); return [{json:{...c,asset_reference:$json.asset_reference,asset_sha256:$json.asset_sha256},binary:{data:{data:$json.asset_base64,mimeType:'image/png',fileName:'mfa-'+c.target_id+'.png'}}}];";
  const forward={id:'binary',name:'Conservar archivo tras begin',type:'n8n-nodes-base.code',typeVersion:2,position:[1650,0],parameters:{jsCode:"if(!$json.may_send) throw new Error('Begin no autorizó el envío'); return [{json:$json,binary:$('Validar placa').item.binary}];"}};
  w.nodes.push(forward);w.connections['Autorizar un envío'].main[0][0].node=forward.name;
  w.connections[forward.name]={main:[[{node:'Meta - UN solo POST',type:'main',index:0}]]};
  get('meta').parameters.authentication='predefinedCredentialType';get('meta').parameters.nodeCredentialType='facebookGraphApi';delete get('meta').parameters.genericAuthType;
  get('meta').parameters.contentType='multipart-form-data';
  get('meta').parameters.bodyParameters={parameters:[{parameterType:'formBinaryData',name:'source',inputDataFieldName:'data'},{name:'caption',value:"={{ $('Reservar').item.json.payload.copy }}"}]};
  for (const node of w.nodes) {
    if (node.type === 'n8n-nodes-base.httpRequest' && node.parameters.genericAuthType === 'httpHeaderAuth') {
      node.credentials = {httpHeaderAuth:{id:'MfaSocialPortal1',name:'MFA - Publicaciones Portal'}};
    }
  }
  w.nodes.push({id:'note',name:'Estado y próximos pasos',type:'n8n-nodes-base.stickyNote',typeVersion:1,position:[200,-330],parameters:{width:750,height:260,content:`## ${label}: listo para conectar, NO activado\nHorario propuesto: ${hour}:00 Argentina.\n1. Desplegar API ledger/render revisada.\n2. Credencial PAT dedicada + Meta existente.\n3. Noticias: activas en portal desdeID834, sin habilitación manual; Eventos sigue manual.\n4. Revisar PNG, copy e ID; recién después publicar workflow.\nNo cambiar allow_publish antes de cumplir esos pasos. Sin IA, hosting extra ni variables de pago.\nMeta sin reintento; callback reintentable. Si no hay candidato, no publica.`}});
  fs.writeFileSync(path.join(output,`${kind}.json`),JSON.stringify(w,null,2)+'\n');
}
console.log('Generados dos workflows diarios inactivos, con bloqueo y PNG multipart.');
