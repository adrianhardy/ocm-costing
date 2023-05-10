<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/rsync.php';

// Project name
set('application', 'orchardcrowncosting');
set('use_atomic_symlink', true);
set('writable_use_sudo', true);
set('rsync',[
    'exclude'      => [
        'yarn.lock',
        'yarn-error.log',
        'received_emails',
        'deploy.php',
        '.idea',
        '.git',
        '.gitignore',
        '.gitattributes',
        'readme.md',
        'phpunit.xml',
        'webpack.mix.js',
        'package-lock.json',
        '_ide_helper.php',
        'deployer.php',
        'deploy.php.dist',
        'deploy.php',
        'package.json',
        'server.php',
        'node_modules',
        'tests',
        'vendor',
        'storage',
        'public/storage',
        'web.config',
        '*.sql',
        '.env',
        '.env.prod'
    ],
    'exclude-file' => false,
    'include' => [
        'public/*'
    ],
    'include-file' => false,
    'filter' => [],
    'filter-file' => false,
    'filter-perdir' => false,
    'flags'        => 'rz', // Recursive, with compress
    'options'      => ['delete'],
    'timeout'      => 3600,
]);

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', []);

// Writable dirs by web server
add('writable_dirs', []);


// Hosts
host('staging')
    ->set('branch', 'master')
    ->user('deploy')
    ->hostname('coststaging.orchardcrownmall.co.uk')
    ->set('bin/php', '/usr/bin/php74')
    ->set('rsync_src', __DIR__)
    ->set('deploy_path', '/home/orchardcrownmall.co.uk/costing/staging');


host('live')
    ->set('branch', 'master')
    ->user('deploy')
    ->hostname('coststaging.orchardcrownmall.co.uk')
    ->set('bin/php', '/usr/bin/php74')
    ->set('rsync_src', __DIR__)
    ->set('deploy_path', '/home/orchardcrownmall.co.uk/costing/live');

task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'rsync',
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:storage:link',
    'artisan:cache:clear',
    'artisan:config:cache',
    'artisan:optimize',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');
