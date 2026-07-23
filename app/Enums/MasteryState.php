<?php

namespace App\Enums;

enum MasteryState: string
{
    case New = 'new';
    case Learning = 'learning';
    case Review = 'review';
    case Mastered = 'mastered';
}
