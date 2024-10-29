@extends('layouts.admin')
@section('page-title')
    {{ __('Store') }}
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block text-white font-weight-bold mb-2">{{ __('Store') }}</h5>
    </div>
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Store') }}</li>
@endsection
@section('action-btn')
    <div class="pr-2">
        <a href="{{ route('store.subDomain') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
            data-bs-placement="top" title="{{ __('Sub Domain') }}">{{ __('Sub Domain') }}</a>

        <a href="{{ route('store.customDomain') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
            data-bs-placement="top" title="{{ __('Custom Domain') }}">{{ __('Custom Domain') }}</a>

        <a href="{{ route('store.grid') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
            data-bs-placement="top" title="{{ __('Grid View') }}"><i class="ti ti-grid-dots"></i></a>
        @can('Create Store')
            <a href="#" data-size="lg" data-url="{{ route('store-resource.create') }}" data-ajax-popup="true"
                data-title="{{ __('Create New Store') }}" class="btn btn-sm btn-primary btn-icon m-1" data-bs-toggle="tooltip"
                data-bs-placement="top" title="{{ __('Create') }}"><i class="ti ti-plus"></i></a>
        @endcan
    </div>
@endsection
@section('filter')
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table mb-0 dataTable">
                            <thead>
                                <tr>
                                    <th>{{ __('User Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Stores') }}</th>
                                    <th>{{ __('Plan') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th>{{ __('Store Display') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $usr)
                                    <tr>
                                        <td>{{ $usr->name }}</td>
                                        <td>{{ $usr->email }}</td>
                                        <td>{{ $usr->stores->count() }}</td>
                                        <td>{{ !empty($usr->currentPlan->name) ? $usr->currentPlan->name : '-' }}</td>
                                        <td>{{ \App\Models\Utility::dateFormat($usr->created_at) }}</td>
                                        <td>
                                            <div class="form-switch disabled-form-switch">
                                               
                                                    <a href="#" data-size="md"
                                                        data-url="{{ route('store-resource.edit.display', $usr->id) }}"
                                                        data-ajax-popup="true" class="action-item"
                                                        data-title="{{ __('Are You Sure?') }}" data-toggle="tooltip" title="Edit"
                                                        data-original-title="{{ $usr->store_display == 1 ? 'Store disable' : 'Store enable' }}">
                                                        <input type="checkbox" class="form-check-input" disabled="disabled"
                                                            name="store_display" id="{{ $usr->id }}"
                                                            {{ $usr->store_display == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="{{ $usr->id }}"></label>
                                                    </a>
                                                
                                            </div>
                                        </td>
                                        <td class="Action">
                                            <div class="d-flex">
                                                @can('Edit Store')
                                                    <a href="#!" class="btn btn-sm btn-icon  bg-light-secondary me-2" data-url="{{ route('store-resource.edit', $usr->id) }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Edit') }}" data-title="{{ __('Edit Store') }}">
                                                        <i  class="ti ti-edit f-20"></i>
                                                    </a>
                                                @endcan
                                                @can('Upgrade Plans')
                                                    <a href="#!" data-url="{{ route('plan.upgrade', $usr->id) }}" class="btn btn-sm btn-icon  bg-light-secondary me-2" data-tooltip="Edit" data-ajax-popup="true" data-title="{{ __('Upgrade Plan') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Upgrade Plan') }}" data-tooltip="View">
                                                        <i   class="ti ti-trophy f-20"></i>
                                                    </a>
                                                @endcan
                                                @can('Delete Store')
                                                    <a class="bs-pass-para btn btn-sm btn-icon bg-light-secondary me-2" href="#"
                                                        data-title="{{ __('Delete Lead') }}"
                                                        data-confirm="{{ __('Are You Sure?') }}"
                                                        data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                                        data-confirm-yes="delete-form-{{ $usr->id }}"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="{{ __('Delete') }}">
                                                        <i class="ti ti-trash f-20"></i>
                                                    </a>
                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['store-resource.destroy', $usr->id], 'id' => 'delete-form-' . $usr->id]) !!}
                                                    {!! Form::close() !!}
                                                @endcan
                                                @can('Reset Password')
                                                    <a href="#!" data-url="{{ route('user.reset', \Crypt::encrypt($usr->id)) }}" class="btn btn-sm btn-icon  bg-light-secondary me-2" data-tooltip="Edit" data-ajax-popup="true" data-title="{{ __('Reset Password') }}" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Reset Password') }}" data-tooltip="View">
                                                        <i   class="fas fa-key f-20"></i>
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
