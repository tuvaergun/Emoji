<?php

/*
 * This file is part of Alt Three Emoji.
 *
 * (c) Alt Three Services Limited
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AltThree\Emoji;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use League\CommonMark\Environment;

/**
 * This is the emoji service provider class.
 *
 * @author Graham Campbell <graham@alt-three.com>
 */
class EmojiServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerEmojiParser();
        $this->registerEnvironment();
    }

    /**
     * Register the emoji parser class.
     *
     * @return void
     */
    protected function registerEmojiParser()
    {
        $app = $this->app;

        $app->singleton('emoji', function ($app) {
            $map = $app->cache->remember('emoji', 10080, function () {
                return json_decode((new Client())->get('https://api.github.com/emojis')->getBody(), true);
            });

            return new EmojiParser($map);
        });

        $this->app->alias('emoji', EmojiParser::class);
    }

    /**
     * Register the environment.
     *
     * @return void
     */
    protected function registerEnvironment()
    {
        $app = $this->app;

        $app->resolving('markdown.environment', function (Environment $environment) use ($app) {
            $environment->addInlineParser($app['emoji']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'emoji',
        ];
    }
}
