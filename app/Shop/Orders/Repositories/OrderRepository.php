<?php

namespace App\Shop\Orders\Repositories;

use Gloudemans\Shoppingcart\Facades\Cart;
use App\Shop\Base\BaseRepository;
use App\Shop\Employees\Employee;
use App\Shop\Employees\Repositories\EmployeeRepository;
use App\Shop\Channels\Channel;
use App\Events\OrderCreateEvent;
use Illuminate\Http\Request;
use App\Mail\sendEmailNotificationToAdminMailable;
use App\Mail\SendOrderToCustomerMailable;
use App\Mail\SendRefundToCustomerMailable;
use App\Shop\Orders\Exceptions\OrderInvalidArgumentException;
use App\Shop\Orders\Exceptions\OrderNotFoundException;
use App\Shop\Orders\Order;
use Validator;
use App\Shop\Comments\OrderCommentRepository;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\Addresses\Repositories\Interfaces\AddressRepositoryInterface;
use App\Shop\Customers\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\VoucherCodes\Repositories\Interfaces\VoucherCodeRepositoryInterface;
use App\Shop\Orders\Transformers\OrderTransformable;
use App\Shop\PaymentMethods\PaymentMethod;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use App\Traits\MyTrait;
use App\Shop\Orders\Requests\NewOrderRequest;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface {

    use OrderTransformable;
    use MyTrait;

    protected $validationFailures = [];

    /**
     *
     * @var type 
     */
    private $allocate_on_order = false;

    /**
     * OrderRepository constructor.
     * @param Order $order
     */
    public function __construct(Order $order) {
        parent::__construct($order);
        $this->model = $order;
    }

    /**
     * Create the order
     * @param array $params
     * @param VoucherCodeRepositoryInterface $voucherCodeRepository
     * @param CourierRepositoryInterface $courierRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param AddressRepositoryInterface $addressRepository
     * @param bool $blManualOrder
     * @return Order
     * @throws OrderInvalidArgumentException
     */
    public function createOrder(array $params, VoucherCodeRepositoryInterface $voucherCodeRepository, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository, bool $blManualOrder = false): Order {
        try {

            $this->validationFailures = [];

            $blFieldsValid = Validator::make(
                            $params, (new NewOrderRequest())->rules()
                    )->errors();

            if ($blFieldsValid->any())
            {
                throw new \Exception('invalid fields found');
            }

            if (isset($params['channel']) && !empty($params['channel']))
            {

                $customer_ref = substr($params['channel']->name, 0, 4) . md5(uniqid(mt_rand(), true) . microtime(true));

                $this->validateCustomerRef($customer_ref);
                $blPriority = isset($params['delivery_method']) && $params['delivery_method']->is_priority == 1 ? 1 : 0;

                $params['customer_ref'] = $customer_ref;
                $params['is_priority'] = $blPriority;
                $params['channel'] = $params['channel']->id;
            }

            if ($blManualOrder === false)
            {
                $items = Cart::content();

                $this->validateTotal($params, $items);
            }

            if (isset($params['voucher_code']) && !empty($params['voucher_code']))
            {

                $this->validateVoucherCode($voucherCodeRepository, $params['voucher_code']);
            }

            $this->validateCustomer($customerRepository, $params['customer_id']);
            $this->validateAddress($addressRepository, $params['address_id']);
            $this->validateCourier($courierRepository, $params['courier_id']);

            if (count($this->validationFailures) > 0)
            {
                $params['order_status_id'] = 13;
            }

            $order = $this->create($params);

            if (count($this->validationFailures) > 0)
            {

                $strMessage = implode('<br>', $this->validationFailures);
                //create comment
                $data = [
                    'content' => $strMessage,
                    'user_id' => auth()->guard('admin')->user()->id
                ];

                $postRepo = new OrderCommentRepository($order);
                $postRepo->createComment($data);
            }


            event(new OrderCreateEvent($order));

            return $order;
        } catch (QueryException $e) {
            throw new OrderInvalidArgumentException($e->getMessage(), 500, $e);
        }
    }

    /**
     * @param array $params
     * @return Order
     * @throws OrderInvalidArgumentException
     */
    public function updateOrder(array $params): Order {
        try {
            $this->update($params, $this->model->id);
            return $this->find($this->model->id);
        } catch (QueryException $e) {
            throw new OrderInvalidArgumentException($e->getMessage());
        }
    }

    /**
     * @param int $id
     * @return Order
     * @throws OrderNotFoundException
     */
    public function findOrderById(int $id): Order {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new OrderNotFoundException($e->getMessage());
        }
    }

    /**
     * Return all the orders
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listOrders(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * @param Order $order
     * @return mixed
     */
    public function findProducts(Order $order) {
        return $order->products;
    }

    /**
     * @param Product $product
     * @param int $quantity
     * @param array $data
     */
    public function associateProduct(Product $product, int $quantity = 1, int $status = 1, array $data = []) {

        $this->model->products()->attach($product, [
            'quantity'             => $quantity,
            'product_name'         => $product->name,
            'product_sku'          => $product->sku,
            'status'               => $status,
            'product_description'  => $product->description,
            'product_price'        => $product->price,
            'product_attribute_id' => isset($data['product_attribute_id']) ? $data['product_attribute_id'] : null,
        ]);

        if ($this->allocate_on_order === true && $status !== 11)
        {
            //$product->quantity = ($product->quantity - $quantity);
        }

        if ($this->allocate_on_order === true && $status !== 11)
        {

            $product->reserved_stock = ($product->reserved_stock + $quantity);
        }

        $product->save();

        return true;
    }

    /**
     * Send email to customer
     */
    public function sendEmailToCustomer() {

        return true;

        Mail::to($this->model->customer)
                ->send(new SendOrderToCustomerMailable($this->findOrderById($this->model->id)));
    }

    /**
     * Send email notification to the admin
     */
    public function sendEmailNotificationToAdmin() {
        $employeeRepo = new EmployeeRepository(new Employee);
        $employee = $employeeRepo->findEmployeeById(1);

        return true;

        Mail::to($employee)
                ->send(new sendEmailNotificationToAdminMailable($this->findOrderById($this->model->id)));
    }

    /**
     * Send email to customer
     */
    public function sendRefundEmailToCustomer() {

        Mail::to($this->model->customer)
                ->send(new SendRefundToCustomerMailable($this->findOrderById($this->model->id)));
    }

    public function sendBackorderEmail() {
        Mail::to($this->model->customer)
                ->send(new SendBackorderToCustomerMailable($this->findOrderById($this->model->id)));
    }

    public function sendHungEmail() {
        Mail::to($this->model->customer)
                ->send(new SendHungMailable($this->findOrderById($this->model->id)));
    }

    /**
     * Send email notification to the admin
     */
    public function sendRefundEmailNotificationToAdmin() {
        $employeeRepo = new EmployeeRepository(new Employee);
        $employee = $employeeRepo->findEmployeeById(1);
        return true;
        Mail::to($employee)
                ->send(new sendEmailNotificationToAdminMailable($this->findOrderById($this->model->id)));
    }

    /**
     * 
     * @param Request $request
     * @return Collection
     */
    public function searchOrder(Request $request): Collection {
        
    }

    /**
     * @return Collection
     */
    public function listOrderedProducts(): Collection {


        return $this->model->products->map(function (Product $product) {


//                    $product->name = $product->pivot->product_name;
//                    $product->sku = $product->pivot->product_sku;
//                    $product->description = $product->pivot->product_description;
//                    $product->price = $product->pivot->product_price;
//                    $product->quantity = $product->pivot->quantity;
//                    $product->product_attribute_id = $product->pivot->product_attribute_id;
                    return $product;
                });
    }

    /**
     * @return Order
     */
    public function transform() {
        return $this->transformOrder($this->model);
    }

    /**
     * @return PaymentMethod
     */
    public function findPaymentMethod(): PaymentMethod {
        return $this->model->paymentMethod;
    }

    /**
     * @param Collection $items
     */
    public function buildOrderDetails(Collection $items, Order $order, Channel $channel = null) {

        $blOrderHung = false;
        $blBackorderAllItems = 0;
        $countBackorderedItems = 0;
        $status = 5;

        foreach ($items as $item)
        {

            $productRepo = new ProductRepository(new Product);
            $product = $productRepo->find($item->id);

            $totalStock = $product->quantity - $product->reserved_stock;
            $status = 14;

            $this->allocate_on_order = $channel->allocate_on_order === 1 ? true : false;

            if (!is_null($channel) && $channel->allocate_on_order === 0)
            {
                $status = 14;
            }
            elseif ($blOrderHung === true || ($totalStock <= 0 &&
                    $blOrderHung === false &&
                    !is_null($channel) &&
                    (int) $channel->backorders_enabled === 0)
            )
            {
                $status = 13;
                $blOrderHung = true;
            }
            elseif ($totalStock <= 0 &&
                    !is_null($channel) &&
                    (int) $channel->backorders_enabled === 1 &&
                    $channel->partial_shipment === 0
            )
            {
                $status = 11;
                $blBackorderAllItems++;
                $countBackorderedItems++;
            }
            elseif ($totalStock <= 0 &&
                    !is_null($channel) &&
                    (int) $channel->backorders_enabled === 1 &&
                    $channel->partial_shipment === 1
            )
            {
                $status = 11;
                $countBackorderedItems++;
            }

            if ($blBackorderAllItems > 0 && !is_null($channel) && $channel->partial_shipment === 0)
            {

                $status = 11;
            }


            if ($item->options->has('product_attribute_id'))
            {
                $this->associateProduct($product, $item->qty, $status, [
                    'product_attribute_id' => $item->options->product_attribute_id
                ]);
            }
            else
            {

                $this->associateProduct($product, $item->qty, $status);
            }
        }

        if (in_array($order->order_status_id, [12, 13]) && !is_null($channel) && $channel->strict_validation === 0)
        {
            
        }
        elseif ($blOrderHung === true && !is_null($channel) && $channel->strict_validation === 1)
        {
            $order->delete();
        }
        elseif ($blOrderHung === true && $order->order_status_id !== 12)
        {
            $order->order_status_id = 13;
            $order->save();
            event(new HungEvent($order));
        }
        elseif ($items->count() == $countBackorderedItems || (

                $items->count() > 1 && $countBackorderedItems > 0 && !is_null($channel) && $channel->partial_shipment === 0))
        {

            $order->order_status_id = 11;
            $order->save();
            event(new BackorderEvent($order));
        }

        return true;
    }

    /**
     * 
     * @param array $items
     * @return boolean
     */
    public function buildOrderLinesForManualOrder(array $items) {

        foreach ($items as $item)
        {

            $productRepo = new ProductRepository(new Product);
            $product = $productRepo->find($item['id']);
            $this->allocate_on_order = false;
            $this->associateProduct($product, $item['quantity'], 14);
        }

        return true;
    }

  /**
   * 
   * @param Order $order
   * @param Channel $channel
   * @param VoucherCodeRepositoryInterface $voucherCodeRepository
   * @param CourierRepositoryInterface $courierRepository
   * @param CustomerRepositoryInterface $customerRepository
   * @param AddressRepositoryInterface $addressRepository
   * @param array $arrParams
   * @return type
   */
    public function cloneOrder(Order $order, Channel $channel, VoucherCodeRepositoryInterface $voucherCodeRepository, CourierRepositoryInterface $courierRepository, CustomerRepositoryInterface $customerRepository, AddressRepositoryInterface $addressRepository, array $arrParams) {
        
        return $this->createOrder([
                    'reference'       => isset($arrParams['order_reference']) ? $arrParams['order_reference'] : md5(uniqid(mt_rand(), true) . microtime(true)),
                    'courier_id'      => $order->courier_id,
                    'customer_id'     => $order->customer_id,
                    'address_id'      => $order->address_id,
                    'order_status_id' => 14,
                    'delivery_method' => isset($arrParams['delivery_method']) ? $arrParams['delivery_method'] : null,
                    'payment'         => 'import',
                    'discounts'       => 0,
                    'total_products'  => isset($arrParams['total_products']) ? $arrParams['total_products'] : $order->total_products,
                    'total'           => isset($arrParams['total']) ? $arrParams['total'] : $order->total,
                    'total_paid'      => isset($arrParams['total_paid']) ? $arrParams['total_paid'] : $order->total_paid,
                    'total_shipping'  => isset($arrParams['total_shipping']) ? $arrParams['total_shipping'] : $order->total_shipping,
                    'tax'             => $order->tax,
                    'channel'         => $channel
                        ], $voucherCodeRepository, $courierRepository, $customerRepository, $addressRepository, true);
    }

    /**
     * Delete the order
     *
     * @param Order $order
     * @return bool
     */
    public function deleteOrder(Order $order): bool {
        return $order->delete();
    }

}
