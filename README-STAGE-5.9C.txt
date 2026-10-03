SHOFEER Stage 5.9C - Driver Passengers

Source-backed requirements:
- /driver/passengers
- first name only
- seat number
- WhatsApp for only 30 minutes
- Contact Admin button
- driver must not see passenger identity
- driver must not see passenger full price or commission
- no direct contact outside emergency context

Important interpretation:
The source says "WhatsApp (last 30 minutes only)" but does not define the exact
start/end semantics. This implementation uses the most privacy-preserving
literal interpretation:
- WhatsApp is visible only from 30 minutes before departure until departure.
- Before that it is hidden.
- After departure it is hidden.

The UI labels WhatsApp as emergency-only.

Contact Admin:
- no admin phone is exposed
- the button creates an in-app notification for active admins
- the notification contains booking code + seat only
- it does not include passenger full name, WhatsApp or identity

Privacy:
The passengers page intentionally queries only:
- booking id
- booking code
- passenger_name (reduced to first name in the response)
- passenger_whatsapp (used only inside the 30-minute gate)
- seat_number
- status

It does not query/render passenger phone, passenger identity, price,
paid amount or commission.

No migration.

Install:
1) php artisan optimize:clear
2) composer dump-autoload
3) php artisan route:list --name=driver.passengers
4) php artisan test tests/Feature/Driver/DriverPassengersTest.php
5) php artisan test tests/Feature/Driver

Page:
http://127.0.0.1:8000/driver/passengers
