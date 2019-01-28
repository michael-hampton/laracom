<?php

namespace App\RabbitMq;

use PhpAmqpLib\Message\AMQPMessage;
use App\Shop\Orders\SaveImport;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Channels\Channel;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Couriers\Courier;
use App\Shop\Addresses\Repositories\AddressRepository;
use App\Shop\Addresses\Address;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\VoucherCodes\VoucherCode;
use App\Shop\Orders\Order;

/**
 * Description of Receiver
 *
 * @author michael.hampton
 */
class Receiver extends Queue {

    /**
     *
     * @var type 
     */
    private $method;

    /**
     * 
     * @param type $queue
     * @throws Exception
     */
    public function __construct($queue, $method) {
        parent::__construct($queue);
        $this->method = $method;
    }

    /**
     * Process incoming request
     * @return boolean
     */
    public function listen() {
        if (!$this->connect()) {
            return false;
        }
        if (!$this->declareQueue()) {
            return false;
        }
        if (!$this->setPrefetch()) {
            return false;
        }
        if (!$this->consume()) {
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
     * don't dispatch a new message to a worker until it has processed and 
     * acknowledged the previous one. Instead, it will dispatch it to the 
     * next worker that is not still busy.
     * @return boolean
     */
    private function setPrefetch() {
        $this->channel->basic_qos(
                null, #prefetch size - prefetch window size in octets, null meaning "no specific limit"
                1, #prefetch count - prefetch window in terms of whole messages
                null    #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
        );
        return true;
    }

    /**
     * 
     * @return boolean
     */
    private function consume() {
        $this->channel->basic_consume(
                $this->queue, #queue
                '', #consumer tag - Identifier for the consumer, valid within the current channel. just string
                false, #no local - TRUE: the server will not send messages to the connection that published them
                false, #no ack, false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we're done with a task
                false, #exclusive - queues may only be accessed by the current connection
                false, #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
                array($this, 'process') #callback
        );
        while (count($this->channel->callbacks)) {
            //$this->log->addInfo('Waiting for incoming messages');
            $this->channel->wait();
        }
        return true;
    }

    /**
     * process received request
     * @param AMQPMessage $msg
     * @return boolean
     */
    public function process(AMQPMessage $msg) {
        $this->{$this->method}($msg);
        /**
         * If a consumer dies without sending an acknowledgement the AMQP broker 
         * will redeliver it to another consumer or, if none are available at the 
         * time, the broker will wait until at least one consumer is registered 
         * for the same queue before attempting redelivery
         */
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        return true;
    }

    /**
     * Generates invoice's pdf
     * 
     * @return WorkerReceiver
     */
    private function importOrder(AMQPMessage $msg) {

        $arrOrder = json_decode($msg->body, true);

        $objSaveImport = new SaveImport();
        $objSaveImport->saveBulkImport(
                new ChannelRepository(new Channel), new OrderRepository(new Order), new VoucherCodeRepository(new VoucherCode), new CourierRepository(new Courier), new CustomerRepository(new Customer), new AddressRepository(new Address), $arrOrder
        );

        $arrDone[] = $orderId;

        return $this;
    }

    /**
     * Generates invoice's pdf
     * 
     * @return WorkerReceiver
     */
    private function bulkOrderImport(AMQPMessage $msg) {

        $arrOrders = json_decode($msg->body, true);

        $arrDone = [];

        foreach ($arrOrders as $orderId => $arrOrder) {

            $objSaveImport = new SaveImport();
            $objSaveImport->saveBulkImport(
                    new ChannelRepository(new Channel), new OrderRepository(new Order), new VoucherCodeRepository(new VoucherCode), new CourierRepository(new Courier), new CustomerRepository(new Customer), new AddressRepository(new Address), $arrOrder
            );

            $arrDone[] = $orderId;
        }
        
        return $this;
    }

}
