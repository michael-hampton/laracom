<?php

namespace App\Shop\Returns\Repositories;

use App\Shop\Returns\Returns;
use App\Shop\Orders\Order;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\Channels\Channel;
use App\Events\OrderCreateEvent;
use Illuminate\Http\Request;
use App\Shop\Returns\Exceptions\ReturnLineInvalidArgumentException;
use App\Shop\Returns\Exceptions\ReturnLineNotFoundException;
use App\Shop\Returns\Repositories\Interfaces\ReturnLineRepositoryInterface;
use App\Shop\Returns\Transformations\ReturnLineTransformable;
use App\Shop\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\Shop\Returns\ReturnLine;

class ReturnLineRepository extends BaseRepository implements ReturnLineRepositoryInterface {

    use ReturnLineTransformable;

    /**
     * ReturnRepository constructor.
     * @param Return $return
     */
    public function __construct(ReturnLine $returnLine) {
        parent::__construct($returnLine);
        $this->model = $returnLine;
    }

    /**
     * Create the return
     *
     * @param array $params
     * @return Address
     */
    public function createReturnLine(array $params, Returns $return): ReturnLine {
        try {
            $params['return_id'] = $return->id;
            $returnLine = new ReturnLine($params);
            $returnLine->save();
            return $returnLine;
        } catch (QueryException $e) {
            throw new ReturnLineInvalidArgumentException('Refund creation error', 500, $e);
        }
    }

    /**
     * @param array $update
     * @return bool
     */
    public function updateReturnLine(array $update): bool {
        return $this->model->update($update);
    }

    /**
     * Soft delete the return
     *
     */
    public function deleteReturnLine() {
        return $this->model->delete();
    }

    /**
     * List all the return
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return array|Collection
     */
    public function listReturnLine(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Return the return
     *
     * @param int $id
     * @return Return
     */
    public function findReturnLineById(int $id): ReturnLine {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ReturnLineNotFoundException($e->getMessage());
        }
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function searchReturnLine(string $text): Collection {
        return $this->model->search($text, [
                    'coupon_code' => 10,
                    'amount' => 5,
                    'amount_type' => 10
                ])->get();
    }

}
