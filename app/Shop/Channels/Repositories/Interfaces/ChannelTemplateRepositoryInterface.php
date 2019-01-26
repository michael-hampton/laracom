<?php
namespace App\Shop\Channels\Repositories\Interfaces;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Channels\ChannelTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
interface ChannelTemplateRepositoryInterface extends BaseRepositoryInterface {
    
   public function createChannelTemplate(array $params): ChannelTemplate;
    
    public function updateChannelTemplate(array $data): bool;
    
    public function updateOrCreate($data, $params);
    
    public function getTemplatesForChannel(Channel $channel);
}
