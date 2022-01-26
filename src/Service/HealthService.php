<?php

namespace App\Service;

class HealthService
{
    private $AppEnv;

    public function __construct($health)
    {
        $this->AppEnv = $health;
    }

    public function getAppEnv()
    {
        return $this->AppEnv;
    }
}