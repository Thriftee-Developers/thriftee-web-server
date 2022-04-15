<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Str;

class StoreController extends Controller
{
    //
    function getAllStores()
    {
        $result = DB::select(
            "SELECT
                stores.store_name,
                stores.uuid,
                stores.store_id,
                Count(DISTINCT ratings.uuid) as rating_count,
                Count(DISTINCT products.uuid) as count,
                AVG(ratings.rate) as rating
            FROM stores

            LEFT JOIN ratings
            ON stores.uuid = ratings.store

            LEFT JOIN products
            ON  products.store = stores.uuid

            WHERE stores.status = 1

            GROUP BY stores.uuid"
        );
        return $result;
    }

    function getStore(Request $req)
    {
        $result = Store::where('uuid', $req->uuid)->first();
        return $result;
    }

    function getStoreByID(Request $req)
    {
        $result = DB::select(
            "SELECT
                stores.store_name,
                stores.uuid,
                stores.store_id,
                Count(DISTINCT ratings.uuid) as rating_count,
                Count(DISTINCT products.uuid) as count,
                AVG(ratings.rate) as rating
            FROM stores

            LEFT JOIN ratings
            ON stores.uuid = ratings.store

            LEFT JOIN products
            ON  products.store = stores.uuid

            WHERE stores.store_id = '$req->store_id'

            GROUP BY stores.uuid"
        );
        return $result;
    }

    function addStore(Request $req)
    {
        $store = new Store;
        $store->uuid = Str::uuid();
        $store->store_id = $req->store_id;
        $store->store_name = $req->store_name;
        $store->phone_code = $req->phone_code;
        $store->contact_no = $req->contact_no;
        $store->email = $req->email;
        $store->country = $req->country;
        $store->state = $req->state;
        $store->city = $req->city;
        $store->street = $req->street;
        $store->image_uri = '';
        $store->password = '';
        $store->status = 0;
        // -1 = terminated
        // 0 = pending(newly created), need to activate or add password
        // 1 = active

        //$store -> password = Hash::make($req -> input('password'));

        if ($this->checkEmail($req->email)) {
            if ($this->checkContactNo($req->contact_no)) {
                $error = $this->sendCompletionLink($store->uuid, $req->email);
                if ($error == "") {
                    $store->save();
                    return ["success" => "success"];
                }
            } else {
                return ["error" => "Contact number is already registered!"];
            }
        } else {
            return ["error" => "Email is already registered!"];
        }
    }

    function checkEmail($email)
    {
        $customer = Customer::where('email', $email)->get();
        $store = Store::where("email", $email)->get();
        if (count($customer) > 0 || count($store) > 0) return false;
        else return true;
    }

    function checkContactNo($contact_no)
    {
        $store = Store::where('contact_no', $contact_no)->get();
        if (count($store) > 0) return false;
        else return true;
    }


    function resendCompletionLink(Request $req)
    {
        $error = $this->sendCompletionLink($req->uuid, $req->email);
        return $error;
    }

    function sendCompletionLink($uuid, $email)
    {
        require base_path("vendor/autoload.php");

        $mail = new PHPMailer(true);
        $emailFrom = 'admin@thriftee.com';
        $link = 'http://localhost:3000' . '/account_completion?' . $uuid;

        try {
            //Recipients
            $mail->setFrom($emailFrom, 'Thriftee');
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Store Account Completion';
            $mail->Body    = 'Hi!<br>
                            Please click the link below to complete the creation of your store account for <h1>Thriftee</h1>.<br>
                            <h1>' . $link . '</h1>';

            $mail->AltBody = 'Hi! Please click the link to complete the creation of your store account for Thriftee.' . $link;

            if ($mail->send()) {
                return "";
            } else {
                return "Email not send";
            }
        } catch (Exception $e) {
            return $e;
        }
    }

    function login(Request $req)
    {
        $store = Store::where('email', $req->email)->first();
        if ($store) {

            if (Hash::check($req->password, $store->password)) {
                if ($store->status == 1) {
                    return $store;
                } else {
                    if ($store->status == 0) {
                        return ["error" => "Incorrect email or password!"];
                    } else {
                        return ["error" => "This account is terminated!"];
                    }
                }
            } else {
                return ["error" => "Incorrect email or password!"];
            }
        } else {
            return ["error" => "There's no account associated with this email"];
        }
    }

    function updatePassword(Request $req)
    {
        $store = Store::where('uuid', $req->uuid)->first();

        if ($store) {
            //New Account
            if ($store->status == 0) {
                //Update password
                $result = $store->update(['password' => Hash::make($req->password)]);

                if ($result) {
                    //Update status
                    $result = $store->update(['status' => 1]);
                    if ($result) {
                        return $result;
                    } else {
                        return ["error" => "Error updating status!"];
                    }
                } else {
                    return ["error" => "Error updating password!"];
                }
            }

            //Old Account
            else {

                //Check current password
                if (Hash::check($req->current_password, $store->password)) {

                    //Update password
                    $result = $store->update(['password' => Hash::make($req->new_password)]);
                    if ($result) {
                        return $result;
                    } else {
                        return ["error" => "Error updating password!"];
                    }
                } else {
                    return ["error" => "Incorrect password!"];
                }
            }
        } else {
            return ["error" => "Store not found!"];
        }
    }

    function checkPassword(Request $req)
    {
        $store = Store::where('uuid', $req->uuid)->first();
        if (!Hash::check($req->password, $store->password)) {
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

    function updateStore(Request $req)
    {
        $store = Store::where('uuid', $req->uuid)->first();
        $result = $store->update([
            'store_name' => $req->store_name,
            'phone_code' => $req->phone_code,
            'contact_no' => $req->contact_no,
            'country' => $req->country,
            'state' => $req->state,
            'city' => $req->city,
            'street' => $req->street,
        ]);
    }
}
