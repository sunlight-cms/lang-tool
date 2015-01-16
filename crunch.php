<?php

namespace SunlightTools\LangTool;

require __DIR__ . '/src/init.php';

// check arguments
if (sizeof($argv) < 2) {
    error(
        "Provide names of the input dictionaries to crunch!\n\n"
        . 'Usage: php %s name1 [name2, name3, ...]',
        basename(__FILE__)
    );
}

// fetch input names
$inputNames = array_slice($argv, 1);

// initialize tool
$tool = new LangTool(__DIR__);

// load dictionaries
status('Loading input dictionaries');
$inputs = Dictionary::fromDirEach($tool->getInputDir(), $inputNames);
status(' - loaded %d dictionaries', sizeof($inputs));

// save
foreach ($inputs as $name => $input) {
    /* @var $input Dictionary */

    status('Crunching "%s"', $name);
    status(' - %d entries', sizeof($input));

    $outputPath = $tool->getOutputDir() . '/crunched.' . $name . '.php';

    $input->compile($outputPath);

    status(' - saved to "%s"', $outputPath);
}

status('Finished');
