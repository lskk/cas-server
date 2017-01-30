<?php
return [
    'lock_timeout'    => env('CAS_LOCK_TIMEOUT', 5000),
    'ticket_expire'   => env('CAS_TICKET_EXPIRE', 300),
    'ticket_len'      => env('CAS_TICKET_LEN', 32),
    'allow_reset_pwd' => env('CAS_ALLOW_RESET_PWD', true),
];