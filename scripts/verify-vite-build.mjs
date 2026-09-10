import { readFile, stat } from 'node:fs/promises';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const declaredAssetFields = ['file', 'css', 'assets'];

function normalizeDeclaredPaths(manifest) {
  if (!manifest || typeof manifest !== 'object' || Array.isArray(manifest)) {
    throw new Error('Vite manifest must contain a JSON object.');
  }

  const declaredPaths = new Set();

  for (const [entryName, entry] of Object.entries(manifest)) {
    if (!entry || typeof entry !== 'object' || Array.isArray(entry)) {
      throw new Error(`Manifest entry "${entryName}" must be an object.`);
    }

    for (const field of declaredAssetFields) {
      if (!(field in entry)) continue;

      const values = Array.isArray(entry[field]) ? entry[field] : [entry[field]];
      for (const value of values) {
        if (typeof value !== 'string' || value.trim() === '') {
          throw new Error(`Manifest entry "${entryName}.${field}" contains an invalid path.`);
        }
        declaredPaths.add(value);
      }
    }
  }

  if (declaredPaths.size === 0) {
    throw new Error('Vite manifest does not declare any build assets.');
  }

  return declaredPaths;
}

function resolveInsideBuild(buildDirectory, declaredPath) {
  if (path.isAbsolute(declaredPath) || path.win32.isAbsolute(declaredPath)) {
    throw new Error(`Unsafe absolute asset path: ${declaredPath}`);
  }

  const buildRoot = path.resolve(buildDirectory);
  const resolvedPath = path.resolve(buildRoot, declaredPath);
  const relativePath = path.relative(buildRoot, resolvedPath);

  if (relativePath === '..' || relativePath.startsWith(`..${path.sep}`) || path.isAbsolute(relativePath)) {
    throw new Error(`Asset path escapes public/build: ${declaredPath}`);
  }

  return resolvedPath;
}

export async function validateViteBuild(buildDirectory = 'public/build') {
  const buildRoot = path.resolve(buildDirectory);
  const manifestPath = path.join(buildRoot, 'manifest.json');
  let manifest;

  try {
    manifest = JSON.parse(await readFile(manifestPath, 'utf8'));
  } catch (error) {
    if (error instanceof SyntaxError) {
      throw new Error(`Invalid Vite manifest JSON: ${manifestPath}`);
    }
    throw new Error(`Unable to read Vite manifest: ${manifestPath}`);
  }

  const declaredPaths = normalizeDeclaredPaths(manifest);

  for (const declaredPath of declaredPaths) {
    const resolvedPath = resolveInsideBuild(buildRoot, declaredPath);
    let fileStats;

    try {
      fileStats = await stat(resolvedPath);
    } catch {
      throw new Error(`Missing Vite build asset: ${declaredPath}`);
    }

    if (!fileStats.isFile()) {
      throw new Error(`Vite build asset is not a file: ${declaredPath}`);
    }
  }

  return { buildDirectory: buildRoot, assetCount: declaredPaths.size };
}

const invokedAsScript = process.argv[1]
  && pathToFileURL(path.resolve(process.argv[1])).href === import.meta.url;

if (invokedAsScript) {
  const buildDirectory = process.argv[2] ?? 'public/build';

  validateViteBuild(buildDirectory)
    .then(({ assetCount }) => {
      process.stdout.write(`Vite build verified: ${assetCount} declared assets present.\n`);
    })
    .catch((error) => {
      process.stderr.write(`Vite build verification failed: ${error.message}\n`);
      process.exitCode = 1;
    });
}
