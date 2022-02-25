<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class StoreController extends Controller
{
    //
    function getStores()
    {
        $result = Store::all();
        return $result;
    }

    function addStore(Request $req)
    {
        $store = new Store;
        $store->uuid = $req->uuid;
        $store->store_id = $req->store_id;
        $store->store_name = $req->store_name;
        $store->contact_no = $req->contact_no;
        $store->email = $req->email;
        $store->province = $req->province;
        $store->municipality = $req->municipality;
        $store->barangay = $req->barangay;
        $store->street = $req->street;
        $store->image_uri = '';
        $store->password = '';
        $store->status = 0;
        // -1 = terminated
        // 0 = pending(newly created), need to activate or add password
        // 1 = active

        //$store -> password = Hash::make($req -> input('password'));

        $error = "";

        if($this -> checkEmail($req->email))
        {
            if($this -> checkContactNo($req->contact_no))
            {
                $error = $this -> sendCompletionLink($req->uuid, $req->email);
                if($error == "") {
                    $store -> save();
                }
            }
            else
            {
                $error = "Contact number is already registered!";
            }
        }
        else
        {
            $error = "Email is already registered!";
        }

        return $error;
    }

    function checkEmail($email)
    {
        $store = Store::where('email', $email)->get();
        if(count($store) > 0) return false;
        else return true;
    }

    function checkContactNo($contact_no)
    {
        $store = Store::where('contact_no', $contact_no)->get();
        if(count($store) > 0) return false;
        else return true;
    }


    function resendCompletionLink(Request $req)
    {
        $error = $this -> sendCompletionLink($req->uuid, $req->email);
        return $error;
    }

    function sendCompletionLink($uuid, $email)
    {
        require base_path("vendor/autoload.php");

        $mail = new PHPMailer(true);
        $emailFrom = 'admin@thriftee.com';
        $link = 'http://localhost:3000'.'/store/account/completion?store='.$uuid;

        try
        {
            //Recipients
            $mail->setFrom($emailFrom, 'Thriftee');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Store Account Completion';
            $mail->Body    = 'Hi!<br>
                            Please click the link below to complete the creation of your store account for <h1>Thriftee</h1>.<br>
                            <h1>'.$link.'</h1>';

            $mail->AltBody = 'Hi! Please click the link to complete the creation of your store account for Thriftee.'.$link;

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

    function login(Request $req)
    {
        $store = Store::where('email', $req->email)->first();
        if(!$store || !Hash::check($req->password, $store->password)) {
           return ["error" => "Incorrect email or password!"];
        }
        return $store;
    }

    function updatePassword(Request $req)
    {
        $store = Store::where('uuid', $req->uuid)->first();

        if($store) {
            $result = $store->update(['password' => Hash::make($req->password)]);

            if($result && $store->status == 0) {
                $result = $store->update(['status' => 1]);
            }
            return $result;
        }
        else{
            return ["error" => "Store not found!"];
        }
    }

    function checkPassword(Request $req)
    {
        $store = Store::where('uuid', $req->uuid)->first();
        if(!Hash::check($req->password, $store->password)) {
           return ["error" => "Incorrect password!"];
        }
        return true;
    }

    function getStatus(Request $req)
    {
        $store = Store::where('uuid', $req->uuid)->first();

        return $store->status;
    }

    function deleteStore(Request $req)
    {
        $result = Store::where('uuid', $req->uuid)->delete();
        return $result;
    }
}
