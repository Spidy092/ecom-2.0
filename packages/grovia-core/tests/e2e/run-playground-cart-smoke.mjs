import { readFile } from 'node:fs/promises';
import { spawn } from 'node:child_process';
import { runCLI } from '@wp-playground/cli';

const blueprint = JSON.parse(await readFile('playground/blueprint.json', 'utf8'));
const branch = process.env.GITHUB_HEAD_REF || process.env.GITHUB_REF_NAME || 'fix/aisleflow-cart-single-truth';

for (const step of blueprint.steps || []) {
  for (const key of ['pluginData', 'themeData']) {
    const resource = step?.[key];
    if (resource?.resource === 'git:directory' && resource.url === 'https://github.com/Spidy092/ecom-2.0') {
      resource.ref = branch;
      resource.refType = 'branch';
    }
  }
}

let cliServer;
try {
  cliServer = await runCLI({
    command: 'server',
    port: 9401,
    blueprint,
  });

  if (!cliServer?.serverUrl) {
    throw new Error('Playground CLI did not return a serverUrl.');
  }

  console.log(`AisleFlow Playground ready at ${cliServer.serverUrl} from ${branch}`);

  const child = spawn(
    'python',
    ['packages/grovia-core/tests/e2e/playground-cart-single-truth.spec.py'],
    {
      stdio: 'inherit',
      env: {
        ...process.env,
        GROVIA_PLAYGROUND_BASE_URL: cliServer.serverUrl.replace(/\/$/, ''),
      },
    },
  );

  const exitCode = await new Promise((resolve, reject) => {
    child.once('error', reject);
    child.once('exit', (code, signal) => {
      if (signal) {
        reject(new Error(`Playground cart smoke terminated by ${signal}.`));
        return;
      }
      resolve(code ?? 1);
    });
  });

  if (exitCode !== 0) {
    throw new Error(`Playground cart smoke exited with status ${exitCode}.`);
  }
} catch (error) {
  console.error(error);
  process.exitCode = 1;
} finally {
  setTimeout(() => process.exit(process.exitCode ?? 0), 50);
}
