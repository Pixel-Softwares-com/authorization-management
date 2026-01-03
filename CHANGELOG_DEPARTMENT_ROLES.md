# Department Roles Refactoring - Changelog

## Summary
تم إعادة هيكلة نظام Department Roles لتحسين المرونة والديناميكية في التعامل مع الأدوار.

## Changes Made

### 1. DepartmentRoleRegistry
#### التغييرات الرئيسية:
- **إلغاء دمج custom_roles**: تم إزالة دمج `custom_roles` مع `default_roles` في method `loadRoles()`
- **الأدوار المتاحة**: الآن يتم تحميل `default_roles` فقط من الإعدادات
- **الإبقاء على المرونة**: تم الإبقاء على إمكانية التعامل مع custom roles عبر parameters في الدوال

#### Methods جديدة:
```php
// Get relation name for a specific role key
DepartmentRoleRegistry::getRelationName(string $roleKey): ?string

// Get relation names for multiple role keys with optional custom roles
DepartmentRoleRegistry::getRelationNamesForKeys(array $roleKeys, array $customRoles = []): array

// Get specific role relations
DepartmentRoleRegistry::getManagerRelation(): ?string
DepartmentRoleRegistry::getEngineerRelation(): ?string
DepartmentRoleRegistry::getRepRelation(): ?string

// Get common role combinations
DepartmentRoleRegistry::getManagerAndEngineerRelations(array $customRoles = []): array
DepartmentRoleRegistry::getAllDefaultRoleRelations(array $customRoles = []): array
```

### 2. BasePolicy
#### التغييرات الرئيسية:
- **إزالة القيم الثابتة**: تم استبدال جميع القيم الثابتة مثل `['managers', 'engineers']` بقيم ديناميكية من `DepartmentRoleRegistry`
- **إزالة custom roles parameters**: تم إزالة `$customRoles` parameter من معظم الدوال
- **دالة واحدة لـ custom roles**: فقط `hasAnyRoleForModel()` تقبل custom roles وتدمجها مع default roles

#### Methods المُحدّثة:
```php
// تستخدم default roles فقط (manager, engineer)
protected function hasManagerOrEngineerRoleForModel(Authenticatable $user, Model $model): bool

// تستخدم manager role فقط
protected function hasManagerRoleForModel(Authenticatable $user, Model $model): bool

// هذه فقط تقبل custom roles وتدمجها مع default roles
protected function hasAnyRoleForModel(Authenticatable $user, Model $model, array $customRoles = []): bool

// تستخدم جميع default roles
protected function hasViewAsPermission(Authenticatable $user, Model $model): bool
```

#### Helper Methods جديدة:
```php
protected function getRoleRelation(string $roleKey): ?string
protected function getManagerRelation(): ?string
protected function getEngineerRelation(): ?string
protected function getRepRelation(): ?string

// تُرجع default roles فقط
protected function getAllDefaultRoles(): array
protected function getManagerAndEngineerRoles(): array

// تدمج custom roles مع default roles
protected function mergeWithDefaultRoles(array $customRoles): array
```

## Benefits / الفوائد

### 1. ديناميكية محسّنة
- لا توجد قيم ثابتة hardcoded في BasePolicy
- يتم الحصول على جميع القيم ديناميكياً من Registry
- سهولة تغيير تسميات الأدوار من ملف الإعدادات فقط

### 2. مرونة أفضل
- دعم custom roles عبر parameters دون الحاجة لتعديل الكود
- يمكن إضافة أدوار إضافية عند الحاجة دون تعديل الملفات الأساسية

### 3. صيانة أسهل
- الكود أكثر قابلية للصيانة والتوسع
- مركزية إدارة الأدوار في Registry واحد
- توثيق أفضل للدوال

## Migration Guide / دليل الترحيل

### للمطورين الذين يستخدمون BasePolicy:

#### قبل التحديث:
```php
// كان يعمل بشكل تلقائي
if ($this->hasManagerOrEngineerRoleForModel($user, $model)) {
    // ...
}
```

#### بعد التحديث:
```php
// ✅ لا يزال يعمل بنفس الطريقة (backward compatible)
if ($this->hasManagerOrEngineerRoleForModel($user, $model)) {
    // يفحص فقط manager و engineer من default roles
}

// ✅ للدوال التي كانت تقبل custom roles
// استخدم hasAnyRoleForModel بدلاً منها
if ($this->hasAnyRoleForModel($user, $model, ['supervisors', 'team_leads'])) {
    // تفحص جميع default roles + custom roles المُمررة
}

// ✅ أو استخدم mergeWithDefaultRoles helper
$roles = $this->mergeWithDefaultRoles(['supervisors', 'team_leads']);
// الآن $roles يحتوي على: ['managers', 'engineers', 'reps', 'supervisors', 'team_leads']
```

### لا حاجة لتعديلات في:
- ✅ الـ Policies الموجودة - backward compatible
- ✅ استخدامات hasAnyRoleForModel - لم يتم تغييرها
- ✅ Traits (HqRoleChecker, BranchRoleChecker) - كانت ديناميكية بالفعل

## Configuration / الإعدادات

