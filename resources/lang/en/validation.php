<?php

return [

    //format: '$$keyword$attribute$$'
    //where keyword can be: invalid, required, incorrect, taken, not_confirmed, not_member,
    //and attribute is the invalid attribute's name or 'username_or_password' or 'user' (means the athenticated user)

    //custom error messages: $$unavailable_for_guests$$, $$available_for_guests_only$$

    //other keywords: $$legacy_money$$, $$deleted_user$$

    'array' => '$$invalid:attribute$$',
    'boolean' => '$$invalid:attribute$$',
    'confirmed' => '$$not_confirmed$:attribute$$',
    'digits' => '$$invalid$:attribute$$',
    'digits$between' => '$$invalid$:attribute$$',
    'email' => '$$invalid$:attribute$$',
    'numeric' => '$$invalid$:attribute$$',
    'exists' => '$$invalid$:attribute$$',
    'in' => '$$invalid$:attribute$$',
    'integer' => '$$invalid$:attribute$$',
    'max' => [
        'numeric' => '$$invalid$:attribute$$',
        'string' => '$$invalid$:attribute$$',
    ],
    'min' => [
        'numeric' => '$$invalid$:attribute$$',
        'string' => '$$invalid$:attribute$$',
    ],
    'not_in' => '$$invalid$:attribute',
    'password' => '$$incorrect$password',
    'regex' => '$$invalid$:attribute$$',
    'required' => '$$required$:attribute$$',
    'size' => [
        'numeric' => '$$invalid$:attribute$$',
        'string' => '$$invalid$:attribute$$',
    ],
    'string' => '$$invalid$:attribute$$',
    'unique' => '$$taken$:attribute$$',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
