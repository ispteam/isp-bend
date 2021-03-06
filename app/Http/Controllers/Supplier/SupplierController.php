<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Validation\ValidationError;
use App\Models\Supplier\Supplier;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\RRequest\Request as Rrequest;
use App\Models\User\User;

class SupplierController extends Controller
{

    public function __construct()
    {
        $this->middleware("isAuthorized")->except(["index", "show", "store", "suppliersEmails", "allCarsPref"]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       try{
            $suppliers = Supplier::with("account")->get();
            return response()->json([
                "suppliers" => $suppliers,
                "statusCode" => 200
            ], 200);
       }catch(Error $err){
            return response()->json([
                "message" => $err->message,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
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
             * For example, nameInArabic => /^[??-??\s]+$/ : "???????? ???????? @kknd" this will return an error because  it accepts only arabic characters.
             * For example, name => /^[A-Za-z\s]+$/ : "Jo bat <><>##" this will return an error because  it accepts only english characters.
             * For example, password => /^[A-Za-z\s]+$/ : "AZSWA\@WASS 111" this will return true because  it accepts only english characters from A-Z,a-z and any character @,$,#,etc...
             * For example, phone => ^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/ : "+966550099881" this will return true because  it accepts only numbers and the country entery number.
             */

        
            $rules = [
                "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
                "email" =>"required|email|unique:users_info,email",
                "phone"  => "required|string|min:10|max:13|unique:users_info,phone|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
                "companyInEnglish" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "companyInArabic" => "required|string|min:2|max:30|regex:/^[??-??\s]+$/",
                "companyCertificate" => "required|max:10000|mimes:png,jpg,jpeg,pdf"
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
                $error->validationMessage = $validator->errors();
                $error->errorMessage = "";
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

            
            
            $supplierAccount = User::create([
                "password" => Hash::make($request->input("password")),
                "email"=> $request->input("email"),
                "phone" => $request->input("phone"),
                "userType" => "2",
            ]);
            
            
            if($supplierAccount == null ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->validationMessage = null;
                $error->messageInArabic = "?????? ??????";
                $error->statusCode = 500;
                throw $error;
            }
            
            
            $certificateName = $supplierAccount->id . "_" . $request->input("companyInEnglish") . "_certificate_" . $request->file("companyCertificate")->extension();
            
            $request->file("companyCertificate")->move(public_path("certificates"), $certificateName);
            
            /**
             * Here We create a new supplier if all user inputs passed the validation.
             * We hash the password to encrypt it from stealing.
             * We convert the address field into json because the column address type is in json format
             */
            $supplier = Supplier::create([
                "supplierId" => $supplierAccount->id,
                "companyInEnglish" => $request->input("companyInEnglish"),
                "companyInArabic" => $request->input("companyInArabic"),
                "companyCertificate" => $certificateName,
                "pref" => $request->input("pref"),
                "carsPref" => $request->input("carsPref")
            ]);


            /**
             * Here we check if there a supplier inserted or not.
             * If not inserted successfully. The system returns an error message.
             */
            if($supplier == null ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "?????? ??????";
                $error->statusCode = 500;
                throw $error;
            }

             /**
             * System will send a response to the client to notify him the registration was succeed
             */
            return response()->json([
                "message" => "Supplier has successfully registered, Wait for the approval",
                "messageInArabic" => "???? ?????????? ?????????????? ???????????? ???????? ?????????? ?????? ????????????????",
                "statusCode" => 201,
            ],201);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "validationMessage" => $err->validationMessage,
                "statusCode" => $err->statusCode
            ]);
            
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function show($supplierId)
    {
        try{

            $supplierId =   $supplierId = intval($supplierId) ? $supplierId : 0;

            $supplier = Supplier::with("account")->where("supplierId", $supplierId)->first();
            if($supplier == null ){
                $error = new Error(null);
                $error->errorMessage = "There is no supplier found";
                $error->messageInArabic = "???? ?????? ?????????? ????????";
                $error->statusCode = 404;
                throw $error;
            }
            return response()->json([
                "supplier" => $supplier,
                "statusCode" => 200
            ], 200);
       }catch(Error $err){
            return response()->json([
                "message" => $err->message,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
       }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Supplier $supplier)
    {
        try{
           /**
             * Here we check the coming client id wheter a number or not.
             * If a number we will store it in the supplierId variable.
             * If not a number we will assign the variable to zero
             */
            $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;

            /**
             * System will call the client with the coming id
             */
            $supplierAccount = User::where("uid", $supplierId)->first();
            $supplier = Supplier::where("supplierId", $supplierId)->first();

            /**
             * System checks the supplier if exists or not.
             * If no supplier is found in the suppliers table, system will return an error
             */
            if($supplier == null || $supplierAccount == null){
                $error = new Error(null);
                $error->errorMessage = "There is no supplier with this id";
                $error->messageInArabic = "???? ???????? ?????????? ????????";
                $error->statusCode = 404;
                throw $error;
            }
        
            $rules = [
                "email" =>"required|email",
                "phone"  => "required|string|min:10|max:13|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
                "companyInEnglish" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "companyInArabic" => "required|string|min:2|max:30|regex:/^[??-??\s]+$/"
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
                $error->validationMessage = $validator->errors();
                $error->errorMessage = "";
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

        

            /**
             * Here We create a new supplier if all user inputs passed the validation.
             * We hash the password to encrypt it from stealing.
             * We convert the address field into json because the column address type is in json format
             */
            User::where("uid", $supplierId)->update([
                "email"=> $request->input("email"),
                "phone" => $request->input("phone")
            ]);



            $supplier = Supplier::where("supplierId", $supplierId)->update([
                "companyInEnglish" => $request->input("companyInEnglish"),
                "companyInArabic" => $request->input("companyInArabic"),
                "pref" => $request->input("pref"),
                "carsPref" => $request->input("carsPref"),
                "updateRequest" => "0"
            ]);

            

             /**
             * System will send a response to the client to notify him the registration was succeed
             */
            return response()->json([
                "message" => "Supplier has successfully updated",
                "messageInArabic" => "???? ?????????? ?????????????? ??????????",
                "statusCode" => 201,
            ],201);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "validationMessage" => $err->validationMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
            
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Supplier  $supplier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{

            $supplierId =   $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;

            $supplierAccount = User::where("uid", $supplierId)->first();


            if($supplierAccount == null ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "???? ?????? ?????????? ????????";
                $error->statusCode = 404;
                throw $error;
            }


            return response()->json([
                "message" => "Supplier has been deleted",
                "messageInArabic" => "???? ?????? ?????????????? ??????????",
                "statusCode" => 200
            ], 200);

       }catch(Error $err){
            return response()->json([
                "message" => $err->message,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
       }
    }

    public function allRequests(Request $request)
    {
        try{
  
            /**
             * Here we check the coming client id wheter a number or not.
             * If a number we will store it in the client$supplierId variable.
             * If not a number we will assign the variable to zero
             */
              $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              if($supplierId == 0 ){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "?????????? ???????? ??????????????";
                $error->statusCode = 422;
                throw $error;
            }

                /**
               * System will call the brand with the coming id
               * The single request will carry the model as well, becasue clients want to see the models information
               */

              $selectedRequests = Rrequest::with(["clients:clients.name,clients.nameInArabic,clients.email,clients.address,clients.phone", "brands:brands.brandName,brands.brandNameInArabic"])->where("supplierId", $supplierId)->get();
  
              /**
               * System checks the client if exists or not.
               * If no client is found in the client table, system will return an error
               */
              if(count($selectedRequests) < 1){
                  $error = new Error(null);
                  $error->errorMessage = "There is no requests related to this client";
                  $error->messageInArabic = "???? ???????? ?????????? ?????????? ???????? ????????????";
                  $error->statusCode = 404;
                  throw $error;
              }
             
             

            return response()->json([
                "requests" => $selectedRequests,
                "statusCode" => 200,
            ], 200);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
            
        }
    }

    public function suppliersEmails($pref, $carsPref=null){
        try{

            $emails = [];
            $suppliers= [];
            if($pref == "cars"){
                json_decode($suppliers = Supplier::with("account")->where("pref", "cars")->get(["supplierId", "pref", "carsPref"]));
            }else if($pref == "vehicles"){
                json_decode($suppliers = Supplier::with("account")->where("pref", "vehicles")->orWhere("pref", "all")->get(["supplierId", "pref", "carsPref"]));
            }else if($pref == "all"){
                json_decode($suppliers = Supplier::with("account")->where("pref", "all")->orWhere("carsPref", "all cars")->get(["supplierId", "pref", "carsPref"]));
            }
            
            if($pref == "cars"){
                for($i=0; $i<count($suppliers); $i++){
                    if($suppliers[$i]["account"]["email"] == null){
                        continue;
                    }
                    $carsPrefs = explode(",", $suppliers[$i]["carsPref"]);
                    for($x=0; $x<count($carsPrefs); $x++){
                        if($carsPrefs[$x] != $carsPref){
                            continue;
                        }
                        array_push($emails, ["email" => $suppliers[$i]["account"]["email"], "pref" => $suppliers[$i]["pref"], "carsPref" => $suppliers[$i]["carsPref"]]);
                    }
                }
            }else{
                for($i=0; $i<count($suppliers); $i++){
                    if($suppliers[$i]["account"]["email"] == null){
                        continue;
                    }
                    array_push($emails, ["email" => $suppliers[$i]["account"]["email"], "pref" => $suppliers[$i]["pref"], "carsPref" => $suppliers[$i]["carsPref"]]);
                }
            }


            return response()->json([
                "emails" => $emails,
                "statusCode" => 200,
            ], 200);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
            
        }
    }

    public function allCarsPref(){
        try{
            $emails = [];
            $suppliers= [];
            json_decode($suppliers = Supplier::with("account")->where("carsPref", "all cars")->get(["supplierId", "pref", "carsPref"]));

            for($i=0; $i<count($suppliers); $i++){
                if($suppliers[$i]["account"]["email"] == null){
                    continue;
                }
                array_push($emails, ["email" => $suppliers[$i]["account"]["email"], "pref" => $suppliers[$i]["pref"], "carsPref" => $suppliers[$i]["carsPref"]]);
            }


            return response()->json([
                "emails" => $emails,
                "statusCode" => 200,
            ], 200);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
            
        }
    }

    public function requestUpdate(Request $request){
        try{
            $supplierId = $request->input("uid");
            Supplier::where("supplierId", $supplierId)->update([
                "updateRequest" => "1"
            ]);
            return response()->json([
                "message" => "Request has been added",
                "messageInArabic" => "???? ?????????? ?????? ??????????",
                "statusCode" => 200
            ], 200);
       }catch(Error $err){
            return response()->json([
                "message" => $err->message,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
       }
    }

    public function acceptRequestUpdate(Request $request){
        try{
            $supplierId = $request->input("supplierId");
            Supplier::where("supplierId", $supplierId)->update([
                "updateRequest" => "2"
            ]);
            return response()->json([
                "message" => "Update Information is available now",
                "messageInArabic" => "?????? ?????????? ?????????? ????????????????",
                "statusCode" => 200
            ], 200);
       }catch(Error $err){
            return response()->json([
                "message" => $err->message,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
       }
    }
    
    public function rejectRequestUpdate(Request $request){
        try{
            $supplierId = $request->input("supplierId");
            Supplier::where("supplierId", $supplierId)->update([
                "updateRequest" => "0"
            ]);
            return response()->json([
                "message" => "Update Information is rejected",
                "messageInArabic" => "???? ?????? ?????????? ????????????????",
                "statusCode" => 200
            ], 200);
       }catch(Error $err){
            return response()->json([
                "message" => $err->message,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
       }
    }
    
}
