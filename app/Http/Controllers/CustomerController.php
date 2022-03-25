<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Store;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        $customer->password = Hash::make($req->password);
        $customer->phone_code = "";
        $customer->contact_no = "";
        $customer->country = "";
        $customer->state = "";
        $customer->city = "";
        $customer->street = "";
        $customer->profile_uri = "";
        $customer->status = 0;

        if($this -> checkEmail($req->email))
        {
            if($this -> checkContactNo($req->contact_no))
            {
                $error = $this -> sendEmailVerification($req->uuid, $req->email);
                if($error == "") {
                    $customer -> save();
                    return ["success" => "success"];
                }
                return ["error" => $error];
            }
            else
            {
                return ["error"=>"Contact number is already registered!"];
            }
        }
        else
        {
            return ["error" => "Email is already registered!."];
        }
    }
    function resendEmailVerification(Request $req)
    {
        $error = $this -> sendEmailVerification($req->uuid, $req->email);
        return $error;
    }

    function sendEmailVerification($uuid, $email)
    {
        require base_path("vendor/autoload.php");

        $mail = new PHPMailer(true);
        $emailFrom = 'admin@thriftee.com';
        $link = 'http://localhost:3000'.'/customer/account_completion?'.$uuid;

        try
        {
            //Recipients
            $mail->setFrom($emailFrom, 'Thriftee');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Customer Account Completion';
            $mail->Body    = 'Hi!<br>
                            Please click the link below to complete the creation of your customer account for <h1>Thriftee</h1>.<br>
                            <h1>'.$link.'</h1>';

            $mail->AltBody = 'Hi! Please click the link to complete the creation of your customer account for Thriftee.'.$link;

            if($mail->send())
            {
                return "";
            }
            else
            {
                return "Email not send";
            }


        }
        catch (Exception $e)
        {
            return $e;
        }
    }

    function checkEmail($email)
    {
        $customer = Customer::where('email', $email)->get();
        $store = Store::where("email", $email)->get();
        if (count($customer) > 0 || count($store) > 0) return false;
        else return true;
    }

    function checkContactNo($email)
    {
        $customer = Customer::where('email', $email)->get();
        if (count($customer) > 0) return false;
        else return true;
    }
    function login(Request $req)
    {
        $customer = Customer::where('email', $req->email)->first();
        if($customer)
        {

            if(Hash::check($req->password, $customer->password)) {
                if($customer-> status == 1) {
                    return $customer;
                }
                else {
                    if($customer->status == 0) {
                        return ["error" => "Incorrect email or password!"];
                    }
                    else {
                        return ["error" => "This account is terminated!"];
                    }
                }
            }
            else {
                return ["error" => "Incorrect email or password!"];
            }

        }
        else
        {
            return ["error" => "There's no account associated with this email"];
        }

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
                if(Hash::check($req->current_password, $customer->password)) {

                    //Update password
                    $result = $customer->update(['password' => Hash::make($req->new_password)]);
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
            'phone_code' => $req->phone_code,
            'contact_no' => $req->contact_no,
            "country" => $req->country,
            'state' => $req->state,
            'city' => $req->city,
            'street' => $req->street
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

    function getStatus(Request $req){
        $result = Customer::where("uuid", $req->uuid)->first();
        return $result->status;
    }
}
