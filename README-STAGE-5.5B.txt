SHOFEER Stage 5.5B - Admin Package Management

Requires Stage 5.5A first.

Adds:
- PackageService with transaction + row locks
- Admin PackageController
- AssignPackageRequest
- UpdatePackageStatusRequest
- /admin/packages UI
- admin routes for assign/status
- PackageManagementTest

Status transitions:
received -> assigned -> in_transit -> delivered

Rules:
- only approved drivers may be assigned
- assignment is allowed only while received/assigned
- optional trip must belong to the selected driver
- completed/cancelled trips cannot be assigned
- in_transit requires an assigned package
- delivered requires in_transit
- cancelled/delivered packages cannot be moved again
- repeated in_transit/delivered actions are idempotent

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=admin.packages
4) php artisan test tests/Feature/Admin/PackageManagementTest.php
5) php artisan test tests/Feature/Passenger/PackageFlowTest.php
6) php artisan test tests/Feature/Passenger
7) php artisan test tests/Feature/Booking

Page:
http://127.0.0.1:8000/admin/packages
