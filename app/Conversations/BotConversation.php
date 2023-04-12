<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Incoming\IncomingMessage;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class BotConversation extends Conversation
{
    protected $questions = [
        ["id" => 1, "parent_id" => null, "title" => "Question 1", "response" => "Response 1"],
        ["id" => 2, "parent_id" => null, "title" => "Question 2", "response" => "Response 2"],
        ["id" => 3, "parent_id" => null, "title" => "Question 3", "response" => "Response 3"],
        ["id" => 4, "parent_id" => 1, "title" => "Question 1.1", "response" => "Response 1.1"],
        ["id" => 5, "parent_id" => 1, "title" => "Question 1.2", "response" => "Response 1.2"],
        ["id" => 6, "parent_id" => 2, "title" => "Question 2.1", "response" => "Response 2.1"],
        ["id" => 7, "parent_id" => 2, "title" => "Question 2.2", "response" => "Response 2.2"],
        ["id" => 8, "parent_id" => 3, "title" => "Question 3.1", "response" => "Response 3.1"],
    ];

    // start the current conversation class
    public function run()
    {
        try {
            $this->start();
            // improve developer experience 10000000%
        } catch (\Exception $ex) {
            $this->say(json_encode($ex));
        }
    }

    // stop the current conversation class
    public function stopsConversation(IncomingMessage $message)
    {
        return in_array($message->getText(), ['stop', 'start']);
    }

    public function start()
    {
        $question = $this->generateQuestion(null);

        $this->ask($question, function (Answer $answer) {
            // when user typed the answer
            if (!$answer->isInteractiveMessageReply()) {
                $this->bot->reply('stop');
                return;
            }

            $value = $answer->getValue() ?? $answer->getText();
            $this->followUp($value);
        });
    }

    protected function followUp(string $value)
    {
        $question = $this->generateQuestion($value);

        $this->ask($question, function (Answer $answer) {
            // when user typed the answer
            if (!$answer->isInteractiveMessageReply()) {
                $this->bot->reply('stop');
                return;
            }

            $value = $answer->getValue() ?? $answer->getText();
            $this->followUp($value);
        });
    }

    protected function generateQuestion(string|null $value): Question
    {
        if (is_null($value)) {
            $buttons = $this->generateButtons(null);
            return Question::create('Do you need any help?')
                ->fallback('Unable to help')
                ->addButtons($buttons);
        }

        $questions = collect($this->questions);

        $question = $questions->where('title', $value)->first();

        $buttons = $this->generateButtons($question['id']);
        return Question::create('Do you need any help?')
            ->fallback('Unable to help')
            ->addButtons($buttons);
    }

    protected function generateButtons(int|null $value): array
    {
        $questions = collect($this->questions);
        $questions = $questions->where('parent_id', $value);

        $buttons = $questions->map(function ($question) {
            return Button::create($question['title'])->value($question['title']);
        })->toArray();

        if ($value) {
            array_push($buttons, Button::create('return to menu')->value('start'));
        }

        return $buttons;
    }
}
