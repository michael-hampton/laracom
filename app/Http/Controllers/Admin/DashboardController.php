<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Shop\Employees\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Shop\Employees\Repositories\EmployeeRepository;
use App\Shop\Channels\Channel;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\Channels\Repositories\ChannelRepository;
use Illuminate\Support\Facades\Auth;
use App\Shop\Channels\Transformations\ChannelTransformable;

class DashboardController extends Controller {

    use ChannelTransformable;

    /**
     * @var EmployeeRepositoryInterface
     */
    private $employeeRepo;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepo;

    /**
     * Dashboard Controller constructor.
     * @param EmployeeRepositoryInterface $employeeRepository
     */
    public function __construct(
    EmployeeRepositoryInterface $employeeRepository, ChannelRepositoryInterface $channelRepository
    ) {
        $this->employeeRepo = $employeeRepository;
        $this->channelRepo = $channelRepository;
    }

    public function index() {
        $currentAuthUserId = Auth::guard('admin')->user()->id;
        $employee = $this->employeeRepo->findEmployeeById($currentAuthUserId);
        
        $employeeRepo = new EmployeeRepository($employee);
        $list = $employeeRepo->findEmployeeChannels();
        
        $employeeStores = $list->map(function (Channel $item) {
                    return $this->transformChannel($item);
                })->all();
                
        return view('admin.dashboard', [
            'employeeChannels' => $this->channelRepo->paginateArrayResults($employeeStores, 8)
        ]);
    }

}
