<?php

namespace App\Shop\Channels\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\Channels\Exceptions\ChannelNotFoundException;
use App\Shop\Channels\Warehouse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use App\Shop\Channels\Channel;
use Illuminate\Support\Facades\DB;

class WarehouseRepository extends BaseRepository {

    /**
     * 
     * @param \App\Shop\Channels\Repositories\Warehouse $warehouse
     */
    public function __construct(Warehouse $warehouse) {
        parent::__construct($warehouse);
        $this->model = $warehouse;
    }

    /**
     * List all the channels
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listWarehouses(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Find the channel by ID
     *
     * @param int $id
     * @return Channel
     */
    public function findWarehouseById(int $id): Warehouse {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ChannelNotFoundException($e->getMessage());
        }
    }

    /**
     * 
     * @param Channel $channel
     * @return type
     */
    public function getWarehousesForChannel(Channel $channel) {
        $warehouses = $this->model
                ->join('channel_warehouses', 'channel_warehouses.warehouse_id', '=', 'warehouse.id')
                ->where('channel_warehouses.channel_id', $channel->id)
                ->select('warehouse.*')
                ->get();

        return $warehouses;
    }

    /**
     * 
     * @param string $name
     * @return type
     */
    public function findByName(string $name) {
        return $this->model->where('name', $name)->first();
    }

}