### ملف config/authorization-management-config.php:
```php
'department_roles' => [
    'default_roles' => [
        'manager' => [
            'relation' => 'managers',
            'dep_role_value' => 'manager',
            // ...
        ],
        'engineer' => [
            'relation' => 'engineers',
            'dep_role_value' => 'engineer',
            // ...
        ],
        'rep' => [
            'relation' => 'reps',
            'dep_role_value' => 'rep',
            // ...
        ],
    ],
    // custom_roles لم يعد يتم دمجها تلقائياً
    'custom_roles' => [
        // يمكن تعريفها هنا لكن لن يتم تحميلها تلقائياً
        // يجب تمريرها كـ parameter عند الحاجة
    ],
],
```

## Testing / الاختبار

تم التحقق من:
- ✅ عدم وجود أخطاء Linting
- ✅ جميع القيم الثابتة تم استبدالها بقيم ديناميكية
- ✅ backward compatibility مع الكود الموجود
- ✅ دعم custom roles عبر parameters

## Notes / ملاحظات

1. **Custom Roles**: يمكن الآن إضافة custom roles عند الحاجة عبر تمريرها كـ parameter للدوال
2. **Default Roles**: الأدوار الافتراضية (manager, engineer, rep) محملة بشكل تلقائي
3. **Cache**: تم الحفاظ على آلية الـ cache الموجودة في Registry

## Breaking Changes / التغييرات المؤثرة

### ⚠️ إذا كنت تستخدم custom roles parameters:
```php
// ❌ لن يعمل بعد الآن
$this->hasManagerOrEngineerRoleForModel($user, $model, ['supervisors']);
$this->hasManagerRoleForModel($user, $model, ['team_leads']);

// ✅ استخدم hasAnyRoleForModel بدلاً منها
$this->hasAnyRoleForModel($user, $model, ['supervisors', 'team_leads']);
```

### ✅ الكود الذي لم يتأثر:
```php
// ✅ يعمل بدون تغيير
$this->hasManagerOrEngineerRoleForModel($user, $model);
$this->hasManagerRoleForModel($user, $model);
$this->hasViewAsPermission($user, $model);
```

## Summary / الخلاصة

**الفلسفة الجديدة:**
- الدوال المحددة (manager, engineer, etc.) تستخدم **default roles فقط**
- دالة `hasAnyRoleForModel()` **الوحيدة** التي تقبل custom roles وتدمجها
- هذا يجعل الكود أكثر وضوحاً ويمنع الخلط بين الأدوار

## Latest Updates (DepartmentRoleRegistry Cleanup)

### التغييرات على DepartmentRoleRegistry:

#### Methods تم إزالتها:
- ❌ `getCustomRoles()` - تم إزالتها بالكامل
- ❌ `$customRoles` parameter من `getRelationNamesForKeys()`
- ❌ `$customRoles` parameter من `getManagerAndEngineerRelations()`
- ❌ `$customRoles` parameter من `getAllDefaultRoleRelations()`

#### Methods الجديدة/المحدثة:
```php
// تم تبسيطها - تُرجع all() مباشرة
getDefaultRoles(): Collection

// تم إزالة $customRoles parameter
getRelationNamesForKeys(array $roleKeys): array
getManagerAndEngineerRelations(): array
getAllDefaultRoleRelations(): array

// Method جديدة للاستخدام الخاص (في BasePolicy::hasAnyRoleForModel فقط)
mergeCustomRolesWithDefaults(array $customRoleRelations): array
```

### النتيجة النهائية:

**DepartmentRoleRegistry:**
- ✅ لا يتعامل مع custom roles إطلاقاً (باستثناء method واحدة للدمج)
- ✅ يُحمّل default roles فقط من الإعدادات
- ✅ جميع الـ methods تُرجع default roles فقط

**BasePolicy:**
- ✅ الدوال المحددة تستخدم default roles فقط
- ✅ `hasAnyRoleForModel()` الوحيدة التي تقبل custom roles
- ✅ custom roles يتم دمجها عبر `mergeCustomRolesWithDefaults()`

## Latest Update (Default Department Name)

### التعديل الجديد:
تم جعل `BasePolicy::getDefaultDepartmentName()` يعتمد على الإعدادات بدلاً من القيمة الثابتة.

#### التغييرات:

**1. في `config/authorization-management-config.php`:**
```php
'department_roles' => [
    // ... other settings
    
    /*
    | Default Department Name
    | The default department name to use in policies when no specific
    | department is specified. This can be overridden in child policies.
    */
    'default_department_name' => 'Electric',
],
```

**2. في `DepartmentRoleRegistry`:**
```php
/**
 * Get the default department name from configuration
 */
public static function getDefaultDepartmentName(): string
{
    return config('authorization-management-config.department_roles.default_department_name', 'Electric');
}
```

**3. في `BasePolicy`:**
```php
// ❌ قبل: قيمة ثابتة
protected function getDefaultDepartmentName(Model $model): string
{
    return 'Electric';
}

// ✅ بعد: من الإعدادات
protected function getDefaultDepartmentName(Model $model): string
{
    return DepartmentRoleRegistry::getDefaultDepartmentName();
}
```

### الفوائد:
- ✅ يمكن تغيير Default Department من ملف الإعدادات بدون تعديل الكود
- ✅ مركزية الإعدادات في مكان واحد
- ✅ يمكن Override في child policies كما كان سابقاً

## Date
January 3, 2026 (Final Update - v2)

