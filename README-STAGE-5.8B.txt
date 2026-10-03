SHOFEER Stage 5.8B - Passenger Dashboard Notifications

Requires Stage 5.8A first.

Source-backed requirement:
Passenger /dashboard includes:
- 5 statistic cards
- upcoming bookings
- latest notifications

This overlay:
- preserves the existing DashboardController booking/statistics/refund behavior
- injects NotificationService
- loads only the authenticated passenger's latest 5 notifications
- exposes unread notification count
- replaces passenger dashboard view with a complete safe Blade view
- adds tests for display, isolation, unread count and 5-item limit

No migration.
No new route.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan test tests/Feature/Passenger/PassengerDashboardNotificationTest.php
4) php artisan test tests/Feature/Passenger/PassengerDashboardTest.php
5) php artisan test tests/Feature/Passenger
6) php artisan test tests/Feature/Booking

Page:
http://127.0.0.1:8000/dashboard
