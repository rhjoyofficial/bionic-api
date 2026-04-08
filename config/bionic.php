<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Notification Contacts
    |--------------------------------------------------------------------------
    |
    | Phone and email used by NotifyAdminOnNewOrder to alert the shop owner
    | whenever a new order is placed. Leave blank to disable that channel.
    |
    */

    'admin_phone' => env('ADMIN_PHONE', ''),
    'admin_email' => env('ADMIN_EMAIL', ''),

];
