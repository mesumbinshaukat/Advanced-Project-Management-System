<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

service('auth')->routes($routes);

$routes->get('/', 'Home::index');

$routes->group('', ['filter' => 'session'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index');
    
    $routes->group('projects', ['filter' => 'permission:projects.view.assigned,projects.view.all'], function($routes) {
        $routes->get('/', 'ProjectsController::index');
        $routes->get('view/(:num)', 'ProjectsController::view/$1');
        $routes->get('create', 'ProjectsController::create', ['filter' => 'permission:projects.create']);
        $routes->get('edit/(:num)', 'ProjectsController::edit/$1', ['filter' => 'permission:projects.edit']);
    });
    
    $routes->group('tasks', ['filter' => 'permission:tasks.view.assigned,tasks.view.all'], function($routes) {
        $routes->get('/', 'TasksController::index');
        $routes->get('kanban/(:num)', 'TasksController::kanban/$1');
        $routes->get('create', 'TasksController::create', ['filter' => 'permission:tasks.create']);
        $routes->get('create/(:num)', 'TasksController::create/$1', ['filter' => 'permission:tasks.create']);
    });
    
    $routes->group('clients', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'ClientsController::index');
        $routes->get('create', 'ClientsController::create');
        $routes->get('edit/(:num)', 'ClientsController::edit/$1');
    });
    
    $routes->group('time', ['filter' => 'permission:time.log,time.view.all'], function($routes) {
        $routes->get('/', 'TimeEntriesController::index');
        $routes->get('create', 'TimeEntriesController::create');
    });
    
    $routes->group('api', function($routes) {
        $routes->group('projects', ['filter' => 'permission:projects.view.assigned,projects.view.all'], function($routes) {
            $routes->get('/', 'Api\ProjectsController::index');
            $routes->get('(:num)', 'Api\ProjectsController::show/$1');
            $routes->post('/', 'Api\ProjectsController::create', ['filter' => 'permission:projects.create']);
            $routes->put('(:num)', 'Api\ProjectsController::update/$1', ['filter' => 'permission:projects.edit']);
            $routes->delete('(:num)', 'Api\ProjectsController::delete/$1', ['filter' => 'permission:projects.delete']);
            $routes->post('(:num)/assign', 'Api\ProjectsController::assignUser/$1', ['filter' => 'permission:projects.assign']);
            $routes->delete('(:num)/users/(:num)', 'Api\ProjectsController::removeUser/$1/$2', ['filter' => 'permission:projects.assign']);
        });
        
        $routes->group('tasks', ['filter' => 'permission:tasks.view.assigned,tasks.view.all'], function($routes) {
            $routes->get('/', 'Api\TasksController::index');
            $routes->get('(:num)', 'Api\TasksController::show/$1');
            $routes->post('/', 'Api\TasksController::create', ['filter' => 'permission:tasks.create']);
            $routes->put('(:num)', 'Api\TasksController::update/$1');
            $routes->delete('(:num)', 'Api\TasksController::delete/$1', ['filter' => 'permission:tasks.delete']);
            $routes->post('(:num)/status', 'Api\TasksController::updateStatus/$1', ['filter' => 'permission:tasks.update.status']);
        });
        
        $routes->group('clients', ['filter' => 'role:admin'], function($routes) {
            $routes->get('/', 'Api\ClientsController::index');
            $routes->get('(:num)', 'Api\ClientsController::show/$1');
            $routes->post('/', 'Api\ClientsController::create');
            $routes->put('(:num)', 'Api\ClientsController::update/$1');
            $routes->delete('(:num)', 'Api\ClientsController::delete/$1');
        });
        
        $routes->group('time', ['filter' => 'permission:time.log,time.view.all'], function($routes) {
            $routes->get('/', 'Api\TimeEntriesController::index');
            $routes->get('(:num)', 'Api\TimeEntriesController::show/$1');
            $routes->post('/', 'Api\TimeEntriesController::create', ['filter' => 'permission:time.log']);
            $routes->put('(:num)', 'Api\TimeEntriesController::update/$1');
            $routes->delete('(:num)', 'Api\TimeEntriesController::delete/$1');
        });
    });
});
