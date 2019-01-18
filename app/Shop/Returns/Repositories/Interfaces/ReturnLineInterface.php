namespace App\Shop\Returns\Repositories\Interfaces;
use App\Shop\Returns\Return;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Orders\Order;
use Illuminate\Support\Collection;
interface ReturnLineRepositoryInterface extends BaseRepositoryInterface {
    /**
     * 
     * @param array $params
     */
    public function createReturnLine(array $params): ReturnLine;
    /**
     * 
     * @param array $update
     */
    public function updateReturnLine(array $update): bool;
    /**
     * 
     */
    public function deleteReturnLine();
    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listReturnLine(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;
    /**
     * 
     * @param int $id
     */
    public function findReturnLineById(int $id): ReturnLine;
    /**
     * 
     * @param string $text
     */
    public function searchReturnLine(string $text): Collection;
}
