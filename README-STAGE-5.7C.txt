SHOFEER Stage 5.7C - Admin Live Map

Requires:
- Stage 5.7A driver_locations + LocationService
- Stage 5.7B driver live submission

Adds:
- Admin LiveController
- /admin/live page
- /admin/live/data JSON polling endpoint
- comprehensive Leaflet/OpenStreetMap view
- live/stale/missing tracking states
- computed operational alerts
- AdminLiveTrackingTest
- full routes/admin.php preserving drivers, cars, trip requests, trips,
  payments, refunds, packages and rewards

Alerts:
The SHOFEER specification says "/admin/live - comprehensive map + alerts"
but does not define alert categories. This implementation intentionally
uses only two operational alerts:
1) no location has been received
2) latest location is stale

No persistent alerts table is added.

Freshness:
Uses config/tracking.php from Stage 5.7A.
Default fresh window is 300 seconds.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=admin.live
4) php artisan test tests/Feature/Admin/AdminLiveTrackingTest.php
5) php artisan test tests/Feature/Tracking/LocationServiceTest.php
6) php artisan test tests/Feature/Driver/DriverLiveTrackingTest.php
7) php artisan test tests/Feature/Passenger/PassengerLiveTrackingTest.php

Page:
http://127.0.0.1:8000/admin/live
