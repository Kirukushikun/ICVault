<?php

namespace App\Enums;

enum ImportStatus: string
{
    case Uploaded = 'uploaded';
    case Parsed = 'parsed';
    case AiConverted = 'ai_converted';
    case Queued = 'queued';
    case Imported = 'imported';
    case Failed = 'failed';
}
