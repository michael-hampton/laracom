<?php

namespace App\Http\Controllers\Admin\Couriers;

use App\Shop\CourierRates\Repositories\CourierRateRepository;
use App\Shop\Couriers\Repositories\Interfaces\CourierRepositoryInterface;
use App\Shop\CourierRates\Repositories\Interfaces\CourierRateRepositoryInterface;
use App\Shop\CourierRates\Requests\CreateCourierRateRequest;
use App\Shop\CourierRates\Requests\UpdateCourierRateRequest;
use App\Shop\Countries\Repositories\CountryRepository;
use App\Shop\Countries\Country;
use App\Http\Controllers\Controller;

class CourierRateController extends Controller {

    /**
     * @var CourierRepositoryInterface
     */
    private $courierRepo;

    /**
     * @var CourierRateRepositoryInterface
     */
    private $courierRateRepo;

    /**
     * CourierRateController constructor.
     * @param CourierRepositoryInterface $courierRepository
     */
    public function __construct(CourierRepositoryInterface $courierRepository, CourierRateRepositoryInterface $courierRateRepository) {
        $this->courierRepo = $courierRepository;
        $this->courierRateRepo = $courierRateRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
                
        return view('admin.courier-rates.list', ['couriers' => $this->courierRateRepo->listCourierRates('name', 'asc')]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $countryRepo = new CountryRepository(new Country);
        $countries = $countryRepo->listCountries();
        return view('admin.courier-rates.create', ['countries' => $countries, 'couriers' => $this->courierRepo->listCouriers('name', 'asc')]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateCourierRateRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCourierRateRequest $request) {
        $this->courierRateRepo->createCourierRate($request->all());
        $request->session()->flash('message', 'Create successful');
        return redirect()->route('admin.courier-rates.index');
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
        return view('admin.courier-rates.edit', ['courier' => $this->courierRateRepo->findCourierRateById($id),
            'countries' => $countries,
            'couriers' => $this->courierRepo->listCouriers('name', 'asc')
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
    public function update(UpdateCourierRateRequest $request, $id) {
        $courier = $this->courierRateRepo->findCourierRateById($id);
        $update = new CourierRateRepository($courier);
        $update->updateCourierRate($request->all());
        $request->session()->flash('message', 'Update successful');
        return redirect()->route('admin.courier-rates.edit', $id);
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
        $courierRepo->delete();
        request()->session()->flash('message', 'Delete successful');
        return redirect()->route('admin.courier-rates.index');
    }

}
