<?php

namespace App\Http\Controllers\Admin;

use App\Http\Validation\ValidationError;
use App\Models\Admin\Admin;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
             /**
             * This variable rule holds all validation that can be implemented to the column fields.
             * You will see regex for both nameInArabic, name. These are expressions to ensure the coming data are clean without any smbols or characters might cause a problem to the systme.
             * For example, nameInArabic => /^[؀-ۿ\s]+$/ : "احمد سالم @kknd" this will return an error because  it accepts only arabic characters.
             * For example, name => /^[A-Za-z\s]+$/ : "Jo bat <><>##" this will return an error because  it accepts only english characters.
             * For example, password => /^[A-Za-z\s]+$/ : "AZSWA\@WASS 111" this will return true because  it accepts only english characters from A-Z,a-z and any character @,$,#,etc...
             * For example, phone => ^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/ : "+966550099881" this will return true because  it accepts only numbers and the country entery number.
             */
            $rules = [
                "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
                "email" =>"required|email|unique:admins,email",
                "phone"  => "required|string|min:10|max:13|unique:admins,phone|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
            ];

            $validator= ValidationError::validationUserInput($request, $rules);

            
            /**
             * Here we will check if some of the fields are failed, so the system will return a validation error of the specific field.
             */
            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = $validator->errors();
                $error->messageInArabic= "";
                $error->statusCode = 422;
                throw $error;
            }

            /**
             * Here is the important part, Why do we need to sanitize the address rather than using regex?
             * The answer is: because the address field is an object which contains multiple fields inside of it. Regex will not go inside the object and check whether some fileds matche the pattern or not. 
             */

            $admin = Admin::create([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "password" => Hash::make($request->input("password")),
                "email"=> $request->input("email"),
                "enterId" => uniqid($request->input("name")[0].$request->input("name")[1]."-", true),
                "phone" => $request->input("phone")
            ]);


            /**
             * Here we check if there a admin inserted or not.
             * If not inserted successfully. The system returns an error message.
             */
            if(count(array($admin))== 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            /**
             * System will send a response to the admin to notify him the registration was succeed
             */
            return response()->json([
                "message" => "admin has successfully registered",
                "messageInArabic" => "تم تسجيل المسؤول بنجاح",
                "statusCode" => 201,
            ]);

    
        }catch(Error $err){
            //If arabic message is existed, then system will display the error along with arabic message
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
        try{
            
            
            $adminId = intval($request->input("adminId")) ? $request->input("adminId") : 0;

          
            $admin = Admin::where("adminId", $adminId)->get();

            
            if(count($admin) == 0){
                $error = new Error(null);
                $error->errorMessage = "There is no admin with this id";
                $error->messageInArabic = "لا يوجد عميل مسجل";
                $error->statusCode = 404;
                throw $error;
            }
           $rules = [
               "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
               "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
           ];

           $validator= ValidationError::validationUserInput($request, $rules);

           
           /**
            * Here we will check if some of the fields are failed, so the system will return a validation error of the specific field.
            */
           if($validator->fails()){
               $error = new Error(null);
               $error->errorMessage = $validator->errors();
               $error->messageInArabic= "";
               $error->statusCode = 422;
               throw $error;
           }

           /**
            * Here is the important part, Why do we need to sanitize the address rather than using regex?
            * The answer is: because the address field is an object which contains multiple fields inside of it. Regex will not go inside the object and check whether some fileds matche the pattern or not. 
            */

           $admin = Admin::where("adminId", $adminId)->update([
               "nameInArabic" => $request->input("nameInArabic"),
               "name" => $request->input("name"),
               "email"=> $request->input("email"),
               "phone" => $request->input("phone")
           ]);


           /**
            * Here we check if there a admin inserted or not.
            * If not inserted successfully. The system returns an error message.
            */
           if(count(array($admin))== 0 ){
               $error = new Error(null);
               $error->errorMessage = "There is something wrong happened";
               $error->messageInArabic = "حصل خطأ";
               $error->statusCode = 500;
               throw $error;
           }

           /**
            * System will send a response to the admin to notify him the registration was succeed
            */
           return response()->json([
               "message" => "admin has successfully updated",
               "messageInArabic" => "تم تعديل بيانات المسؤول بنجاح",
               "statusCode" => 201,
           ]);

   
       }catch(Error $err){
           //If arabic message is existed, then system will display the error along with arabic message
           return response()->json([
               "message" => $err->errorMessage,
               "messageInArabic" => $err->messageInArabic,
               "statusCode" => $err->statusCode
           ]);
       }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        //
    }
}
