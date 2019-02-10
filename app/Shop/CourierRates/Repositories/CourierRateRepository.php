<?php

namespace App\Shop\CourierRates\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\CourierRates\Exceptions\CourierRateNotFoundException;
use App\Shop\CourierRates\Exceptions\CourierRateInvalidArgumentException;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use App\Shop\CourierRates\CourierRate;
use App\Shop\Couriers\Courier;
use App\Shop\Channels\Channel;
use App\Shop\CourierRates\Transformations\CourierRateTransformable;
use Illuminate\Support\Facades\DB;

class CourierRateRepository extends BaseRepository implements CourierRateRepositoryInterface {

    use CourierRateTransformable;

    /**
     * CountryRepository constructor.
     * @param Country $country
     */
    public function __construct(CourierRate $courierRate) {
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
    public function listCourierRates(string $order = 'id', string $sort = 'desc'): Collection {
        return $this->model->get();
    }

    /**
     * @param array $params
     * @return Country
     */
    public function createCourierRate(array $params): CourierRate {
        return $this->create($params);
    }

    /**
     * Find the country
     *
     * @param $id
     * @return Country
     * @throws CountryNotFoundException
     */
    public function findCourierRateById(int $id): CourierRate {
        try {
            return $this->transformCourierRate($this->findOneOrFail($id));
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
    public function updateCourierRate(array $params): CourierRate {

        try {
            $this->model->where('id', $this->model->id)->update($params);
            return $this->findCourierRateById($this->model->id);
        } catch (QueryException $e) {
            throw new CourierRateInvalidArgumentException($e);
        }
    }

    /**
     * 
     * @param type $total
     * @param Courier $courier
     * @param Channel $channel
     * @return type
     */
    public function findShippingMethod($total, Courier $courier, Channel $channel, int $country_id) {

        $rates = $this->model
                ->whereRaw('? between range_from and range_to', [$total])
                ->where('courier', '=', $courier->id)
                ->where('channel', '=', $channel->id)
                ->where('country', '=', $country_id)
                ->get();

        if (isset($rates[0]))
        {
            return $rates[0];
        }

        return 0;
    }

    /**
     * 
     * @param type $total
     * @param Courier $courier
     * @param Channel $channel
     * @param int $country_id
     * @return type
     */
    public function getShippingMethods($total, Channel $channel, int $country_id, $courier = null) {

        $query = $this->model->where('channel', '=', $channel->id)
                        ->where('country', '=', $country_id)
                        ->where(function ($query) use ($total) {
                            $query->where('range_from', '<=', $total);
                            $query->where('range_to', '>=', $total);
                        })
            
            if($courier !== null) {
                $query->where('courier', '=', $courier);
            }
                        $query->groupBy('courier')
                        ->get();
        
        return $query;
    }

    /**
     * Delete the courier rate
     *
     * @param CourierRate $courierRate
     *
     * @return bool
     * @throws \Exception
     * @deprecated
     * @use removeCourierRate
     */
    public function removeCourierRate(): bool {
        return $this->model->where('id', $this->model->id)->delete();
    }

}
