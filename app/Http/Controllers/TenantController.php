<?php

namespace App\Http\Controllers;

use App\Models\BillPlan;
use App\Models\Taxation;
use App\Models\Tenant;
use App\Models\TenantFinance;
use App\Models\User;
use App\Models\TenantLog;
use App\Models\TenantMinuteLog;
use App\Models\TenantLowBalanceNotification;
use App\Providers\EncreptDecrept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    // customers list Telnet list
    public function customers(Request $request)
    {
        
        $agent_id = $request->id ? $request->id : '';
        $id = EncreptDecrept::decrept($agent_id); 
        $perpage = 4;
        $tenant = Tenant::orderBy('id', 'DESC');
        $activetenant = Tenant::where('status', 'ACTIVE');
        $suspendedtenant = Tenant::where('suspended', 'YES');
        if(!empty($id)){
            $tenant = $tenant->where("agent_id",$id);
            $activetenant = $activetenant->where("agent_id",$id);
            $suspendedtenant = $suspendedtenant->where("agent_id",$id);
        }
        $response['billplan_list'] = BillPlan::get();
        $response['billplan_type'] = BillPlan::select('type')->groupBy('type')->get();
        $response['taxation'] = Taxation::get();
        if ($request->ajax()) {
            if ($request->search) {
                $search_key = $request->search;
                $tenant->where(function ($tenant1) use ($search_key) {
                    $tenant1 = $tenant1->where('first_name', 'LIKE', "%{$search_key}%")->orWhere('last_name', 'LIKE', "%{$search_key}%")->orWhere('email', 'LIKE', "%{$search_key}%")->orWhere('company_name', 'LIKE', "%{$search_key}%");
                });
                $activetenant->where(function ($activetenant1) use ($search_key) {
                    $activetenant1 = $activetenant1->where('first_name', 'LIKE', "%{$search_key}%")->orWhere('last_name', 'LIKE', "%{$search_key}%")->orWhere('email', 'LIKE', "%{$search_key}%")->orWhere('company_name', 'LIKE', "%{$search_key}%");
                });
                $suspendedtenant->where(function ($suspendedtenant1) use ($search_key) {
                    $suspendedtenant1 = $suspendedtenant1->where('first_name', 'LIKE', "%{$search_key}%")->orWhere('last_name', 'LIKE', "%{$search_key}%")->orWhere('email', 'LIKE', "%{$search_key}%")->orWhere('company_name', 'LIKE', "%{$search_key}%");
                });
            }
        }


        $tenant = $tenant->paginate($perpage);

        if ($request->ajax()) {
            $response_ajex['totalrecords'] = $tenant->total();
            $response_part['records'] = $tenant;
            $response_part['page'] = 'tenant';
            $view = view('user.data', $response_part)->render();
            $response_ajex['html'] = $view;
            $response_ajex['activetenant'] = $activetenant->count();
            $response_ajex['suspendedtenant'] = $suspendedtenant->count();
            return response()->json($response_ajex);
        }

        $response['records'] = $tenant;
        $response['agent_id'] = $agent_id;
        $response['totalrecords'] = $tenant->total();
        $response['activetenant'] = $activetenant->count();
        $response['suspendedtenant'] = $suspendedtenant->count();
        // dd($response);
        return view('tenant.index', $response);
    }

    // customer add model - add cusomer 
    public function customersAddAjex(Request $request){
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|min:3',
            'lastname' => 'required|min:3',
            'email' => 'required|email',
            'contact_no' => 'required|digits:10',
            'address' => 'required|min:5',
            'country' => 'required|min:2',
            'state' => 'required|min:2',
            'city' => 'required|min:2',
            'postal_code' => 'required|min:5',
            'company_name' => 'required|min:3'
        ]);
        if ($validator->fails()) {
            $data_responce = ["status" => "danger", "data" => "Validation error", "error" => $validator->messages()];
            return response()->json($data_responce, 200);
        }

        $update["account_number"] = "";
        // $update["agent_id"] = Auth::user()->id;
        $update["first_name"] = $request->firstname;
        $update["last_name"] = $request->lastname;
        $update["email"] = $request->email;
        $update["phone_number"] = $request->contact_no;
        $update["address"] = $request->address;
        $update["country"] = $request->country;
        $update["state"] = $request->state;
        $update["city"] = $request->city;
        $update["postal_code"] = $request->postal_code;
        $update["company_name"] = $request->company_name;
        
        
        try {
            if ($request->id == 0) {
                $update["join_date"] = date("Y-m-d");
                $add_tenant = Tenant::create($update);

                // $role[] = 'TENANT';
                // $add_tenant->assignRole($role);
                if ($inserted_id = $add_tenant->id) {
                    return response()->json(["status" => "success", "data" => $inserted_id, "error" => 0]);
                }
            } else {
                $update["modified_at"] = date('Y-m-d H:i:s');
                Tenant::where("id", $request->id)->update($update);
                return response()->json(["status" => "success", "data" => "Update Sucessfully", "error" => 0]);
            }


            return response()->json(["status" => "fail", "data" => "Something wrong", "error" => 0]);
        } catch (\Exception $e) {
            return response()->json(["status" => "fail", "data" => "Record not found", "error" => $e->getMessage()]);
        }
    }
    // customers Edit Telnet Edit
    public function customersedit($id){
        // dd($id);
        $perpage = 3;
        $decrypted_id = EncreptDecrept::decrept($id);
        $id = $decrypted_id;
        if (Tenant::where("id", $decrypted_id)->count() == 0) {
            return redirect('/customers');
        }
        // dd($id);
        
        // $id = $decrypted_id[0];
        // $id = $decrypted_id;
        $response['customer'] = Tenant::where('id', $id)->first();
        // dd($response['customer']);
        $response['user'] = User::where('tenant_id', $id)->first();
        // $response['billplan'] = AgentBillplan::select('bill_plan.name', 'bill_plan.type', 'bill_plan.id as bill_plan_id', 'agent_billplan.id as agent_billplan_id', 'agent_billplan.commission')->where('agent_id', $id)->leftjoin('bill_plan', 'bill_plan.id', 'agent_billplan.billplan_id')->where("agent_billplan.status", "ACTIVE")->orderBy('agent_billplan.id', 'desc')->paginate($perpage);
        // $response['billplan_list'] = BillPlan::get();
        // $response['i'] = 1;
        // if ($request->ajax()) {
        //     // dd($id);
        //     $response_part['i'] = $perpage * ($request->page - 1);
        //     if ($request->addnewplan) {
        //         $response_part['inew'] = "addnewplan";
        //         $response_part['i'] = 0;
        //     }

        //     $response_part['page'] = 'agentedit_billing';
        //     $response_part['billplan'] = $response['billplan'];
        //     // dd($id);
        //     $view = view('user.data', $response_part)->render();
        //     $response_ajex['html'] = $view;

        //     return response()->json($response_ajex);
        // }
        return view('tenant.edit', $response);

    }

    // Customerlist page
    // add new customer(tenent) model Information tab
    public function customer_add_ajax(Request $request)
    {
    //  dd($request->bill_id);   
        $check = $request->check ? $request->check : '';
        //return response()->json(["status" => "success", "data" => 25, "error" => 0]);
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|min:3',
            'lastname' => 'required|min:3',
            'email' => 'required|email',
            'contact_no' => 'required|digits:10',
            'address' => 'required|min:5',
            'country' => 'required|min:2',
            'state' => 'required|min:2',
            'city' => 'required|min:2',
            'postal_code' => 'required|min:5',
            'company_name' => 'required|min:3'
        ]);
        if ($validator->fails()) {
            $data_responce = ["status" => "danger", "data" => "Validation error", "error" => $validator->messages()];
            return response()->json($data_responce, 200);
        }else{
            if(!empty($check) && $check == 'check'){
                
                $data_responce = ["status" => "success", "data" => "Validate", "error" => 0];
                return response()->json($data_responce, 200);
            }
            
        }

        
        $update["billpan_id"] = $request->bill_id ? $request->bill_id : '1';
        $update["account_number"] = Tenant::max('account_number')+1;
        $update["agent_id"] = (Auth::user()->role == "AGENT") ? Auth::user()->tenant_id : Auth::user()->id;
        // dd($update["agent_id"]);
        $update["first_name"] = $request->firstname;
        $update["last_name"] = $request->lastname;
        $update["email"] = $request->email;
        $update["phone_number"] = $request->contact_no;
        $update["address"] = $request->address;
        $update["country"] = $request->country;
        $update["state"] = $request->state;
        $update["city"] = $request->city;
        $update["postal_code"] = $request->postal_code;
        $update["company_name"] = $request->company_name;

        try {
            // if ($request->id == 0) {
                $update["join_date"] = date("Y-m-d");
                $add_agent = Tenant::create($update);

                // $role[] = 'TENANT';
                // $add_agent->assignRole($role);
                if ($inserted_id = $add_agent->id) {
                    return response()->json(["status" => "success", "data" => $inserted_id, "error" => 0]);
                }
            // } else {
            //     $update["modified_at"] = date('Y-m-d H:i:s');
            //     Agent::where("id", $request->id)->update($update);
            //     return response()->json(["status" => "success", "data" => "Update Sucessfully", "error" => 0]);
            // }


            return response()->json(["status" => "fail", "data" => "Something wrong", "error" => 0]);
        } catch (\Exception $e) {
            return response()->json(["status" => "fail", "data" => "Record not found", "error" => $e->getMessage()]);
        }
    }

    // agentlist page
    // add new user model Credential tab
    public function customer_cred_add_ajax(Request $request)
    {        
        $check = $request->check ? $request->check : '';
        // dd($request->all());
        // return response()->json(["status" => "success", "data" => 50, "error" => 0]);
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'firstname_user' => 'required|min:3',
            'lastname_user' => 'required|min:3',
            'email_user' => 'required|email',
            'contact_no_user' => 'required|digits:10',
            'password' => 'required|regex:/(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[\W_])/u',
        ]);
        if ($validator->fails()) {
            $data_responce = ["status" => "danger", "data" => "Validation error", "error" => $validator->messages()];
            return response()->json($data_responce, 200);
        }else{
            if(!empty($check) && $check == 'check'){
                $data_responce = ["status" => "success", "data" => "Validate", "error" => 0];
                return response()->json($data_responce, 200);
            }
        }
        $update["username"] = $request->username;
        $account_number =  Tenant::max('account_number')+1;
        // dd($account_number);
        $update["account_number"] = $account_number;
        // $update["tenant_id"] = $request->agent_id;
        $update["tenant_id"] = $request->agent_id_qstring;
        $update["password"] = Hash::make($request->password);
        $update["firstname"] = $request->firstname_user;
        // $update["name"] = $request->firstname_user;
        $update["lastname"] = $request->lastname_user;
        $update["email"] = $request->email_user;
        $update["phoneno"] = $request->contact_no_user;
        // $update["role"] = 'ADMIN';
        $update["role"] = ($request->role && !empty($request->role)) ? $request->role : 'AGENT';        
        $update["superuser"] = 0;
        $update["status"] = 'ENABLED';
        // dd($update);
        // DB::enableQueryLog();
        try {
            if ($request->id == 0) {
                $update["create_at"] = date("Y-m-d H:i:s");
                // $update["created_at"] = date("Y-m-d H:i:s");
                $add_user = User::create($update);
                // dd(\DB::getQueryLog());
                if ($inserted_id = $add_user->id) {
                    return response()->json(["status" => "success", "data" => $inserted_id, "error" => 0]);
                }
            } else {
                $update["updated_at"] = date('Y-m-d H:i:s');
                $user = User::where("id", $request->id)->update($update);
                //dd($user);
                // dd(\DB::getQueryLog());
                return response()->json(["status" => "success", "data" => "Update Sucessfully", "error" => 0]);
            }


            return response()->json(["status" => "fail", "data" => "Something wrong", "error" => 0]);
        } catch (\Exception $e) {
            return response()->json(["status" => "fail", "data" => "Record not found", "error" => $e->getMessage()]);
        }
    }
    public function billplan_get_on_type(Request $request){
        
        $billplan_list = BillPlan::select('id', 'name');
        if($request->type != ''){
            $billplan_list = $billplan_list->where('type', $request->type);
        }
        $billplan_list = $billplan_list->get();        
        foreach($billplan_list as $data){
            $response1[$data->id] = $data->name;
        }
        $response['billplan_list'] = $response1;
        return response()->json(["status" => "success", "data" => $response, "error" => 0]);

    }
    public function customer_bill_plan_add_ajex(Request $request){
        
        // \DB::enableQueryLog();
        $response['message'] = "Added Successfully";
        $status = [];
        $validator = Validator::make($request->all(), [                       
            'billplan_type' => 'required',
            'billplan_id' => 'required',
            'taxation' => 'required',
            'call_per_seconds' => 'required',
            'concurrent_call' => 'required',
            'port' => 'required',

        ]);
        if ($validator->fails()) {
            $data_responce = ["status" => "danger", "data" => "Validation error", "error" => $validator->messages()];
            return response()->json($data_responce, 200);
        }
        $tenant = Tenant::find($request->tenant_id);
        $billplan = Billplan::find($request->billplan_id);
        if($tenant){
            try {
                $tenant_low_balance_notification['notification_threshold'] = 10;
                $tenant_low_balance_notification['Isnotification'] = 'YES';
                $tenant_low_balance_notification['tenant_account_code'] = $tenant->account_number;
                $low_balance_save = TenantLowBalanceNotification::create($tenant_low_balance_notification);
                $status['TenantLowBalanceNotification'] = $low_balance_save;
            } catch (\Exception $e) {
                return response()->json(["status" => "fail", "data" => "Issue in TenantLowBalanceNotification", "error" => $e->getMessage()]);
            }           
            
            
            if($request->billplan_type == 'PREPAID'){
                
                $tenant_finance_insert['credit_limit'] = 0;
                $tenant_finance_insert['late_fee'] = 0;
                try {
                    $tenant_log['account_number'] = $tenant->account_number;
                    $tenant_log['summary'] = "Account Created - Montly Payment charges applied";
                    $tenant_log['debit'] = $billplan->monthly_payment;
                    $tenant_log['balance'] = -1 * $billplan->monthly_payment;
                    $tenant_log['created_date'] = date("Y-m-d");
                    $tenant_log_save = TenantLog::create($tenant_log);
                    $status['TenantLog'] = $tenant_log_save;
                    
                } catch (\Exception $e) {
                    return response()->json(["status" => "fail", "data" => "Issue in TenantLog", "error" => $e->getMessage()]);
                }               
               
                try {
                    $tenant_update['balance'] = $tenant_log['balance'];
                    $tenant_update['effective_balance'] = $tenant_log['balance'];
                    if ($tenant_update['balance'] < 0) {
                        $tenant_update['suspended'] = 'YES';
                        $tenant_update['suspend_reason'] = 'ACTIVATION';
                    }

                    $port_price = bcmul($billplan->per_channel_price, $request->port,5);
                    $tenant_log2['account_number'] = $tenant->account_number;
                    $tenant_log2['summary'] = "Port charges applied for number of ports : ".$request->port;
                    $tenant_log2['debit'] = $port_price;
                    $tenant_log2['balance'] = bcsub($tenant_update['balance'], $port_price,5);
                    $tenant_log2['created_date'] =date("Y-m-d");
                    $tenant_log2_save = TenantLog::create($tenant_log2);
                    $status['TenantLog2'] = $tenant_log2_save;
                    
                } catch (\Exception $e) {
                    return response()->json(["status" => "fail", "data" => "Issue in TenantLog second record", "error" => $e->getMessage()]);
                }                
              
                try {
                    $tenant_update['balance'] = $tenant_log['balance'];
                    $tenant_update['effective_balance'] = $tenant_log2['balance'];
                    if ($tenant_update['balance'] < 0) {
                        $tenant_update['suspended'] = 'YES';
                        $tenant_update['suspend_reason'] = 'ACTIVATION';
                    }
    
    
                    $minute_log['account_number'] = $tenant->account_number;
                    $minute_log['type'] = 'ADD';
                    $minute_log['monthly_minutes'] = $tenant->monthly_mins;
                    $minute_log['additional_minutes'] = $tenant->additional_mins;
                    $minute_log['comment'] = 'Plan Minutes';
                    $minute_log['balance_monthly_min'] = $tenant->monthly_mins;
                    $minute_log['balance_additional_min'] = $tenant->additional_mins;
                    $minute_log_save = TenantMinuteLog::create($minute_log);
                    $status['TenantMinuteLog'] = $minute_log_save;
                    
                } catch (\Exception $e) {
                    return response()->json(["status" => "fail", "data" => "Issue in TenantMinuteLog", "error" => $e->getMessage()]);
                }              
                
            }
            

            try {
                // $texation_data = Taxation::first('id', $request->taxation);
                $tenant_finance_insert['agent_id'] = (Auth::user()->role == "AGENT") ? Auth::user()->tenant_id : Auth::user()->id;
                $tenant_finance_insert['account_number'] = $tenant->account_number;
                $tenant_finance_insert['invoice_generate_date'] = date("Y-m-d");
                $tenant_finance_insert['invoice_start_date'] = date("Y-m-d");
                $tenant_finance_insert['invoice_end_date'] = date('Y-m-d')." 23:59:59";
                $tenant_finance_insert['billplan_type'] = $request->billplan_type;
                $tenant_finance_insert['billplan_id'] = $request->billplan_id;
                $tenant_finance_insert['taxation'] = $request->taxation;
                $tenant_finance_insert['port'] = $request->port;
                $tenant_finance_insert['concurrent_call'] = $request->concurrent_call;
                $tenant_finance_insert['call_per_seconds'] = $request->call_per_seconds;                
                $tenant_finance_save = TenantFinance::create($tenant_finance_insert);
                $status['TenantFinance'] = $tenant_finance_save;
                
            } catch (\Exception $e) {
                return response()->json(["status" => "fail", "data" => "Issue in TenantFinance", "error" => $e->getMessage()]);
            }
            // $response['status']= $status;          
            $data_responce = ["status" => "danger", "data" => $response, "error" =>0];
            return response()->json($data_responce, 200);
        }

       
    }
    public function calculateRate(){

    }
}
