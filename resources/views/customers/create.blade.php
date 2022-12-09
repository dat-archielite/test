@extends('layouts.master')

@section('content')
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>{{ __('Import Customers') }}</span>
            <a href="{{ route('home') }}">{{ __('Go to home') }}</a>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('customers.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">{{ __('CSV file') }}</label>
                    <input type="file" name="file" id="file" @class(['form-control', 'is-invalid' => $errors->has('file')])>
                    @error('file')
                        <div class="invalid-feedback">
                        {{ $message }}
                        </div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
            </form>
        </div>
    </div>
@endsection
