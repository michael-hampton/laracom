<?php

namespace App\Shop\Channels\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\Channels\Exceptions\ChannelInvalidArgumentException;
use App\Shop\Channels\Exceptions\ChannelNotFoundException;
use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Products\Product;
use App\Shop\Channels\Transformations\ChannelTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ChannelRepository extends BaseRepository implements ChannelRepositoryInterface {

    use ChannelTransformable;

    /**
     *
     * @var type 
     */
    private $validationFailures = [];

    /**
     *
     * @var type 
     */
    private $blValid = true;

    /**
     * ChannelRepository constructor.
     * @param Channel $channel
     */
    public function __construct(Channel $channel) {
        parent::__construct($channel);
        $this->model = $channel;
    }

    /**
     * List all the channels
     *
     * @param string $order
     * @param string $sort
     * @param array $columns
     * @return Collection
     */
    public function listChannels(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection {
        return $this->all($columns, $order, $sort);
    }

    /**
     * Create the channel
     *
     * @param array $params
     * @return Channel
     */
    public function createChannel(array $params): Channel {

        try {
            $channel = new Channel($params);

            if (!$channel->validate())
            {
                $this->validationFailures = $channel->getValidationFailures();
                $this->blValid = false;

                return $channel;
            }

            $channel->save();
            return $channel;
        } catch (QueryException $e) {
            throw new ChannelInvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Update the channel
     *
     * @param array $data
     *
     * @return bool
     * @throws ChannelInvalidArgumentException
     */
    public function updateChannel(array $data): bool {
        try {

            $this->model->fill($data);

            if (!$this->model->validate(true))
            {

                $this->blValid = false;
                $this->validationFailures = $this->model->getValidationFailures();
                return false;
            }

            return $this->model->where('id', $this->model->id)->update($data);
        } catch (QueryException $e) {
            throw new ChannelInvalidArgumentException($e);
        }
    }

    /**
     * Find the channel by ID
     *
     * @param int $id
     * @return Channel
     */
    public function findChannelById(int $id): Channel {
        try {
            return $this->transformChannel($this->findOneOrFail($id));
        } catch (ModelNotFoundException $e) {
            throw new ChannelNotFoundException($e->getMessage());
        }
    }

    /**
     * Delete the channel
     *
     * @param Channel $channel
     * @return bool
     */
    public function deleteChannel(Channel $channel): bool {
        return $channel->delete();
    }

    /**
     * Associate a product in a channel
     *
     * @param Product $product
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function associateProduct(Product $product) {
        return $this->model->products()->save($product);
    }

    /**
     * Return all the products associated with the channel
     *
     * @return mixed
     */
    public function findProducts(): Collection {
        return $this->model->products;
    }

    /**
     * @param array $params
     */
    public function syncProducts(array $params) {
        $this->model->products()->sync($params);
    }

    /**
     * Detach the association of the product
     *
     */
    public function detachProducts() {
        $this->model->products()->detach();
    }

    /**
     * @param $file
     * @param null $disk
     * @return bool
     */
    public function deleteFile(array $file, $disk = null): bool {
        return $this->update(['cover' => null], $file['channel']);
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function searchChannel(string $text): Collection {
        return $this->model->searchChannel($text);
    }

    /**
     * @return mixed
     */
    public function findChannelImages(): Collection {
        return $this->model->images()->get();
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    public function saveCoverImage(UploadedFile $file): string {
        return $file->store('channels', ['disk' => 'images']);
    }

    /**
     * Detach the employees
     */
    public function detachEmployees() {
        $this->model->employees()->detach();
    }

    /**
     * Return the employees which the Store is associated with
     *
     * @return Collection
     */
    public function getEmployees(): Collection {
        return $this->model->employees()->get();
    }

    /**
     * Sync the employees
     *
     * @param array $params
     */
    public function syncEmployees(array $params) {
        $this->model->employees()->sync($params);
    }

    /**
     * List all the stores without Employee
     * @return array
     */
    public function channelsWithoutEmployee(): Collection {
        return $this->model->doesntHave('employees')->get();
    }

    /**
     * 
     * @param string $name
     * @return type
     */
    public function findByName(string $name) {
        return $this->model->where('name', $name)->first();
    }

    public function getValidationFailures() {
        return $this->validationFailures;
    }

}
