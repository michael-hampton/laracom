<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Log;

class StudInsertController extends Controller {

    public function insertform() {
        return view('stud_create');
    }

    public function index() {
        $users = DB::select('select * from student');

        return view('stud_view', ['users' => $users]);
    }

    public function insert(Request $request) {
        $name = $request->input('stud_name');
          Log::info(['Request'=>$request]);
        DB::insert('insert into student (name) values(?)', [$name]);
        echo "Record inserted successfully.<br/>";
        echo '<a href = "/insert">Click Here</a> to go back.';
    }

    public function edit(Request $request, $id) {
        $name = $request->input('stud_name');
        DB::update('update student set name = ? where id = ?', [$name, $id]);
        echo "Record updated successfully.<br/>";
        echo '<a href = "/edit-records">Click Here</a> to go back.';
    }

}
