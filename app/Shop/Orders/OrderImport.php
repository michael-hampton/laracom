<?php

namespace App\Shop\Orders;

use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Channels\Repositories\ChannelRepository;
use App\Shop\Import\BaseImport;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\VoucherCodes\Repositories\VoucherCodeRepository;
use App\Shop\Vouchers\Repositories\VoucherRepository;
use App\Shop\CourierRates\Repositories\CourierRateRepository;
use App\RabbitMq\Worker;
use App\Traits\OrderImportValidation;
use App\Traits\VoucherValidationScope;

class OrderImport extends BaseImport {

    use OrderImportValidation,
        VoucherValidationScope;

    /**
     *
     * @var type 
     */
    protected $requiredFields = array(
        'order_id',
        'channel',
        'customer',
        'courier',
        'voucher_code',
        'product',
        'quantity',
        'price'
    );

    /**
     *
     * @var type 
     */
    private $arrOrders = [];

    /**
     *
     * @var type 
     */
    private $arrStatuses;

    /**
     *
     * @var type 
     */
    private $courier;

    /**
     *
     * @var type 
     */
    private $channel;

    /**
     *
     * @var type 
     */
    private $deliveryAddress;

    /**
     *
     * @var type 
     */
    private $voucherAmount = 0;

    /**
     *
     * @var type 
     */
    private $arrCouriers = [];

    /**
     *
     * @var type 
     */
    private $arrCustomers = [];

    /**
     *
     * @var type 
     */
    private $arrVouchers = [];

    /**
     *
     * @var type 
     */
    private $arrProducts = [];

    /**
     *
     * @var type 
     */
    private $arrExistingProducts = [];

    /**
     *
     * @var type 
     */
    private $arrChannels = [];
    
    /**
     *
     * @var type 
     */
    private $arrOrderVouchers = [];

    /**
     *
     * @var type 
     */
    private $productRepo;

    /**
     *
     * @var type 
     */
    private $orderTotal = 0;

    /**
     *
     * @var type 
     */
    private $objWorker;

    /**
     *
     * @var type 
     */
    private $objCustomer;

    /**
     *
     * @var type 
     */
    private $objCourierRate;

    /**
     *
     * @var type 
     */
    private $shipping;
    
    /**
     *
     * @var type 
     */
    private $lineCount = 1;
    
    /**
     *
     * @var type 
     */
    private $voucherCodeRepo;
    
    /**
     *
     * @var type 
     */
    private $voucherRepo;

    /**
     * 
     * @param CourierRepository $courierRepo
     * @param OrderStatusRepository $orderStatusRepo
     * @param ChannelRepository $channelRepo
     * @param ProductRepository $productRepo
     * @param CustomerRepository $customerRepo,
     * @param VoucherCodeRepository $voucherCodeRepo
     * @param CourierRateRepository $courierRateRepo
     * * @param VoucherRepository $voucherRepo
     * @param Worker $worker
     */
    public function __construct(
    CourierRepository $courierRepo, OrderStatusRepository $orderStatusRepo, ChannelRepository $channelRepo, ProductRepository $productRepo, CustomerRepository $customerRepo, VoucherCodeRepository $voucherCodeRepo, CourierRateRepository $courierRateRepo, VoucherRepository $voucherRepo, Worker $worker
    ) {
        parent::__construct();
        $this->productRepo = $productRepo;
        $this->arrCouriers = $courierRepo->listCouriers()->keyBy('id');
        $this->arrChannels = $channelRepo->listChannels()->keyBy('id');
        $this->objCustomer = $customerRepo->listCustomers()->keyBy('id');
        $this->arrCustomers = array_change_key_case($this->objCustomer->keyBy('name')->toArray(), CASE_LOWER);
        $this->arrExistingProducts = $productRepo->listProducts()->keyBy('name');
        $this->objCourierRate = $courierRateRepo;
        $this->arrStatuses = $orderStatusRepo->listOrderStatuses()->keyBy('name');
        $this->arrVouchers = $voucherRepo->listVoucher()->keyBy('id');
        $this->voucherCodeRepo = $voucherCodeRepo;
        $this->voucherRepo = $voucherRepo;
        $this->objWorker = $worker;
    }

