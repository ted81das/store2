@extends('storefront.layout.theme1')
@section('page-title')
    {{ __('Product Details') }}
@endsection
@php
$imgpath=\App\Models\Utility::get_file('uploads/product_image/');
$proimg=\App\Models\Utility::get_file('uploads/is_cover_image/');
@endphp
@section('content')
<div class="wrapper">
    <section class="product-detail-section padding-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="product-slider">
                        <div class="pdp-det-slider">
                            @foreach ($products_image as $key => $productss)
                                <div class="pdp-main-itm {{ $key == 0 ? 'active' : '' }}"  data-slide-number="{{ $key }}">
                                    @if (!empty($products_image[$key]->product_images))
                                    <div class="pdp-itm-inner">
                                        <img src="{{ $imgpath. $products_image[$key]->product_images }}"  data-remote="{{ $imgpath. $products_image[$key]->product_images }}" data-type="image" data-toggle="lightbox" data-gallery="example-gallery" alt="product">
                                    </div>    
                                    @else
                                    <div class="pdp-itm-inner">
                                        <img src="{{ asset(Storage::url('uploads/product_image/default.jpg')) }}"  data-remote="{{ $imgpath. $products_image[$key]->product_images }}" data-type="image" data-toggle="lightbox" data-gallery="example-gallery" alt="product">
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="pdp-thumb-slider">
                            @foreach ($products_image as $key => $productss)
                                <div class="pdp-thumb-itm" data-slide-to="{{ $key }}">
                                    <div class="pdp-thumb-inner">
                                        @if (!empty($products_image[$key]->product_images))
                                            <img src="{{ $imgpath. $products_image[$key]->product_images}}" alt="...">
                                        @else
                                            <img src="{{ asset(Storage::url('uploads/product_image/default.jpg')) }}" alt="...">
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="review-box-2">
                        <h5>{{ __('Reviews') }}:
                            <b>{{ $avg_rating }}/5</b>
                            <span> ({{ __('reviews') }}) </span>
                        </h5>
                        <div class="review-star">
                            <div class="product-rating">
                                @if ($store_setting->enable_rating == 'on')
                                    @for ($i = 1; $i <= 5; $i++)
                                        @php
                                            $icon = 'fa-star';
                                            $color = '';
                                            $newVal1 = $i - 0.5;
                                            if ($avg_rating < $i && $avg_rating >= $newVal1) {
                                                $icon = 'fa-star-half-alt';
                                            }
                                            if ($avg_rating >= $newVal1) {
                                                $color = 'text-primary';
                                            }
                                        @endphp
                                        <i class="star fas {{ $icon . ' ' . $color }}"></i>
                                    @endfor
                                @endif
                            </div>
                            @if (Auth::guard('customers')->check())
                                {{--  <a href="#" data-size="md" class="modal-target"  data-modal="Myaccount" data-url="{{ route('rating', [$store->slug, $products->id]) }}"><i class="fas fa-plus"></i></a>  --}}
                                <a href="#"
                                        class="btn btn-sm btn-primary btn-icon-only rounded-circle float-right text-white"
                                        data-size="lg" data-toggle="modal"
                                        data-url="{{ route('rating', [$store->slug, $products->id]) }}"
                                        data-ajax-popup="true" data-title="{{ __('Create New Rating') }}">
                                        <i class="fas fa-plus"></i>
                                    </a>
                            @endif
                        </div>
                    </div>
                    <div class="review-scroll">
                        @foreach ($product_ratings as $product_key => $product_rating)
                        @if ($product_rating->rating_view == 'on')
                  
                            <div class="review-top d-flex">
                                <p>{{ $product_rating->name }} :</p>
                                <span>{{ $product_rating->title }}</span>
                            </div>
                            <div class="review-box-bottom">
                                <div class="rating-pdp">
                                    <span>
                                        @for ($i = 0; $i < 5; $i++)
                                        <i class="star fas fa-star {{ $product_rating->ratting > $i ? 'text-primary' : '' }}"></i>
                                        @endfor
                                    </span>
                                    <p>{{ $avg_rating }}/5 ({{ $user_count }} {{ __('reviews') }})</p>
                                </div>
                                <p>{{ $product_rating->description }}</p>
                            </div>
                        @endif
                    @endforeach
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    <div class="pdp-summery">
                        <div class="customer-product-review">
                            <div class="product-rating d-flex align-items-center">
                                <div class="rating-pdp">
                                    @if ($store_setting->enable_rating == 'on')
                                        <span>
                                            @for ($i = 1; $i <= 5; $i++)
                                                @php
                                                    $icon = 'fa-star';
                                                    $color = '';
                                                    $newVal1 = $i - 0.5;
                                                    if ($avg_rating < $i && $avg_rating >= $newVal1) {
                                                        $icon = 'fa-star-half-alt';
                                                    }
                                                    if ($avg_rating >= $newVal1) {
                                                        $color = 'text-primary';
                                                    }
                                                @endphp
                                                <i class="star fas {{ $icon . ' ' . $color }}"></i>
                                            @endfor
                                        </span>
                                        <p>{{ $avg_rating }}/5 ({{ $user_count }} {{ __('reviews') }})</p>
                                    @endif
                                </div>
                                <div class="wish-btn-wrap">
                                    @if (Auth::guard('customers')->check())
                                        @if (!empty($wishlist) && isset($wishlist[$products->id]['product_id']))
                                            @if ($wishlist[$products->id]['product_id'] != $products->id)
                                                <a href="#" class="btn-icon add_to_wishlist wishlist_{{ $products->id }}" data-id="{{ $products->id }}">
                                                    <i class="far fa-heart"></i>
                                                </a>
                                            @else
                                                <a href="#" class="btn-icon">
                                                    <i class="fas fa-heart"></i>
                                                </a>
                                            @endif
                                        @else
                                            <a href="#" class="btn-icon add_to_wishlist wishlist_{{ $products->id }}" data-id="{{ $products->id }}">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        @endif
                                    @else
                                        <a href="#" class="btn-icon add_to_wishlist wishlist_{{ $products->id }}" data-id="{{ $products->id }}">
                                            <i class="far fa-heart"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <h2>{{ $products->name }}</h2>
                            <p>{!! $products->detail !!}
                            </p>
                            
                            @if ($products->enable_product_variant == 'on')
                                <input type="hidden" id="product_id" value="{{ $products->id }}">
                                <input type="hidden" id="variant_id" value="">
                                <input type="hidden" id="variant_qty" value="">
                                <div class="p-color mt-3">
                                    <p class="mb-0">{{__('VARIATION:')}}</p>
                                    @foreach ($product_variant_names as $key => $variant)
                                        <div class="col-sm-6 mb-4 mb-sm-0">
                                            <p class="d-block h6 mb-0">
                                            <p class="mb-0 variant_name">{{ empty($variant->variant_name) ? $variant['variant_name'] :  $variant->variant_name}}</p>
        
                                            <select name="product[{{ $key }}]"  id="pro_variants_name"
                                                class="form-control variant-selection  pro_variants_name{{ $key }} pro_variants_name variant_loop variant_val">
                                                {{-- <option value="">{{ __('Select') }}</option> --}}
                                                @foreach ($variant->variant_options ?? $variant['variant_options']  as $key => $values)
                                                    <option value="{{ $values }}" id="{{ $values }}_varient_option">{{ $values }}</option>
                                                @endforeach
                                            </select>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                             @endif
                            <div class="price product-price">
                                <span class="variation_price">
                                    @if ($products->enable_product_variant == 'on')
                                     {{ \App\Models\Utility::priceFormat(0) }} 
                                    @else
                                       {{ \App\Models\Utility::priceFormat($products->price) }}
                                    @endif
                                </span>
                                <del>{{ \App\Models\Utility::priceFormat($products->last_price) }}</del>
                            </div>
                            <span class=" mb-0 text-danger product-price-error"></span>
                        </div>
                        <div class="addcart-btn">
                            <a href="#" class="btn add_to_cart" data-id="{{ $products->id }}">{{ __('Add to cart') }}
                                <i class="fas fa-shopping-basket"></i>
                            </a>
                            <p>{{ __('Category') }}:<span>  {{ $products->product_category() }}</span></p>
                            <p>{{ __('ID') }}:<span> {{ $products->SKU }}</span></p>
                        </div>
                        <ul class="product-variables">
                            @if (!empty($products->custom_field_1) && !empty($products->custom_value_1))
                                <li>
                                    <span class="var-left"><b>{{ $products->custom_field_1 }} :</b></span>
                                    <span class="var-right">{{ $products->custom_value_1 }}</span>
                                </li>
                            @endif
                            @if (!empty($products->custom_field_2) && !empty($products->custom_value_2))
                                <li>
                                    <span class="var-left"><b>{{ $products->custom_field_2 }} : </b> </span>
                                    <span class="var-right">{{ $products->custom_value_2 }}</span>
                                </li>
                            @endif
                            @if (!empty($products->custom_field_3) && !empty($products->custom_value_3))
                                <li>
                                    <span class="var-left"><b>{{ $products->custom_field_3 }} :</b> </span>
                                    <span class="var-right"> {{ $products->custom_value_3 }}</span>
                                </li>
                            @endif
                            @if (!empty($products->custom_field_4) && !empty($products->custom_value_4))
                                <li>
                                    <span class="var-left"><b>{{ $products->custom_field_4 }} : </b> </span>
                                    <span class="var-right"> {{ $products->custom_value_4 }}</span>
                                </li>
                            @endif
                        </ul>
                        <div class="description-accordion">
                            @if (!empty($products->description))
                                <div class="set has-children">
                                    <a href="javascript:;" class="acnav-label">
                                        <span>{{ __('DESCRIPTION') }}</span> 
                                    </a>
                                    <div class="acnav-list">
                                    <p> {!! $products->description !!}</p>
                                    </div>
                                </div>
                            @endif
                            @if (!empty($products->specification))
                                <div class="set has-children">
                                    <a href="javascript:;" class="acnav-label">
                                        <span>{{ __('SPECIFICATION') }}</span> 
                                    </a>
                                    <div class="acnav-list">
                                    <p>{!! $products->specification !!}</p>
                                    </div>
                                </div>
                            @endif
                            @if (!empty($products->detail))
                                <div class="set has-children">
                                    <a href="javascript:;" class="acnav-label">
                                        <span>{{ __('DETAILS') }}</span> 
                                    </a>
                                    <div class="acnav-list">
                                    <p> {!! $products->detail !!}</p>
                                    </div>
                                </div>
                            @endif
                            @if(!empty($products->attachment))
                                <div class="set has-children">
                                    <a href="javascript:;" class="acnav-label">
                                        <span> {{__('Download instruction ')}}</span>
                                    </a>
                                    <div class="acnav-list">
                                        <div class="btn">
                                            <a href="{{asset(Storage::url('uploads/is_cover_image/'.$products->attachment))}}" class="btn-instruction" download="{{$products->attachment}}">
                                                <span class="btn-inner--icon">
                                                    <i class="fas fa-shopping-basket"></i>
                                                </span>
                                                {{__('Download instruction .pdf')}}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="related-product-section padding-top">
        <div class="container">
            <div class="section-title">
                <h2>{{ __('Related products') }}</h2>
            </div>
            <div class="row product-row">
                @foreach ($all_products as $key => $product)
                    @if ($product->id != $products->id)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-12">
                            <div class="product-card">
                                <div class="card-img">
                                    <a href="{{ route('store.product.product_view', [$store->slug, $product->id]) }}">
                                        @if (!empty($product->is_cover) )
                                            <img alt="Image placeholder" src="{{ $proimg. $product->is_cover}}">
                                        @else
                                            <img alt="Image placeholder" src="{{ asset(Storage::url('uploads/is_cover_image/default.jpg')) }}">
                                        @endif
                                    </a>
                                    {{--  <div class="heart-icon">  --}}
                                        @if(!empty($wishlist) && isset($wishlist[$product->id]['product_id']))
                                            @if($wishlist[$product->id]['product_id'] != $product->id)
                                                <a  class="heart-icon action-item wishlist-icon bg-light-gray add_to_wishlist wishlist_{{$product->id}}" data-id="{{$product->id}}">
                                                    <i class="far fa-heart"></i>
                                                </a>
                                            @else
                                                <a class="heart-icon action-item wishlist-icon bg-light-gray" data-id="{{$product->id}}" disabled>
                                                    <i class="fas fa-heart"></i>
                                                </a>
                                            @endif
                                        @else
                                            <a class="heart-icon action-item wishlist-icon bg-light-gray add_to_wishlist wishlist_{{$product->id}}" data-id="{{$product->id}}">
                                                <i class="far fa-heart"></i>
                                            </a>
                                        @endif
                                    {{--  </div>  --}}
                                </div>
                                <div class="card-content">
                                    <div class="rating">
                                        @if ($store->enable_rating == 'on')
                                            @for ($i = 1; $i <= 5; $i++)
                                                @php
                                                    $icon = 'fa-star';
                                                    $color = '';
                                                    $newVal1 = $i - 0.5;
                                                    if ($product->product_rating() < $i && $product->product_rating() >= $newVal1) {
                                                        $icon = 'fa-star-half-alt';
                                                    }
                                                    if ($product->product_rating() >= $newVal1) {
                                                        $color = 'text-warning';
                                                    }
                                                    
                                                @endphp
                                                <i class="star fas {{ $icon . ' ' . $color }}"></i>
                                            @endfor
                                        @endif
                                    </div>
                                    <h6>
                                        <a href="{{ route('store.product.product_view', [$store->slug, $product->id]) }}">{{ $product->name }}</a>
                                    </h6>
                                <p>{{ __('Category') }}: {{ $product->product_category() }}</p>
                                    
                                    <div class="last-btn">
                                        <div class="price">
                                            @if ($product->enable_product_variant == 'on')
                                                <ins>{{ __('In variant') }}</ins>
                                            @else
                                                <ins>{{ \App\Models\Utility::priceFormat($product->price) }}</ins> 
                                            @endif
                                           
                                        </div>
                                        @if ($product->enable_product_variant == 'on')
                                        <a href="{{ route('store.product.product_view', [$store->slug, $product->id]) }}" class="cart-btn add_to_cart" data-id="{{$product->id}}"> <i class="fas fa-shopping-basket"></i></a>
                                        @else
                                        <a href="{{ route('store.product.product_view', [$store->slug, $product->id]) }}" class="cart-btn add_to_cart" data-id="{{$product->id}}"> <i class="fas fa-shopping-basket"></i></a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>
