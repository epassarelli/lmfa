const fs = require('node:fs');
const assert = require('node:assert/strict');
const workflow = JSON.parse(fs.readFileSync(`${__dirname}/mfa-social-publications.inactive.json`, 'utf8'));
assert.equal(workflow.active, false);
assert.equal(workflow.nodes.some(n => /schedule/i.test(n.type)), false);
for (const node of workflow.nodes) {
  assert.equal(Boolean(node.credentials), false, 'El export no debe contener referencias a credenciales reales');
  if (node.parameters.jsCode) new Function('$json', '$vars', '$', '$execution', node.parameters.jsCode);
}
const meta = workflow.nodes.find(n => n.id === 'meta');
assert.equal(meta.retryOnFail, false);
assert.equal(workflow.nodes.find(n => n.id === 'result').retryOnFail, true);
const classify = new Function('$json', '$', workflow.nodes.find(n => n.id === 'classify').parameters.jsCode);
const lookup = () => ({ item: { json: {target_id:1,attempt_id:2,lease:'fixture'} } });
for (const [input, outcome] of [
  [{statusCode:200,body:{post_id:'145931542128207_123'}},'success'],
  [{statusCode:400,body:{error:{code:100}}},'definitive_failure'],
  [{statusCode:500,body:{error:{code:1}}},'uncertain'],
  [{error:'timeout'},'uncertain'],
  [{statusCode:200,body:{id:'photo-id-only'}},'uncertain'],
  [{statusCode:408,body:{error:{code:1}}},'uncertain'],
]) assert.equal(classify(input, lookup)[0].json.outcome, outcome);
console.log('Export JSON y sintaxis válidos; seis escenarios de clasificación pasaron. No se ejecutó n8n ni HTTP.');
