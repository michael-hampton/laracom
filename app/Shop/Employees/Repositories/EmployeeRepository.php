<?php

namespace App\Shop\Employees\Repositories;

use App\Shop\Base\BaseRepository;
use App\Shop\Employees\Employee;
use App\Shop\Employees\Exceptions\EmployeeNotFoundException;
use App\Shop\Employees\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Shop\Channels\Channel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class EmployeeRepository extends BaseRepository implements EmployeeRepositoryInterface {

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
     * EmployeeRepository constructor.
     * @param Employee $employee
     */
    public function __construct(Employee $employee) {
        parent::__construct($employee);
        $this->model = $employee;
    }

    /**
     * List all the employees
     *
     * @param string $order
     * @param string $sort
     * @return array
     */
    public function listEmployees(string $order = 'id', string $sort = 'desc'): Collection {
        return $this->all(['*'], $order, $sort);
    }

    /**
     * Create the employee
     *
     * @param array $params
     * @return Employee
     */
    public function createEmployee(array $params): Employee {
        $collection = collect($params);
        $employee = new Employee(($collection->except('password'))->all());
        $employee->password = bcrypt($collection->only('password'));

        if (!$employee->validate())
        {
            $this->validationFailures = $employee->getValidationFailures();
            $this->blValid = false;

            return $employee;
        }

        $employee->save();
        return $employee;
    }

    /**
     * Find the employee by id
     *
     * @param int $id
     * @return Employee
     */
    public function findEmployeeById(int $id): Employee {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new EmployeeNotFoundException;
        }
    }

    /**
     * Update employee
     *
     * @param array $params
     * @return bool
     */
    public function updateEmployee(array $params): bool {

        if(isset($params['password'])) {
            $params['password'] = bcrypt($params['password']);
        }
        
        $this->model->fill($params);

        if (!$this->model->validate(true))
        {

            $this->blValid = false;
            $this->validationFailures = $this->model->getValidationFailures();
            return false;
        }
        
        $blValid = $this->model->update($params);
        
        return $blValid;
    }

    /**
     * @param array $roleIds
     */
    public function syncRoles(array $roleIds) {
        $this->model->roles()->sync($roleIds);
    }

    /**
     * @return Collection
     */
    public function listRoles(): Collection {
        return $this->model->roles()->get();
    }

    public function listRolesByEmployee(Employee $employee): Collection {
        return $employee->roles()->get();
    }

    /**
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool {
        return $this->model->hasRole($roleName);
    }

    /**
     * @param Employee $employee
     * @return bool
     */
    public function isAuthUser(Employee $employee): bool {
        $isAuthUser = false;
        if (Auth::guard('admin')->user()->id == $employee->id)
        {
            $isAuthUser = true;
        }
        return $isAuthUser;
    }

    /**
     * Associate a channel to a Employee
     *
     * @param Store $channel
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function associateChannel(Channel $channel) {
        return $this->model->channels()->save($channel);
    }

    /**
     * Return all the channels associated with the employee
     *
     * @return mixed
     */
    public function findEmployeeChannels(): Collection {
        return $this->model->channels;
    }

    /**
     * @param array $params
     */
    public function syncChannels(array $params) {
        return $this->model->stores()->sync($params);
    }

    /**
     * List all the employees without Channel
     * @return array
     */
    public function employeesWithoutChannel(): Collection {
        return $this->model->doesntHave('channels')->paginate(15)->get();
    }

    public function getValidationFailures() {
        return $this->validationFailures;
    }

}
