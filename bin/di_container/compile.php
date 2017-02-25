#!/usr/bin/env php
<?php
require __DIR__ . '/../../src/backend/App/Initializer.php';
\Si\App\Initializer::initFrameworkCommand(__DIR__ . "/../../../../..", "config");     /// application top directory, config subdirectory
    // autoloading + add namespace for application, create app, run Runner

function exec_cmd($command, $display_output = false, $log_file = null) {
    $output = [];
    $status = 0;
    if ($log_file !== null) {
        $command .= " > {$log_file} 2>&1";
    }
    exec($command, $output, $status);
    if ($display_output) {
        echo "\n";
        echo "status: " . $status . "\n";
        foreach ($output as $line) {
            echo $line . "\n";
        }
    }
    if ($log_file !== null) {
        echo(":: results writeln to " . $log_file . "\n");
    }
};

$app_dir = \Si\App\Ctx::app_dir();
$compiled_file = \Si\App\Ctx::config()['di_container']['compiled_file'];

@mkdir("{$app_dir}/".dirname($compiled_file), 0775, true);

$command = "{$app_dir}/vendor/bin/zen build {$app_dir}/{$compiled_file}";
exec_cmd($command . " \"Si\\DiContainer\\CompilerConfig\"");
