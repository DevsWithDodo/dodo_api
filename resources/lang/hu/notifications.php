<?php

return [
    'guest' => "Egy vendég",
    'user' => [
        'nickname_changed' => [
            //name
            'title' => ':name lettél!',
            'descr' => 'átállította a becenevedet.',
        ],
        'joined' => [
            //group
            'title' => ':Group új taggal bővült',
            //user, group
            'descr' => ':user a legújabb madár a rajban, legyetek hozzá kedvesek!',
        ],
        'approve' => [
            //group
            'title' => "Egy felhasználó csatlakozni szeretne egy csoportodhoz",
            //user, group
            'descr' => "Döntsd el, hogy :user tagja lehet-e a(z) :group csoportnak",
        ],
        'approved' => [
            //group
            'title' => ':Group tagja lettél',
            'descr' => 'Egy admin elfogadta a jelentkezési kérelmedet.'
        ],
        'promoted_to_admin' => [
            //group
            'title' => 'Előléptettek!',
            //user, group
            'descr' => ':user adminná tett! Mostantól tudod szerkeszteni :group különböző adatait.',
        ],
    ],
    'group' => [
        'name' => [
            'updated' => 'Egy csoportod neve megváltozott',
        ],
        'boosted' => [
            //user, group
            'title' => ':Group fel lett fejlesztve',
            //user, group
            'descr' => ':user felhasználta egy csoportfejlesztő Dodóját. Mostantól a csoport létszáma akár 30 is lehet, és használhatjátok a statisztikákat is! ',
        ],
    ],
    'payment' => [
        'created' => 'Új fizetés!',
        'updated' => 'Az egyik fizetésedet módosították',
        'deleted' => 'Az egyik fizetésedet törölték',
    ],
    'request' => [
        'created' => 'Új kérés érkezett a bevásárlólistára!',
        'fulfilled' => 'A kérésedet teljesítették',
    ],
    'purchase' => [
        'created' => 'Új vásárlás!',
        'updated' => 'Egy vásárlást módosítottak',
        'deleted' => 'Egy vásárlást töröltek',
    ],
    'shopping' => [
        //user
        'title' => ':user boltban van',
        //user, store, group
        'descr' => 'Ha szeretnél valamit innen: :store,  akkor írj :group bevásárlólistájára!',
    ],
    'message_from_developers' => 'Üzenet a fejlesztőktől',
];
