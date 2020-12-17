<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Greg Gilberts Recaptcha Package
    |--------------------------------------------------------------------------
    |
    | Indicate if you want to use Recaptcha
    |
    | Make sure you have it installed and configured
    | https://github.com/greggilbert/recaptcha

    */
    'use_greggilbert_recaptcha'    => false,

    /*
    |--------------------------------------------------------------------------
    | Session Key Names
    |--------------------------------------------------------------------------
    |
    | Password Protect uses the session to stores routes the user navigates to
    |
    | Below are the default session key names that are used
    | leave them be unless you already uses theses key names elsewhere
    |
    */
    'desired_route_key_name'     => 'pp_desiredroute',
    'root_route_key_name'    => 'pp_rootroute',
];
