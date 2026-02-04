<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    /**
     * --------------------------------------------------------------------
     * Default Group
     * --------------------------------------------------------------------
     * The group that a newly registered user is added to.
     */
    public string $defaultGroup = 'developer';

    /**
     * --------------------------------------------------------------------
     * Groups
     * --------------------------------------------------------------------
     * An associative array of the available groups in the system, where the keys
     * are the group names and the values are arrays of the group info.
     *
     * Whatever value you assign as the key will be used to refer to the group
     * when using functions such as:
     *      $user->addGroup('admin');
     *
     * @var array<string, array<string, string>>
     *
     * @see https://codeigniter4.github.io/shield/quick_start_guide/using_authorization/#change-available-groups for more info
     */
    public array $groups = [
        'admin' => [
            'title'       => 'Administrator',
            'description' => 'Full system access with all capabilities',
        ],
        'developer' => [
            'title'       => 'Developer',
            'description' => 'Limited access to assigned projects and tasks only',
        ],
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions
     * --------------------------------------------------------------------
     * The available permissions in the system.
     *
     * If a permission is not listed here it cannot be used.
     */
    public array $permissions = [
        'projects.create'        => 'Can create projects',
        'projects.edit'          => 'Can edit projects',
        'projects.delete'        => 'Can delete projects',
        'projects.archive'       => 'Can archive projects',
        'projects.view.all'      => 'Can view all projects',
        'projects.view.assigned' => 'Can view assigned projects',
        'projects.assign'        => 'Can assign users to projects',
        'clients.create'         => 'Can create clients',
        'clients.edit'           => 'Can edit clients',
        'clients.delete'         => 'Can delete clients',
        'clients.view.all'       => 'Can view all clients',
        'tasks.create'           => 'Can create tasks',
        'tasks.edit'             => 'Can edit tasks',
        'tasks.delete'           => 'Can delete tasks',
        'tasks.assign'           => 'Can assign tasks',
        'tasks.view.all'         => 'Can view all tasks',
        'tasks.view.assigned'    => 'Can view assigned tasks',
        'tasks.update.status'    => 'Can update task status',
        'time.log'               => 'Can log time entries',
        'time.view.all'          => 'Can view all time entries',
        'time.view.own'          => 'Can view own time entries',
        'financials.view'        => 'Can view financial data',
        'financials.edit'        => 'Can edit financial data',
        'analytics.view'         => 'Can view analytics',
        'users.manage'           => 'Can manage users',
        'settings.manage'        => 'Can manage settings',
        'comments.create'        => 'Can create comments',
        'files.upload'           => 'Can upload files',
        'checkins.submit'        => 'Can submit check-ins',
    ];

    /**
     * --------------------------------------------------------------------
     * Permissions Matrix
     * --------------------------------------------------------------------
     * Maps permissions to groups.
     *
     * This defines group-level permissions.
     */
    public array $matrix = [
        'admin' => [
            'projects.*',
            'clients.*',
            'tasks.*',
            'time.*',
            'financials.*',
            'analytics.*',
            'users.*',
            'settings.*',
        ],
        'developer' => [
            'projects.view.assigned',
            'tasks.view.assigned',
            'tasks.update.status',
            'time.log',
            'time.view.own',
            'comments.create',
            'files.upload',
            'checkins.submit',
        ],
    ];
}
