<?php

/**
 * Pins every intra-monorepo `spiral/*` constraint of the split packages to the version being released.
 *
 * Runs on the release PR branch, after release-please has bumped `.github/release-please/manifest.json`.
 * All packages share one version, so every sibling dependency becomes `^<version>`.
 * Only `require` and `require-dev` are touched; `suggest` and external `spiral/*` packages are left as authored.
 *
 * Usage: php .github/release-please/sync-deps.php
 */

declare(strict_types=1);

const MANIFEST = '.github/release-please/manifest.json';
const PACKAGES = ['src/*/composer.json', 'src/Bridge/*/composer.json'];
const SECTIONS = ['require', 'require-dev'];

$root = \getcwd();

$version = readJson($root . '/' . MANIFEST)['.'] ?? null;
if (!\is_string($version) || !\preg_match('/^\d+\.\d+\.\d+$/', $version)) {
    \fwrite(\STDERR, 'Unexpected root version in ' . MANIFEST . ': ' . \var_export($version, true) . "\n");
    exit(1);
}

$files = [];
foreach (PACKAGES as $pattern) {
    foreach (\glob($root . '/' . $pattern) ?: [] as $file) {
        $files[] = $file;
    }
}

$packages = [];
foreach ($files as $file) {
    $packages[readJson($file)['name']] = true;
}

$changed = [];
foreach ($files as $file) {
    $original = (string) \file_get_contents($file);
    $updated = syncFile($original, $packages, '^' . $version);
    if ($updated !== $original) {
        \file_put_contents($file, $updated);
        $changed[] = \substr($file, \strlen($root) + 1);
    }
}

echo $changed === []
    ? "No constraints needed updating.\n"
    : "Synced spiral/* constraints to ^$version in " . \count($changed) . " files.\n";

/**
 * Rewrites constraints with a regex instead of re-encoding the JSON, so untouched lines keep their formatting.
 *
 * @param array<non-empty-string, true> $packages monorepo package names
 */
function syncFile(string $content, array $packages, string $constraint): string
{
    foreach (SECTIONS as $section) {
        // Section bodies hold plain strings only, so `[^{}]*` captures the whole block.
        $pattern = '/("' . $section . '"\s*:\s*\{)([^{}]*)(\})/';
        $content = (string) \preg_replace_callback(
            $pattern,
            static fn(array $m): string => $m[1] . \preg_replace_callback(
                '/("(spiral\/[^"]+)"\s*:\s*")[^"]*(")/',
                static fn(array $dep): string => isset($packages[$dep[2]])
                    ? $dep[1] . $constraint . $dep[3]
                    : $dep[0],
                $m[2],
            ) . $m[3],
            $content,
        );
    }

    return $content;
}

function readJson(string $path): array
{
    if (!\is_file($path)) {
        \fwrite(\STDERR, "File not found: $path\n");
        exit(1);
    }

    return \json_decode((string) \file_get_contents($path), true, flags: \JSON_THROW_ON_ERROR);
}
