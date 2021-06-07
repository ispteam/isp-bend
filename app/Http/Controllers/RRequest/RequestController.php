<?php

namespace App\Http\Controllers\RRequest;

use App\Http\Validation\ValidationError;
use App\Models\Model\MModel;
use App\Models\RRequest\Request as Rrequest;
use App\Models\Supplier\Supplier;
use Error;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class RequestController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{

            // $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;
            $clientId = intval($request->input("clientId")) ? $request->input("clientId") : 0;
            $modelId = intval($request->input("modelId")) ? $request->input("modelId") : 0;
    
            // To check the supplier id && client id && model id values if it's equals to zero, then it will throw an error because there is no id with id zero
            if($clientId == 0 || $modelId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier or mdoel or client";
                $error->messageInArabic = "معرّف خاطئ للمورّد او الموديل او العميل";
                $error->statusCode = 422;
                throw $error;
            }
    
            $rules = [
                "address" => "required",
                "description" => "required|string|min:5|max:200|regex:/^[A-Za-z\s]+$/",
                "quantity" =>"required|string|regex:/^[0-9]+/",
                "field" => "required|string|min:2|max:7|regex:/^[A-Za-z\s]+$/", 
            ];
    
            $validator= ValidationError::validationUserInput($request, $rules);
    
            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = $validator->errors();
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

            $sanitizedAddress = ValidationError::sanitizeAddress($request->input("address"));
    
            $request = Rrequest::create([
                "description" => $request->input("description"),
                "address" => json_encode($sanitizedAddress),
                "field" => $request->input("field"),
                "modelId" => $modelId,
                "clientId" => $clientId,
                "quantity" => $request->input("quantity")
            ]);
    
            if(count(array($request)) == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }
    
            return response()->json([
                "message" => "request has been registered successfully",
                "messageInArabic" => "تم تسجيل الطلب بنجاح",
                "statusCode" => 201,
            ], 201);
    
    
            }catch(Error $err){
                return response()->json([
                    "message" => $err->errorMessage,
                    "messageInArabic" => $err->messageInArabic,
                    "statusCode" => $err->statusCode
                ], $err->statusCode);
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }



    public function updateAmounts(Request $request)
    {
        try{
    
            $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;
            $requestId= intval($request->input("requestId")) ? $request->input("requestId") : 0;

            if($supplierId == 0 || $requestId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "معرّف خاطئ للمورّد";
                $error->statusCode = 422;
                throw $error;
            }

            $rules= [
                "amount" => "required|numeric|regex:/^[+-]?([0-9]*[.])?[0-9]+/"
            ];


            $validator = ValidationError::validationUserInput($request, $rules);

            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = $validator->errors();
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

            

            $Rrequest= Rrequest::where("requestId", $requestId)->first();

            if($Rrequest == null){
                $error = new Error(null);
                $error->errorMessage = "There is no request with this id";
                $error->messageInArabic = "لا يوجد طلب مسجل";
                $error->statusCode = 404;
                throw $error;
            }


            //Check if the final amount has been chose, so supplier can't add a new price
            if($Rrequest->finalAmount > 0){

                $error = new Error(null);
                $error->errorMessage = "Adding amounts period has been finished";
                $error->messageInArabic = "فترة اضافة سعر جديد انتهت";
                $error->statusCode = 404;
                throw $error;

            }

            $newAmount= $request->input("amount");
            // $sanitizedAmount= filter_var($newAmount, FILTER_SANITIZE_NUMBER_FLOAT);

            $amounts= json_decode($Rrequest->amounts);

            //Check if the amounts array is null, then we make the amount as an array 
            if($amounts == ""){
                $amounts= [];
            }
            /**
             * We push the amount that supplier wants to add + his name
             * The array contains object will be like that: 
             * [
             *   {
             *      supplierNameInEnglish: Aziz,
             *      supplierNameInArabic: "عزيز",
             *      amount: 120.20
             *   }
             * ]
             */
            $selectedSupplier= Supplier::where("supplierId", $supplierId)->first();
            array_push($amounts, ["amount" => $newAmount, "supplierNameInEnglish" => $selectedSupplier->name , "supplierNameInArabic" => $selectedSupplier->nameInArabic]);

            

            //we just update the amounts json array that holds the amount and the supplier name
            $request = Rrequest::where("requestId", $requestId)->update([
                // "amounts" => json_encode($amounts),
                "amounts" => json_encode($amounts),
            ]);
    
            if($request == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }
    
            return response()->json([
                "message" => "amounts has been added successfully",
                "messageInArabic" => "تم اضافة السعر الجديد بنجاح",
                "statusCode" => 201,
            ], 200);
    
    
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }



    public function showFullAmounts(Request $request){
        try{

            $requestId= intval($request->input("requestId")) ? $request->input("requestId") : 0;

            if($requestId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "معرّف خاطئ للمورّد";
                $error->statusCode = 422;
                throw $error;
            }

            $request= Rrequest::where("requestId", $requestId)->first();
            //We just return the amounts array that contains supplier name and his amount
            // $amounts = json_decode($request->amounts);

            if($request == null){
                $error = new Error(null);
                $error->errorMessage = "There is no request with this id";
                $error->messageInArabic = "لا يوجد طلب مسجل";
                $error->statusCode = 404;
                throw $error;
            }

            $amounts = json_decode($request->amounts);
            if($amounts == null){
                $error = new Error(null);
                $error->errorMessage = "There is no amount yet";
                $error->messageInArabic = "لا يوجد سعر مسجل حتى الان";
                $error->statusCode = 404;
                throw $error;
            }
            return response()->json([
                "data" => $amounts
            ], 200);

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    public function selectBestPrice(Request $request)
    {
        try{
            $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;
            $requestId = intval($request->input("requestId")) ? $request->input("requestId") : 0;

            if($supplierId == 0 || $requestId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "معرّف خاطئ للمورّد";
                $error->statusCode = 422;
                throw $error;
            }

               

              $selectedRequest= Rrequest::where("requestId", $requestId)->first();

               /**
               * System checks the request if exists or not.
               * If no request is found in the request table, system will return an error
               */

              if($selectedRequest == null){
                $error = new Error(null);
                $error->errorMessage = "There is no request with this id";
                $error->messageInArabic = "لا يوجد طلب مسجل";
                $error->statusCode = 404;
                throw $error;
            }


              //We access to the model with model id to update the quantity
              $model = MModel::where("modelId", $selectedRequest->modelId)->first();

              //Set the new quantity
              $newQuantity = $model->quantity - $selectedRequest->quantity;
  
              $updatedModel = MModel::where("modelId", $selectedRequest->modelId)->update([
                  "quantity" => $newQuantity
              ]);
  
              //Check if the model whether been updated or not
              if($updatedModel == 0){
                  $error = new Error(null);
                  $error->errorMessage = "There is something wrong happened";
                  $error->messageInArabic = "حصل خطأ";
                  $error->statusCode = 500;
                  throw $error;
              }
           
            

           

            //To add the final amount that client chose, and add the supplier id of best supplier's offer
            $updatedRequest= Rrequest::where("requestId", $requestId)->update([
                "finalAmount" => $request->input("finalAmount"),
                "supplierId" => $supplierId,
                "requestStatus" => "1",
                "amounts" => null
            ]);

            if($updatedRequest == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            //We access to the model with model id to update the quantity
            $model = MModel::where("modelId", $selectedRequest->modelId)->first();

            //Set the new quantity
            $newQuantity = $model->quantity - $selectedRequest->quantity;

            $updatedModel = MModel::where("modelId", $selectedRequest->modelId)->update([
                "quantity" => $newQuantity
            ]);

            //Check if the model whether been updated or not
            if($updatedModel == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }
            


            return response()->json([
                "message" => "final amount has been added successfully",
                "messageInArabic" => "تم اضافة السعر النهائي الجديد بنجاح",
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
}
