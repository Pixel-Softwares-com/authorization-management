# Overview on AuthorizationManagement :

## Policy :

### Policy creating & registering

- create a new policy using command : php artisan make:policy 
- make the new created policy class inherit App\CustomLibs\AuthorizationManagement\PolicyManagement\Policies\BasePolicy class .
- register the policy by adding it to policy map found in "authorization-management-config" config file 's policies array 
(if the file is not found create a new one and return an array has policies array valued key).
- because of AuthServiceProvider you don't need to register anything again (all policies found in config file will be registered automatically).

### Policy calling :
- use App\CustomLibs\AuthorizationManagement\PolicyManagement\Policies\BasePolicy 's static method check and pass it the arguments (policyAction , ModelClass ) .
Example : we want to check if user has the permission
- 


- The permission name you want to check must exist in permissions database table .
- 

<hr>

## Independent Gates :

### Independent Gates creating & registering

- create a class , And make it inherit App\CustomLibs\AuthorizationManagement\independentGateManagement\IndependentGates\IndependentGate class .
- register the IndependentGate class by adding it to IndependentGates array found in "authorization-management-config" config file 's independent_gates array
  (if the file is not found create a new one and return an array has independent_gates array valued key).
- because of AuthServiceProvider you don't need to register anything again (all IndependentGates found in config file will be defined in runTime automatically).