</div>

@endsection

@push('script-page')
    <script>
        $(document).ready(function() {
            set_variant_price();
        });

        $(document).on('click', '.add_to_wishlist', function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            $.ajax({
                type: "POST",
                url: '{{ route('store.addtowishlist', [$store->slug, '__product_id']) }}'.replace(
                    '__product_id', id),
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    if (response.status == "Success") {
                        show_toastr('Success', response.message, 'success');
                        $('.wishlist_' + response.id).removeClass('add_to_wishlist');
                        $('.wishlist_' + response.id).html('<i class="fas fa-heart"></i>');
                        $('.wishlist_count').html(response.count);
                    } else {
                        show_toastr('Error', response.error, 'error');
                    }
                },
                error: function(result) {}
            });
        });
        $(".add_to_cart").click(function(e) {
            e.preventDefault();
            var id = $(this).attr('data-id');
            var variants = [];
            $(".variant-selection").each(function(index, element) {
                variants.push(element.value);
            });

            if (jQuery.inArray('', variants) != -1) {
                show_toastr('Error', "{{ __('Please select all option.') }}", 'error');
                return false;
            }
            var variation_ids = $('#variant_id').val();

            $.ajax({
                url: '{{ route('user.addToCart', ['__product_id', $store->slug, 'variation_id']) }}'.replace(
                    '__product_id', id).replace('variation_id', variation_ids ?? 0),
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    variants: variants.join(' : '),
                },
                success: function(response) {
                    if (response.status == "Success") {
                        show_toastr('Success', response.success, 'success');
                        $("#shoping_counts").html(response.item_count);
                    } else {
                        show_toastr('Error', response.error, 'error');
                    }
                },
                error: function(result) {
                    console.log('error');
                }
            });
        });

        $(document).on('change', '#pro_variants_name', function() {
            set_variant_price();
        });

        function set_variant_price() {
            var variants = [];
            $(".variant-selection").each(function(index, element) {
                variants.push(element.value);
            });

            if (variants.length > 0) {
                $.ajax({
                    url: '{{ route('get.products.variant.quantity') }}',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        variants: variants.join(' : '),
                        product_id: $('#product_id').val()
                    },
                    
                    success: function(data) {
                        $('.product-price-error').hide();
                        $('.product-price').show();

                        $('.variation_price').html(data.price);
                        $('#variant_id').val(data.variant_id);
                        $('#variant_qty').val(data.quantity);


                        var variant_message_array = [];
                        $( ".variant_loop" ).each(function( index ) {
                                var variant_name = $(this).prev().text();
                                var variant_val = $(this).val();
                                variant_message_array.push(variant_val+" "+variant_name);
                        });
                        var variant_message = variant_message_array.join(" and ");

                        if(data.variant_id == 0) {
                            $('.add_to_cart').hide();

                            $('.product-price').hide();
                            $('.product-price-error').show();
                            var message =  '<span class=" mb-0 text-danger">This product is not available with '+variant_message+'.</span>';
                            $('.product-price-error').html(message);
                        }else{
                            $('.add_to_cart').show();
                        }
                    }
                });
            }
        }
    </script>
@endpush