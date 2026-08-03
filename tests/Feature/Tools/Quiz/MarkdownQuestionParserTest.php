<?php

namespace Tests\Feature\Tools\Quiz;

use App\Tools\Quiz\Services\MarkdownQuestionParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MarkdownQuestionParserTest extends TestCase
{
    public function test_lettered_options_make_a_multiple_choice_question(): void
    {
        $result = (new MarkdownQuestionParser)->parse(<<<'MD'
            Q: What does PSR-4 define?
            a) Autoloading standard
            b) Coding style guide
            c) Testing framework
            d) Deployment process
            Answer: a
            Explanation: PSR-4 maps namespaces to file paths.
            Difficulty: easy
            MD);

        $this->assertSame([[
            'difficulty' => 'easy',
            'type' => 'multiple_choice',
            'prompt' => 'What does PSR-4 define?',
            'options_json' => ['Autoloading standard', 'Coding style guide', 'Testing framework', 'Deployment process'],
            'answer' => 'Autoloading standard',
            'explanation' => 'PSR-4 maps namespaces to file paths.',
        ]], $result);
    }

    public function test_a_fenced_block_with_no_options_makes_a_code_question(): void
    {
        $result = (new MarkdownQuestionParser)->parse(<<<'MD'
            Q: Write a function that reverses a string.
            ```php
            function reverse(string $s): string {
                return strrev($s);
            }
            ```
            MD);

        $this->assertSame('code', $result[0]['type']);
        $this->assertSame('medium', $result[0]['difficulty']);
        $this->assertNull($result[0]['options_json']);
        $this->assertStringContainsString('return strrev($s);', $result[0]['answer']);
    }

    public function test_a_plain_answer_line_with_no_fence_or_options_makes_a_fill_blank_question(): void
    {
        $result = (new MarkdownQuestionParser)->parse(
            "Q: The ____ directive escapes output in Blade.\nAnswer: {{ }}"
        );

        $this->assertSame('fill_blank', $result[0]['type']);
        $this->assertSame('{{ }}', $result[0]['answer']);
        $this->assertNull($result[0]['options_json']);
    }

    public function test_blank_lines_and_dash_separators_between_questions_are_ignored(): void
    {
        $result = (new MarkdownQuestionParser)->parse(<<<'MD'
            Q: first
            Answer: one

            ---

            Q: second
            Answer: two
            MD);

        $this->assertCount(2, $result);
        $this->assertSame('one', $result[0]['answer']);
        $this->assertSame('two', $result[1]['answer']);
    }

    public function test_option_delimiters_accept_dot_or_paren_case_insensitively(): void
    {
        $result = (new MarkdownQuestionParser)->parse(<<<'MD'
            Q: pick one
            A. first
            B) second
            Answer: b
            MD);

        $this->assertSame('second', $result[0]['answer']);
    }

    public function test_a_q_line_inside_a_fenced_block_does_not_start_a_new_question(): void
    {
        $result = (new MarkdownQuestionParser)->parse(<<<'MD'
            Q: fence containing a Q-looking line
            ```
            // Q: this is not a real question
            echo "ok";
            ```
            MD);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('Q: this is not a real question', $result[0]['answer']);
    }

    public function test_an_mc_answer_letter_with_no_matching_option_is_surfaced_not_dropped(): void
    {
        $result = (new MarkdownQuestionParser)->parse(<<<'MD'
            Q: broken answer letter
            a) one
            b) two
            Answer: z
            MD);

        $this->assertCount(1, $result);
        $this->assertSame('multiple_choice', $result[0]['type']);
        $this->assertSame('z', $result[0]['answer']);
        $this->assertSame(['one', 'two'], $result[0]['options_json']);
    }

    public function test_a_question_with_no_answer_at_all_is_dropped(): void
    {
        $this->expectException(RuntimeException::class);

        (new MarkdownQuestionParser)->parse("Q: a question with nothing after it\nJust prose, no Answer: line.");
    }

    public function test_no_q_lines_anywhere_throws(): void
    {
        $this->expectException(RuntimeException::class);

        (new MarkdownQuestionParser)->parse('Just some notes, no questions here.');
    }

    public function test_a_mixed_file_produces_all_three_types_in_order(): void
    {
        $result = (new MarkdownQuestionParser)->parse(<<<'MD'
            Q: mc one
            a) x
            b) y
            Answer: a

            Q: fill one
            Answer: literal answer

            Q: code one
            ```
            return true;
            ```
            MD);

        $this->assertSame(['multiple_choice', 'fill_blank', 'code'], array_column($result, 'type'));
    }
}
