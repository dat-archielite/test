<?php

namespace App\Http\Controllers;

use App\Events\DownloadedFile;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Session\Store;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Reader\Exception\ReaderNotOpenedException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;

class CustomerController extends Controller
{
    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Benchmark::dd(function () use ($request) {
            try {
                DB::beginTransaction();
                $path = $request->file('file')->store('csv');

                $customers = (new FastExcel())->import($disk->path($path), function ($customer) {
                    return [
                        'first_name' => $customer['First Name'],
                        'last_name' => $customer['Last Name'],
                        'email' => $customer['Email'],
                        'phone' => $customer['Phone'],
                    ];
                });

                foreach (array_chunk($customers->toArray(), 10000) as $customers) {
                    Customer::insertOrIgnore($customers);
                }

                $disk->delete($path);

                DB::commit();

                return to_route('home')->with('success', __('Import CSV file successfully!'));
            } catch (\Exception) {
                DB::rollBack();

                return to_route('home')->withErrors([
                    'file' => [__('Has errors when uploading file')],
                ]);
            }
        });
    }

    /**
     * @throws IOException
     * @throws WriterNotOpenedException
     * @throws UnsupportedTypeException
     * @throws InvalidArgumentException
     */
    public function exportAll()
    {
        $disk = Storage::disk('local');

        (new FastExcel(Customer::all()))->export($disk->path('customers.csv'), function (Customer $customer) {
            return [
                'Phone' => $customer->phone,
                'Email' => $customer->email,
                'First Name' => $customer->first_name,
                'Last Name' => $customer->last_name,
            ];
        });

        DownloadedFile::dispatch('customers.csv');

        return $disk->download('customers.csv');
    }

    public function deleteAll()
    {
        Customer::truncate();

        return to_route('home')->with('success', __('All customers have been deleted successfully!'));
    }
}
