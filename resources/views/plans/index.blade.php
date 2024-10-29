@extends('layouts.admin')
@section('page-title')
    {{ __('Plans') }}
@endsection
@php
    $dir = asset(Storage::url('uploads/plan'));
@endphp
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('Home') }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ __('Plans') }}</li>
@endsection
@section('title')
    <div class="d-inline-block">
        <h5 class="h4 d-inline-block text-white font-weight-bold mb-0 ">{{ __('Plans') }}</h5>
    </div>
@endsection
@section('action-btn')

    @if (Auth::user()->type == 'super admin')
    @if ((isset($admin_payments_setting['is_stripe_enabled']) && $admin_payments_setting['is_stripe_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_paypal_enabled']) && $admin_payments_setting['is_paypal_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_paystack_enabled']) && $admin_payments_setting['is_paystack_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_flutterwave_enabled']) && $admin_payments_setting['is_flutterwave_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_razorpay_enabled']) && $admin_payments_setting['is_razorpay_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_mercado_enabled']) && $admin_payments_setting['is_mercado_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_paytm_enabled']) && $admin_payments_setting['is_paytm_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_mollie_enabled']) && $admin_payments_setting['is_mollie_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_skrill_enabled']) && $admin_payments_setting['is_skrill_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_coingate_enabled']) && $admin_payments_setting['is_coingate_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_paymentwall_enabled']) && $admin_payments_setting['is_paymentwall_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_toyyibpay_enabled']) && $admin_payments_setting['is_toyyibpay_enabled'] == 'on') ||
    (isset($admin_payments_setting['is_payfast_enabled']) && $admin_payments_setting['is_payfast_enabled'] == 'on') || 
    (isset($admin_payments_setting['enable_bank']) && $admin_payments_setting['enable_bank'] == 'on') ||
    (isset($admin_payments_setting['manually_enabled']) && $admin_payments_setting['manually_enabled'] == 'on')
    )
            @can('Create Plans')
                <a class="btn btn-sm btn-icon  btn-primary me-2 text-white" data-url="{{ route('plans.create') }}" data-title="{{ __('Add Plan') }}" data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Add Plan') }}">
                    <i  data-feather="plus"></i>
                </a>
            @endcan
        @endif
    @endif
