<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\Plan;
use App\Models\Store;
use App\Models\User;
use App\Models\Utility;
use App\Models\PixelFields;
use Artisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function __construct()
    {
        if (Auth::check()) {
            $user = Auth::user()->current_store;
            $store = Store::where('id', $user)->first();
            \App::setLocale(isset($store->lang) ? $store->lang : 'en');
        }
    }
    public function index()
    {
        if(\Auth::user()->can('Manage Settings')){
            $settings = Utility::settings();

            if (Auth::user()->type == 'super admin') {
                $admin_payment_setting = Utility::getAdminPaymentSetting();

                return view('settings.index', compact('settings', 'admin_payment_setting'));
            } else {
                $user = Auth::user();
                $store_settings = Store::where('id', $user->current_store)->first();

                if ($store_settings) {
                    $store_payment_setting = Utility::getPaymentSetting();

                    $serverName = str_replace(
                        [
                            'http://',
                            'https://',
                        ], '', env('APP_URL')
                    );

                    $serverIp = gethostbyname($serverName);
                    if ($serverIp == $_SERVER['SERVER_ADDR']) {
                        $serverIp;
                    } else {
                        $serverIp = request()->server('SERVER_ADDR');
                    }

                    $plan = Plan::where('id', $user->plan)->first();
                    $app_url = trim(env('APP_URL'), '/');
                    $store_settings['store_url'] = $app_url . '/store/' . $store_settings['slug'];

                    if (!empty($store_settings->enable_subdomain) && $store_settings->enable_subdomain == 'on') {
                        // Remove the http://, www., and slash(/) from the URL
                        $input = env('APP_URL');

                        // If URI is like, eg. www.way2tutorial.com/
                        $input = trim($input, '/');
                        // If not have http:// or https:// then prepend it
                        if (!preg_match('#^http(s)?://#', $input)) {
                            $input = 'http://' . $input;
                        }

                        $urlParts = parse_url($input);

                        // Remove www.
                        $subdomain_name = preg_replace('/^www\./', '', $urlParts['host']);
                        // Output way2tutorial.com
                    } else {
                        $subdomain_name = str_replace(
                            [
                                'http://',
                                'https://',
                            ], '', env('APP_URL')
                        );
                    }
                                    
                    try {
                        $pwa_data = \File::get(storage_path('uploads/customer_app/store_' . $store_settings->id . '/manifest.json'));
                        $pwa_data = json_decode($pwa_data);

                    } catch (\Throwable$th) {
                        $pwa_data = '';
                    }
                    $PixelFields = PixelFields::where('store_id' , $user->current_store)->orderBy('id')->get();
                    return view('settings.index', compact('settings', 'store_settings', 'plan', 'serverIp', 'subdomain_name', 'store_payment_setting','pwa_data','PixelFields'));
                } else {
                    return redirect()->back()->with('error', __('Permission denied.'));
                }
            }
        }
        else{
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }
  
    public function saveBusinessSettings(Request $request)
    {
        // dd($request->all());
        $user = \Auth::user();

        if (\Auth::user()->type == 'super admin') {
            // if ($request->logo_dark) {
            //     $request->validate(
            //         [
            //             'logo_dark' => 'image|mimes:png|max:20480',
            //         ]
            //     );
            //     $logoName = 'logo-dark.png';

            //     $path = $request->file('logo_dark')->storeAs('uploads/logo/', $logoName);
            //     \DB::insert(
            //         'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
            //             $logoName,
            //             'logo_dark',
            //             $user->creatorId(),
            //             '0',
            //         ]
            //     );

            // }
            // if ($request->logo_light) {
            //     $request->validate(
            //         [
            //             'logo_light' => 'image|mimes:png|max:20480',
            //         ]
            //     );
            //     $lightlogoName = 'logo-light.png';

            //     $path = $request->file('logo_light')->storeAs('uploads/logo/', $lightlogoName);
            //     \DB::insert(
            //         'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
            //             $lightlogoName,
            //             'logo_light',
            //             $user->creatorId(),
            //             '0',
            //         ]
            //     );
            // }

            // if ($request->favicon) {
            //     $request->validate(
            //         [
            //             'favicon' => 'image|mimes:png|max:20480',
            //         ]
            //     );
            //     $favicon = 'favicon.png';
            //     $path = $request->file('favicon')->storeAs('uploads/logo/', $favicon);

            //     \DB::insert(
            //         'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
            //             $favicon,
            //             'favicon',
            //             \Auth::user()->creatorId(),
            //             '0',

            //         ]
            //     );
            // }

            if($request->logo_dark)
            {
                $logoName = 'logo-dark.png';
                $dir = 'uploads/logo/';

                $validation =[
                    'mimes:'.'png',
                    'max:'.'20480',
                ];
                $path = Utility::upload_file($request,'logo_dark',$logoName,$dir,$validation);
                if($path['flag'] == 1){

                $logo_dark = $path['url'];

                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }

            }

            if($request->logo_light)
            {

                $logoName = 'logo-light.png';
                $dir = 'uploads/logo/';
                $validation =[
                    'mimes:'.'png',
                    'max:'.'20480',
                ];
                $path = Utility::upload_file($request,'logo_light',$logoName,$dir,$validation);
                // dd($path);
                if($path['flag'] == 1){
                    $logo_light = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }
            }


            if($request->favicon)
            {
                $favicon = 'favicon.png';
                $dir = 'uploads/logo/';
                $validation =[
                    'mimes:'.'png',
                    'max:'.'20480',
                ];


                $path = Utility::upload_file($request,'favicon',$favicon,$dir,$validation);
                if($path['flag'] == 1){
                    $favicon = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }

            }


            if (!empty($request->title_text) || !empty($request->color) || !empty($request->cust_theme_bg) || !empty($request->cust_darklayout) || !empty($request->footer_text) || !empty($request->default_language) || !empty($request->display_landing_page) || !empty($request->email_verification)) {
                $post = $request->all();
                if (!isset($request->display_landing_page)) {
                    $post['display_landing_page'] = 'off';
                }
                if (!isset($request->signup_button)) {
                    $post['signup_button'] = 'off';
                }
                if (!isset($request->cust_theme_bg)) {
                    $post['cust_theme_bg'] = 'off';
                }
                if (!isset($request->cust_darklayout)) {
                    $post['cust_darklayout'] = 'off';
                }
                if (!isset($request->email_verification)) {
                    $post['email_verification'] = 'off';
                }
                $SITE_RTL = $request->has('SITE_RTL') ? $request->SITE_RTL : 'off';
                $post['SITE_RTL'] = $SITE_RTL;

                unset($post['_token'], $post['logo_dark'], $post['logo_light'], $post['favicon']);
                foreach ($post as $key => $data) {
                    $settings = Utility::settings();
                    if (in_array($key, array_keys($settings))) {
                        \DB::insert(
                            'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                $data,
                                $key,
                                $user->creatorId(),
                                '0',
                            ]
                        );
                    }
                }
            }

        } else {
            $post = $request->all();
            if ($request->logo_dark) {

                $logoName = time() . '_logo-dark.png';

                $dir = 'uploads/logo/';

                $validation =[
                    'mimes:'.'png',
                    'max:'.'20480',
                ];
                $path = Utility::upload_file($request,'logo_dark',$logoName,$dir,$validation);


                if($path['flag'] == 1){
                    $logo_dark = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $logo_dark = !empty($request->logo_dark) ? $logoName : 'logo_dark.png';

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                        $logoName,
                        'company_logo_dark',
                        $user->creatorId(),
                        $user->current_store,
                    ]
                );

            }
            if ($request->logo_light) {
                
                $lightlogoName = time() . 'logo-light.png';
                $dir = 'uploads/logo/';
                $validation =[
                    'mimes:'.'png',
                    'max:'.'20480',
                ];
                $path = Utility::upload_file($request,'logo_light',$lightlogoName,$dir,$validation);

                if($path['flag'] == 1){
                    $logo_light = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }   
                $company_logo = !empty($request->logo_light) ? $lightlogoName : 'logo-light.png';
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                        $lightlogoName,
                        'company_logo_light',
                        $user->creatorId(),
                        $user->current_store,
                    ]
                );
            }

            if ($request->favicon) {

                $favicon = time() . 'favicon.png';

                $dir = 'uploads/logo/';
                $validation =[
                    'mimes:'.'png',
                    'max:'.'20480',
                ];
                $path = Utility::upload_file($request,'favicon',$favicon,$dir,$validation);

                if($path['flag'] == 1){
                    $company_favicon = $path['url'];
                }else{
                    return redirect()->back()->with('error', __($path['msg']));
                }
                $company_favicon = !empty($request->favicon) ? $favicon : 'favicon.png';


                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                        $favicon,
                        'company_favicon',
                        $user->creatorId(),
                        $user->current_store,

                    ]
                );
            }

            if (!empty($request->title_text) || !empty($request->color) || !empty($request->cust_theme_bg) || !empty($request->cust_darklayout) || !empty($request->footer_text)) {

                $SITE_RTL = $request->has('SITE_RTL') ? $request->SITE_RTL : 'off';
                $post['SITE_RTL'] = $SITE_RTL;
                if (!isset($request->cust_theme_bg)) {
                    $post['cust_theme_bg'] = 'off';
                }
                if (!isset($request->cust_darklayout)) {
                    $post['cust_darklayout'] = 'off';
                }

                unset($post['_token'], $post['logo_dark'], $post['logo_light'], $post['favicon']);

                // $settings = Utility::settings();
                // if (in_array($key, array_keys($settings))) {
                //     \DB::insert(
                //         'insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                //             $data,
                //             $key,
                //             $user->creatorId(),
                //             $user->current_store,
                //         ]
                //     );
                // }
                foreach ($post as $key => $data) {
                    if ($data != '') {
                        \DB::insert('insert into settings (`value`, `name`,`created_by`,`store_id`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)', [
                            $data,
                            $key,
                            \Auth::user()->creatorId(),
                            \Auth::user()->current_store,
                        ]
                        );
                    }
                }
            }

        } 

        return redirect()->back()->with('success', __('Business setting successfully saved.'));
    }

    public function saveCompanySettings(Request $request)
    {

        
            $request->validate(
                [
                    'company_name' => 'required|string|max:50',
                    'company_email' => 'required',
                    'company_email_from_name' => 'required|string',
                ]
            );
            $post = $request->all();
            unset($post['_token']);

            foreach ($post as $key => $data) {
                $settings = Utility::settings();
                if (in_array($key, array_keys($settings))) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                            $data,
                            $key,
                            \Auth::user()->current_store,
                        ]
                    );
                }
            }

        
    }

    public function saveEmailSettings(Request $request)
    {

        if (\Auth::user()->type == 'super admin') {
            $request->validate(
                [
                    'mail_driver' => 'required|string|max:50',
                    'mail_host' => 'required|string|max:50',
                    'mail_port' => 'required|string|max:50',
                    'mail_username' => 'required|string|max:50',
                    'mail_password' => 'required|string|max:50',
                    'mail_encryption' => 'required|string|max:50',
                    'mail_from_address' => 'required|string|max:50',
                    'mail_from_name' => 'required|string|max:50',
                ]
            );

            $arrEnv = [
                'MAIL_DRIVER' => $request->mail_driver,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => $request->mail_password,
                'MAIL_ENCRYPTION' => $request->mail_encryption,
                'MAIL_FROM_NAME' => $request->mail_from_name,
                'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            ];
            Artisan::call('config:cache');
            Artisan::call('config:clear');
            Utility::setEnvironmentValue($arrEnv);

            return redirect()->back()->with('success', __('Setting successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function saveSystemSettings(Request $request)
    {
       
        $request->validate(
            [
                'site_currency' => 'required',
            ]
        );
        $post = $request->all();
        unset($post['_token']);
        if (!isset($post['shipping_display'])) {
            $post['shipping_display'] = 'off';
        }
        foreach ($post as $key => $data) {
            $settings = Utility::settings();
            if (in_array($key, array_keys($settings))) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                        $data,
                        $key,
                        \Auth::user()->current_store,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', __('Setting successfully updated.'));
       
    }

    public function savePusherSettings(Request $request)
    {
        if (\Auth::user()->type == 'super admin') {
            $request->validate(
                [
                    'pusher_app_id' => 'required',
                    'pusher_app_key' => 'required',
                    'pusher_app_secret' => 'required',
                    'pusher_app_cluster' => 'required',
                ]
            );

            $arrEnvStripe = [
                'PUSHER_APP_ID' => $request->pusher_app_id,
                'PUSHER_APP_KEY' => $request->pusher_app_key,
                'PUSHER_APP_SECRET' => $request->pusher_app_secret,
                'PUSHER_APP_CLUSTER' => $request->pusher_app_cluster,
            ];
            Artisan::call('config:cache');
            Artisan::call('config:clear');
            $envStripe = Utility::setEnvironmentValue($arrEnvStripe);

            if ($envStripe) {
                return redirect()->back()->with('success', __('Pusher successfully updated.'));
            } else {
                return redirect()->back()->with('error', __('Something went wrong.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

    }

    public function savePaymentSettings(Request $request)
    {
        if (\Auth::user()->type == 'super admin') {
            $request->validate(
                [
                    'currency' => 'required|string|max:255',
                    'currency_symbol' => 'required|string|max:255',
                ]
            );

            if (isset($request->enable_stripe) && $request->enable_stripe == 'on') {
                $request->validate(
                    [
                        'stripe_key' => 'required|string|max:255',
                        'stripe_secret' => 'required|string|max:255',
                    ]
                );
            } elseif (isset($request->enable_paypal) && $request->enable_paypal == 'on') {
                $request->validate(
                    [
                        'paypal_mode' => 'required|string',
                        'paypal_client_id' => 'required|string',
                        'paypal_secret_key' => 'required|string',
                    ]
                );
            }
           
            $request->user = Auth::user()->creatorId();

            $arrEnv = [
                'CURRENCY_SYMBOL' => $request->currency_symbol,
                'CURRENCY' => $request->currency,
                'ENABLE_STRIPE' => $request->enable_stripe ?? 'off',
                'STRIPE_KEY' => $request->stripe_key,
                'STRIPE_SECRET' => $request->stripe_secret,
                'ENABLE_PAYPAL' => $request->enable_paypal ?? 'off',
                'PAYPAL_MODE' => $request->paypal_mode,
                'PAYPAL_CLIENT_ID' => $request->paypal_client_id,
                'PAYPAL_SECRET_KEY' => $request->paypal_secret_key,
            ];
            Artisan::call('config:cache');
            Artisan::call('config:clear');
            Utility::setEnvironmentValue($arrEnv);
            $post = $request->all();
            self::adminPaymentSettings($request);
            unset($post['_token'], $post['stripe_key'], $post['stripe_secret']);
            foreach ($post as $key => $data) {
                $settings = Utility::settings();
                if (in_array($key, array_keys($settings))) {

                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                            $data,
                            $key,
                            $request->user,
                            date('Y-m-d H:i:s'),
                            date('Y-m-d H:i:s'),
                        ]
                    );
                }
            }

            return redirect()->back()->with('success', __('Payment setting successfully saved.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function saveOwnerPaymentSettings(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->first();

        $validator = \Validator::make(
            $request->all(), [
                'currency' => 'required|string|max:255',
                'currency_symbol' => 'required|string|max:255',
                // 'telegrambot' => 'required',
                // 'telegramchatid' => 'required',
            ]
        );

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();
            return redirect()->back()->with('error', $messages->first());
        }


        if (isset($request->enable_stripe) && $request->enable_stripe == 'on') {
            $request->validate(
                [
                    'stripe_key' => 'required|string|max:255',
                    'stripe_secret' => 'required|string|max:255',
                ]
            );
        } elseif (isset($request->enable_paypal) && $request->enable_paypal == 'on') {
            $request->validate(
                [
                    'paypal_mode' => 'required|string',
                    'paypal_client_id' => 'required|string',
                    'paypal_secret_key' => 'required|string',
                ]
            );
        }

        $store['currency'] = $request->currency_symbol;
        $store['currency_code'] = $request->currency;
        $store['currency_symbol_position'] = $request->currency_symbol_position;
        $store['currency_symbol_space'] = $request->currency_symbol_space;
        $store['is_stripe_enabled'] = $request->is_stripe_enabled ?? 'off';
        $store['STRIPE_KEY'] = $request->stripe_key;
        $store['STRIPE_SECRET'] = $request->stripe_secret;
        $store['is_paypal_enabled'] = $request->is_paypal_enabled ?? 'off';
        $store['PAYPAL_MODE'] = $request->paypal_mode;
        $store['PAYPAL_CLIENT_ID'] = $request->paypal_client_id;
        $store['PAYPAL_SECRET_KEY'] = $request->paypal_secret_key;
        $store['ENABLE_WHATSAPP'] = $request->enable_whatsapp ?? 'off';
        $store['WHATSAPP_NUMBER'] = str_replace(' ', '', $request->whatsapp_number);
        $store['ENABLE_COD'] = $request->enable_cod ?? 'off';
        $store['ENABLE_BANK'] = $request->enable_bank ?? 'off';
        $store['BANK_NUMBER'] = $request->bank_number;
        $store['enable_telegram'] = $request->enable_telegram ?? 'off';
        $store['telegrambot'] = str_replace(' ', '', $request->telegrambot);
        $store['telegramchatid'] = str_replace(' ', '', $request->telegramchatid);

        $store->update();

        self::shopePaymentSettings($request);

        return redirect()->back()->with('success', __('Payment Store setting successfully created.'));
        
    }

    public function saveOwneremailSettings(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->first();

        $request->validate(
            [
                'mail_driver' => 'required|string|max:50',
                'mail_host' => 'required|string|max:50',
                'mail_port' => 'required|string|max:50',
                'mail_username' => 'required|string|max:50',
                'mail_password' => 'required|string|max:50',
                'mail_encryption' => 'required|string|max:50',
                'mail_from_address' => 'required|string|max:50',
                'mail_from_name' => 'required|string|max:50',
            ]
        );

        $store['mail_driver'] = $request->mail_driver;
        $store['mail_host'] = $request->mail_host;
        $store['mail_port'] = $request->mail_port;
        $store['mail_username'] = $request->mail_username;
        $store['mail_password'] = $request->mail_password;
        $store['mail_encryption'] = $request->mail_encryption;
        $store['mail_from_address'] = $request->mail_from_address;
        $store['mail_from_name'] = $request->mail_from_name;
        $store->update();

        return redirect()->back()->with('success', __('Email Store setting successfully created.'));
    }

    public function saveOwnertwilioSettings(Request $request, $slug)
    {
        $store = Store::where('slug', $slug)->first();

        $validator = \Validator::make(
            $request->all(), [
                'is_twilio_enabled' => 'required',
                'twilio_sid' => 'required',
                'twilio_token' => 'required',
                'twilio_from' => 'required',
                'notification_number' => 'required|numeric',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $store['is_twilio_enabled'] = $request->is_twilio_enabled ?? 'off';
        $store['twilio_sid'] = $request->twilio_sid;
        $store['twilio_token'] = $request->twilio_token;
        $store['twilio_from'] = $request->twilio_from;
        $store['notification_number'] = $request->notification_number;
        $store->update();

        return redirect()->back()->with('success', __('Twilio Store setting successfully created.'));

    }

    public function saveCompanyPaymentSettings(Request $request)
    {
        if (isset($request->enable_stripe) && $request->enable_stripe == 'on') {
            $request->validate(
                [
                    'stripe_key' => 'required|string',
                    'stripe_secret' => 'required|string',
                ]
            );
        } elseif (isset($request->enable_paypal) && $request->enable_paypal == 'on') {
            $request->validate(
                [
                    'paypal_mode' => 'required|string',
                    'paypal_client_id' => 'required|string',
                    'paypal_secret_key' => 'required|string',
                ]
            );
        }
        $post = $request->all();
        $post['enable_paypal'] = isset($request->enable_paypal) ? $request->enable_paypal : '';
        $post['enable_stripe'] = isset($request->enable_stripe) ? $request->enable_stripe : '';
        unset($post['_token']);
        foreach ($post as $key => $data) {
            $settings = Utility::settings();
            if (in_array($key, array_keys($settings))) {
                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                        $data,
                        $key,
                        \Auth::user()->current_store,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ]
                );
            }
        }
        return redirect()->back()->with('success', __('Payment setting successfully updated.'));

    }

    public function testMail(Request $request)
    {
        $user = \Auth::user();

        $data = [];
        $data['mail_driver'] = $request->mail_driver;
        $data['mail_host'] = $request->mail_host;
        $data['mail_port'] = $request->mail_port;
        $data['mail_username'] = $request->mail_username;
        $data['mail_password'] = $request->mail_password;
        $data['mail_encryption'] = $request->mail_encryption;
        $data['mail_from_address'] = $request->mail_from_address;
        $data['mail_from_name'] = $request->mail_from_name;

        return view('settings.test_mail', compact('data'));
    }

    public function testSendMail(Request $request)
    {
        $validator = \Validator::make(
            $request->all(), [
                //    'email' => 'required|email',
                'mail_driver' => 'required',
                'mail_host' => 'required',
                'mail_port' => 'required',
                'mail_username' => 'required',
                'mail_password' => 'required',
                'mail_from_address' => 'required',
                'mail_from_name' => 'required',
            ]
        );
        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            // return redirect()->back()->with('error', $messages->first());
            return response()->json(
                [
                    'is_success' => false,
                    'message' => $messages->first(),
                ]
            );
        }
        try
        {
            config(
                [
                    'mail.driver' => $request->mail_driver,
                    'mail.host' => $request->mail_host,
                    'mail.port' => $request->mail_port,
                    'mail.encryption' => $request->mail_encryption,
                    'mail.username' => $request->mail_username,
                    'mail.password' => $request->mail_password,
                    'mail.from.address' => $request->mail_from_address,
                    'mail.from.name' => $request->mail_from_name,
                ]
            );
            Mail::to($request->email)->send(new testMail());
        } catch (\Exception $e) {
            return response()->json(
                [
                    'is_success' => false,
                    'message' => $e->getMessage(),
                ]
            );
        }
        // return redirect()->back()->with('success', __('Email send Successfully.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        return response()->json(
            [
                'is_success' => true,
                'message' => __('Email send Successfully'),
            ]
        );

    }
    // public function testSendMail(Request $request)
    // {
    //     if (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'Owner') {

    //         $validator = \Validator::make($request->all(), ['email' => 'required|email']);
    //         if ($validator->fails()) {
    //             $messages = $validator->getMessageBag();

    //             return redirect()->back()->with('error', $messages->first());
    //         }

    //         try
    //         {
    //             if (\Auth::user()->type != 'super admin') {

    //                 $store = Store::find(Auth::user()->current_store);

    //                 config(
    //                     [
    //                         'mail.driver' => $store->mail_driver,
    //                         'mail.host' => $store->mail_host,
    //                         'mail.port' => $store->mail_port,
    //                         'mail.encryption' => $store->mail_encryption,
    //                         'mail.username' => $store->mail_username,
    //                         'mail.password' => $store->mail_password,
    //                         'mail.from.address' => $store->mail_from_address,
    //                         'mail.from.name' => $store->mail_from_name,
    //                     ]
    //                 );
    //             }

    //             Mail::to($request->email)->send(new TestMail());
    //         } catch (\Exception $e) {

    //             $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
    //         }

    //         return redirect()->back()->with('success', __('Email send Successfully.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    //     } else {
    //         return redirect()->back()->with('error', __('Permission denied.'));
    //     }
    // }

    public function shopePaymentSettings($request)
    {
        if (isset($request->custom_field_title_1) ) {
            $post['custom_field_title_1'] = $request->custom_field_title_1;
        }
        if (isset($request->custom_field_title_2) ) {

            $post['custom_field_title_2'] = $request->custom_field_title_2;
        }
        if (isset($request->custom_field_title_3) ) {

            $post['custom_field_title_3'] = $request->custom_field_title_3;
        }
        if (isset($request->custom_field_title_4) ) {
            $post['custom_field_title_4'] = $request->custom_field_title_4;
        }
        
        if (isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on') {
            $request->validate(
                [
                    'stripe_key' => 'required|string|max:255',
                    'stripe_secret' => 'required|string|max:255',
                ]
            );
            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
            $post['stripe_key'] = $request->stripe_key;
            $post['stripe_secret'] = $request->stripe_secret;
        } else {
            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
        }

        if (isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on') {
            $request->validate(
                [
                    'paypal_mode' => 'required|string',
                    'paypal_client_id' => 'required|string',
                    'paypal_secret_key' => 'required|string',
                ]
            );
            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode'] = $request->paypal_mode;
            $post['paypal_client_id'] = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        } else {
            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
        }

        if (isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on') {
            $request->validate(
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        } else {
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
        }

        if (isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on') {
            $request->validate(
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        } else {
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
        }

        if (isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on') {
            $request->validate(
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        } else {
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
        }

        if (isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on') {
            $request->validate(
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );
            $post['is_paytm_enabled'] = $request->is_paytm_enabled;
            $post['paytm_mode'] = $request->paytm_mode;
            $post['paytm_merchant_id'] = $request->paytm_merchant_id;
            $post['paytm_merchant_key'] = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        } else {
            $post['is_paytm_enabled'] = $request->is_paytm_enabled;
        }

        if (isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on') {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token'] = $request->mercado_access_token;
            $post['mercado_mode'] = $request->mercado_mode;
        } else {
            $post['is_mercado_enabled'] = 'off';
        }

        if (isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on') {
            $request->validate(
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key'] = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        } else {
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
        }

        if (isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on') {
            $request->validate(
                [
                    'skrill_email' => 'required|email',
                ]
            );
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email'] = $request->skrill_email;
        } else {
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
        }

        if (isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on') {
            $request->validate(
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode'] = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        } else {
            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
        }

        if (isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(), [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_private_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;
            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_private_key'] = $request->paymentwall_private_key;
        } else {
            $post['is_paymentwall_enabled'] = 'off';
        }
        // dd($request->all());

        if (isset($request->enable_telegram) && $request->enable_telegram == 'on') {

            $validator = \Validator::make(
                $request->all(), [
                    'telegrambot' => 'required',
                    'telegramchatid' => 'required',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['enable_telegram'] = $request->enable_telegram;
            $post['telegrambot'] = $request->telegrambot;
            $post['telegramchatid'] = $request->telegramchatid;
        } else {
            $post['enable_telegram'] = 'off';
        }
        if(isset($request->is_toyyibpay_enabled) && $request->is_toyyibpay_enabled == 'on')
        {
            $request->validate(
                [
                    'toyyibpay_category_code' => 'required|string',
                    'toyyibpay_secret_key' => 'required|string',
                ]
            );
            $post['is_toyyibpay_enabled'] = $request->is_toyyibpay_enabled;
            $post['toyyibpay_category_code'] = $request->toyyibpay_category_code;
            $post['toyyibpay_secret_key'] = $request->toyyibpay_secret_key;
        }
        else
        {
            $post['is_toyyibpay_enabled'] = $request->is_toyyibpay_enabled;
        }
        if(isset($request->is_payfast_enabled) && $request->is_payfast_enabled == 'on')
        {
            $request->validate(
                [
                    'payfast_mode'=>'required|string',
                    'payfast_merchant_id' => 'required|string',
                    'payfast_merchant_key' => 'required|string',
                    'payfast_signature'=>'required|string',
                ]
            );
            $post['is_payfast_enabled'] = $request->is_payfast_enabled;
            $post['payfast_mode'] = $request->payfast_mode;
            $post['payfast_merchant_id'] = $request->payfast_merchant_id;
            $post['payfast_merchant_key'] = $request->payfast_merchant_key;
            $post['payfast_signature'] = $request->payfast_signature;
        }
        else
        {
            $post['is_payfast_enabled'] = $request->is_payfast_enabled;
        }
        foreach ($post as $key => $data) {

            $arr = [
                $data,
                $key,
                Auth::user()->current_store,
                Auth::user()->creatorId(),
            ];

            \DB::insert(
                'insert into store_payment_settings (`value`, `name`, `store_id`,`created_by`) values (?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', $arr
            );

        }
    }

    public function adminPaymentSettings($request)
    {
        
        if (isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on') {
            $request->validate(
                [
                    'stripe_key' => 'required|string|max:255',
                    'stripe_secret' => 'required|string|max:255',
                ]
            );
            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
            $post['stripe_key'] = $request->stripe_key;
            $post['stripe_secret'] = $request->stripe_secret;
        } else {
            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
        }

        if (isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on') {
            $request->validate(
                [
                    'paypal_mode' => 'required|string',
                    'paypal_client_id' => 'required|string',
                    'paypal_secret_key' => 'required|string',
                ]
            );

            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode'] = $request->paypal_mode;
            $post['paypal_client_id'] = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        } else {
            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
        }

        if (isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on') {
            $request->validate(
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        } else {
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
        }

        if (isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on') {
            $request->validate(
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        } else {
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
        }

        if (isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on') {
            $request->validate(
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        } else {
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
        }
        if (isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on') {
            $request->validate(
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );
            $post['is_paytm_enabled'] = $request->is_paytm_enabled;
            $post['paytm_mode'] = $request->paytm_mode;
            $post['paytm_merchant_id'] = $request->paytm_merchant_id;
            $post['paytm_merchant_key'] = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        } else {
            $post['is_paytm_enabled'] = $request->is_paytm_enabled;
        }

        if (isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on') {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token'] = $request->mercado_access_token;
            $post['mercado_mode'] = $request->mercado_mode;
        } else {
            $post['is_mercado_enabled'] = 'off';
        }

        if (isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on') {
            $request->validate(
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key'] = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        } else {
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
        }

        if (isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on') {
            $request->validate(
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode'] = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        } else {
            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
        }
       
        if (isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on') {

            $validator = \Validator::make(
                $request->all(), [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_private_key' => 'required|string',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;
            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_private_key'] = $request->paymentwall_private_key;
        } else {
            $post['is_paymentwall_enabled'] = 'off';
        }
        if(isset($request->is_toyyibpay_enabled) && $request->is_toyyibpay_enabled == 'on')
        {
            $request->validate(
                [
                    'toyyibpay_category_code' => 'required|string',
                    'toyyibpay_secret_key' => 'required|string',
                ]
            );
            $post['is_toyyibpay_enabled'] = $request->is_toyyibpay_enabled;
            $post['toyyibpay_category_code'] = $request->toyyibpay_category_code;
            $post['toyyibpay_secret_key'] = $request->toyyibpay_secret_key;
        }
        else
        {
            $post['is_toyyibpay_enabled'] = $request->is_toyyibpay_enabled;
        }
        if(isset($request->is_payfast_enabled) && $request->is_payfast_enabled == 'on')
        {
            $request->validate(
                [
                    'payfast_mode'=>'required|string',
                    'payfast_merchant_id' => 'required|string',
                    'payfast_merchant_key' => 'required|string',
                    'payfast_signature'=>'required|string',
                ]
            );
            $post['is_payfast_enabled'] = $request->is_payfast_enabled;
            $post['payfast_mode'] = $request->payfast_mode;
            $post['payfast_merchant_id'] = $request->payfast_merchant_id;
            $post['payfast_merchant_key'] = $request->payfast_merchant_key;
            $post['payfast_signature'] = $request->payfast_signature;
        }
        else
        {
            $post['is_payfast_enabled'] = $request->is_payfast_enabled;
        }
        if(isset($request->manually_enabled) && $request->manually_enabled == 'on')
        {
            $post['manually_enabled'] = $request->manually_enabled;
        }
        else
        {
            $post['manually_enabled'] = $request->manually_enabled;
        }
        if(isset($request->enable_bank) && $request->enable_bank == 'on')
        {
            $request->validate(
                [
                    'bank_number' => 'required|string|max:255',
                ]
            );
            $post['enable_bank'] = $request->enable_bank;
            $post['bank_number'] = $request->bank_number;
        }
        else
        {
            $post['enable_bank'] = $request->enable_bank;
        }
        
        if (isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on') {
            $request->validate(
                [
                    'skrill_email' => 'required|email',
                ]
            );
           
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email'] = $request->skrill_email;
        } else {
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
        }
        foreach ($post as $key => $data) {
            $arr = [
                $data,
                $key,
                $request->user,
            ];
            \DB::insert(
                'insert into admin_payment_settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', $arr
            );

        }
    }

    public function recaptchaSettingStore(Request $request)
    {
        // return redirect()->back()->with('error', __('This operation is not perform due to demo mode.'));
        if (\Auth::user()->type == 'super admin') {

            $user = \Auth::user();
            $rules = [];
            if ($request->recaptcha_module == 'yes') {
                $rules['google_recaptcha_key'] = 'required|string|max:50';
                $rules['google_recaptcha_secret'] = 'required|string|max:50';
            }
            $validator = \Validator::make(
                $request->all(), $rules
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }
            $arrEnv = [
                'RECAPTCHA_MODULE' => $request->recaptcha_module ?? 'no',
                'NOCAPTCHA_SITEKEY' => $request->google_recaptcha_key,
                'NOCAPTCHA_SECRET' => $request->google_recaptcha_secret,
            ];
            if (Utility::setEnvironmentValue($arrEnv)) {
                return redirect()->back()->with('success', __('Recaptcha Settings updated successfully'));
            } else {
                return redirect()->back()->with('error', __('Something is wrong'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function storageSettingStore(Request $request)
    {

        if(isset($request->storage_setting) && $request->storage_setting == 'local')
        {
            $request->validate(
                [

                    'local_storage_validation' => 'required',
                    'local_storage_max_upload_size' => 'required',
                ]
            );

            $post['storage_setting'] = $request->storage_setting;
            $local_storage_validation = implode(',', $request->local_storage_validation);
            $post['local_storage_validation'] = $local_storage_validation;
            $post['local_storage_max_upload_size'] = $request->local_storage_max_upload_size;

        }
        if(isset($request->storage_setting) && $request->storage_setting == 's3')

        {

            $request->validate(
                [
                    's3_key'                  => 'required',
                    's3_secret'               => 'required',
                    's3_region'               => 'required',
                    's3_bucket'               => 'required',
                    's3_url'                  => 'required',
                    's3_endpoint'             => 'required',
                    's3_max_upload_size'      => 'required',
                    's3_storage_validation'   => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['s3_key']                     = $request->s3_key;
            $post['s3_secret']                  = $request->s3_secret;
            $post['s3_region']                  = $request->s3_region;
            $post['s3_bucket']                  = $request->s3_bucket;
            $post['s3_url']                     = $request->s3_url;
            $post['s3_endpoint']                = $request->s3_endpoint;
            $post['s3_max_upload_size']         = $request->s3_max_upload_size;
            $s3_storage_validation              = implode(',', $request->s3_storage_validation);
            $post['s3_storage_validation']      = $s3_storage_validation;

        }

        if(isset($request->storage_setting) && $request->storage_setting == 'wasabi')
        {
            $request->validate(
                [
                    'wasabi_key'                    => 'required',
                    'wasabi_secret'                 => 'required',
                    'wasabi_region'                 => 'required',
                    'wasabi_bucket'                 => 'required',
                    'wasabi_url'                    => 'required',
                    'wasabi_root'                   => 'required',
                    'wasabi_max_upload_size'        => 'required',
                    'wasabi_storage_validation'     => 'required',
                ]
            );
            $post['storage_setting']            = $request->storage_setting;
            $post['wasabi_key']                 = $request->wasabi_key;
            $post['wasabi_secret']              = $request->wasabi_secret;
            $post['wasabi_region']              = $request->wasabi_region;
            $post['wasabi_bucket']              = $request->wasabi_bucket;
            $post['wasabi_url']                 = $request->wasabi_url;
            $post['wasabi_root']                = $request->wasabi_root;
            $post['wasabi_max_upload_size']     = $request->wasabi_max_upload_size;
            $wasabi_storage_validation          = implode(',', $request->wasabi_storage_validation);
            $post['wasabi_storage_validation']  = $wasabi_storage_validation;
        }

        foreach($post as $key => $data)
        {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];

            \DB::insert(
                'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', $arr
            );
        }

        return redirect()->back()->with('success', 'Storage setting successfully updated.');

    }
    public function CreatePixel(){
        $user = Auth::user();
        $store_settings = Store::where('id', $user->current_store)->first();
        return view('settings.edit_pixel',compact('store_settings'));
    }
    public function savePixelSettings(Request $request, $slug){
    
        $store = Store::where('slug', $slug)->where('created_by',\Auth::user()->creatorId())->first();

        $request->validate([
            'platform'=>'required',
            'pixel_id'=>'required'
        ]);
        $pixel_fields = new PixelFields();
        $pixel_fields->platform = $request->platform;
        $pixel_fields->pixel_id = $request->pixel_id;
        $pixel_fields->store_id = $store->id;
        $pixel_fields->save();

        return redirect()->back()->with('success', __('Fields Saves Successfully.!'));
    }
    public function pixelDelete($id){
        $pixelfield= PixelFields::find($id);
        $pixelfield->delete();
        return redirect()->back()->with('success', __('Pixel Deleted Successfully!'));
    }
    public function CookieConsent(Request $request)
    {

        $settings= Utility::settings();
        
        if($settings['enable_cookie'] == "on" && $settings['cookie_logging'] == "on"){
            $allowed_levels = ['necessary', 'analytics', 'targeting'];
            $levels = array_filter($request['cookie'], function($level) use ($allowed_levels) {
                return in_array($level, $allowed_levels);
            });
            $whichbrowser = new \WhichBrowser\Parser($_SERVER['HTTP_USER_AGENT']);
            // Generate new CSV line
            $browser_name = $whichbrowser->browser->name ?? null;
            $os_name = $whichbrowser->os->name ?? null;
            $browser_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : null;
            $device_type = Utility::get_device_type($_SERVER['HTTP_USER_AGENT']);

//            $ip = $_SERVER['REMOTE_ADDR'];
            $ip = '49.36.83.154';
            $query = @unserialize(file_get_contents('http://ip-api.com/php/' . $ip));


            $date = (new \DateTime())->format('Y-m-d');
            $time = (new \DateTime())->format('H:i:s') . ' UTC';


            $new_line = implode(',', [$ip, $date, $time,json_encode($request['cookie']), $device_type, $browser_language, $browser_name, $os_name,
                isset($query)?$query['country']:'',isset($query)?$query['region']:'',isset($query)?$query['regionName']:'',isset($query)?$query['city']:'',isset($query)?$query['zip']:'',isset($query)?$query['lat']:'',isset($query)?$query['lon']:'']);

            if(!file_exists(storage_path(). '/uploads/sample/data.csv')) {

                $first_line = 'IP,Date,Time,Accepted cookies,Device type,Browser language,Browser name,OS Name,Country,Region,RegionName,City,Zipcode,Lat,Lon';
                file_put_contents(storage_path() . '/uploads/sample/data.csv', $first_line . PHP_EOL , FILE_APPEND | LOCK_EX);
            }
            file_put_contents(storage_path() . '/uploads/sample/data.csv', $new_line . PHP_EOL , FILE_APPEND | LOCK_EX);

            return response()->json('success');
        }
        return response()->json('error');
    }
    public function saveCookieSettings(Request $request)
    {

            $validator = \Validator::make(
                $request->all(), [
                    'cookie_title' => 'required',
                    'cookie_description' => 'required',
                    'strictly_cookie_title' => 'required',
                    'strictly_cookie_description' => 'required',
                    'more_information_title' => 'required',
                    'contactus_url' => 'required',
                ]
            );

            $post = $request->all();

            unset($post['_token']);

            if ($request->enable_cookie)
            {
                $post['enable_cookie'] = 'on';
            }
            else{
                $post['enable_cookie'] = 'off';
            }
            if ( $request->cookie_logging)
            {
                $post['cookie_logging'] = 'on';
            }
            else{
                $post['cookie_logging'] = 'off';
            }

            $post['cookie_title']            = $request->cookie_title;
            $post['cookie_description']            = $request->cookie_description;
            $post['strictly_cookie_title']            = $request->strictly_cookie_title;
            $post['strictly_cookie_description']            = $request->strictly_cookie_description;
            $post['more_information_title']            = $request->more_information_title;
            $post['contactus_url']            = $request->contactus_url;

            $settings = Utility::settings();
            foreach ($post as $key => $data) {

                if (in_array($key, array_keys($settings))) {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                            $data,
                            $key,
                            \Auth::user()->creatorId(),
                            date('Y-m-d H:i:s'),
                            date('Y-m-d H:i:s'),
                        ]
                    );
                }
            }
            return redirect()->back()->with('success', 'Cookie setting successfully saved.');
        }
}
