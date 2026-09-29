SHOFEER Stage 5.4B - Passenger Balance

Adds:
- balances migration
- balance_transactions migration
- Balance model
- BalanceTransaction model
- BalanceService with credit/debit idempotency
- Passenger BalanceController
- /dashboard/balance
- balance page
- PassengerBalanceTest
- updated routes/web.php preserving badges + booking/refund flow

Important:
The project specification names balances and balance_transactions but does not define their columns.
This package uses an explicit ledger design:
- one balance per user
- credit/debit transaction log
- business idempotency_key unique
- before/after snapshots

The "Use Balance" and "Refund" buttons are intentionally non-operational until payment/cash-out business rules are specified.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=dashboard.balance
5) php artisan test tests/Feature/Passenger/PassengerBalanceTest.php
6) php artisan test tests/Feature/Passenger
7) php artisan test tests/Feature/Booking
