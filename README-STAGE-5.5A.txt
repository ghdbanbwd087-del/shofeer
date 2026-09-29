SHOFEER Stage 5.5A - Packages

Implements:
- packages table
- PackageStatus enum
- Package model
- PackagePolicy (view, update, cancel)
- authenticated passenger create/store
- /dashboard/packages tabs
- package details
- public /track lookup with privacy-safe output
- private local image storage
- feature tests

Specification-backed fields:
- description, category, weight, size, images
- sender name, phone, WhatsApp, city
- recipient name, phone, city
- from, to, requested date
- tracking status: received, assigned, in transit, delivered

Additional conservative design:
- cancelled status added because PackagePolicy explicitly requires cancel
- cancellation is allowed only while status is received (before driver assignment)
- public tracking never exposes sender/recipient names or phone numbers
- driver assignment is intentionally deferred to Stage 5.5B admin/packages

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=packages
5) php artisan route:list --name=dashboard.packages
6) php artisan test tests/Feature/Passenger/PackageFlowTest.php
7) php artisan test tests/Feature/Passenger
8) php artisan test tests/Feature/Booking

Pages:
- /packages/create
- /dashboard/packages
- /track
