<?php

namespace App\RabbitMq;

use PhpAmqpLib\Connection\AMQPConnection;

/**
 * Description of Queue
 *
 * @author michael.hampton
 */
class Queue {

    /**
     *
     * @var type 
     */
    protected $config;

    /**
     *
     * @var type 
     */
    protected $connection;

    /**
     *
     * @var type 
     */
    protected $queue;

    /**
     *
     * @var type 
     */
    protected $channel;

    /**
     * 
     * @param type $queue
     * @throws Exception
     */
    public function __construct($queue) {

        require_once('RabbitMqConfig.php');

        $objConfig = new RabbitMqConfig();
        $config = $objConfig->getConfig();

        if (!$config) {
            throw new Exception('Cant get config');
        }

        $this->config = $config;
        $this->queue = $queue;
    }

    /**
     * 
     * @return boolean
     */
    protected function connect() {


        try {
            $this->connection = new AMQPConnection($this->config['host'], 5672, $this->config['username'], $this->config['password']);
        } catch (Exception $ex) {
            echo 'Unable to connect';
            return false;
        }

        return true;
    }

    /**
     * 
     * @return boolean
     */
    protected function declareQueue() {
        
        $this->channel = $this->connection->channel();

        $this->channel->queue_declare(
                $this->queue, #queue - Queue names may be up to 255 bytes of UTF-8 characters
                false, #passive - can use this to check whether an exchange exists without modifying the server state
                true, #durable, make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
                false, #exclusive - used by only one connection and the queue will be deleted when that connection closes
                false               #auto delete - queue is deleted when last consumer unsubscribes
        );

        return true;
    }

    /**
     * close connection
     * @return boolean
     */
    protected function closeConnection() {
        $this->connection->close();

        return true;
    }

    /**
     * close channel
     * @return boolean
     */
    protected function closeChannel() {
        $this->channel->close();

        return true;
    }

}
