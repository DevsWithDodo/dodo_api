<?php

return [
    'accepted'             => ':attribute muss akzeptiert werden.',
    'active_url'           => ':attribute ist keine gültige Internet-Adresse.',
    'after'                => ':attribute muss ein Datum nach dem :date sein.',
    'after_or_equal'       => ':attribute muss ein Datum nach dem :date oder gleich dem :date sein.',
    'alpha'                => ':attribute darf nur aus Buchstaben bestehen.',
    'alpha_dash'           => ':attribute darf nur aus Buchstaben, Zahlen, Binde- und Unterstrichen bestehen.',
    'alpha_num'            => ':attribute darf nur aus Buchstaben und Zahlen bestehen.',
    'array'                => ':attribute muss ein Array sein.',
    'before'               => ':attribute muss ein Datum vor dem :date sein.',
    'before_or_equal'      => ':attribute muss ein Datum vor dem :date oder gleich dem :date sein.',
    'between'              => [
        'array'   => ':attribute muss zwischen :min & :max Elemente haben.',
        'file'    => ':attribute muss zwischen :min & :max Kilobytes groß sein.',
        'numeric' => ':attribute muss zwischen :min & :max liegen.',
        'string'  => ':attribute muss zwischen :min & :max Zeichen lang sein.',
    ],
    'boolean'              => ':attribute muss entweder \'true\' oder \'false\' sein.',
    'confirmed'            => ':attribute stimmt nicht mit der Bestätigung überein.',
    'date'                 => ':attribute muss ein gültiges Datum sein.',
    'date_format'          => ':attribute entspricht nicht dem gültigen Format für :format.',
    'different'            => ':attribute und :other müssen sich unterscheiden.',
    'digits'               => ':attribute muss :digits Stellen haben.',
    'digits_between'       => ':attribute muss zwischen :min und :max Stellen haben.',
    'dimensions'           => ':attribute hat ungültige Bildabmessungen.',
    'distinct'             => ':attribute beinhaltet einen bereits vorhandenen Wert.',
    'email'                => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'exists'               => 'Der gewählte Wert für :attribute ist ungültig.',
    'file'                 => ':attribute muss eine Datei sein.',
    'filled'               => ':attribute muss ausgefüllt sein.',
    'image'                => ':attribute muss ein Bild sein.',
    'in'                   => 'Der gewählte Wert für :attribute ist ungültig.',
    'in_array'             => 'Der gewählte Wert für :attribute kommt nicht in :other vor.',
    'integer'              => ':attribute muss eine ganze Zahl sein.',
    'ip'                   => ':attribute muss eine gültige IP-Adresse sein.',
    'ipv4'                 => ':attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6'                 => ':attribute muss eine gültige IPv6-Adresse sein.',
    'json'                 => ':attribute muss ein gültiger JSON-String sein.',
    'max'                  => [
        'array'   => ':attribute darf maximal :max Elemente haben.',
        'file'    => ':attribute darf maximal :max Kilobytes groß sein.',
        'numeric' => ':attribute darf maximal :max sein.',
        'string'  => ':attribute darf maximal :max Zeichen haben.',
    ],
    'mimes'                => ':attribute muss den Dateityp :values haben.',
    'mimetypes'            => ':attribute muss den Dateityp :values haben.',
    'min'                  => [
        'array'   => ':attribute muss mindestens :min Elemente haben.',
        'file'    => ':attribute muss mindestens :min Kilobytes groß sein.',
        'numeric' => ':attribute muss mindestens :min sein.',
        'string'  => ':attribute muss mindestens :min Zeichen lang sein.',
    ],
    'not_in'               => 'Der gewählte Wert für :attribute ist ungültig.',
    'numeric'              => ':attribute muss eine Zahl sein.',
    'password'             => 'Das Passwort ist falsch.',
    'present'              => ':attribute muss vorhanden sein.',
    'regex'                => ':attribute Format ist ungültig.',
    'required'             => ':attribute muss ausgefüllt werden.',
    'required_if'          => ':attribute muss ausgefüllt werden, wenn :other den Wert :value hat.',
    'required_unless'      => ':attribute muss ausgefüllt werden, wenn :other nicht den Wert :values hat.',
    'required_with'        => ':attribute muss ausgefüllt werden, wenn :values ausgefüllt wurde.',
    'required_with_all'    => ':attribute muss ausgefüllt werden, wenn :values ausgefüllt wurde.',
    'required_without'     => ':attribute muss ausgefüllt werden, wenn :values nicht ausgefüllt wurde.',
    'required_without_all' => ':attribute muss ausgefüllt werden, wenn keines der Felder :values ausgefüllt wurde.',
    'same'                 => ':attribute und :other müssen übereinstimmen.',
    'size'                 => [
        'array'   => ':attribute muss genau :size Elemente haben.',
        'file'    => ':attribute muss :size Kilobyte groß sein.',
        'numeric' => ':attribute muss gleich :size sein.',
        'string'  => ':attribute muss :size Zeichen lang sein.',
    ],
    'string'               => ':attribute muss ein String sein.',
    'timezone'             => ':attribute muss eine gültige Zeitzone sein.',
    'unique'               => ':attribute ist bereits vergeben.',
    'uploaded'             => ':attribute konnte nicht hochgeladen werden.',
    'url'                  => ':attribute muss eine URL sein.',

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
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */
    'incorrect_username_or_password' => 'Falscher Benutzername oder falsches Passwort',
    'attributes' => [
        'username' => 'Benutzername',
        'password' => 'Passwort',
        'group_name' => 'Name der Gruppe',
        'currency' => 'Währung',
        'member_nickname' => 'Spitzname',
        'name' => 'Name',
        'invitation_token' => 'Einladung',
        'nickname' => 'Spitzname',
        'group' => 'Gruppe',
        'amount' => 'Betrag',
        'note' => 'Notiz',
        'user_id' => 'Nutzer',
        'reaction' => 'Reaktion',
        'default_currency' => 'Währung',
        'password_reminder' => 'Passwort Erinnerung',
        'language' => 'Sprache',
        'old_password' => 'altes Passwort',
        'new_password' => 'neues Passwort',
        'password_confirmation' => 'Passwortbestätigung',
    ],
];