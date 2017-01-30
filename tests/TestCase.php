<?php

use App\Services\User;
use App\Services\Service;

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    protected function initDemoUser()
    {
        return User::createOrUpdate('demo', 'Demo Name', 'secret', 'demo@demo.com');
    }

    protected function initService()
    {
        return Service::createOrUpdate(
            'demo',
            [
                'demo.com',
            ]
        );
    }
}
