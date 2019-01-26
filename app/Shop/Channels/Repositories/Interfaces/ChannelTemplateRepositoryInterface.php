<?php

namespace App\Shop\Channels\Repositories\Interfaces;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Channels\ChannelTemplate;
use Illuminate\Http\Request;
use App\Shop\Channels\Channel;
use Illuminate\Support\Collection;

interface ChannelTemplateRepositoryInterface extends BaseRepositoryInterface {

    /**
     * 
     * @param array $params
     */
    public function createChannelTemplate(array $params): ChannelTemplate;

    /**
     * 
     * @param array $data
     */
    public function updateChannelTemplate(array $data): bool;

    /**
     * 
     * @param type $data
     * @param type $params
     */
    public function updateOrCreate($data, $params);

    /**
     * 
     * @param Channel $channel
     */
    public function getTemplatesForChannel(Channel $channel);
}
