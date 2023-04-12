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
