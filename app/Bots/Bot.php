<?php

namespace App\Bots;

use RTippin\Messenger\Support\BotActionHandler;
use Throwable;

class Bot extends BotActionHandler
{
    /**
     * @inheritDoc
     */
    public static function getSettings(): array
    {
        return [
            'alias' => 'bot_alias',
            'description' => 'The bots description.',
            'name' => 'Bot',
            'unique' => true,
            'match' => 'execut',
            'triggers' => ['bot'],
        ];
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->composer()->message('');
    }
}
