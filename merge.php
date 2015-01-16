<?php

namespace SunlightTools\LangTool;

require __DIR__ . '/src/init.php';

// check arguments
if (sizeof($argv) < 2) {
    error(
        "Provide names of the input dictionaries to merge!\n\n"
        . 'Usage: php %s name1 [name2, name3, ...]',
        basename(__FILE__)
    );
}

// fetch input names
$inputNames = array_slice($argv, 1);

// initialize tool
$tool = new LangTool(__DIR__);

// load dictionaries
status('Loading dictionaries');

$templates = Dictionary::fromDirEach($tool->getCurrentTemplateDir());
$oldTemplate = Dictionary::fromDirMerged($tool->getOldTemplateDir());
$input = Dictionary::fromDirMerged($tool->getInputDir(), $inputNames);

if (0 === sizeof($input)) {
    error('Empty/invalid input dictionaries!');
}

$templateEntryCount = array_reduce($templates, function ($count, Dictionary $template) {
    return $count + sizeof($template);
}, 0);

status(' - loaded %d template entries', $templateEntryCount);
status(' - loaded %d old template entries', sizeof($oldTemplate));
status(' - loaded %d input dictionary entries', sizeof($input));

// merge
$processor = new Processor();

foreach ($templates as $name => $template) {
    /* @var $template Dictionary */

    $merger = new Merger(
        $processor,
        $input,
        $template,
        $oldTemplate
    );

    status('Merging with "%s"', $name);

    $merged = $merger->merge($missingCount, $cleanedCount, $caseFixCount);

    status(
        ' - %d missing + %d outdated = %d entries need updating',
        $missingCount,
        $cleanedCount,
        $missingCount + $cleanedCount
    );

    status(' - fixed case in %d entries', $caseFixCount);

    $outputPath = $tool->getOutputDir() . '/merged.' . $name . '.php';

    $merged->compileWithMeta($outputPath, $template->entries);

    status(' - saved to "%s"', $outputPath);
}

status('Finished');
