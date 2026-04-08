<?php

namespace App\Domains\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;

class CustomerDashboard extends Controller
{
    public function index()
    {
        return view('account.dashboard');
    }
}
