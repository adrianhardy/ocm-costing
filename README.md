
## Auto-generated Models
`php artisan code:model` will overwrite the contents of `App\Models\Base` with
models derived from the database structure. Be aware that this process does not 
work with sqlite, so if you're using sqlite to speed up development, you will 
need to switch to mysql, call `php artisan migrate:fresh` and then 
`php artisan code:model`. 

## Connection to OrchardCrownMall API
When managing products, this system will try to call out to the OCM API. By
default, `.env` sets it as `http://localhost:8000` and it will get *hammered*.

##  Quality Tools
`./vendor/bin/phpqa` will run a suite of quality tools on your code.

## Roles and Permissions

### Roles
When running migrations, roles for Super, Admin and Supplier should
be created and set to protected, so they cannot be deleted by the 
user...

To make a new role protected, it's probably better to 
make a new migration than try and crowbar it into the existing one.

### Permissions
Permissions can be created by running `php artisan setup:permissions`.
You can create a new permission by adding its name to the array
and running the command again. 

### Permission Access
When trying to corner off something, if you're trying to do it in a
controller, `$model->hasPermissionTo('example)` should be used...

You can use middleware to block off routes with `role:role`,
`permission:permission` or `role_or_permission:role|permission`.
Restricting by permission is preferred, but you can still use
roles if necessary.