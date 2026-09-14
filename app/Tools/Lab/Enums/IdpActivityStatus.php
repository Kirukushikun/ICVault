<?php

namespace App\Tools\Lab\Enums;

enum IdpActivityStatus: string
{
    case ToKickoff = 'To Kick-off';
    case InProgress = 'In Progress';
    case Completed = 'Completed';
    case OnHold = 'On Hold';
}
