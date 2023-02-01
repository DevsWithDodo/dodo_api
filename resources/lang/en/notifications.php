<?php

return [

    'guest' => "A guest",
    'user' => [
        'nickname_changed' => [
            //name
            'title' => 'Call me :name!',
            'descr' => 'updated your nickname.',
        ],
        'joined' => [
            //group
            'title' => 'New member',
            //user, group
            'descr' => ':user is the newest bird in :group! Be kind!',
        ],
        'approve' => [
            //group
            'title' => "A user wants to join your group",
            //user, group
            'descr' => "Approve or deny :user's request in :group.",
        ],
        'approved' => [
            //group
            'title' => 'You became a member of :Group',
            'descr' => 'An admin approved your request to join.'
        ],
        'promoted_to_admin' => [
            //group
            'title' => 'You bacame an admin!',
            //user, group'
            'descr' => ':user promoted you to be an admin! From now on, you can edit the group\'s details and other members in the group.',
        ],
    ],
    'group' => [
        'name' => [
            'updated' => 'A group\'s name changed',
        ],
        'boosted' => [
            //user, group
            'title' => ':group just got a new boost!',
            //user, group
            'descr' =>  ':user offered one boost for the group. The member limit lifted to 30! Also, you can see the statistics of the group!',
        ],
    ],
    'payment' => [
        'created' => 'New payment',
        'updated' => 'A payment has been updated',
        'deleted' => 'A payment has been revoked',
    ],
    'request' => [
        'created' => 'A new request has been added to your shopping list',
        'fulfilled' => 'Your request hase been fulfilled!',
    ],
    'purchase' => [
        'created' => 'New purchase',
        'updated' => 'A purchase has been updated',
        'deleted' => 'A purchase has been revoked',
    ],
    'shopping' => [
        //user
        'title' => 'Ask something from :user!',
        //user, store, group
        'descr' => 'You have been notified that :user is shopping in :store right now. Write something to :group\'s shopping list if you need something!',
    ],
    'message_from_developers' => 'Message from the developers',
];
