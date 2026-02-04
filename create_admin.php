<?php

require 'vendor/autoload.php';

$pathsConfig = new Config\Paths();
$bootstrap = rtrim(realpath(ROOTPATH . $pathsConfig->systemDirectory) ?: ROOTPATH . $pathsConfig->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR;
require $bootstrap . 'bootstrap.php';

$app = Config\Services::codeigniter();
$app->initialize();

$users = auth()->getProvider();

$user = $users->findByCredentials(['email' => 'admin@example.com']);

if ($user) {
    echo "Admin user already exists.\n";
} else {
    $user = new \CodeIgniter\Shield\Entities\User([
        'username' => 'admin',
        'email'    => 'admin@example.com',
        'password' => 'admin123',
    ]);

    $users->save($user);
    
    $user = $users->findByCredentials(['email' => 'admin@example.com']);
    $user->addGroup('admin');
    
    echo "Admin user created successfully!\n";
    echo "Email: admin@example.com\n";
    echo "Password: admin123\n";
    echo "Group: admin\n";
}
