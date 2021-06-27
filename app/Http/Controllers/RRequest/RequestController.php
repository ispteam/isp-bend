<?php

namespace App\Http\Controllers\RRequest;

use App\Http\Validation\ValidationError;
use App\Models\RRequest\Request as Rrequest;
use App\Models\Supplier\Supplier;
use Error;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class RequestController extends Controller
{

    public function store(Request $request)
    {
        try{

            $clientId = intval($request->input("clientId")) ? $request->input("clientId") : 0;
            $brandId = intval($request->input("brandId")) ? $request->input("brandId") : 0;
    
            // To check the supplier id && client id && model id values if it's equals to zero, then it will throw an error because there is no id with id zero
            if($clientId == 0 || $brandId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier or mdoel or client";
                $error->messageInArabic = "معرّف خاطئ للعلامة التجارية او العميل";
                $error->statusCode = 422;
                throw $error;
            }
    
            $rules = [
                "address" => "required",
                "description" => "required|string|min:5|max:200|regex:/^[A-Za-z\s]+$/",
                "quantity" =>"required|numeric|regex:/^[0-9]+/",
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

            $sanitizedAddress = ValidationError::sanitizeArray($request->input("address"));
            $sanitizedModel = ValidationError::sanitizeArray($request->input("model"));

    

    
            $request = Rrequest::create([
                "description" => $request->input("description"),
                "address" => json_encode($sanitizedAddress),
                "model" => json_encode($sanitizedModel),
                "field" => $request->input("field"),
                "clientId" => $clientId,
                "brandId" => $brandId,
                "quantity" => $request->input("quantity")
            ]);
    
            if($request == null){
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

    public function updateAmounts(Request $request)
    {
        try{
    
            $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;

            if($supplierId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "معرّف خاطئ للمورّد";
                $error->statusCode = 422;
                throw $error;
            }


            $requestId= intval($request->input("requestId")) ? $request->input("requestId") : 0;

            $Rrequest= Rrequest::where("requestId", $requestId)->first();

            if(count(array($Rrequest)) == 0){
                $error = new Error(null);
                $error->errorMessage = "There is no request with this id";
                $error->messageInArabic = "لا يوجد طلب مسجل";
                $error->statusCode = 404;
                throw $error;
            }

            if($Rrequest->finalAmount > 0){
                $error = new Error(null);
                $error->errorMessage = "The request has already been closed";
                $error->messageInArabic = "تم اقفال الطلب";
                $error->statusCode = 422;
                throw $error;
            }


            $newAmount= $request->input("amount");
            // $sanitizedAmount= filter_var($newAmount, FILTER_SANITIZE_NUMBER_FLOAT);

            $amounts= json_decode($Rrequest->amounts);

            //Check if the amounts array is null, then we make the amount as an array 
            if(is_null($amounts)){
                $amounts= [];
            }

            /**
             * We push the amount that supplier wants to add + his name
             * The array contains object will be like that: 
             * [
             *   {
             *      supplierName: Aziz,
             *      amount: 120
             *   }
             * ]
             */
            $supplier =  Supplier::with("accounts")->where("supplierId", $supplierId)->get(["supplierId"])->first();
            array_push($amounts, ["amount" => $newAmount, "supplierId" => $supplier->supplierId ,"name" => $supplier->accounts->name, "nameInArabic" => $supplier->accounts->nameInArabic ]);

            

            // //we just update the amounts json array that holds the amount and the supplier name
            $request = Rrequest::where("requestId", $requestId)->update([
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

    public function showFullAmounts(Request $request)
    {
        try{
            $requestId = intval($request->input("requestId")) ? $request->input("requestId") : 0;

            $Rrequest= Rrequest::where("requestId", $requestId)->first();

            if($Rrequest == null){
                $error = new Error(null);
                $error->errorMessage ="There is no request";
                $error->messageInArabic = "لم يتم ايجاد الطلب";
                $error->statusCode = 404;
                throw $error;
            }

            //We just return the amounts array that contains supplier name and his amount
            $amounts = json_decode($Rrequest->amounts);
            if(is_null($amounts)){
                $error = new Error(null);
                $error->errorMessage = "There is no amount yet";
                $error->messageInArabic = "لا يوجد سعر مسجل حتى الان";
                $error->statusCode = 404;
                throw $error;
            }
            return response()->json([
                "data" => $amounts
            ]);
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
                $error->errorMessage ="Invalid id for supplier or request or model";
                $error->messageInArabic = "معرّف خاطئ للمورّد أو الطلب او الموديل";
                $error->statusCode = 422;
                throw $error;
            }


            $selectedRequest = Rrequest::where("requestId", $requestId)->first();

            if($selectedRequest == null){
                $error = new Error(null);
                $error->errorMessage = "There is no supplier found";
                $error->messageInArabic = "لم يتم ايجاد مورد";
                $error->statusCode = 500;
                throw $error;
            }



            //To add the final amount that client chose, and add the supplier id of best supplier's offer
            $request= Rrequest::where("requestId", $requestId)->update([
                "finalAmount" => $request->input("finalAmount"),
                "supplierId" => $supplierId,
                "amounts" => null
            ]);

            if($request == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "final amount has been added successfully",
                "messageInArabic" => "تم اضافة السعر النهائي الجديد بنجاح",
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

    
    public function moveToShipper(Request $request)
    {
        try{
            $requestId = intval($request->input("requestId")) ? $request->input("requestId") : 0;

            if( $requestId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for request";
                $error->messageInArabic = "  معرّف خاطئ للطلب";
                $error->statusCode = 422;
                throw $error;
            }

            $request= Rrequest::where("requestId", $requestId)->update([
                "requestStatus" => "1",
               
            ]);

            if($request == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "move to shipper",
                "messageInArabic" => "الانتقال إلى شركة الشحن ",
                "statusCode" => 200

            ],200);

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
     
    }

    
    public function cancelRequest(Request $request)
    {
        try{
            $requestId = intval($request->input("requestId")) ? $request->input("requestId") : 0;

            if( $requestId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for request";
                $error->messageInArabic = "  معرّف خاطئ للطلب";
                $error->statusCode = 422;
                throw $error;
            }

            $request= Rrequest::where("requestId", $requestId)->update([
                "requestStatus" => "2",
               
            ]);

            if($request == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "shipment has been canceled",
                "messageInArabic" => " ألغيت الشحنة",
                "statusCode" => 200

            ],200);

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    public function complete(Request $request)
    {
        try{
            $requestId = intval($request->input("requestId")) ? $request->input("requestId") : 0;

            if( $requestId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for request";
                $error->messageInArabic = "  معرّف خاطئ للطلب";
                $error->statusCode = 422;
                throw $error;
            }

            $request= Rrequest::where("requestId", $requestId)->update([
                "requestStatus" => "3",
               
            ]);

            if($request == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "Request has been delivered",
                "messageInArabic" => " تم توصيل الطلب",
                "statusCode" => 200

            ],200);

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }
}
