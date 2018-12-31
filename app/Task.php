<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Task extends Model {

    public function getAllTasks() {
        $arrTasks = DB::select("SELECT * FROM tasks ORDER BY created_at ASC");

        if (!$arrTasks) {

            return false;
        }

        $arrTasks = collect($arrTasks)->map(function($x) {
                    return (array) $x;
                })->toArray();

        return $arrTasks;
    }

}
