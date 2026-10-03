SHOFEER Stage 5.7A - Passenger Live Tracking Foundation

Implements:
- driver_locations history table
- DriverLocation model
- LocationService with server-side GPS validation
- configurable speed / accuracy limits
- passenger /dashboard/live page
- passenger-only /dashboard/live/data JSON endpoint
- 15-second UI refresh
- OpenStreetMap iframe when a location exists
- passenger privacy tests
- GPS validation tests

Security / privacy:
- Passenger sees only locations tied to their own confirmed active booking.
- Only Boarding / InProgress trips are considered live.
- Invalid coordinates are rejected.
- Reported speed and implied movement speed use configurable limits.
- Location timestamps too far in the future are rejected.
- Freshness defaults to 5 minutes.

Important:
The SHOFEER source requires LocationService GPS validation and SyncDriverLocationJob realtime,
but does not define a location table or exact GPS thresholds. This stage adds driver_locations
as an implementation layer and makes thresholds configurable.

ETA:
eta_at is optional and displayed only if a trusted source stores it.
No fake ETA is calculated from departure_at.

This package intentionally does NOT overwrite routes/driver.php.
Stage 5.7B should add verified-driver location submission/live driver UI after inspecting
the current driver routes.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=dashboard.live
5) php artisan test tests/Feature/Passenger/PassengerLiveTrackingTest.php
6) php artisan test tests/Feature/Tracking/LocationServiceTest.php
7) php artisan test tests/Feature/Passenger
8) php artisan test tests/Feature/Booking

Page:
http://127.0.0.1:8000/dashboard/live
