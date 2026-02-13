<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Register Shield auth routes but disable public registration
$authRoutes = service('auth')->routes($routes);

// Remove the register route to prevent public registration
// Only admins can create users through the admin panel
$routes->match(['GET', 'POST'], 'register', static function() {
    return redirect()->to('login')->with('error', 'User registration is disabled. Contact an administrator to create an account.');
});

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
        $routes->get('view/(:num)', 'TasksController::view/$1');
        $routes->get('kanban/(:num)', 'TasksController::kanban/$1');
        $routes->get('create', 'TasksController::create', ['filter' => 'permission:tasks.create']);
        $routes->get('create/(:num)', 'TasksController::create/$1', ['filter' => 'permission:tasks.create']);
        $routes->get('edit/(:num)', 'TasksController::edit/$1', ['filter' => 'permission:tasks.edit']);
    });
    
    $routes->group('clients', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'ClientsController::index');
        $routes->get('create', 'ClientsController::create');
        $routes->get('edit/(:num)', 'ClientsController::edit/$1');
    });
    
    $routes->group('time', ['filter' => 'permission:time.log,time.view.all'], function($routes) {
        $routes->get('/', 'TimeEntriesController::index');
        $routes->get('create', 'TimeEntriesController::create');
        $routes->get('tracker', 'TimeEntriesController::tracker');
        $routes->post('store', 'TimeEntriesController::store');
    });
    
    $routes->group('notes', ['filter' => 'permission:tasks.view.assigned,tasks.view.all'], function($routes) {
        $routes->get('/', 'NotesController::index');
        $routes->get('create', 'NotesController::create');
        $routes->post('store', 'NotesController::store');
        $routes->get('edit/(:num)', 'NotesController::edit/$1');
        $routes->post('update/(:num)', 'NotesController::update/$1');
        $routes->get('delete/(:num)', 'NotesController::delete/$1');
    });
    
    $routes->group('messages', ['filter' => 'permission:projects.view.assigned,projects.view.all'], function($routes) {
        $routes->get('(:num)', 'MessagesController::index/$1');
        $routes->post('store', 'MessagesController::store');
    });
    
    $routes->group('developers', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'DevelopersController::index');
        $routes->get('workload/(:num)', 'DevelopersController::workload/$1');
    });
    
    $routes->group('users', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'UsersController::index');
        $routes->get('create', 'UsersController::create');
        $routes->post('store', 'UsersController::store');
        $routes->get('edit/(:num)', 'UsersController::edit/$1');
        $routes->post('update/(:num)', 'UsersController::update/$1');
        $routes->get('delete/(:num)', 'UsersController::delete/$1');
    });
    
    $routes->group('check-in', function($routes) {
        $routes->get('/', 'CheckInController::index');
        $routes->post('store', 'CheckInController::store');
        $routes->get('team', 'CheckInController::team', ['filter' => 'role:admin']);
    });
    
    $routes->group('alerts', function($routes) {
        $routes->get('/', 'AlertsController::index');
        $routes->get('resolve/(:num)', 'AlertsController::resolve/$1');
        $routes->get('generate', 'AlertsController::generate', ['filter' => 'role:admin']);
    });
    
    $routes->group('templates', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'TemplatesController::index');
        $routes->get('create-project', 'TemplatesController::createProject');
        $routes->post('store-project', 'TemplatesController::storeProject');
        $routes->get('create-task', 'TemplatesController::createTask');
        $routes->post('store-task', 'TemplatesController::storeTask');
        $routes->get('use-project/(:num)', 'TemplatesController::useProjectTemplate/$1');
        $routes->post('apply-project', 'TemplatesController::applyProjectTemplate');
    });
    
    $routes->group('profitability', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'ProfitabilityController::index');
        $routes->get('project/(:num)', 'ProfitabilityController::project/$1');
    });
    
    $routes->group('capacity', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'CapacityController::index');
    });
    
    $routes->group('performance', ['filter' => 'role:admin'], function($routes) {
        $routes->get('/', 'PerformanceController::index');
        $routes->get('developer/(:num)', 'PerformanceController::developer/$1');
        $routes->get('update-all', 'PerformanceController::updateAll');
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
        
        $routes->group('time-entries', ['filter' => 'permission:time.log,time.view.all'], function($routes) {
            $routes->get('/', 'Api\TimeEntriesController::index');
            $routes->get('(:num)', 'Api\TimeEntriesController::show/$1');
            $routes->post('/', 'Api\TimeEntriesController::create', ['filter' => 'permission:time.log']);
            $routes->put('(:num)', 'Api\TimeEntriesController::update/$1');
            $routes->delete('(:num)', 'Api\TimeEntriesController::delete/$1');
        });
        
        $routes->group('notes', ['filter' => 'permission:tasks.view.assigned,tasks.view.all'], function($routes) {
            $routes->get('/', 'Api\NotesController::index');
            $routes->post('/', 'Api\NotesController::create');
            $routes->put('(:num)', 'Api\NotesController::update/$1');
            $routes->delete('(:num)', 'Api\NotesController::delete/$1');
            $routes->post('pin/(:num)', 'Api\NotesController::pin/$1');
        });
        
        $routes->group('messages', ['filter' => 'permission:projects.view.assigned,projects.view.all'], function($routes) {
            $routes->get('/', 'Api\MessagesController::index');
            $routes->post('/', 'Api\MessagesController::create');
            $routes->post('(:num)/read', 'Api\MessagesController::markRead/$1');
            $routes->get('unread', 'Api\MessagesController::unreadCount');
        });
        
        $routes->group('assignment', ['filter' => 'permission:projects.view.all'], function($routes) {
            $routes->get('suggest', 'Api\AssignmentController::suggest');
            $routes->get('workload', 'Api\AssignmentController::workload');
            $routes->get('workload/(:num)', 'Api\AssignmentController::workload/$1');
        });
    });
});
