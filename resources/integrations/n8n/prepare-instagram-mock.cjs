const fs=require('node:fs'),path=require('node:path');
const w=JSON.parse(fs.readFileSync(path.join(__dirname,'daily/instagram-news.json'),'utf8'));
w.id='MfaIGMockProtocol1';w.name='MFA - Instagram simulador privado (sin Meta)';
for(const n of w.nodes){
  if(n.name==='Configuración y bloqueo')n.parameters.jsCode="return [{json:{api_base_url:'http://127.0.0.1:18791/api/v1',allow_publish:true,test_news_id:833}}];";
  if(n.type==='n8n-nodes-base.httpRequest'){
    delete n.credentials;delete n.parameters.authentication;delete n.parameters.genericAuthType;delete n.parameters.nodeCredentialType;
    n.parameters.url=n.parameters.url.replaceAll('https://graph.facebook.com/v27.0','http://127.0.0.1:18791/meta');
  }
  if(n.name==='Esperar cinco segundos')n.parameters.amount=0.01;
}
fs.writeFileSync(path.join(__dirname,'../../../project/artifacts/instagram-mock.json'),JSON.stringify(w,null,2));
