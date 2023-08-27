<?php

namespace App\Enums;

enum TrialStatus: string {
    case TRIAL = 'trial';
    case EXPIRED = 'expired';
    case SEEN = 'seen';
}