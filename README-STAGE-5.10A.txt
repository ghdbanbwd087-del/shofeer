SHOFEER Stage 5.10A - Admin Dashboard

Source-backed requirements:
- /admin
- 22 navigation items
- 8 statistic cards
- 4 charts
- latest activity
- alerts

Implementation choices where source is not specific:

8 statistic cards:
1) Users
2) Approved drivers
3) Current/upcoming trips
4) Confirmed bookings
5) Pending payments
6) Total approved payment amount
7) Package requests
8) Average rating

4 charts:
- New users, last 7 days
- New bookings, last 7 days
- Created trips, last 7 days
- Approved payment amount, last 7 days

Alerts:
- Pending driver reviews
- Pending trip requests
- Pending payments
- Pending refunds

Latest activity:
The source requires an AuditLog system, but the current project does not yet
have the AuditLog implementation. This stage therefore creates a temporary
operational activity feed from the latest users, bookings, payments, and trips.
It does NOT claim to be the final append-only audit log.

Navigation:
The source lists admin pages 43 through 63 (21 admin pages) while also saying
the sidebar has 22 links. This stage keeps the 21 listed admin destinations
plus Logout as the 22nd navigation item. Pages not implemented yet are shown
disabled rather than registering fake routes.

No migration.
No external chart library/CDN is used.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=admin.dashboard
4) php artisan test tests/Feature/Admin/AdminDashboardTest.php
5) php artisan test tests/Feature/Admin

Page:
http://127.0.0.1:8000/admin
