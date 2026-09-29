SHOFEER Stage 5.4C - Passenger Points

Adds:
- points ledger migration
- Point model
- PointService
- Passenger PointController
- /dashboard/points
- points page
- PassengerPointsTest
- updated routes/web.php preserving badges, balance, booking and refund routes

Design notes:
- The specification requires a points table and a page showing current points + how to earn/use.
- It does NOT define an earning formula or conversion value.
- Therefore no automatic points are granted yet.
- PointService supports safe earn/spend operations for future reward rules.
- Each operation uses a business idempotency key.
- User row locking serializes concurrent point operations.
- Overspending is blocked.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=dashboard.points
5) php artisan test tests/Feature/Passenger/PassengerPointsTest.php
6) php artisan test tests/Feature/Passenger
7) php artisan test tests/Feature/Booking
