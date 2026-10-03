SHOFEER Stage 5.10C - Admin Drivers

Source-backed requirement:
- /admin/drivers
- 4 tabs
- review

The source does not separately name the 4 tabs, but the Driver schema provides
exactly 4 documented statuses:
- pending
- approved
- rejected
- suspended

Therefore the four tabs map directly to those source-defined statuses.

Important compatibility choice:
The existing Admin\DriverController already provides:
- show
- private document access
- approve
- reject
- suspend

This stage does NOT replace that controller.
It adds DriverReviewController only for the index/list page and changes only
admin.drivers.index to point to it. Existing review actions remain untouched.

List privacy:
- does not display national ID
- does not display license number
- does not display raw phone
- shows only document completeness state

No migration.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=admin.drivers
4) php artisan test tests/Feature/Admin/AdminDriversTabsTest.php
5) run the existing driver admin tests if present
6) php artisan test tests/Feature/Admin

Page:
http://127.0.0.1:8000/admin/drivers
