<?php

namespace App\Enums;

enum Status: string
{
    case Pending = 'pending';
    case Declined = 'declined';
    case Accepted = 'accepted';
}
