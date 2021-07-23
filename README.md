
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
