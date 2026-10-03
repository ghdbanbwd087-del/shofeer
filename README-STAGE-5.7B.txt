SHOFEER Stage 5.7B - Driver Live Tracking

Requires Stage 5.7A first.

Adds:
- Driver LiveController
- StoreDriverLocationRequest
- SyncDriverLocationJob
- /driver/live UI
- /driver/live/location endpoint
- DriverLiveTrackingTest
- updated routes/web.php preserving Stage 5.7A and all passenger routes

How it works:
- Verified driver opens /driver/live
- Browser geolocation is requested only after the driver presses Start
- Location is submitted about every 15 seconds
- The selected trip must belong to the authenticated driver
- Only Boarding or InProgress trips may receive live locations
- SyncDriverLocationJob is dispatched synchronously so the passenger sees the update immediately
- LocationService from Stage 5.7A validates coordinates, accuracy, reported speed and implied speed

Security:
- auth
- role:driver
- verified.driver
- trip ownership validation
- active-trip validation
- CSRF
- throttling
- server-side GPS validation

No new migration is required in Stage 5.7B.
It uses driver_locations from Stage 5.7A.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=driver.live
4) php artisan test tests/Feature/Driver/DriverLiveTrackingTest.php
5) php artisan test tests/Feature/Tracking/LocationServiceTest.php
6) php artisan test tests/Feature/Passenger/PassengerLiveTrackingTest.php

Page:
http://127.0.0.1:8000/driver/live
