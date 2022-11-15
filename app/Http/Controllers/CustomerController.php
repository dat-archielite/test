<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;

class CustomerController extends Controller
{
    public function create()
    {
        return view('customers.create');
    }

    /**
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     */
    public function store(StoreCustomerRequest $request)
    {
        try {
            DB::beginTransaction();
            $path = $request->file('file')->store('csv');

            $customers = (new FastExcel())->import(Storage::disk('local')->path($path), function ($customer) {
                return [
                    'id' => $customer['ID'],
                    'first_name' => $customer['First Name'],
                    'last_name' => $customer['Last Name'],
                    'email' => $customer['Email'],
                    'phone' => $customer['Phone'],
                ];
            });

            collect($customers)
                ->chunk(10000)
                ->each(function ($chunk) {
                    Customer::insert($chunk->toArray());
                });

            Storage::disk('local')->delete($path);

            DB::commit();
        } catch (\Exception) {
            DB::rollBack();
        }

        return to_route('home')->with('success', __('Import CSV file successfully!'));
    }

    public function deleteAll()
    {
        Customer::truncate();

        return to_route('home')->with('success', __('All customers have been deleted successfully!'));
    }
}
