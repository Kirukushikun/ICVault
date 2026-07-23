<?php

namespace App\Services;

use App\Enums\MasteryState;
use App\Models\Question;

class MasteryService
{
    /**
     * Consecutive correct answers needed, at each state, to advance to the next one.
     */
    private const array ADVANCE_THRESHOLD = [
        'new' => 1,
        'learning' => 2,
        'review' => 3,
    ];

    private const array NEXT_STATE = [
        'new' => MasteryState::Learning,
        'learning' => MasteryState::Review,
        'review' => MasteryState::Mastered,
    ];

    /**
     * A wrong answer drops the question back one state. `new` has nowhere to drop to.
     */
    private const array PREVIOUS_STATE = [
        'mastered' => MasteryState::Review,
        'review' => MasteryState::Learning,
        'learning' => MasteryState::New,
        'new' => MasteryState::New,
    ];

    public function recordAttempt(Question $question, bool $correct): Question
    {
        $correct ? $this->applyCorrect($question) : $this->applyIncorrect($question);

        $question->last_reviewed_at = now();
        $question->save();

        return $question;
    }

    private function applyCorrect(Question $question): void
    {
        $state = $question->mastery_state->value;

        if ($state === MasteryState::Mastered->value) {
            $question->mastery_streak++;

            return;
        }

        $streak = $question->mastery_streak + 1;

        if ($streak >= self::ADVANCE_THRESHOLD[$state]) {
            $question->mastery_state = self::NEXT_STATE[$state];
            $question->mastery_streak = 0;
        } else {
            $question->mastery_streak = $streak;
        }
    }

    private function applyIncorrect(Question $question): void
    {
        $question->mastery_state = self::PREVIOUS_STATE[$question->mastery_state->value];
        $question->mastery_streak = 0;
    }
}
