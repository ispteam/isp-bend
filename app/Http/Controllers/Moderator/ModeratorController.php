<?php

namespace App\Http\Controllers\Moderator;
use App\Http\Validation\ValidationError;
use App\Models\Moderator\Moderator;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class ModeratorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

   

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * Why do we use try and catch ?
         * Because it has two ways, succeed and failure
         * Succeessful part is inside try which means all processes considered to be passed and succeed.
         * catch which accepts error object to catch any error will happen in try.
         * If an error happened in try, catch will handle it and will stop executing try from working.
         */
        try{
            /**
             * This variable rule holds all validation that can be implemented to the column fields.
             * You will see regex for both nameInArabic, name. These are expressions to ensure the coming data are clean without any smbols or characters might cause a problem to the systme.
             * For example, nameInArabic => /^[؀-ۿ\s]+$/ : "احمد سالم @kknd" this will return an error because  it accepts only arabic characters.
             * For example, name => /^[A-Za-z\s]+$/ : "Jo bat <><>##" this will return an error because  it accepts only english characters.
             * For example, password => /^[A-Za-z\s]+$/ : "AZSWA\@WASS 111" this will return true because  it accepts only english characters from A-Z,a-z and any character @,$,#,etc...
             */
        
            $rules = [
                "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
                "email" =>"required|email|unique:moderators,email",
                "phone"  => "required|string|min:10|max:13|unique:clients,phone|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/"
            ];
    
            /**
             * ValidationError is a class contains validationUserInput function which accepts 2 arguments
             * First one is the request which holds all the coming data to be validated
             * Second argument is the rules that will be matched with the coming data to validate.
             */
            $validator = ValidationError::validationUserInput($request, $rules);

            /**
             * Here we will check if some of the fields are failed, so the system will return a validation error of the specific field.
             */
            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = $validator->errors();
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }


            /**
             * Here We create a new Moderator if all user inputs passed the validation.
             * We hash the password to encrypt it from stealing.
             */
            $moderator = Moderator::create([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "password" => Hash::make($request->input("password")),
                "email"=> $request->input("email"),
            ]);


            /**
             * Here we check if there a Moderator inserted or not.
             * If not inserted successfully. The system returns an error message.
             */
            if(count(array($moderator))== 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "Moderator has successfully registered",
                "messageInArabic" => "تم تسجيل المشرف بنجاح",
                "statusCode" => 201,
            ]);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "message" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ]);
            
        }
        
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Moderator  $moderator
     * @return \Illuminate\Http\Response
     */
    public function show(Moderator $moderator)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Moderator  $moderator
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        try{

            /**
             * Here we check the coming Moderator id wheter a number or not.
             * If a number we will store it in the ModeratorId variable.
             * If not a number we will assign the variable to zero
             */
            $moderatorId = intval($request->input("moderatorId")) ? $request->input("moderatorId") : 0;

            /**
             * System will call the Moderator with the coming id
             */
            $moderator = Moderator::where("moderatorId", $moderatorId)->get();

            /**
             * System checks the Moderator if exists or not.
             * If no Moderator is found in the Moderators table, system will return an error
             */
            if(count($moderator) == 0){
                $error = new Error(null);
                $error->errorMessage = "There is no Moderator with this id";
                $error->messageInArabic = "لا يوجد مشرف مسجل";
                $error->statusCode = 404;
                throw $error;
            }

            $rules = [
                "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "phone"  => "required|string|min:10|max:13|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/"
            ];
    
        
            $validator = ValidationError::validationUserInput($request, $rules);

           
            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = $validator->errors();
                $error->messageInArabic= "";
                $error->statusCode = 422;
                throw $error;
            }

            

           
            $moderator = Moderator::where("ModeratorId", $moderatorId)->update([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
            ]);

        


            /**
             * Here we check if the Moderator updated or not.
             * If not updated successfully. The system returns an error message.
             */
            if($moderator == 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "Moderator has been updated successfully",
                "messageInArabic" => "تم تحديث المشرف بنجاح",
                "statusCode" => 200,
            ]);
            

        }catch(Error $err){
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
     * @param  \App\Models\Moderator  $moderator
     * @return \Illuminate\Http\Response
     */
    public function destroy(Moderator $moderator)
    {
        //
    }
}
