<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Iconify\IconsJSON\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\SplFileInfo;
use Codeat3\BladeIconGeneration\IconProcessor;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Codeat3\BladeIconGeneration\Exceptions\InvalidFileExtensionException;

$svgNormalization = static function (string $tempFilepath, array $iconSet, SplFileInfo $sourceFile) {
    // perform generic optimizations
    try {
        $iconProcessor = new IconProcessor($tempFilepath, $iconSet, $sourceFile);
        $iconProcessor->optimize()->save();
    } catch (InvalidFileExtensionException $e) {
        print_r($e->getMessage());
        unlink($tempFilepath);
        return;
    }
};

function importIcons()
{
    $collectionPath = file_get_contents(Finder::locate('solar'));
    foreach (json_decode($collectionPath, true)['icons'] as $name => $icon) {
        file_put_contents(__DIR__ . '/../dist/' . $name . '.svg', '<svg viewBox="0 0 24 24">' . trim($icon['body']) . '</svg>');
    }
}

function getVersionFromCommitFile()
{
    // get the version from the .commit file
    $versionFromCommitFile = file_get_contents('.commit');
    $versionFromCommitFile = Str::of($versionFromCommitFile)->trim()->toString();
    return $versionFromCommitFile;
}

function getUpdatedPackageVersion()
{
    // get the version from the updated composer file
    $process = new Process(['composer', 'show', 'iconify/json', '--format=json']);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
        throw new ProcessFailedException($process);
    }

    $iconifyMeta = json_decode($process->getOutput(), true);

    return $iconifyMeta['source']['reference'];
}

$versionFromCommitFile = getVersionFromCommitFile();
$latestVersion = getUpdatedPackageVersion();

if ($versionFromCommitFile != $latestVersion) {
    importIcons();
    file_put_contents('.commit', $latestVersion);
} else {
    dd('should not update');
}

return [
    [
        'source' => __DIR__ . '/../dist/',
        'destination' => __DIR__ . '/../resources/svg',
        'safe' => false,
        'after' => $svgNormalization,
    ],
];
