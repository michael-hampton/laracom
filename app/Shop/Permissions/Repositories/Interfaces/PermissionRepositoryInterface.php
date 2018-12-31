<?php

namespace App\Shop\Permissions\Repositories\Interfaces;

use App\Shop\Base\Interfaces\BaseRepositoryInterface;
use App\Shop\Permissions\Permission;
use Illuminate\Support\Collection;

interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * 
     * @param array $data
     */
    public function createPermission(array $data) : Permission;

    /**
     * 
     * @param int $id
     */
    public function findPermissionById(int $id) : Permission;

    /**
     * 
     * @param array $data
     */
    public function updatePermission(array $data) : bool;

    /**
     * 
     */
    public function deletePermissionById() : bool;

    /**
     * 
     * @param type $columns
     * @param string $orderBy
     * @param string $sortBy
     */
    public function listPermissions($columns = array('*'), string $orderBy = 'id', string $sortBy = 'asc') : Collection;
}
