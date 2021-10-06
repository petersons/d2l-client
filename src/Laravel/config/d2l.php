<?php

declare(strict_types=1);

return [
    'lms_user_id' => env('LMS_USER_ID'),
    'lms_user_key' => env('LMS_USER_KEY'),
    'app_id' => env('D2L_APP_ID'),
    'app_key' => env('D2L_APP_KEY'),
    'host' => env('D2L_HOST'),
    'org_id' => env('D2L_ORG_ID'),
    'installation_code' => env('D2L_INSTALLATION_CODE'),
    'p_key' => env('D2L_PKEY'),
    'guid_login_uri' => env('D2L_GUID_LOGIN_URI', '/d2l/lp/auth/login/ssoLogin.d2l'),
    'api_lp_version' => env('D2L_API_LP_VERSION'),
    'api_le_version' => env('D2L_API_LE_VERSION'),
];
