<?php

namespace App\Http\Controllers;

/**
 * IndexController
 *
 * @author michael.hampton
 */
class IndexController extends BaseController {

    public function index() {


        $objTask = new \App\Task();

        $arrTasks = $objTask->getAllTasks();

        return view('tasks', [
            'tasks' => $arrTasks
        ]);
    }

}