@endsection
@section('content')
    <div class="row">
        @foreach ($plans as $plan)
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12">
                <div class="plan_card">
                    <div class="card price-card price-1 wow animate__fadeInUp" data-wow-delay="0.2s" style="
                                        visibility: visible;
                                        animation-delay: 0.2s;
                                        animation-name: fadeInUp;
                                      ">
                        <div class="card-body">
                            <span class="price-badge bg-primary">{{ $plan->name }}</span>
                            @if (\Auth::user()->type !== 'super admin' && \Auth::user()->plan == $plan->id)
                                <div class="d-flex flex-row-reverse m-0 p-0 plan-active-status">
                                    <span class="d-flex align-items-center ">
                                        <i class="f-10 lh-1 fas fa-circle text-primary"></i>
                                        <span class="ms-2">{{ __('Active') }}</span>
                                    </span>
                                </div>
                            @endif
    
                            <div class="text-end">
                                <div class="">
                                    @if( \Auth::user()->type == 'super admin')
                                        @can('Edit Plans')
                                            <div class="d-inline-flex align-items-center">
                                                <a class="btn btn-sm btn-icon  bg-light-secondary me-2" data-url="{{ route('plans.edit',$plan->id) }}" data-title="{{__('Edit Plan')}}" data-ajax-popup="true" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('Edit') }}">
                                                    <i  class="ti ti-edit f-20"></i>
                                                </a>
                                            </div>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                            <h3 class="mb-4 f-w-600">
                                {{ env('CURRENCY_SYMBOL') ? env('CURRENCY_SYMBOL') : '$' }}{{ $plan->price . ' / ' . __(\App\Models\Plan::$arrDuration[$plan->duration]) }}</small>
                            </h3>
                            <div class="plan-card-detail text-center">
                                <p class="mb-0">
                                    {{ __('Trial : ') . $plan->trial_days . __(' Days') }}<br />
                                </p>
    
                                <ul class="list-unstyled d-inline-block my-4">
                                    @if ($plan->enable_custdomain == 'on')
                                        <li class="d-flex align-items-center">
                                            <span class="theme-avtar">
                                            <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Custom Domain') }}
                                        </li>
                                    @else
                                        <li class="text-danger d-flex align-items-center">
                                                <span class="theme-avtar">
                                                <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Custom Domain') }}
                                        </li>
                                    @endif
                                    @if ($plan->enable_custsubdomain == 'on')
                                        <li class="d-flex align-items-center">
                                                <span class="theme-avtar">
                                                <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Sub Domain') }}
                                        </li>
                                    @else
                                        <li class="text-danger d-flex align-items-center">
                                                    <span class="theme-avtar">
                                                <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Sub Domain') }}
                                        </li>
                                    @endif
                                    @if ($plan->shipping_method == 'on')
                                        <li class="d-flex align-items-center">
                                                <span class="theme-avtar">
                                                    <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Shipping Method') }}
                                        </li>
                                    @else
                                        <li class="text-danger d-flex align-items-center">
                                                <span class="theme-avtar">
                                                    <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Shipping Method') }}
                                        </li>
                                    @endif
    
                                    @if ($plan->additional_page == 'on')
                                        <li class="d-flex align-items-center">
                                                <span class="theme-avtar">
                                                    <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Additional Page') }}
                                        </li>
                                    @else
                                        <li class="text-danger d-flex align-items-center">
                                                <span class="theme-avtar">
                                                <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Additional Page') }}
                                        </li>
                                    @endif
                                    @if ($plan->blog == 'on')
                                        <li class="d-flex align-items-center">
                                                <span class="theme-avtar">
                                                    <i class="text-primary ti ti-circle-plus"></i></span>{{ __('Blog') }}
                                        </li>
                                    @else
                                        <li class="text-danger d-flex align-items-center">
                                                <span class="theme-avtar">
                                                    <i class="text-danger ti ti-circle-plus"></i></span>{{ __('Blog') }}
                                        </li>
    
                                    @endif
                                     <li class="d-flex align-items-center">
                                        @if ($plan->pwa_store == 'on')
                                        <span class="theme-avtar">
                                            <i class="text-primary ti ti-circle-plus"></i
                                        ></span>
                                            {{ __('Progressive Web App (PWA)') }}
                                        @else
                                            <span class="theme-avtar">
                                            <i class="text-danger ti ti-circle-plus"></i
                                            ></span>
                                        {{ __('Progressive Web App (PWA)') }}
    
                                        @endif
                                    </li>
                                </ul>
                                @if ($plan->description)
                                    <p>
                                        {{ $plan->description }}<br />
                                    </p>
                                @endif
                            </div>
                            <div class="row mb-3">
                                <div class="col-4 text-center">
                                    @if ($plan->max_products == '-1')
                                        <span class="h5 mb-0">{{ __('Lifetime') }}</span>
                                    @else
                                        <span class="h5 mb-0">{{ $plan->max_products }}</span>
                                    @endif
                                    <span class="d-block text-sm">{{ __('Products') }}</span>
                                </div>
                                <div class="col-4 text-center">
                                        <span class="h5 mb-0">
                                            @if ($plan->max_stores == '-1')
                                                <span class="h5 mb-0">{{ __('Lifetime') }}</span>
                                            @else
                                                <span class="h5 mb-0">{{ $plan->max_stores }}</span>
                                            @endif
                                        </span>
                                    <span class="d-block text-sm">{{ __('Store') }}</span>
                                </div>
                                <div class="col-4 text-center">
                                    <span class="h5 mb-0">
                                        @if ($plan->max_users == '-1')
                                            <span class="h5 mb-0">{{ __('Lifetime') }}</span>
                                        @else
                                            <span class="h5 mb-0">{{ $plan->max_users }}</span>
                                        @endif
                                    </span>
                                    <span class="d-block text-sm">{{ __('Users') }}</span>
                                </div>
                            </div>
                            <div class="row">
                                @if (\Auth::user()->type != 'super admin')
                                    @if($plan->price <= 0)
                                        <div class="col-12">
                                            <p class="server-plan font-bold text-center mx-sm-5 mt-4">
                                                {{ __('Lifetime') }}
                                            </p>
                                        </div>
                                    @elseif (\Auth::user()->plan == $plan->id && date('Y-m-d') < \Auth::user()->plan_expire_date && \Auth::user()->is_trial_done != 1)
                                        <h5 class="h6 my-4">
                                            {{ __('Expired : ') }}
                                            {{ \Auth::user()->plan_expire_date? \App\Models\Utility::dateFormat(\Auth::user()->plan_expire_date): __('Lifetime') }}
                                        </h5>
                                    @elseif(\Auth::user()->plan == $plan->id && !empty(\Auth::user()->plan_expire_date) && \Auth::user()->plan_expire_date < date('Y-m-d'))
                                        <div class="col-12">
                                            <p class="server-plan font-weight-bold text-center mx-sm-5">
                                                {{ __('Expired') }}
                                            </p>
                                        </div>
                                    @elseif(\Auth::user()->plan == $plan->id && $plan->duration == 'Lifetime')
                                        <h5 class="h6 my-4">
                                            {{ __('Expired : ') }}
                                            {{ __('Lifetime') }}
                                        </h5>
                                    @endif
                                @endif
                            
                                @if(\Auth::user()->type =='Owner' && $plan->id != \Auth::user()->plan && $plan->id !== 1)
                                    <div class="{{ $plan->id == 1 ? 'col-12' : 'col-8' }}">
                                        <a href="{{ route('stripe', \Illuminate\Support\Facades\Crypt::encrypt($plan->id)) }}"
                                            class="btn  btn-primary d-flex justify-content-center align-items-center ">{{ __('Subscribe') }}
                                                <i class="fas fa-arrow-right m-1"></i></a>
                                            <p></p>
                                    </div>
                                @endif
                                @if (\Auth::user()->type != 'super admin' && \Auth::user()->plan != $plan->id)
                                    @if ($plan->id != 1)
                                        @if (\Auth::user()->requested_plan != $plan->id)
                                            <div class="col-4">
                                                <a href="{{ route('send.request',[\Illuminate\Support\Facades\Crypt::encrypt($plan->id)]) }}"
                                                   class="btn btn-primary btn-icon"
                                                   data-title="{{ __('Send Request') }}"  data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{ __('Send Request') }}">
                                                    <span class="btn-inner--icon"><i class="fas fa-share"></i></span>
                                                </a>
                                            </div>
                                        @else
                                            <div class="col-4">
                                                <a href="{{  route('request.cancel',\Auth::user()->id) }} }}"
                                                   class="btn btn-icon m-1 btn-danger"
                                                   data-title="{{ __('Cancle Request') }}"  data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{ __('Cancel Request') }}">
                                                    <span class="btn-inner--icon"><i class="fas fa-times"></i></span>
                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <h5></h5>
                    <div class="table-responsive">
                        <table class="table mb-0 dataTable ">
                            <thead>
                            <tr>
                                <th> {{ __('Order Id') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Plan Name') }}</th>
                                <th> {{ __('Price') }}</th>
                                <th> {{ __('Payment Type') }}</th>
                                <th> {{ __('Status') }}</th>
                                <th> {{ __('Coupon') }}</th>
                                <th> {{ __('Invoice') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td>{{ $order->order_id }}</td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td>{{ $order->user_name }}</td>
                                    <td>{{ $order->plan_name }}</td>
                                    <td>{{ env('CURRENCY_SYMBOL') . $order->price }}</td>
                                    <td>{{ $order->payment_type }}</td>
                                    <td>
                                        @if ($order->payment_status == 'succeeded')
                                            <i class="mdi mdi-circle text-primary"></i>
                                            {{ ucfirst($order->payment_status) }}
                                        @else
                                            <i class="mdi mdi-circle text-danger"></i>
                                            {{ ucfirst($order->payment_status) }}
                                        @endif
                                    </td>

                                    <td>{{ !empty($order->total_coupon_used)? (!empty($order->total_coupon_used->coupon_detail)? $order->total_coupon_used->coupon_detail->code: '-'): '-' }}
                                    </td>

                                    <td class="text-center">
                                        @if ($order->receipt != 'free coupon' && $order->payment_type == 'STRIPE')
                                            <a href="{{ $order->receipt }}" title="Invoice" target="_blank"
                                               class=""><i class="fas fa-file-invoice"></i> </a>
                                        @elseif ($order->payment_type == 'Bank Transfer')
                                            <a href="{{ \App\Models\Utility::get_file($order->receipt) }}"  title="Invoice" target="_blank"
                                                class="" download="">
                                                <i class="fas fa-file-invoice"></i>
                                            </a>
                                        @elseif($order->receipt == 'free coupon')
                                            <p>{{ __('Used') . '100 %' . __('discount coupon code.') }}</p>
                                        @elseif($order->payment_type == 'Manually')
                                            <p>{{ __('Manually plan upgraded by super admin') }}</p>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->payment_status == 'pending' && $order->payment_type == 'Bank Transfer')
                                            <div class="action-btn bg-warning ms-2">
                                                <a  class="mx-3 btn btn-sm  align-items-center"
                                                    data-url="{{ route('bank_transfer.show',$order->id) }}"
                                                    data-ajax-popup="true" data-size="lg" data-bs-toggle="tooltip" title=""
                                                    data-title="{{ __('Payment Status') }}"
                                                    data-bs-original-title="{{ __('Payment Status') }}">
                                                    <i class="ti ti-caret-right text-white"></i>
                                                </a>
                                            </div>

                                        @endif
                                       
                                        <a class="bs-pass-para btn btn-sm btn-icon bg-light-secondary" href="#"
                                            data-title="{{ __('Delete Order') }}"
                                            data-confirm="{{ __('Are You Sure?') }}"
                                            data-text="{{ __('This action can not be undone. Do you want to continue?') }}"
                                            data-confirm-yes="delete-form-{{ $order->id }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ __('Delete') }}">
                                            <i class="ti ti-trash f-20"></i>
                                        </a>
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['planorder.destroy', $order->id], 'id' => 'delete-form-' . $order->id]) !!}
                                        {!! Form::close() !!}
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

@push('scripts')
    <script>
        $(document).ready(function() {
            var tohref = '';
            @if (Auth::user()->is_register_trial == 1)
                tohref = $('#trial_{{ Auth::user()->interested_plan_id }}').attr("href");
            @elseif(Auth::user()->interested_plan_id != 0)
                tohref = $('#interested_plan_{{ Auth::user()->interested_plan_id }}').attr("href");
            @endif

            if (tohref != '') {
                window.location = tohref;
            }
        });
    </script>
@endpush
