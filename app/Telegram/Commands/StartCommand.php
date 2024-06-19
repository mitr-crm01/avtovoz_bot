<?php

namespace App\Telegram\Commands;

use App\Models\TelegramUser;
use Exception;
use JsonException;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{
    protected string $name = 'start';

    /**
     * @inheritDoc
     */
    public function handle(): void
    {
        try {
            // Извлечение информации о пользователе из сообщения
            $user = $this->getUpdate()->getMessage()->getFrom();

            // Проверка, существует ли пользователь в базе данных
            $telegramUser = TelegramUser::updateOrCreate(
                ['telegram_id' => $user->getId()],
                [
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName() ?? null,
                    'username' => $user->getUsername() ?? null,
                    'language_code' => $user->getLanguageCode() ?? null,
                    'is_premium' => $user->isPremium() ?? false,
                ]
            );

            // Отправка ответа пользователю
            $this->replyWithMessage([
                'text' => 'Press on a button',
                'reply_markup' => $this->buildKeyboard(),
            ]);
        } catch (Exception $e) {
            // Логирование ошибки
            Log::error('Error handling /start command: ' . $e->getMessage());

            // Отправка сообщения об ошибке пользователю
            $this->replyWithMessage([
                'text' => 'Возникла ошибка. Попробуйте позже!',
            ]);
        }
    }

    /**
     * @throws JsonException
     */
    private function buildKeyboard(): false|string
    {
        return json_encode([
            'inline_keyboard' => [
                [
                    ['text' => 'Test 1', 'callback_data' => 'test_btn 1'],
                    ['text' => 'Test 2', 'callback_data' => 'test_btn 2'],
                    ['text' => 'Test 3', 'callback_data' => 'test_btn 3'],
                ],
                [
                    ['text' => '🎲 Random Number', 'callback_data' => 'random_number']
                ],
                [
                    ['text' => '🎲 Inline Keyboard', 'callback_data' => 'inline_kbd']
                ],
                [
                    ['text' => 'Void', 'callback_data' => 'void']
                ],
            ]
        ], JSON_THROW_ON_ERROR);
    }
}
