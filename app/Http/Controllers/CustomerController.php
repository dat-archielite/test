<?php

namespace App\Http\Controllers;

use App\Events\DownloadedFile;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends Controller
{
    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        $disk = Storage::disk('local');

        $path = $request->file('file')->store('csv');

        $customers = (new FastExcel())->import($disk->path($path), function ($customer) {
            return [
                'first_name' => $customer['First Name'],
                'last_name' => $customer['Last Name'],
                'email' => $customer['Email'],
                'phone' => $customer['Phone'],
            ];
        });

//        foreach ($customers as $key => $customer) {
//            $validator = Validator::make($customer, [
//                'first_name' => 'required|min:3|max:50',
//                'last_name' => 'required|min:3|max:50',
//                'email' => 'required|email',
//                'phone' => 'required|min:10|max:20',
//            ]);
//
//            if ($validator->fails()) {
//                $validator->errors()->add($key, $validator->getMessageBag()->first());
//            }
//        }

        try {
            DB::beginTransaction();

            foreach (array_chunk($customers->toArray(), 10000) as $customers) {
                Customer::insert($customers);
            }

            $disk->delete($path);

            DB::commit();

            return to_route('home')->with('success', __('Import CSV file successfully!'));
        } catch (Exception) {
            DB::rollBack();

            return to_route('home')->withErrors(['file' => __('Has errors when uploading file')]);
        }
    }

    public function export(): BinaryFileResponse
    {
        $disk = Storage::disk('local');

        $customers = new FastExcel(Customer::select(['phone', 'email', 'first_name', 'last_name'])->get());

        $customers->export($disk->path('customers.csv'), function (Customer $customer) {
            return [
                'Phone' => $customer->phone,
                'Email' => $customer->email,
                'First Name' => $customer->first_name,
                'Last Name' => $customer->last_name,
            ];
        });

        DownloadedFile::dispatch('customers.csv');

        return response()->download($disk->path('customers.csv'))->deleteFileAfterSend();
    }

    public function truncate(): RedirectResponse
    {
        Customer::truncate();

        return to_route('home')->with('success', __('All customers have been deleted successfully!'));
    }
}
