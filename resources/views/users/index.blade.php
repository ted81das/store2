@extends('layouts.admin')
@section('page-title')
    {{ __('Product') }}
@endsection
@php
$profile=\App\Models\Utility::get_file('uploads/profile/');
@endphp
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block text-white font-weight-bold mb-0 ">{{ __('users') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Users') }}</li>
@endsection
@section('action-btn')
@can('Create User')
    <a class="btn btn-sm btn-icon text-light btn-primary me-2" data-url="{{ route('users.create') }}" data-title="{{ __('Add User') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create') }}">
        <i  data-feather="plus"></i>
    </a>
@endcan
@endsection
@section('filter')
@endsection
@php
$logo=\App\Models\Utility::get_file('uploads/profile/');
@endphp
@section('content')
    <div class="row">
        @foreach ($users as $user)
            <div class="col-lg-3 col-sm-6 col-md-6">
                <div class="card text-center">
                    <div class="card-header border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <div class="badge p-2 px-3 rounded bg-primary">{{ ucfirst($user->type) }}</div>
                            </h6>
                        </div>
                        @if (Gate::check('Edit User') || Gate::check('Delete User'))
                            <div class="card-header-right">
                                <div class="btn-group card-option">
                                    <button type="button" class="btn dropdown-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="feather icon-more-vertical"></i>
                                    </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('Edit User')
                                        <a href="#" class="dropdown-item" data-url="{{ route('users.edit', $user->id) }}" data-size="md" data-ajax-popup="true" data-title="{{ __('Update User') }}">
                                            <i class="ti ti-edit"></i>
                                            <span class="ms-2">{{ __('Edit') }}</span>
                                        </a>
                                    @endcan
                                    @can('Reset Password')
                                        <a href="#" class="dropdown-item" data-url="{{ route('users.reset', \Crypt::encrypt($user->id)) }}" data-ajax-popup="true" data-size="md" data-title="{{ __('Change Password') }}">
                                            <i class="ti ti-key"></i>
                                            <span class="ms-2">{{ __('Reset Password') }}</span>
                                        </a>
                                    @endcan
                                    @can('Delete User')
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['users.destroy', $user->id], 'id' => 'delete-form-' . $user->id]) !!}
                                        <a href="#" class="bs-pass-para dropdown-item"
                                            data-confirm="{{ __('Are You Sure?') }}"
                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                            data-confirm-yes="delete-form-{{ $user->id }}"
                                            title="{{ __('Delete') }}" data-bs-toggle="tooltip"
                                            data-bs-placement="top"><i class="ti ti-trash"></i><span
                                                class="ms-2">{{ __('Delete') }}</span></a>
                                        {!! Form::close() !!}
                                    @endcan
                                </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="avatar">
                            <a href="{{ !empty($user->avatar) ?($profile . $user->avatar) :  $logo."avatar.png" }}" target="_blank">
                                <img src="{{ !empty($user->avatar) ? ($profile . $user->avatar) :  $logo."avatar.png" }}" class="rounded-circle" alt="">
                            </a>
                        </div>
                        <h4 class="mt-2 text-primary">{{ $user->name }}</h4>
                        <small class="">{{ $user->email }}</small>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-md-3">
            @can('Create User')
                <a class="btn-addnew-project" data-url="{{ route('users.create') }}" data-title="{{ __('Add User') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Create') }}"><i class="ti ti-plus text-white"></i>
                    <div class="bg-primary proj-add-icon">
                        <i class="ti ti-plus"></i>
                    </div>
                    <h6 class="mt-4 mb-2">{{ __('New User') }}</h6>
                    <p class="text-muted text-center">{{ __('Click here to add New User') }}</p>
                </a>
            @endcan
        </div>
    </div>

@endsection
