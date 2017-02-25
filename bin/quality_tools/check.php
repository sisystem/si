#!/usr/bin/env php
<?php
require __DIR__ . '/../../src/backend/App/Initializer.php';
\Si\App\Initializer::initFrameworkCommand(__DIR__ . "/../../../../..", "config");     /// application top directory, config subdirectory
    // autoloading + add namespace for application, create app, run Runner

$fw_dir = __DIR__."/../..";
@mkdir("{$fw_dir}/doc/out/quality_tools", 0775, true);

chdir($fw_dir);

$exitStatuses = [];

foreach ([
    'PHPCS' => 'vendor/bin/phpcs --standard=PSR2 --extensions=php,inc,js,ts --ignore=*/tests/* --encoding=utf-8 src \
                ',
                #--runtime-set ignore_errors_on_exit 1 --runtime-set ignore_warnings_on_exit 1
                #-v
                #--report=full/xml/csv/source/summary/gitblame/checkstyle
                #--report-TYPE=FILE
    'PHPMD' => 'vendor/bin/phpmd src text codesize,cleancode,unusedcode,design,naming \
                ',
                #--ignore-violations-on-exit
                #controversial
                #--reportfile file.xml   --- def. stdout
                #--suffixec php,inc
                #--exclude=PAT1,PAT2
                #--strict                --- ignore @SuppressWarnings annotation
    'PHPCPD'=> 'vendor/bin/phpcpd src',
                #--suffixes php,php5,inc     --- def. php
                #--min-lines 4       --- min. identical lines (def. 5)
                #--min-tokens 40     --- min. identical tokens (def. 70)
                #--log-pmd file.xml  --- export to XML
    'PHPLOC'=>  'vendor/bin/phploc src',
                #--progress
                #--log-csv file.csv  --- export to csv
    'PDEPEND'=> 'vendor/bin/pdepend --summary-xml=doc/out/quality_tools/pdepend.xml \
                --jdepend-chart=doc/out/quality_tools/jdepend.svg \
                --overview-pyramid=doc/out/quality_tools/pyramid.svg src',
    'echo1' =>  ["charts written to 'doc/out/quality_tools'",
                "metrics description: https://pdepend.org/documentation/software-metrics/index.html"],
    'PHPMETRICS' => 'vendor/bin/phpmetrics \
                --plugins=./vendor/phpmetrics/composer-extension/ComposerExtension.php \
                --report-html=doc/out/quality_tools/phpmetrics.html src \
                --report-cli \
                --chart-bubbles=doc/out/quality_tools/chart.svg \
                ',
                #--report-html/csv/json/xml=FILE \
                #--report-xml=php://stdout -q
                #--excluded-dirs=REGEX
                #--level     --- depth of summary report
    'echo2' =>  ["charts written to 'doc/out/quality_tools'",
                "metrics description: www.phpmetrics.org/documentation/index.html"],
] as $tool => $command) {
    if (strpos($tool, "echo") !== false) {
        foreach ($command as $line) {
            echo $line . "\n";
        }
        continue;
    }
    echo "\n       ******** {$tool} ********\n";
    $output = [];
    $status = 0;
    exec($command, $output, $status);
    echo "exit status: " . $status . "\n";
    $exitStatuses[$tool] = $status;
    foreach ($output as $line) {
        echo $line . "\n";
    }
}

echo "\nexit statuses:\n";
var_export($exitStatuses);