    /**
     * 
     * @param type $file
     * @return boolean
     */
    private function importCsv($file) {
        $handle = fopen($file, 'r');

        if (!$handle)
        {
            return false;
        }

        //Parse the first row, instantiate all the validators
        $row = $this->parseFirstRow($this->fgetcsv($handle));

        if (!empty($this->arrErrors))
        {
            return false;
        }

        while (($data = $this->fgetcsv($handle)) !== false)
        {

            $this->orderTotal = 0;
            $order = array_map('trim', $this->mapData($data));

            foreach ($order as $key => $params)
            {
                $this->checkRule(['key' => $key, 'value' => $params]);
            }

            $this->validateChannel($order['channel']);
            $this->validateCourier($order['courier']);
            $this->validateCustomer($order['customer']);
            $this->validateCustomerAddress();
            $this->validateProduct($order['product']);
            $this->buildOrderProduct($order);
            $this->setOrderTotal($order);
            $this->validateVoucher($order);
            $this->calculateShippingCost();

            $this->lineCount++;

            if (!empty($this->arrErrors))
            {
                continue;
            }

            $this->buildOrder($order);
        }

        if (!empty($this->arrErrors))
        {
            return false;
        }

        if (!$this->sendToQueue())
        {
            $this->arrErrors[] = 'Failed to add to queue';
            return false;
        }

        fclose($handle);
        return true;
    }

    /**
     * 
     * @param type $data
     * @return type
     */
    private function mapData($data) {
        list(
                $order['order_id'],
                $order['channel'],
                $order['customer'],
                $order['courier'],
                $order['voucher_code'],
                $order['product'],
                $order['quantity'],
                $order['price']
                ) = $data;

        return $order;
    }

    /**
     * 
     * @param type $order
     * @return boolean
     */
    private function setOrderTotal($order) {

        $this->orderTotal += $order['price'];

        if (isset($this->arrOrders[$order['order_id']]['total']) && !empty($this->arrOrders[$order['order_id']]['total']))
        {
            $this->orderTotal += $this->arrOrders[$order['order_id']]['total'];
        }

        return true;
    }

    /**
     * 
     * @param type $order
     * @return boolean
     */
    private function buildOrderProduct($order) {

        if (empty($this->product))
        {

            $this->arrErrors[$this->lineCount]['product'] = 'Invalid product';
            return false;
        }

        $this->arrProducts[$order['order_id']][] = array(
            'product'  => $this->product['name'],
            'id'       => $this->product['id'],
            'quantity' => $order['quantity']
        );

        return true;
    }

    /**
     * Checks a CSV file for validity based on defined policies.
     *
     * Stops on the first violation
     *
     * @access public
     * @param string $file Full path
     * @return boolean
     */
    public function isValid($file) {
        if (!file_exists($file))
        {
            $this->arrErrors[$this->lineCount]['file'] = 'File ' . $file . ' does not exist.';
            return false;
        }
        $this->importCsv($file);
        return empty($this->arrErrors);
    }

    /**
     * 
     * @param type $order
     * @return boolean
     */
    private function buildOrder($order) {

        $os = $this->arrStatuses['Waiting Allocation'];

        $this->arrOrders[$order['order_id']] = [
            'reference'       => md5(uniqid(mt_rand(), true) . microtime(true)),
            'courier_id'      => $this->courier->id,
            'customer_id'     => $this->customer['id'],
            'voucher_code'    => !empty($this->objVoucher) ? $this->objVoucher->id : null,
            'voucher_id'      => !empty($this->objVoucher) ? $this->objVoucher : null,
            'address_id'      => $this->deliveryAddress->id,
            'order_status_id' => $os->id,
            'payment'         => 'import',
            'discounts'       => $this->voucherAmount,
            'total_shipping'  => $this->shippingCost,
            'total_products'  => 0,
            'total'           => $this->orderTotal,
            'total_paid'      => 0,
            'delivery_method' => $this->shipping,
            'channel'         => $this->channel,
            'tax'             => 0,
        ];

        $this->arrOrders[$order['order_id']]['products'] = $this->arrProducts[$order['order_id']];

        return true;
    }

    /**
     * 
     * @return boolean
     */
    private function sendToQueue() {

        $this->objWorker->execute(json_encode($this->arrOrders));

        return true;
    }

}
