SHOFEER Stage 5.9B - Driver Badges

Source-backed requirements:
- /driver/badges
- current badge
- next badge
- benefits
- driver badge benefit includes lower commission

Important:
The source does NOT define:
- driver badge names
- trip thresholds
- commission reduction percentage

Therefore this stage does NOT invent those values.

Implementation:
- adds badges.audience = passenger|driver
- all existing badges default to passenger
- passenger BadgeService behavior remains isolated
- driver progress uses completed Trip records belonging to the driver
- earned driver badges are stored in the existing user_badges table
- duplicate award protection remains via unique(user_id, badge_id)
- page explicitly explains lower commission is controlled by admin badge settings
- no commission percentage is invented or applied automatically

Because no source-defined driver badges exist yet, a real database may show the
empty state until driver badges are added from the future admin badges screen.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=driver.badges
5) php artisan test tests/Feature/Driver/DriverBadgesTest.php
6) php artisan test tests/Feature/Passenger/PassengerBadgesTest.php
7) php artisan test tests/Feature/Driver

Page:
http://127.0.0.1:8000/driver/badges
