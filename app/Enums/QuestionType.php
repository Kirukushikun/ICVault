<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case FillBlank = 'fill_blank';
    case Code = 'code';
}
