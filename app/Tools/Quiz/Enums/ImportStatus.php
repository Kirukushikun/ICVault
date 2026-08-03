<?php

namespace App\Tools\Quiz\Enums;

enum ImportStatus: string
{
    case Uploaded = 'uploaded';
    case Parsed = 'parsed';

    /** Candidates generated (AI or manual) and awaiting human review. */
    case Ready = 'ready';
    case Imported = 'imported';
    case Failed = 'failed';
}
