<?php

namespace App\Http\Controllers;
use App\Models\User\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{

    public function addToken(Request $request){
        try{
            
            
            $adminId = intval($request->input("adminId")) ? $request->input("adminId") : 0;

          
            $admin = User::where("uid", $adminId)->first();

            
            if($admin == null){
                $error = new Error(null);
                $error->errorMessage = "There is no admin with this id";
                $error->messageInArabic = "لا يوجد مدير مسجل";
                $error->statusCode = 404;
                throw $error;
            }


           $admin = User::where("uid", $adminId)->update([
               "token" => $request->input("token"),
           ]);

           /**
            * System will send a response to the admin to notify him the registration was succeed
            */
           return response()->json([
               "statusCode" => 200,
           ],200);

   
       }catch(Error $err){
           return response()->json([
               "message" => $err->errorMessage,
               "messageInArabic" => $err->messageInArabic,
               "statusCode" => $err->statusCode
           ]);
       }
    }

    public function logout(Request $request){
        $uid = $request->input("uid");

        User::where('uid', $uid)->update([
            "token" => null
        ]);
    }

    // public function testEmail() {
    //     Mail::raw('Welcome in our website, We"re glad to be in our team !', function ($message) {
    //         $message->from('no-reply@isp.com', 'ISP');
    //         $message->sender('no-reply@isp.com', 'ISP');
    //         $message->to('abofahad.en@gmail.com', 'ABDULAZIZ');
    //         $message->subject('Welcome');
    //     });

    //     return response()->json([
    //         "message" => "message sent successfully"
    //     ], 200);
    // }
} 
