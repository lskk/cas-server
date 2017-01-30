<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Service;
use App\Services\User;

class HomeController extends Controller
{
    public function indexAction()
    {
        return view(
            'admin.dashboard',
            [
                'user'    => User::dashboard(),
                'service' => Service::dashboard(),
            ]
        );
    }
}
