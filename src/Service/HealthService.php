<?php

namespace App\Service;

class HealthService
{
    private $app_env;

    public function __construct($app_env)
    {
        $this->app_env = $app_env;
    }

    public function getAppEnv()
    {
        return $this->app_env;
    }
}
