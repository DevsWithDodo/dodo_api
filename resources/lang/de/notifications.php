<?php

return [

    'guest' => "Ein Gast",
    'user' => [
        'nickname_changed' => [
            //name
            'title' => 'Neustens heiße ich :name!',
            'descr' => 'hat deinen Spitznamen geändert.',
        ],
        'joined' => [
            //group
            'title' => 'Neues Mitglied',
            //user, group
            'descr' => ':user ist der neuste Vogel in der Gruppe :group! Seid freundlich!',
        ],
        'approve' => [
            //group
            'title' => "Ein Nutzer möchte der Gruppe beitreten",
            //user, group
            'descr' => "Genehmige oder lehne die Anfrage von :user ab.",
        ],
        'approved' => [
            //group
            'title' => 'Du bist das neuste Mitglied von :Group',
            'descr' => 'Ein Admin hat deine Anfrage genehmigt.'
        ],
        'promoted_to_admin' => [
            //group
            'title' => 'Aus großer Kraft folgt große Verantwortung!',
            //user, group'
            'descr' => ':user hat dich zu einem Admin von :Group gemacht! Du kannst die Gruppe, die Mitglider und die Gäste verwalten.',
        ],
    ],
    'group' => [
        'name' => [
            'updated' => 'Eine deiner Gruppen hat einen neuen Namen',
        ],
        'boosted' => [
            //user, group
            'title' => ':group wurder geboostet!',
            //user, group
            'descr' =>  ':user hat die Gruppe geboostet. Zukünftig können bis zu 30 Leute der Gruppe beitreten, und ihr könnt euch auch die Statistiken ansehen!',
        ],
    ],
    'payment' => [
        'created' => 'Neue Zahlung',
        'updated' => 'Eine Zahlung wurde geändert',
        'deleted' => 'Eine Zahlung wurde zurückgerufen',
    ],
    'request' => [
        'created' => 'Ein neues Element wurde zur Einkaufsliste hinzugefügt',
        'fulfilled' => 'Dein Wunsch wurde erhört',
    ],
    'purchase' => [
        'created' => 'Neuer Einkauf',
        'updated' => 'Ein Einkauf wurde geändert',
        'deleted' => 'Ein Einkauf wurde zurückgerufen',
    ],
    'shopping' => [
        //user
        'title' => 'Wünsch dir was von :user!',
        //user, store, group
        'descr' => ':user kauft gerade i :store ein. Schreib etwas auf den Einkaufszettel von :group wenn du was brauchst!',
    ],
    'message_from_developers' => 'Nachricht von den Entwicklern',
];
