SHOFEER Stage 5.4A - Passenger Badges

This overlay adds:
- badges + user_badges migrations
- Badge + UserBadge models
- BadgeService
- Passenger BadgeController
- /dashboard/badges route
- Passenger badges Blade page
- BadgeSeeder
- PassengerBadgesTest

After extracting into the project root:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan db:seed --class=BadgeSeeder
5) php artisan route:list --name=dashboard.badges
6) php artisan test tests/Feature/Passenger/PassengerBadgesTest.php
7) php artisan test tests/Feature/Passenger
8) php artisan test tests/Feature/Booking
