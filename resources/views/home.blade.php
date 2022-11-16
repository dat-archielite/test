@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>{{ __('Customers') }}</span>
            <div class="d-flex">
                <a href="{{ route('customers.create') }}" class="btn btn-primary me-2">{{ __('Import Customer') }}</a>
                @if($customers->total())
                    <form action="{{ route('customers.export-all') }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-success me-2">{{ __('Export all') }}</button>
                    </form>
                    <form action="{{ route('customers.delete-all') }}" method="post">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('Delete all') }}</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>{{ __('ID') }}</th>
                        <th>{{ __('First name') }}</th>
                        <th>{{ __('Last name') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Phone') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->first_name }}</td>
                            <td>{{ $customer->last_name }}</td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ __('No data to display') }}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $customers->links() }}
        </div>
    </div>
@endsection
