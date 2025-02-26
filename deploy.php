<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/rsync.php';

// Project name
set('application', 'orchardcrowncosting');
set('use_atomic_symlink', true);
set('writable_use_sudo', false);
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

host('live')
    ->set('branch', 'master')
    ->user('great-lederberg_1h250g29tgvi')
    ->hostname('costing.orchardcrownmall.co.uk')
    ->set('rsync_src', __DIR__)
    ->set('bin/php', '/opt/plesk/php/7.4/bin/php')
    ->set('bin/composer', '/opt/plesk/php/7.4/bin/php /usr/local/psa/var/modules/composer/composer.phar')
    ->set('deploy_path', '/var/www/vhosts/costing.orchardcrownmall.co.uk/costinglive.orchardcrownmall.co.uk');

task('deploy', [
    'deploy:info',
    'deploy:custom_prep',
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

// Plesk sometimes creates the real directories current/public if they don't exist
// which means deployer can't deploy...
task('deploy:custom_prep', function(){
    $fakeDirectory = test('[ ! -L {{deploy_path}}/current ] && [ -d {{deploy_path}}/current ]');
    if ($fakeDirectory) {
        if (test('[ -f {{deploy_path}}/current/public/index.html ]')) {
            run ("rm {{deploy_path}}/current/public/index.html");
        }
        if (test('[ -d {{deploy_path}}/current/public ]')) {
            run("rmdir {{deploy_path}}/current/public");
        }
        run ("rmdir {{deploy_path}}/current");
    }
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.

before('deploy:symlink', 'artisan:migrate');
