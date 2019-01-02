<?php

namespace App\Shop\Orders\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\Employees\Employee;
use App\Shop\Employees\Repositories\EmployeeRepository;
use App\Shop\Channels\Channel;
use App\Events\OrderCreateEvent;
use Illuminate\Http\Request;
use App\Mail\sendEmailNotificationToAdminMailable;
use App\Mail\SendOrderToCustomerMailable;
use App\Shop\Orders\Exceptions\OrderInvalidArgumentException;
use App\Shop\Orders\Exceptions\OrderNotFoundException;
use App\Shop\Orders\Order;
use App\Shop\Orders\Repositories\Interfaces\OrderRepositoryInterface;
use App\Shop\Orders\Transformers\OrderTransformable;
use App\Shop\PaymentMethods\PaymentMethod;
use App\Shop\OrderStatuses\OrderStatus;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface {

    use OrderTransformable;

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
     *
     * @param array $params
     * @return Order
     * @throws OrderInvalidArgumentException
     */
    public function createOrder(array $params): Order {
        try {

            if(isset($params['channel']) && !empty($params['channel'])) {
            $customer_ref = substr($params['channel']->name, 0, 4) . md5(uniqid(mt_rand(), true) . microtime(true));
            $blPriority = $params['channel']->has_priority;

            $params['customer_ref'] = $customer_ref;
            $params['is_priority'] = $blPriority;
            $params['channel'] = $params['channel']->id;
            }

            $order = $this->create($params);

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
    public function associateProduct(Product $product, int $quantity = 1, array $data = []) {
        $this->model->products()->attach($product, [
            'quantity' => $quantity,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_description' => $product->description,
            'product_price' => $product->price,
            'product_attribute_id' => isset($data['product_attribute_id']) ? $data['product_attribute_id'] : null,
        ]);

        $product->quantity = ($product->quantity - $quantity);
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
     * 
     * @param Request $request
     * @return Collection
     */
    public function searchOrder(Request $request): Collection {

        $q = Order::query()
                ->join('customers', 'orders.customer_id', '=', 'customers.id')
                ->join('order_product', 'orders.id', '=', 'order_product.order_id')
                ->join('products', 'products.id', '=', 'order_product.product_id')
                ->join('voucher_codes', 'orders.voucher_code', '=', 'voucher_codes.voucher_id');

        if ($request->has('q') && count($request->q)) {
            $q->where('customer_ref', 'like', '%' . $request->q . '%');
        }

        if ($request->has('name') && count($request->name)) {
            $q->where('customers.name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('email') && count($request->email)) {
            $q->where('customers.email', 'like', '%' . $request->email . '%');
        }
        
        if ($request->has('voucher_code') && count($request->voucher_code)) {
            $q->where('voucher_codes.coupon_code', 'like', '%' . $request->voucher_code . '%');
        }
        
        if ($request->has('product_name') && count($request->product_name)) {
            $q->where('products.name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->has('status') && count($request->status)) {
            $q->where('order_status_id', $request->status);
        }

        if ($request->has('channel') && count($request->channel)) {
            $q->where('channel', $request->channel);
        }
        
        $q->groupBy('orders.id');
        $q->orderBy('orders.order_date', 'DESC')->orderBy('is_priority', 'ASC');

        return $q->get();
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
    public function buildOrderDetails(Collection $items) {
        $items->each(function ($item) {

            $productRepo = new ProductRepository(new Product);
            $product = $productRepo->find($item->id);
            if ($item->options->has('product_attribute_id')) {
                $this->associateProduct($product, $item->qty, [
                    'product_attribute_id' => $item->options->product_attribute_id
                ]);
            } else {
                $this->associateProduct($product, $item->qty);
            }
        });

        return true;
    }

    public function cloneOrder(Order $order, Channel $channel) {

        return $this->createOrder([
                    'reference' => md5(uniqid(mt_rand(), true) . microtime(true)),
                    'courier_id' => $order->courier_id,
                    'customer_id' => $order->customer_id,
                    'address_id' => $order->address_id,
                    'order_status_id' => 9,
                    'payment' => $order->payment,
                    'discounts' => $order->discounts,
                    'total_products' => $order->total_products,
                    'total' => $order->total,
                    'total_paid' => $order->total_paid,
                    'total_shipping' => $order->total_shipping,
                    'tax' => $order->tax,
                    'channel' => $channel
        ]);
    }

}
