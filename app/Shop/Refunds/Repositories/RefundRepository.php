<?php

namespace App\Shop\Refunds\Repositories;

use App\Shop\Refunds\Refund;
use App\Shop\Orders\Order;
use App\Shop\OrderProducts\OrderProduct;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Customer;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\PaymentMethods\Paypal\Repositories\PayPalExpressCheckoutRepository;
use App\Shop\PaymentMethods\Stripe\StripeRepository;
use Illuminate\Http\Request;
use App\Shop\Refunds\Exceptions\RefundInvalidArgumentException;
use App\Shop\Refunds\Exceptions\RefundNotFoundException;
use App\Shop\Refunds\Repositories\Interfaces\RefundRepositoryInterface;
use App\Shop\Refunds\Transformations\RefundTransformable;
use App\Shop\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class RefundRepository extends BaseRepository implements RefundRepositoryInterface {

    use RefundTransformable;

    /**
     * RefundRepository constructor.
     * @param Refund $refund
     */
    public function __construct(Refund $refund) {
        parent::__construct($refund);
        $this->model = $refund;
    }

    /**
     * Create the refund
     *
     * @param array $params
     * @return Address
     */
    public function createRefund(array $params): Refund {
        try {

            $refund = new Refund($params);

            $refund->save();

            return $refund;
        } catch (QueryException $e) {
            throw new RefundInvalidArgumentException('Refund creation error', 500, $e);
        }
    }

    /**
     * @param array $update
     * @return bool
     */
    public function updateRefund(array $update): bool {
        return $this->model->update($update);
    }

    /**
     * Soft delete the refund
     *
     */
    public function deleteRefund() {
        return $this->model->delete();
    }

    /**
     * List all the refund
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return array|Collection
     */
    public function listRefund(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    public function getRefundsForOrderByLineId(\App\Shop\Orders\Order $order) {
        $arrAllRefunds = $this->listRefund('line_id', 'asc')->where('order_id', $order->id);

        if (empty($arrAllRefunds)) {

            return [];
        }


        $arrRefunds = [];

        foreach ($arrAllRefunds as $objRefund) {

            $arrRefunds[$objRefund->line_id] = $objRefund;
        }
        
        

        return $arrRefunds;
    }

    /**
     * Return the refund
     *
     * @param int $id
     * @return Address
     */
    public function findRefundById(int $id): Refund {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new RefundNotFoundException($e->getMessage());
        }
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function searchRefund(string $text): Collection {
        return $this->model->search($text, [
                    'coupon_code' => 10,
                    'amount' => 5,
                    'amount_type' => 10
                ])->get();
    }
    
    public function refundLinesForOrder(Request $request) {
        
        $refundAmount = 0;
                
        $order = (new OrderRepository(new Order))->findOrderById($request->order_id);
        
        foreach($request->lineIds as $lineId) {
            
            $orderProduct = (new OrderProductRepository(new OrderProduct))->findOrderProductById($lineId);
            
            $refundAmount += $orderProduct->product_price;
            
            $orderProductRepo = new OrderProductRepository($orderProduct);

            $data = [];
            $data['date_refunded'] = date('Y-m-d'); //add request
            $data['quantity'] = $orderProduct->quantity;
            $data['line_id'] = $lineId;
            $data['order_id'] = $request->order_id;
            $data['status'] = $request->status;
            $data['amount'] = $orderProduct->product_price;
            
            $this->createRefund($data);

            $orderProductRepo->updateOrderProduct(
                    [
                        'status' => $request->status
                    ], $request->lineId
            );
        }
        
        
        $customer = (new CustomerRepository(new Customer))->findCustomerById($order->customer_id);
        $totalPaid = $order->total_paid - $refundAmount;
        
        $orderRepo = new OrderRepository($order);
        
        $orderRepo->updateOrder(['total_paid' => $totalPaid, 'amount_refunded' => $refundAmount]);
        
        switch ($order->payment) {
            case 'paypal':

                if (!(new PayPalExpressCheckoutRepository())->doRefund($order, $refundAmount)) {

                    die('cant do refund');
                }
                break;

            case 'stripe':
                if (!(new StripeRepository($customer))->doRefund($order, $refundAmount)) {
                    die('Cant do refund');
                }
                break;
        }
        
        //event(new RefundsCreateEvent($order));
        
        return true;
    }

}
