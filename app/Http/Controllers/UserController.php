<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Plan;
use App\Models\Utility;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(\Auth::user()->can('Manage User')){
         
          
          
            $users = User::where('created_by','=',\Auth::user()->creatorId())->where('current_store',\Auth::user()->current_store)->get();
            return view('users.index',compact('users'));
            
        }
        else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::user()->can('Create User')) {
            $user  = \Auth::user();
            $roles = Role::where('created_by', '=', $user->creatorId())->where('store_id',$user->current_store)->get()->pluck('name', 'id');
            return view('users.create',compact('roles'));
        }else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('Create User')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => ['required',
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('created_by', \Auth::user()->id)->where('current_store',\Auth::user()->current_store);
                    })
                    ],
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            $objUser    = \Auth::user()->creatorId();
            $objUser = User::find($objUser);
            $role_r = Role::findById($request->role);
            $date = date("Y-m-d H:i:s");
            $total_users = \Auth::user()->countUsers();
            $plan = Plan::find($objUser->plan);
            if ($total_users < $plan->max_users || $plan->max_users == -1) {
                $user =  new User();
                $user->name =  $request['name'];
                $user->email =  $request['email'];
                $user->password = Hash::make($request['password']);
                $user->type = $role_r->name;
                $user->lang = $default_language->value ?? 'en';
                $user->created_by = \Auth::user()->creatorId();
                $user->email_verified_at = $date;
                $user->current_store = $objUser->current_store;
                $user->plan = $objUser->plan;
                $user->save();

                $user->assignRole($role_r);
                $module = 'New User';
                $store = \Auth::user()->current_store;
                $webhook =  Utility::webhook($module, $store);

                if ($webhook) {
                    $parameter = json_encode($user);

                    // 1 parameter is  URL , 2 parameter is data , 3 parameter is method
                    $status = Utility::WebhookCall($webhook['url'], $parameter, $webhook['method']);
                    if ($status != true) {
                        $msg = 'Webhook call failed.';
                    }
                }
            }
            else{
                             
                return redirect()->back()->with('error', __('Your Userds limit is over Please upgrade plan'));
            }
            return redirect()->route('users.index')->with('success', __('User successfully created.'));
        }
        else{
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('profile');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (\Auth::user()->can('Edit User')) {
            $user  = User::find($id);
            $roles = Role::where('created_by', '=', \Auth::user()->creatorId())->where('store_id',\Auth::user()->current_store)->get()->pluck('name', 'id');
            return view('users.edit', compact('user', 'roles'));
        } else {
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, User $user)
    {
        if (\Auth::user()->can('Edit User')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'name' => 'required',
                    'email' => ['required|unique:users,email,' . $id,
                        Rule::unique('users')->where(function ($query)  use ($user) {
                            return $query->whereNotIn('id',[$user->id])->where('created_by',  \Auth::user()->creatorId())->where('current_store', \Auth::user()->current_store);
                        })
                    ],
                ]
            );
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }
            $user = User::findOrFail($id);

            $role          = Role::findById($request->role);
            $input         = $request->all();
            $input['type'] = $role->name;
            $user->fill($input)->save();

            $user->assignRole($role);
            $roles[] = $request->role;
            $user->roles()->sync($roles);
            return redirect()->route('users.index')->with('success', 'User successfully updated.');
        }
        else{
            return response()->json(['error' => __('Permission denied.')], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('Delete User')) {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('users.index')->with('success', 'User successfully deleted.');
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function reset($id){
        if (\Auth::user()->can('Reset Password')) {
            $Id        = \Crypt::decrypt($id);

            $user = User::find($Id);

            $employee = User::where('id', $Id)->first();

            return view('users.reset', compact('user', 'employee'));
        }
        else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
    public function updatePassword(Request $request, $id){
        if (\Auth::user()->can('Reset Password')) {
            $validator = \Validator::make(
                $request->all(),
                [
                    'password' => 'required|confirmed|same:password_confirmation',
                ]
            );

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $user                 = User::where('id', $id)->first();
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->save();

            return redirect()->route('users.index')->with(
                'success',
                'User Password successfully updated.'
            );
        }
        else{
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }
}
