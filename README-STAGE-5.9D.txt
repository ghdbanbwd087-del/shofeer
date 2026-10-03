SHOFEER Stage 5.9D - Driver Profile

Source-backed requirement:
- /driver/profile
- driver data
- change password

Security choices:
- verification identifiers are NOT rendered as raw values
- national ID and license number are shown only as protected/present states
- document storage paths are not exposed
- password change requires current password
- new password requires confirmation
- minimum password length comes from PASSWORD_MIN_LENGTH (default 12),
  matching the source configuration
- remember_token is rotated after password change
- current session is regenerated

This stage intentionally does NOT allow editing identity, license,
verification status or documents because the source does not say the driver
may modify verified identity data from this page.

No migration.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=driver.profile
4) php artisan test tests/Feature/Driver/DriverProfileTest.php
5) php artisan test tests/Feature/Driver

Page:
http://127.0.0.1:8000/driver/profile
