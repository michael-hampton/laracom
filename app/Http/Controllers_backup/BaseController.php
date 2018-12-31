<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

/**
 * BaseController
 *
 * @author michael.hampton
 */
class BaseController extends Controller {

    /**
     * 
     * @return type
     */
    public function __construct() {

        if (!Auth::check()) {

            Redirect::to('login')->send();
        }
    }

}
