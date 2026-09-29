SHOFEER Stage 5.6 - Passenger Ratings

Implements:
- ratings table
- Rating model
- RatingPolicy (view, update)
- RatingService with transaction + row locks
- Passenger RatingController
- Store/Update validation requests
- /dashboard/ratings
- create rating for a completed confirmed booking
- update own rating
- driver average rating recalculation
- one rating per booking
- feature tests
- updated routes/web.php preserving packages, rewards-facing passenger routes, booking and payment routes

Conservative rules:
- score is 1..5
- comment is optional, max 1000 chars
- only the booking owner can rate
- booking must be confirmed
- trip departure must be in the past
- rating is unique per booking
- passenger may update their own rating
- no arbitrary edit window is imposed because the specification does not define one

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=dashboard.ratings
5) php artisan test tests/Feature/Passenger/PassengerRatingsTest.php
6) php artisan test tests/Feature/Passenger
7) php artisan test tests/Feature/Booking

Page:
http://127.0.0.1:8000/dashboard/ratings

Note:
This package intentionally does not blindly replace passenger/bookings/index.blade.php because the current full file was not available in the latest source dump. The ratings page itself lists all eligible past bookings and allows rating them safely.
