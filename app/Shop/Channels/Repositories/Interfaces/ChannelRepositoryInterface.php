<?php

namespace App\Shop\Channels\Repositories\Interfaces;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Channels\Channel;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface ChannelRepositoryInterface extends BaseRepositoryInterface {

    /**
     * 
     * @param string $order
     * @param string $sort
     * @param array $columns
     */
    public function listChannels(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;

    /**
     * 
     * @param array $data
     */
    public function createChannel(array $data): Channel;

    /**
     * 
     * @param array $data
     */
    public function updateChannel(array $data): bool;

    /**
     * 
     * @param int $id
     */
    public function findChannelById(int $id): Channel;

    /**
     * 
     * @param Channel $channel
     */
    public function deleteChannel(Channel $channel): bool;

    /**
     * 
     * @param array $file
     * @param type $disk
     */
    public function deleteFile(array $file, $disk = null): bool;

    /**
     * 
     * @param string $text
     */
    public function searchChannel(string $text): Collection;

    /**
     * 
     * @param UploadedFile $file
     */
    public function saveCoverImage(UploadedFile $file): string;

    /**
     * detachEmployees
     */
    public function detachEmployees();

    /**
     * getEmployees
     */
    public function getEmployees(): Collection;

    /**
     * channelsWithoutEmployee
     */
    public function channelsWithoutEmployee(): Collection;

    /**
     * 
     * @param string $name
     */
    public function findByName(string $name);
}
