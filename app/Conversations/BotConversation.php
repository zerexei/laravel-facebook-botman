<?php

namespace App\Conversations;

use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;

class BotConversation extends Conversation
{
    public function run()
    {
        try {
            $this->start();
            // improve developer experience 10000000%
        } catch (\Exception $ex) {
            $this->say(json_encode($ex));
        }
    }

    public function start()
    {
        $question = $this->generateQuestion();

        $this->ask($question, function (Answer $answer) {
            // when user typed the answer
            if (!$answer->isInteractiveMessageReply()) {
                $this->bot->reply('stop');
            }

            $value = $answer->getValue() ?? $answer->getText();
            $this->say("you said {$value}");
        });
    }

    protected function generateQuestion()
    {
        return Question::create('Do you need any help?')
            ->fallback('Unable to help')
            ->addButtons([]);
    }

    protected function generateButton()
    {
        return [
            Button::create('Of course')->value('yes'),
            Button::create('Hell no!')->value('no'),
        ];
    }
}
