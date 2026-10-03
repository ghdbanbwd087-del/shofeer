SHOFEER Stage 5.8A - Notifications Core + Driver + Admin

Source-backed requirements:
- Passenger dashboard: latest notifications
- Driver /driver/notifications: list
- Admin /admin/notifications: individual / broadcast sending
- NotificationService
- notifications table

This stage implements the independent core plus Driver/Admin surfaces.
Passenger dashboard integration is intentionally deferred to Stage 5.8B
to avoid blindly overwriting the current DashboardController and dashboard Blade.

Implements:
- standard Laravel-style database notifications table
- AppNotification model
- NotificationService
- admin individual notification
- admin broadcast to all active users except sender
- driver notification list
- unread count
- mark one as read
- mark all as read
- ownership protection
- feature tests
- updated routes/web.php based on Stage 5.7B
- updated routes/admin.php based on Stage 5.7C

Important design limits:
- In-app database notifications only in this stage.
- WhatsApp and Email are NOT automatically coupled to every notification because
  the source lists those as separate jobs/services and does not prescribe that behavior.
- Broadcast means all active users except the sending admin.
- No invented notification categories or priorities.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=driver.notifications
5) php artisan route:list --name=admin.notifications
6) php artisan test tests/Feature/Driver/DriverNotificationTest.php
7) php artisan test tests/Feature/Admin/AdminNotificationTest.php

Pages:
- http://127.0.0.1:8000/driver/notifications
- http://127.0.0.1:8000/admin/notifications

Next:
Stage 5.8B integrates latest notifications into the passenger dashboard
after inspecting the current full DashboardController and dashboard view.
