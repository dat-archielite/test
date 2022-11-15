<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class HomeController extends Controller
{
    public function __invoke()
    {
        $customers = Customer::query()->paginate();

        return view('home', compact('customers'));
    }
}
