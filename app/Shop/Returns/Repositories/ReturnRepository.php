<?php
namespace App\Shop\Returns\Repositories;
use App\Shop\Returns\Returns;
use App\Events\ReturnsCreateEvent;
use App\Shop\Orders\Order;
use App\Shop\OrderProducts\Repositories\OrderProductRepository;
use App\Shop\Channels\Channel;
use App\Events\OrderCreateEvent;
use Illuminate\Http\Request;
use App\Shop\Returns\Exceptions\ReturnInvalidArgumentException;
use App\Shop\Returns\Exceptions\ReturnNotFoundException;
use App\Shop\Returns\Repositories\Interfaces\ReturnRepositoryInterface;
use App\Shop\Returns\Transformations\ReturnTransformable;
use App\Shop\Base\BaseRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
class ReturnRepository extends BaseRepository implements ReturnRepositoryInterface {
    use ReturnTransformable;
    /**
     * ReturnRepository constructor.
     * @param Return $return
     */
    public function __construct(Returns $return) {
        parent::__construct($return);
        $this->model = $return;
    }
    /**
     * Create the return
     *
     * @param array $params
     * @return Address
     */
    public function createReturn(array $params): Returns {
        try {
            $return = new Returns($params);
            $return->save();
            return $return;
        } catch (QueryException $e) {
            throw new ReturnInvalidArgumentException('Refund creation error', 500, $e);
        }
    }
    /**
     * @param array $update
     * @return bool
     */
    public function updateReturn(array $update): bool {
        return $this->model->update($update);
    }
    /**
     * Soft delete the return
     *
     */
    public function deleteReturn() {
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
    public function listReturn(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }
    
    /**
     * Return the return
     *
     * @param int $id
     * @return Return
     */
    public function findReturnById(int $id): Returns {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ReturnNotFoundException($e->getMessage());
        }
    }
    /**
     * @param string $text
     * @return mixed
     */
    public function searchReturn(string $text): Collection {
        return $this->model->search($text, [
                    'coupon_code' => 10,
                    'amount' => 5,
                    'amount_type' => 10
                ])->get();
    }
}
