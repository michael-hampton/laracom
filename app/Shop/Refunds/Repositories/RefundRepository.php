<?php

namespace App\Shop\Refunds\Repositories;

use App\Shop\Refunds\Refund;
use App\Events\RefundsCreateEvent;
use App\Shop\Orders\Order;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\Channels\Channel;
use App\Events\OrderCreateEvent;
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
    
    private $arrLineIds = [];

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
     * 
     * @return type
     */
    public function setOrderLinesToCompleted() {
        return $this->model->whereIn('line_id', $this->arrLineIds)->update(['status' => 4]);
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

    /**
     * 
     * @param Request $request
     * @param Order $order
     * @param Channel $channel
     * @return boolean
     */
    public function calculateRefundAmount(Request $request, Order $order, Channel $channel = null, Collection $orderLines) {

        $refundAmount = 0;
        
        if($order->amount_refunded >= $order->total_paid) {
            return false;
        }

        foreach ($orderLines as $orderProduct) {
            
            if(!in_array($orderProduct->id, $request->lineIds)){
                
                continue;
            }

            //$orderProduct = (new OrderProductRepository(new OrderProduct))->findOrderProductById($lineId);

            $refundAmount += $orderProduct->product_price;

            $data = [];
            $data['date_refunded'] = date('Y-m-d'); //add request
            $data['quantity'] = $orderProduct->quantity;
            $data['line_id'] = $orderProduct->id;
            $data['order_id'] = $request->order_id;
            $data['status'] = 1;
            $data['amount'] = $orderProduct->product_price;

            $this->createRefund($data);

            $this->arrLineIds[] = $orderProduct->id;
        }
        
        if (!empty($order->voucher_code) && $order->discounts > 0)
        {
            $refundAmount -= $order->discounts;
        }
        
        $refundAmount += $order->total_shipping;
        
        $totalRefunded = $order->amount_refunded + $refundAmount;
        
        if($totalRefunded >= $order->total_paid) {
            $difference = $totalRefunded - $order->total_paid;
            
            if($difference <= 0) {
                return false;
            }
            
            $refundAmount = $difference;
        }
        
        
        if ($refundAmount > $order->total_paid)
        {
            $refundAmount = $order->total_paid;
        }

        //event(new RefundsCreateEvent($order));

        return $refundAmount;
    }

}
