<?php
namespace Deployer;
require 'recipe/common.php';

function exec_cmd($command, $display_output = false, $log_file = null) {
    $output = [];
    $status = 0;
    if ($log_file !== null) {
        $command .= " > {$log_file} 2>&1";
    }
    exec($command, $output, $status);
    if ($display_output) {
        echo "\n";
        foreach ($output as $line) {
            echo $line . "\n";
        }
    }
    if ($log_file !== null) {
        write(":: results writeln to " . $log_file . "\n");
    }
};

$fw_dir = __DIR__;
chdir($fw_dir);

set('ssh_type', 'native');
set('ssh_multiplexing', true);

task('checks', function() use ($fw_dir) {
    cd($fw_dir);
    $log = "var/log/checks.log";
    $command = "bin/quality_tools/check.php";
    exec_cmd($command, false, $log);
})->desc("Project quality checks");

task('composer', function() use ($fw_dir) {
    cd($fw_dir);
    $command = "composer dump-autoload";
    exec_cmd($command, true);
})->desc("Composer autoload update");

task('doxygen', function() use ($fw_dir) {
    cd($fw_dir);
    $log = "var/log/doxygen.log";
    $command = "doxygen";
    exec_cmd($command, false, $log);
})->desc("Generate documentation with Doxygen");

task('build', [
    'checks',
    'composer',
    'doxygen',
]);

/*
set('repository', 'git@domain.com:username/repository.git');
set('shared_files', []);
set('shared_dirs', []);
set('writable_dirs', []);

// Servers

server('production', 'domain.com')
    ->user('username')
    ->identityFile()
    ->set('deploy_path', '/var/www/domain.com');


// Tasks

desc('Restart PHP-FPM service');
task('php-fpm:restart', function () {
    // The user must have rights for restart service
    // /etc/sudoers: username ALL=NOPASSWD:/bin/systemctl restart php-fpm.service
    run('sudo systemctl restart php-fpm.service');
});
after('deploy:symlink', 'php-fpm:restart');

desc('Deploy your project');
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:clear_paths',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success'
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
 */

/*

task('deploy:staging', function() {
    writeln('<info>Deploying...</info>');
    /// Deploy:
    $appFiles = ['app', 'bootstrap', 'public', 'composer.json', 'composer.lock', 'artisan', '.env', ]; /// dirs to deploy
    $deployPath = env('deploy_path');   /// use deploy_path from server definition
    foreach ($appFiles as $file)
    {
        upload($file, "{$deployPath}/{$file}");
    }
    /// Run commands:
    cd($deployPath);
    run("composer update --no-dev --prefer-dist --optimize-autoloader");
    /// perform usual things
    set('writable_dirs', ['app/storage']);  /// make dirs writable by server user
    writeln('<info>Deployment is done.</info>');
})->desc('Taks description')    /// help message
  ->onlyOn('test');             /// run only on this stage, ex. when task used in after()
task('glowny', ['subtaskA', 'subtaskB']);


runLocally('git checkout '.get('branch', '').' 2> /dev/null');      /// local
run("chown -R www-data:www-data app/storage");                      /// on server
run('cd {{release_path}} && php {{release_path}}/{{bin_dir}}/console mopa:bootstrap:install:font --env={{env}}');
if (askConfirmation('Rebuild database?', false)) { run(...); }      /// conditionaly
*/
