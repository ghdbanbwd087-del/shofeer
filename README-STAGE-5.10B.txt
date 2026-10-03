SHOFEER Stage 5.10B - Admin Users

Source-backed requirement:
- /admin/users
- table
- filtering
- actions

Because the source does not specify the exact filters or actions, this stage
uses only fields already present in the User schema.

Filters:
- name/email search
- role: passenger / driver / admin
- account status: active / inactive

Action:
- activate/deactivate account using users.is_active

Not implemented in this stage:
- deleting users
- changing user role
- exposing raw phone/PII

Safety:
- current admin cannot deactivate its own account
- the UI does not expose raw encrypted phone data
- disabling the final usable admin is prevented by self-deactivation guard;
  the controller also contains a last-active-admin check when disabling
  another active admin

Routes:
GET   /admin/users
PATCH /admin/users/{user}/status

No migration.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=admin.users
4) php artisan test tests/Feature/Admin/AdminUsersTest.php
5) php artisan test tests/Feature/Admin

Page:
http://127.0.0.1:8000/admin/users
