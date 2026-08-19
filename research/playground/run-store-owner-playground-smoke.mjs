import { readFile } from 'node:fs/promises';
import { spawn } from 'node:child_process';
import { runCLI } from '@wp-playground/cli';

const blueprintPath = 'research/playground/store-owner-validation.blueprint.json';
const blueprint = JSON.parse(await readFile(blueprintPath, 'utf8'));

let cliServer;
try {
  // runCLI resolves only after the server and Blueprint are ready. This avoids
  // probing WordPress over HTTP before the real browser arrives, which would
  // consume Playground's Blueprint login hand-off.
  cliServer = await runCLI({
    command: 'server',
    port: 9400,
    blueprint,
  });

  if (!cliServer?.serverUrl) {
    throw new Error('Playground CLI did not return a serverUrl.');
  }

  console.log(`Playground ready at ${cliServer.serverUrl}`);

  const child = spawn(
    'python',
    ['research/playground/store-owner-playground.spec.py'],
    {
      stdio: 'inherit',
      env: {
        ...process.env,
        BT_PLAYGROUND_BASE_URL: cliServer.serverUrl.replace(/\/$/, ''),
      },
    },
  );

  const exitCode = await new Promise((resolve, reject) => {
    child.once('error', reject);
    child.once('exit', (code, signal) => {
      if (signal) {
        reject(new Error(`Playground browser smoke terminated by ${signal}.`));
        return;
      }
      resolve(code ?? 1);
    });
  });

  if (exitCode !== 0) {
    throw new Error(`Playground browser smoke exited with status ${exitCode}.`);
  }
} catch (error) {
  console.error(error);
  process.exitCode = 1;
} finally {
  // The CLI currently owns worker/server lifecycle. Force termination after the
  // browser assertion so CI never leaves Playground workers behind.
  setTimeout(() => process.exit(process.exitCode ?? 0), 50);
}
