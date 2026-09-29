SHOFEER Stage 5.4D - Financial & Monthly Rewards

This stage adds:
- financial_rewards table: admin-defined reward rules
- monthly_rewards table: immutable monthly award records
- FinancialReward model
- MonthlyReward model
- RewardService
- Admin RewardController and validation requests
- /admin/rewards management page
- monthly runner
- tests
- updated routes/admin.php preserving existing driver, trip, payment and refund routes

Important design choice:
The project specification names financial_rewards and monthly_rewards but does not define their columns,
amounts, thresholds, or exact winner rules. Therefore this stage DOES NOT seed invented monetary values.
Administrators explicitly create each rule with:
- code
- name
- amount
- minimum completed trips in that month
- active/inactive state
- display order

Safety:
- monthly award unique per user + reward + month
- business idempotency key unique
- BalanceService credit also has an independent idempotency key
- user and reward rows are locked during award
- reward amount is snapshotted in monthly_rewards
- rerunning a month does not double-credit balances

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan migrate
4) php artisan route:list --name=admin.rewards
5) php artisan test tests/Feature/Admin/FinancialRewardTest.php
6) php artisan test tests/Feature/Passenger
7) php artisan test tests/Feature/Booking

Admin page:
http://127.0.0.1:8000/admin/rewards
