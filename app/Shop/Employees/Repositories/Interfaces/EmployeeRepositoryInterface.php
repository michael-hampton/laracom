<?php
namespace App\Shop\Employees\Repositories\Interfaces;
use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Employees\Employee;
use App\Shop\Channels\Channel;
use Illuminate\Support\Collection;

/**
 * 
 */
interface EmployeeRepositoryInterface extends BaseRepositoryInterface
{
    
    /**
     * 
     * @param string $order
     * @param string $sort
     */
    public function listEmployees(string $order = 'id', string $sort = 'desc'): Collection;
    
    /**
     * 
     * @param array $params
     */
    public function createEmployee(array $params) : Employee;
    
    /**
     * 
     * @param int $id
     */
    public function findEmployeeById(int $id) : Employee;
    
    /**
     * 
     * @param array $params
     */
    public function updateEmployee(array $params) : bool;
    
    /**
     * 
     * @param array $roleIds
     */
    public function syncRoles(array $roleIds);
    
    /**
     * 
     */
    public function listRoles() : Collection;
    
    /**
     * 
     * @param string $roleName
     */
    public function hasRole(string $roleName) : bool;
    
    /**
     * 
     * @param Employee $employee
     */
    public function isAuthUser(Employee $employee): bool;
    
    /**
     * 
     * @param Store $channel
     */
    public function associateChannel(Channel $channel);
    
    /**
     * 
     */
    public function findEmployeeChannels() : Collection;
    
    /**
     * 
     * @param array $params
     */
    public function syncChannels(array $params);
    
   /**
    * 
    */
    public function employeesWithoutChannel(): Collection;
}