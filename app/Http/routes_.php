<?php

use App\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

//Route::group(['middleware' => 'auth'], function () {
//    Route::get('/', 'IndexController@index');
//     Route::get('index', 'IndexController@index');
//});

Route::group(['middleware' => ['web']], function () {

    Route::get('index', function () {
        if (Auth::check()) {
            return redirect('index/index');
        } else {
            die('2');
            return view('login.login');
        }
    });

    Route::get('index', 'IndexController@index');

    Route::resource('test', 'TestController');

    Route::get('terminate', [
        'middleware' => 'terminate',
        'uses' => 'ABCController@index',
    ]);

    Route::get('/register', function() {
        return view('register');
    });
    Route::post('/user/register', array('uses' => 'UserRegistration@postRegister'));

    Route::get('/cookie/set', 'CookieController@setCookie');
    Route::get('/cookie/get', 'CookieController@getCookie');

    Route::get('insert', 'StudInsertController@insertform');
    Route::post('create', 'StudInsertController@insert');
    Route::get('view-records', 'StudInsertController@index');

    Route::get('session/get', 'SessionController@accessSessionData');
    Route::get('session/set', 'SessionController@storeSessionData');
    Route::get('session/remove', 'SessionController@deleteSessionData');

    Route::get('/uploadfile', 'UploadFileController@index');
    Route::post('/uploadfile', 'UploadFileController@showUploadFile');

    Route::get('sendbasicemail', 'MailController@basic_email');
    Route::get('sendhtmlemail', 'MailController@html_email');
    Route::get('sendattachmentemail', 'MailController@attachment_email');

    Route::get('ajax', function() {
        return view('message');
    });
    Route::post('/getmsg', 'AjaxController@index');

    // route to show the login form
    Route::get('login', array('uses' => 'HomeController@showLogin'));

    //Route::get('index', array('uses' => 'IndexController@index'));
// route to process the form
    Route::post('login', array('uses' => 'HomeController@doLogin'));

    Route::get('logout', array('uses' => 'HomeController@doLogout'));

    /**
     * Add A New Task
     */
    Route::post('/task', function (Request $request) {
        $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return redirect('/')
                            ->withInput()
                            ->withErrors($validator);
        }

        $task = new Task;
        $task->name = $request->name;
        $task->save();

        return redirect('/');
    });

    /**
     * Delete An Existing Task
     */
    Route::delete('/task/{id}', function ($id) {
        Task::findOrFail($id)->delete();

        return redirect('/');
    });
});

