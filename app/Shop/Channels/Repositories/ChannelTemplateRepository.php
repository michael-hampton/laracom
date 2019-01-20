<?php

namespace App\Shop\Channels\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\Channels\Exceptions\ChannelInvalidArgumentException;
use App\Shop\Channels\Exceptions\ChannelNotFoundException;
use App\Shop\Channels\ChannelTemplate;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Products\Product;
use App\Shop\Channels\Transformations\ChannelTransformable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class ChannelTemplateRepository extends BaseRepository {

    use ChannelTransformable;

    /**
     * ChannelRepository constructor.
     * @param Channel $channel
     */
    public function __construct(ChannelTemplate $channelTemplate) {
        parent::__construct($channelTemplate);
        $this->model = $channelTemplate;
    }

  
    /**
     * Create the channel
     *
     * @param array $params
     * @return Channel
     */
    public function createChannelTemplate(array $params): ChannelTemplate {

        try {
            $channelTemplate = new ChannelTemplate($params);
            $channelTemplate->save();
            return $channelTemplate;
        } catch (QueryException $e) {
            throw \Exception($e->getMessage());
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
    public function updateChannelTemplate(array $data): bool {
        try {
            return $this->model->where('id', $this->model->id)->update($data);
        } catch (QueryException $e) {
            throw new \Exception($e);
        }
    }

}
