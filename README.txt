SHOFEER Driver Routes Prefix Fix

This fixes the remaining Driver test failures caused by missing route names:

- driver.cars.store
- driver.cars.destroy
- driver.register.store
- driver.trips.index
- driver.request-trip
- driver.request-trip.store

Root cause:
routes/driver.php was being loaded directly under the web middleware only.
Its internal route names were therefore registered as cars.store,
register.store, trips.index, request-trip.store, etc.

Fix:
Load the entire legacy file with:
- URL prefix: /driver
- route-name prefix: driver.

This also prevents legacy driver routes from colliding with:
- /
- /register
- /trips
- other public/passenger routes

The Stage 5.9 driver.dashboard route is then registered last to ensure the
new Blade dashboard overrides the old JSON dashboard.

Install:
1) Expand-Archive .\shofeer-driver-routes-prefix-fix.zip -DestinationPath . -Force
2) php artisan optimize:clear
3) php artisan route:list --name=driver.
4) php artisan test tests/Feature/Driver

Expected important route names:
driver.register.store
driver.cars.store
driver.cars.destroy
driver.request-trip
driver.request-trip.store
driver.trips.index
driver.dashboard
driver.earnings.index
driver.live.index
driver.notifications.index
