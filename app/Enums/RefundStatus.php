<?php

namespace App\Enums;

/*
|--------------------------------------------------------------------------
| Refund Status
|--------------------------------------------------------------------------
|
| pending   = بانتظار مراجعة الإدارة
| processed = تم تنفيذ الاسترداد
| rejected  = تم رفض الطلب
|
*/

enum RefundStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Rejected = 'rejected';
}
