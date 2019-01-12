<?php
namespace App\Search;

use Illuminate\Http\Request;

interface FilterInterface {

    /**
     * 
     * @param Request $filters
     */
    public static function apply(Request $filters);
}
