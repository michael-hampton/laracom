<?php

namespace App\Http\Controllers\Admin\Couriers;

use App\Shop\CourierRates\Repositories\CourierRateRepository;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\Channels\Repositories\Interfaces\ChannelRepositoryInterface;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use App\Shop\CourierRates\Requests\CreateCourierRateRequest;
use App\Shop\CourierRates\Requests\UpdateCourierRateRequest;
use Illuminate\Http\Request;
use App\Shop\Countries\Repositories\CountryRepository;
use App\Shop\Countries\Country;
use App\Shop\CourierRates\CourierRate;
use App\Shop\CourierRates\Transformations\CourierRateTransformable;
use App\Http\Controllers\Controller;
use App\Search\CourierRateSearch;

class CourierRateController extends Controller {

    use CourierRateTransformable;

    /**
     * @var CourierRepositoryInterface
     */
    private $courierRepo;

    /**
     * @var CourierRateRepositoryInterface
     */
    private $courierRateRepo;

    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepo;

    /**
     * 
     * @param CourierRepositoryInterface $courierRepository
     * @param CourierRateRepositoryInterface $courierRateRepository
     * @param \App\Http\Controllers\Admin\Couriers\ChannelRepositoryInterface $channelRepository    
     */
    public function __construct(CourierRepositoryInterface $courierRepository, CourierRateRepositoryInterface $courierRateRepository, ChannelRepositoryInterface $channelRepository) {
        $this->courierRepo = $courierRepository;
        $this->courierRateRepo = $courierRateRepository;
        $this->channelRepo = $channelRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        $courier_rates = $this->courierRateRepo->listCourierRates('id');
        $couriers = $this->courierRepo->listCouriers()->keyBy('id');

        $countries = (new CountryRepository(new Country))->listCountries();



        return view('admin.courier-rates.list', [
            'couriers' => $couriers,
            'courier_rates' => $courier_rates,
            'countries' => $countries,
            'channels' => $this->channelRepo->listChannels()
                ]
        );
    }

    public function search(Request $request) {

        $courier_rates = CourierRateSearch::apply($request);
        $couriers = $this->courierRepo->listCouriers()->keyBy('id');

        $countries = (new CountryRepository(new Country))->listCountries();



        return view('admin.courier-rates.search', [
            'couriers' => $couriers,
            'courier_rates' => $courier_rates,
            'countries' => $countries,
            'channels' => $this->channelRepo->listChannels()
                ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $countryRepo = new CountryRepository(new Country);
        $countries = $countryRepo->listCountries();
        return view('admin.courier-rates.create', [
            'countries' => $countries,
            'couriers' => $this->courierRepo->listCouriers('name', 'asc'),
            'channels' => $this->channelRepo->listChannels()
                ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateCourierRateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCourierRateRequest $request) {

        $this->courierRateRepo->createCourierRate($request->all());
        echo json_encode(['http_code' => 200]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id) {
        $countryRepo = new CountryRepository(new Country);
        $countries = $countryRepo->listCountries();
        return view('admin.courier-rates.edit', [
            'courier' => $this->courierRateRepo->findCourierRateById($id),
            'countries' => $countries,
            'couriers' => $this->courierRepo->listCouriers('name', 'asc'),
            'channels' => $this->channelRepo->listChannels()
                ]
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCourierRateRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

        foreach($request->rates as $rateId => $rate) {
           
            $csv_errors = Validator::make(
                                $rate, (new UpdateCourierRateRequest())->rules()
                        )->errors();
            
            if ($csv_errors->any()) {
                echo json_encode(['http_code' => 400]);
                die;
            }
            
            $courierRate = $this->courierRateRepo->findCourierRateById($rateId);
            $courierRepo = new CourierRateRepository($courierRate);
            
            $courierRepo->updateCourierRate($rate);
        }
        

        echo json_encode(['http_code' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id) {
        $courier = $this->courierRateRepo->findCourierRateById($id);

        $courierRepo = new CourierRateRepository($courier);
        $courierRepo->removeCourierRate();
        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.courier-rates.index');
    }

}
