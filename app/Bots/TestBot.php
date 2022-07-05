<?php

namespace App\Bots;

use RTippin\Messenger\Support\BotActionHandler;
use Throwable;

class TestBot extends BotActionHandler
{
    /**
     * @inheritDoc
     */
    public static function getSettings(): array
    {
        return [
            'alias' => 'bot_alias',
            'description' => 'The bots description.',
            'name' => 'Bot Name',
            'unique' => false,
            'match' => null,
            'triggers' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function errorMessages(): array
    {
        return [];
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->composer()->message('Create something magical!');
    }
}
