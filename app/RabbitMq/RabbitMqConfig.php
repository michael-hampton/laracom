<?php

namespace App\RabbitMq;

class RabbitMqConfig {

    public function getConfig() {
        return array(
            'username' => 'guest',
            'password' => 'guest',
            'host' => 'localhost',
            'port' => 15672
        );
    }

}
