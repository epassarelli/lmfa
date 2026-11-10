const fs=require('node:fs');
const cp=require('node:child_process');
const assert=require('node:assert/strict');
async function main(){
  // This runner must only execute the isolated mock workflow, never the live drafts.
  const wf=JSON.parse(fs.readFileSync('/tmp/mfa-mock-workflow.json','utf8'));
  assert.equal(wf.id,'MfaMockProtocol1');
  assert.equal(wf.nodes.find(n=>n.id==='meta').parameters.url,'http://127.0.0.1:5688/meta');
  assert.equal(wf.nodes.some(n=>n.credentials),false);
  const results=[];
  for(const [scenario,status,posts] of [['success','success',1],['reject','failed',1],['server_error','uncertain',1],['lost_meta','uncertain',1],['lost_result','success',1],['lost_begin','processing',0]]){
    await fetch('http://127.0.0.1:5688/control/reset?scenario='+scenario);
    let exit=0;
    try{cp.execFileSync('n8n',['execute','--id=MfaMockProtocol1','--rawOutput'],{env:{...process.env,N8N_RUNNERS_BROKER_PORT:'5680'},stdio:['ignore','pipe','pipe'],timeout:60000,maxBuffer:15*1024*1024});}
    catch(e){exit=e.status??1;}
    const state=await (await fetch('http://127.0.0.1:5688/control/state')).json();
    assert.equal(state.status,status,scenario);assert.equal(state.meta_posts,posts,scenario);assert.equal(state.begins,1,scenario);
    if(posts)assert.equal(state.png_verified,true,scenario);
    if(scenario==='lost_result')assert.equal(state.callbacks,2,'Only callback retries');
    results.push({...state,execution_exit:exit});
    console.log(JSON.stringify(results.at(-1)));
  }
  fs.writeFileSync('/tmp/mfa-mock-protocol-evidence.json',JSON.stringify(results,null,2));
}
main().catch(e=>{console.error(e.message);process.exitCode=1});
