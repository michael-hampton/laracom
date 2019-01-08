<?php
namespace App\Shop\CourierRates\Repositories;
use App\Shop\Base\BaseRepository;
use App\Shop\CourierRates\Exceptions\CourierRateInvalidArgumentException;
use App\Shop\CourierRates\Exceptions\CourierRateNotFoundException;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use App\Shop\CourierRates\CourierRate;

class CourierRateRepository extends BaseRepository implements CountryRepositoryInterface
{
    /**
     * CountryRepository constructor.
     * @param Country $country
     */
    public function __construct(CourierRate $courierRate)
    {
        parent::__construct($courierRate);
        $this->model = $courierRate;
    }
    /**
     * List all the courier rates
     *
     * @param string $order
     * @param string $sort
     * @return Collection
     */
    public function listCourierRates(string $order = 'id', string $sort = 'desc') : Collection
    {
        return $this->model->get();
    }
    /**
     * @param array $params
     * @return Country
     */
    public function createCourierRate(array $params) : CourierRate
    {
        return $this->create($params);
    }
    /**
     * Find the country
     *
     * @param $id
     * @return Country
     * @throws CountryNotFoundException
     */
    public function findCourierRateById(int $id) : CourierRate
    {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new CourierRateNotFoundException($e->getMessage());
        }
    }
    
    /**
     * Update the courier rate
     *
     * @param array $params
     * @return CourierRate
     */
    public function updateCourierRate(array $params) : CourierRate
    {
        try {
            $this->model->update($params);
            return $this->findCourierRateById($this->model->id);
        } catch (QueryException $e) {
            throw new CountryInvalidArgumentException($e->getMessage());
        }
    }
    
    public function findShippingMethod($total) {
        
        $query = DB::table('couriers');
        $query->whereRaw('? between range_from and range_to', [$total])
        return $query->get();
        
    }
}
