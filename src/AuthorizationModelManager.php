<?php

namespace AuthorizationManagement;

/**
 * Authorization Model Manager
 * 
 * Manages model class references for authorization system.
 * Can be configured from AppServiceProvider in consuming packages.
 * 
 * Usage in PixelAppPackageServiceProvider:
 *   AuthorizationModelManager::setBranchModel(\App\Models\Branch::class);
 *   AuthorizationModelManager::setDepartmentModel(\App\Models\Department::class);
 *   AuthorizationModelManager::setUserModel(\App\Models\User::class);
 */
class AuthorizationModelManager
{
    protected static array $models = [
        'branch' => null,
        'department' => null,
        'user' => null,
    ];

    protected static array $config = [
        'main_branch_id' => 1,
    ];

    // ================== Model Setters ==================

    /**
     * Set the Branch model class
     */
    public static function setBranchModel(string $modelClass): void
    {
        static::$models['branch'] = $modelClass;
    }

    /**
     * Set the Department model class
     */
    public static function setDepartmentModel(string $modelClass): void
    {
        static::$models['department'] = $modelClass;
    }

    /**
     * Set the User model class
     */
    public static function setUserModel(string $modelClass): void
    {
        static::$models['user'] = $modelClass;
    }

    /**
     * Set multiple models at once
     * 
     * @param array $models ['branch' => BranchClass, 'department' => DeptClass, 'user' => UserClass]
     */
    public static function setModels(array $models): void
    {
        foreach ($models as $key => $modelClass) {
            if (array_key_exists($key, static::$models)) {
                static::$models[$key] = $modelClass;
            }
        }
    }

    // ================== Model Getters ==================

    /**
     * Get the Branch model class
     */
    public static function getBranchModel(): ?string
    {
        return static::$models['branch'];
    }

    /**
     * Get the Department model class
     */
    public static function getDepartmentModel(): ?string
    {
        return static::$models['department'];
    }

    /**
     * Get the User model class
     */
    public static function getUserModel(): ?string
    {
        return static::$models['user'];
    }

    // ================== Config Setters ==================
 

    /**
     * Set the main branch ID (HQ)
     */
    public static function setMainBranchId(int $branchId): void
    {
        static::$config['main_branch_id'] = $branchId;
    }

    // ================== Config Getters ==================

    /**
     * Get the main branch ID
     */
    public static function getMainBranchId(): int
    {
        return static::$config['main_branch_id'];
    }

    // ================== Model Instance Helpers ==================

    /**
     * Get a branch instance by ID
     * 
     * @throws \RuntimeException if branch model not configured
     */
    public static function getBranchById(int $branchId)
    {
        $branchModel = static::getBranchModel();
        if (!$branchModel) {
            throw new \RuntimeException(
                'Branch model not configured. Call AuthorizationModelManager::setBranchModel() in your ServiceProvider.'
            );
        }
        return $branchModel::find($branchId);
    }

    /**
     * Get the main branch (HQ) instance
     */
    public static function getMainBranch()
    {
        return static::getBranchById(static::getMainBranchId());
    }

    /**
     * Get a new query builder for Branch model
     */
    public static function branchQuery()
    {
        $branchModel = static::getBranchModel();
        if (!$branchModel) {
            throw new \RuntimeException(
                'Branch model not configured. Call AuthorizationModelManager::setBranchModel() in your ServiceProvider.'
            );
        }
        return $branchModel::query();
    }

    /**
     * Get a new query builder for Department model
     */
    public static function departmentQuery()
    {
        $departmentModel = static::getDepartmentModel();
        if (!$departmentModel) {
            throw new \RuntimeException(
                'Department model not configured. Call AuthorizationModelManager::setDepartmentModel() in your ServiceProvider.'
            );
        }
        return $departmentModel::query();
    }

    /**
     * Get a new query builder for User model
     */
    public static function userQuery()
    {
        $userModel = static::getUserModel();
        if (!$userModel) {
            throw new \RuntimeException(
                'User model not configured. Call AuthorizationModelManager::setUserModel() in your ServiceProvider.'
            );
        }
        return $userModel::query();
    }

    // ================== Validation ==================

    /**
     * Check if all models are configured
     */
    public static function isConfigured(): bool
    {
        return static::$models['branch'] !== null 
            && static::$models['department'] !== null 
            && static::$models['user'] !== null;
    }

    /**
     * Validate that all required models are configured
     * 
     * @throws \RuntimeException if configuration is incomplete
     */
    public static function validateConfiguration(): void
    {
        if (!static::isConfigured()) {
            $missing = [];
            foreach (static::$models as $key => $value) {
                if ($value === null) {
                    $missing[] = $key;
                }
            }
            throw new \RuntimeException(
                'AuthorizationModelManager is not fully configured. Missing: ' . implode(', ', $missing) . 
                '. Configure these in your AppServiceProvider boot() method.'
            );
        }
    }

    /**
     * Reset all configurations (useful for testing)
     */
    public static function reset(): void
    {
        static::$models = [
            'branch' => null,
            'department' => null,
            'user' => null,
        ];
        static::$config = [
            'main_branch_id' => 1,
        ];
    }
}

