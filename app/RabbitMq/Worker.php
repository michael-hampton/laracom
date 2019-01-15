<?php

namespace App\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;

/**
 * Description of Worker
 *
 * @author michael.hampton
 */
class Worker extends Queue {

    /**
     * 
     * @param type $queue
     * @throws Exception
     */
    public function __construct($queue) {

        parent::__construct($queue);
    }

    /**
     * Sends a task to the workers
     * @param type $invoiceNum
     * @return boolean
     */
    public function execute($invoiceNum) {


        if (!$this->connect()) {

            echo 'Unable to connect';
            return false;
        }

        if (!$this->declareQueue()) {

            return false;
        }

        if (!$this->publish($invoiceNum)) {

            return false;
        }

        if (!$this->closeChannel()) {

            return false;
        }

        if (!$this->closeConnection()) {

            return false;
        }

        return true;
    }

    /**
     * 
     * @param type $invoiceNum
     * @return boolean
     */
    private function publish($invoiceNum) {
        $msg = new AMQPMessage(
                $invoiceNum, array('delivery_mode' => 2) # make message persistent, so it is not lost if server crashes or quits
        );

        $this->channel->basic_publish(
                $msg, #message 
                '', #exchange
                $this->queue     #routing key (queue)
        );

        return true;
    }

}
