<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    //
    function addCustomer(Request $req)
    {
        $customer = new Customer();
        $customer->uuid = Str::uuid();
        $customer->lname = $req->lname;
        $customer->fname = $req->fname;
        $customer->email = $req->email;
        $customer->contact_no = "";
        $customer->province = "";
        $customer->municipality = "";
        $customer->barangay = "";
        $customer->street = "";
        $customer->profile_uri = "";
        $customer->password = Hash::make($req->password);
        $customer->status = 0;

        if ($this->checkEmail($req->email)) {
            if ($this->checkContactNo($req->email)) {
                $customer->save();
                return ["success" => "Customer added successfully"];
            } else {
                return ["warning" => "Contact number is already registered!"];
            }
        } else {
            return ["warning" => "Email is already registered"];
        }
    }

    function checkEmail($email)
    {
        $customer = Customer::where('email', $email)->get();
        if (count($customer) > 0) return false;
        else return true;
    }

    function checkContactNo($email)
    {
        $customer = Customer::where('email', $email)->get();
        if (count($customer) > 0) return false;
        else return true;
    }

    function updatePassword(Request $req)
    {
        $customer = Customer::where('uuid', $req->uuid)->first();

        if($customer) {
            //New Account
            if($customer->status == 0) {
                //Update password
                $result = $customer->update(['password' => Hash::make($req->password)]);

                if($result) {
                    //Update status
                    $result = $customer->update(['status' => 1]);
                    if($result){
                        return $result;
                    }
                    else {
                        return ["error" => "Error updating status!"];
                    }

                }
                else{
                    return ["error" => "Error updating password!"];
                }
            }

            //Old Account
            else {

                //Check current password
                if(Hash::check($req->password, $customer->password)) {

                    //Update password
                    $result = $customer->update(['password' => Hash::make($req->password)]);
                    if($result){
                        return $result;
                    }
                    else {
                        return ["error" => "Error updating password!"];
                    }
                }
                else {
                    return ["error" => "Incorrect password!"];
                }
            }
        }
        else{
            return ["error" => "Customer not found!"];
        }
    }

    function checkPassword(Request $req)
    {
        $customer = Customer::where('uuid', $req->uuid)->first();
        if(!Hash::check($req->password, $customer->password)) {
           return ["error" => "Incorrect password!"];
        }
        return true;
    }

    function updateCustomer(Request $req)
    {
        $customer = Customer::where('uuid', $req->uuid)->first();
        $result = $customer->update([
            'lname' => $req->lname,
            'fname' => $req->fname,
            'email' => $req->email,
            'contact_no' => $req->contact_no,
            'province' => $req->province,
            'municipality' => $req->municipality,
            "barangay" => $req->barangay,
            'street' => $req->street,
            "profile_uri" => $req->profile_uri,
        ]);
        return $result;
    }

    function getCustomerByUUID(Request $req){
        $result = Customer::where("uuid", $req->uuid)->first();
        return $result;
    }

    function getCustomerByEmail(Request $req){
        $result = Customer::where("email", $req->email)->first();
        return $result;
    }
}
