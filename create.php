<?php

namespace SunlightTools\LangTool;

require __DIR__ . '/src/init.php';

// initialize tool
$tool = new LangTool(__DIR__);

// load dictionaries
status('Loading dictionaries');
$templates = Dictionary::fromDirEach($tool->getCurrentTemplateDir());
status(' - loaded %d templates', sizeof($templates));

// create
foreach ($templates as $name => $template) {
    /* @var $template Dictionary */

    status('Saving "%s"', $name);
    status(' - %d entries', sizeof($template));

    $outputPath = $tool->getOutputDir() . '/new.' . $name . '.php';

    $template->compileWithMeta($outputPath, $template->entries, true);

    status(' - saved to "%s"', $outputPath);
}

status('Finished');
