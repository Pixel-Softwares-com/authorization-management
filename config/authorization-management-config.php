<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Policies Configuration
    |--------------------------------------------------------------------------
    |
    | Register your model-policy mappings here.
    |
    */
    "policies" => [],

    /*
    |--------------------------------------------------------------------------
    | Independent Gates Configuration
    |--------------------------------------------------------------------------
    |
    | Register your independent gate classes here.
    |
    */
    "independent_gates" => [],

    /*
    |--------------------------------------------------------------------------
    | Custom Exception Class
    |--------------------------------------------------------------------------
    |
    | Specify a custom exception class for authorization failures.
    | Leave empty to use the default Exception class.
    |
    */
    "custom-exception-class" => "",

    /*
    |--------------------------------------------------------------------------
    | Department Roles Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for department-based role authorization.
    | Department Roles define a user's position/function within a department
    | (e.g., manager, engineer, representative).
    |
    */
    'department_roles' => [

        /*
        |--------------------------------------------------------------------------
        | Default Department Roles
        |--------------------------------------------------------------------------
        |
        | These are the standard department roles that come pre-configured.
        | Each role includes:
        | - relation: The Eloquent relationship name (e.g., 'managers')
        | - dep_role_value: The value stored in the 'dep_role' pivot column
        | - view_as_constant_prefix: Prefix for ViewAs constants
        | - label: Human-readable label
        | - enabled: Whether this role is active in the system
        | - can_be_disabled: Whether this role can be turned off
        |
        */
        'default_roles' => [
            'manager' => [
                'relation' => 'managers',
                'dep_role_value' => 'manager',
                'view_as_constant_prefix' => 'MANAGER',
                'label' => 'Manager',
                'enabled' => true,
                'can_be_disabled' => false,
            ],

            'engineer' => [
                'relation' => 'engineers',
                'dep_role_value' => 'engineer',
                'view_as_constant_prefix' => 'ENGINEER',
                'label' => 'Engineer',
                'enabled' => true,
                'can_be_disabled' => true,
            ],

            'rep' => [
                'relation' => 'reps',
                'dep_role_value' => 'rep',
                'view_as_constant_prefix' => 'REP',
                'label' => 'Representative',
                'enabled' => true,
                'can_be_disabled' => false,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Custom Department Roles
        |--------------------------------------------------------------------------
        |
        | Add your custom department roles here. Each custom role should follow
        | the same structure as default roles.
        |
        | Example:
        | 'supervisor' => [
        |     'relation' => 'supervisors',
        |     'dep_role_value' => 'supervisor',
        |     'view_as_constant_prefix' => 'SUPERVISOR',
        |     'label' => 'Supervisor',
        |     'enabled' => true,
        |     'can_be_disabled' => true,
        | ],
        |
        */
        'custom_roles' => [
            // Add your custom department roles here
        ],

        /*
        |--------------------------------------------------------------------------
        | Role Hierarchy
        |--------------------------------------------------------------------------
        |
        | Define the hierarchy of department roles for permission inheritance.
        | Higher numbers = more authority.
        |
        */
        'hierarchy' => [
            'manager' => 100,
            'engineer' => 50,
            'rep' => 30,
        ],

        /*
        |--------------------------------------------------------------------------
        | Department Specific Settings
        |--------------------------------------------------------------------------
        |
        | Configure which roles are available for specific department types.
        | This allows different departments to have different role sets.
        |
        */
        'department_specific_roles' => [
            // 'IT' => ['manager', 'engineer', 'rep', 'tech_lead'],
            // 'HR' => ['manager', 'rep', 'hr_specialist'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Validation Settings
        |--------------------------------------------------------------------------
        |
        | Validation rules for the dep_role column.
        |
        */
        'validation' => [
            'max_length' => 50,
            'nullable' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Cache Settings
        |--------------------------------------------------------------------------
        |
        | Configure caching for department roles.
        |
        */
        'cache' => [
            'enabled' => true,
            'ttl' => 3600, // 1 hour
        ],
    ],
];
