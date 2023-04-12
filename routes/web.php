<?php

use App\Conversations\BotConversation;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Incoming\Answer;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function () {
    $x = new class
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

        public function generateQuestion(string|null $value)
        {
            if (is_null($value)) {
                return $this->generateButtons(null);
            }

            $questions = collect($this->questions);

            $question = $questions->where('title', $value)->first();
            $buttons = $this->generateButtons($question['id']);
            return $buttons;
        }

        public function generateButtons(int|null $value): array
        {
            $questions = collect($this->questions);
            $questions = $questions->where('parent_id', $value);
            return $questions->map(function ($question) {
                return $question['title'];
            })->toArray();
        }
    };
    $y = new $x;
    dd($y->generateQuestion(null));
});

Route::get('/botman/chat', function () {
    return view("chat");
});

Route::match(['post', 'get'], '/botman', function () {
    $botman = resolve('botman');

    $botman->hears('start', function (BotMan $bot) {
        $bot->startConversation(new BotConversation());
    });

    $botman->hears('hello', function (BotMan $bot) {
        $bot->typesAndWaits(2);
        $bot->reply('Hello yourself.');
    });

    $botman->hears('inline conversation', function (BotMan $bot) {
        $bot->ask('Do you want to continue?', function (Answer $answer, Conversation $conversation) {
            $value = $answer->getText();
            $conversation->say("Your answer is {$value}");
        });
    });

    $botman->fallback(function ($bot) {
        $bot->reply('Sorry, I did not understand these commands. Here is a list of commands I understand: ...');
    });

    $botman->listen();
});
