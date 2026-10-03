SHOFEER Stage 5.9A - Driver Dashboard + Earnings

Source-backed requirements:
- /driver: statistics for trips, passengers, rating, earnings
- /driver/earnings: net earnings + trip table
- driver must NOT see total trip price or commission

Implementation choice because the source does not prescribe the accounting formula:
- earnings are counted only from Completed trips
- only Confirmed bookings are included
- booking net = max(0, paid_amount - commission)
- commission and gross amounts are never rendered on the driver earnings page

Adds:
- Driver DashboardController
- /driver dashboard view
- /driver/earnings view
- routes inside the existing protected driver group
- privacy/accounting feature tests

No migration.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=driver.dashboard
4) php artisan route:list --name=driver.earnings
5) php artisan test tests/Feature/Driver/DriverDashboardEarningsTest.php
6) php artisan test tests/Feature/Driver

Pages:
http://127.0.0.1:8000/driver
http://127.0.0.1:8000/driver/earnings
