<?php

namespace App\Http\Controllers\RRequest;

use App\Http\Validation\ValidationError;
use App\Models\Client\Client;
use App\Models\Payment\Payment;
use App\Models\RRequest\Request as Rrequest;
use App\Models\Supplier\Supplier;
use App\Models\User\User;
use Error;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RequestController extends Controller
{

    public function __construct()
    {
        $this->middleware("isAuthorized")->except(["index","show", "pendingRequests", "assignedRequests", "singleRequest", "cancelRequestSupplier", "store" ,"addSingleRequest"]);
    }

    public function index(){
        try{
            $requests= Rrequest::with(["clients", "brands", "suppliers"])->get();
            // if(count($requests) < 1){
            //     $error= new Error(null);
            //     $error->message = "No request is found";
            //     $error->messageInArabic = "لم يتم ايجاد طلب";
            //     $error->statusCode= 404;
            //     throw $error;
            // }

            return response()->json([
                "requests" => $requests,
                "statusCode" => 200
            ], 200);
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    public function show($clientId, $offset=0){
        try{
            $requests= Rrequest::with(["clients", "brands", "suppliers"])->orderBy("created_at", "DESC")->where("clientId", $clientId)->limit(6)->offset($offset)->get();
            $length= Rrequest::with(["clients", "brands", "suppliers"])->where("clientId", $clientId)->count();
            
            // if(count($requests) < 1){
            //     $error= new Error(null);
            //     $error->message = "No request is found";
            //     $error->messageInArabic = "لم يتم ايجاد طلب";
            //     $error->statusCode= 404;
            //     throw $error;
            // }

            return response()->json([
                "requests" => $requests,
                "length" => $length,
                "statusCode" => 200
            ], 200);
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }


    public function store(Request $request)
    {
        try{

            $transformedRequest = $request->json()->all();
            $clientId = intval($transformedRequest["information"]["clientId"]) ? $transformedRequest["information"]["clientId"] : null;
            $sanitizedAddress = []; 
            $sanitizedModel= [];
            $sanitizedAddressArabic= [];
            $requestsIds= [];
            $user = null;
            $transformedInformation = $request->input("information");
            if($request->input("registration") != null){
                $registrationInfo = $request->input("registration");
                $rules = [
                    // "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                    "name" => "required|string|min:2|max:30|regex:/^[A-Za-z؀-ۿ\s]+$/",
                    "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
                    "email" =>"required|email|unique:users_info,email",
                    // "address" => "required",
                    "phone"  => "required|string|min:10|max:13|unique:users_info,phone|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
                ];
                $validator = ValidationError::validationRequest($registrationInfo, $rules);
                if($validator->fails()){
                    $error = new Error(null);
                    $error->validationMessage = $validator->errors();
                    $error->errorMessage = "";
                    $error->messageInArabic = "";
                    $error->statusCode = 422;
                    throw $error;
                }
                $user = User::create([
                    "email" => $registrationInfo["email"],
                    "password" => Hash::make($registrationInfo["password"]),
                    "name" => $registrationInfo["name"],
                    "phone" => $registrationInfo["phone"]
                ]);
                Client::create([
                    "clientId" => $user->id,
                ]);
                $clientId = $user->id;
            };

            for ($i=0; $i < count($transformedRequest["data"]) ; $i++) { 
                
                $rules = [
                    "description" => "required|string|min:3|max:200|regex:/^[A-Za-z0-9؀-ۿ\s]+$/",
                    "quantity" =>"required|numeric|regex:/^[0-9]+/",
                ];
                $validator= ValidationError::validationRequest($transformedRequest["data"][$i], $rules);
        
                if($validator->fails()){
                    $error = new Error(null);
                    $error->validationMessage = $validator->errors();
                    $error->messageInArabic = "";
                    $error->errorMessage = "";
                    $error->statusCode = 422;
                    throw $error;
                }
                $sanitizedAddress[$i] = ValidationError::sanitizeArray($transformedRequest["information"]["address"]);
                // $sanitizedAddressArabic[$i] = ValidationError::sanitizeArray($transformedRequest["data"][$i]["addressArabic"]);
                $sanitizedModel[$i] = ValidationError::sanitizeArray($transformedRequest["data"][$i]["model"]);
                
                $requesInfo[$i] = Rrequest::create([
                    "requestNum" => rand(0, intval(10000000000)) + $clientId,
                    "description" => $transformedRequest["data"][$i]["description"],
                    "address" => json_encode($sanitizedAddress[$i]),
                    // "addressArabic" => json_encode($sanitizedAddressArabic[$i]),
                    "model" => json_encode($sanitizedModel[$i]),
                    "field" => $transformedRequest["information"]["field"],
                    "clientId" => $clientId,
                    "brandId" => $transformedRequest["data"][$i]["brandId"],
                    "quantity" => $transformedRequest["data"][$i]["quantity"]
                ]);
        
                if($requesInfo[$i] == null){
                    $error = new Error(null);
                    $error->errorMessage = "There is something wrong happened";
                    $error->messageInArabic = "حصل خطأ";
                    $error->validationMessage = null;
                    $error->statusCode = 500;
                    throw $error;
                }

                $requestsIds[$i] = $requesInfo[$i]->id;

            }
            
            
            
            // // To check the supplier id && client id && model id values if it's equals to zero, then it will throw an error because there is no id with id zero
            
            return response()->json([
                "message" => "request has been registered successfully",
                "messageInArabic" => "تم تسجيل الطلب بنجاح",
                "statusCode" => 201,
            ], 201);
    
    
            }catch(Error $err){
                return response()->json([
                    "message" => $err->errorMessage,
                    "validationMessage" => $err->validationMessage,
                    "messageInArabic" => $err->messageInArabic,
                    "statusCode" => $err->statusCode,
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
            $newShipmentFees= $request->input("shipmentFees");
            // $sanitizedAmount= filter_var($newAmount, FILTER_SANITIZE_NUMBER_FLOAT);

            $amounts= json_decode($Rrequest->amounts);

            $supplier= null;

            //Check if the amounts array is null, then we make the amount as an array 
            if(is_null($amounts)){
                $amounts= [];
            }else{
                for($i=0; $i < count($amounts); $i++){
                    if($amounts[$i]->supplierId == $request->input("supplierId")){
                        $amounts[$i]->amount = $newAmount;
                        $amounts[$i]->shipmentFees = $newShipmentFees;
                    }
                }
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
            if(count($amounts) > 0){
                $request = Rrequest::where("requestId", $requestId)->update([
                    "amounts" => json_encode($amounts),
                ]);
            }else{
                $supplier =  Supplier::with("account")->where("supplierId", $supplierId)->get(["supplierId", "companyInEnglish", "companyInArabic"])->first();
                array_push($amounts, ["amount" => $newAmount,
                                      "shipmentFees" => $request->input("shipmentFees"),
                                      "supplierId" => $supplier->supplierId,
                                      "email" => $supplier->account->email,
                                      "companyName" => $supplier->companyInEnglish, 
                                      "companyNameInArabic" => $supplier->companyInArabic 
                                    ]);
                $request = Rrequest::where("requestId", $requestId)->update([
                    "amounts" => json_encode($amounts),
                ]);
            }

            

            // //we just update the amounts json array that holds the amount and the supplier name
    
            if($request == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }
    
            return response()->json([
                "message" => "amount has been added successfully",
                "messageInArabic" => "تم اضافة السعر الجديد بنجاح",
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

    public function rejectOffer(Request $request)
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


            // $newAmount= $request->input("amount");
            // $newShipmentFees= $request->input("shipmentFees");
            // // $sanitizedAmount= filter_var($newAmount, FILTER_SANITIZE_NUMBER_FLOAT);

            $amounts= json_decode($Rrequest->amounts);

            // for($i=0; $i < count($amounts); $i++){
            //     if($amounts[$i]->supplierId == $request->input("supplierId")){
            //         $amounts[$i]->amount = $newAmount;
            //         $amounts[$i]->shipmentFees = $newShipmentFees;
            //     }
            // }


            $amounts = array_values(array_filter($amounts, function($key) use($supplierId){
                return $key->supplierId != $supplierId;
            }));
            


            $request = Rrequest::where("requestId", $requestId)->update([
                "amounts" => json_encode($amounts),
            ]);

            

            // //we just update the amounts json array that holds the amount and the supplier name
    
            // if($request == 0){
            //     $error = new Error(null);
            //     $error->errorMessage = "There is something wrong happened";
            //     $error->messageInArabic = "حصل خطأ";
            //     $error->statusCode = 500;
            //     throw $error;
            // }
    
            return response()->json([
                "message" => "amount has been added successfully",
                "messageInArabic" => "تم اضافة السعر الجديد بنجاح",
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
                "requestStatus" => "1",
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
                $error->validationMessage = null;
                $error->statusCode = 422;
                throw $error;
            }

            $rules = [
                "shipperName" => "required|string|min:3|max:15|regex:/^[A-Za-z0-9؀-ۿ\s]+$/",
                "trackingNumber" =>"required|string|min:10|regex:/^[A-Za-z0-9]+/",
            ];

            $validator= ValidationError::validationUserInput($request, $rules);

            if($validator->fails()){
                $error = new Error(null);
                $error->validationMessage = $validator->errors();
                $error->errorMessage = "";
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

            $request= Rrequest::where("requestId", $requestId)->update([
                "requestStatus" => "2",
                "shipperName" => $request->input("shipperName"),
                "trackingNumber" => $request->input("trackingNumber"),
            ], 200);

            return response()->json([
                "message" => "move to shipper",
                "messageInArabic" => "الانتقال إلى شركة الشحن ",
                "statusCode" => 200

            ],200);

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "validationMessage" => $err->validationMessage,
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
                "requestStatus" => "4",
                "supplierId" => null,
               
            ]);

            
            
            if($request == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            $request = Rrequest::where("requestId", $requestId)->first();
            
            if($request->finalAmount > 0){
                Payment::where("requestId", $requestId)->update([
                    "status" => "refund"
                ]);
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
                "statusCode" => $err->statusCode,
            ], $err->statusCode);
        }
    }

    public function cancelRequestSupplier(Request $request)
    {
        try{
            $requestId = intval($request->input("requestId")) ? $request->input("requestId") : 0;
            $supplier = json_decode(Supplier::where("supplierId", $request->input("uid"))->first());
            if( $requestId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for request";
                $error->messageInArabic = "  معرّف خاطئ للطلب";
                $error->statusCode = 422;
                throw $error;
            }

            $request= Rrequest::where("requestId", $requestId)->update([
                "requestStatus" => "0",
                "supplierId" => null,
            ]);

            Payment::where("requestId", $requestId)->update([
                "status" => "refund"
            ]);

            Supplier::where("supplierId", $supplier->supplierId)->update([
                "cancelTimes" => $supplier->cancelTimes + 1
            ]);
            

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

    public function addSingleRequest (Request $request){
        try{
            $user = null;
            $transformedInformation = $request->input("information");
            $clientId = $transformedInformation["clientId"];
            if($request->input("registration") != null){
                $registrationInfo = $request->input("registration");
                $rules = [
                    // "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                    "name" => "required|string|min:2|max:30|regex:/^[A-Za-z؀-ۿ\s]+$/",
                    "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
                    "email" =>"required|email|unique:users_info,email",
                    // "address" => "required",
                    "phone"  => "required|string|min:10|max:13|unique:users_info,phone|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
                ];
                $validator = ValidationError::validationRequest($registrationInfo, $rules);
                if($validator->fails()){
                    $error = new Error(null);
                    $error->validationMessage = $validator->errors();
                    $error->errorMessage = "";
                    $error->messageInArabic = "";
                    $error->statusCode = 422;
                    throw $error;
                }
                $user = User::create([
                    "email" => $registrationInfo["email"],
                    "password" => Hash::make($registrationInfo["password"]),
                    "name" => $registrationInfo["name"],
                    "phone" => $registrationInfo["phone"]
                ]);
                Client::create([
                    "clientId" => $user->id,
                ]);
                $clientId = $user->id;
            };

            $rules = [
                "description" => "required|string|min:3|max:200|regex:/^[A-Za-z0-9؀-ۿ\s]+$/",
                "quantity" =>"required|numeric|regex:/^[0-9]+/",
            ];

            $validator = ValidationError::validationUserInput($request, $rules);

            if($validator->fails()){
                $error = new Error(null);
                $error->validationMessage = $validator->errors();
                $error->errorMessage = "";
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

            $sanitizedModel = ValidationError::sanitizeArray($request->input("model"));
            $sanitizedAddress = ValidationError::sanitizeArray($transformedInformation["address"]);

            Rrequest::create([
                "requestNum" => rand(0, intval(10000000000)) + $clientId,
                "description" => $request->input("description"),
                "address" => json_encode($sanitizedAddress),
                // "addressArabic" => json_encode($sanitizedAddressArabic[$i]),
                "model" => json_encode($sanitizedModel),
                "field" => $transformedInformation["field"],
                "clientId" => $clientId,
                "brandId" => $request->input("brandId"),
                "quantity" => $request->input("quantity")
            ]);

            return response()->json([
                // "xx"=> $transformedInformation
                "message" => "Request has been added successfully",
                "messageInArabic" => "تم اضافة الطلب بنجاح",
                "statusCode" => 201
            ], 201);



        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "validationMessage" => $err->validationMessage,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }



    // public function onDelivery(Request $request){
    //     try{
    //         $requestId = intval($request->input("requestId")) ? $request->input("requestId") : 0;

    //         if( $requestId == 0){
    //             $error = new Error(null);
    //             $error->errorMessage ="Invalid id for request";
    //             $error->messageInArabic = "  معرّف خاطئ للطلب";
    //             $error->statusCode = 422;
    //             throw $error;
    //         }

    //         $request= Rrequest::where("requestId", $requestId)->update([
    //             "requestStatus" => "5",
               
    //         ]);

    //         if($request == 0){
    //             $error = new Error(null);
    //             $error->errorMessage = "There is something wrong happened";
    //             $error->messageInArabic = "حصل خطأ";
    //             $error->statusCode = 500;
    //             throw $error;
    //         }

    //         return response()->json([
    //             "message" => "Request has been delivered",
    //             "messageInArabic" => " تم توصيل الطلب",
    //             "statusCode" => 200

    //         ],200);

    //     }catch(Error $err){
    //         return response()->json([
    //             "message" => $err->errorMessage,
    //             "messageInArabic" => $err->messageInArabic,
    //             "statusCode" => $err->statusCode
    //         ], $err->statusCode);
    //     }
    // }

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

    public function detailedRequest(Request $request){
        try{
            $request= Rrequest::with(["clients", "brands", "suppliers"])->where("clientId", $request->input("clientId"))->where("requestId", $request->input("requestId"))->first();
            if($request == null){
                $error= new Error(null);
                $error->message = "No request is found";
                $error->messageInArabic = "لم يتم ايجاد طلب";
                $error->statusCode= 404;
                throw $error;
            }

            return response()->json([
                "request" => $request,
                "statusCode" => 200
            ], 200);
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    public function pendingRequests (Request $request , $offset=0, $pref) {
        try{
            $requests = [];
            $length = null;
            $carsPref = $request->input("supplierCarsPref");
            if($pref == "cars"){
                if(!in_array("all cars", $carsPref)){
                    $req= Rrequest::with(["clients", "brands", "suppliers"])->orderBy("created_at", "DESC")->where("requestStatus", "0")->where("finalAmount", "=" , "0")->where("field", "cars")->limit(6)->offset($offset)->get();
                    foreach($req as $request){
                        foreach($carsPref as $carPref)
                        if($request->brands->brandName == $carPref){
                            $requests[] = $request;
                        }
                    }
                    $length = count($requests);
                }else{
                    $requests= Rrequest::with(["clients", "brands", "suppliers"])->orderBy("created_at", "DESC")->where("requestStatus", "0")->where("finalAmount", "=" , "0")->where("field", "cars")->limit(6)->offset($offset)->get();
                    $length = Rrequest::where("requestStatus", "0" )->where("field", "cars")->where("finalAmount", "=" , "0")->count();
                }
            }
            else if($pref == "vehicles"){
                $requests= Rrequest::with(["clients", "brands", "suppliers"])->orderBy("created_at", "DESC")->where("requestStatus", "0")->where("finalAmount", "=" , "0")->where("field", "big vehicles")->limit(6)->offset($offset)->get();
                $length = Rrequest::where("requestStatus", "0" )->where("field", "big vehicles")->where("finalAmount", "=" , "0")->count();
            }else if($pref == "all"){
                $requests= Rrequest::with(["clients", "brands", "suppliers"])->orderBy("created_at", "DESC")->where("requestStatus", "0")->where("finalAmount", "=" , "0")->limit(6)->offset($offset)->get();
                $length = Rrequest::where("requestStatus", "0" )->where("finalAmount", "=" , "0")->count();
            }
            

            return response()->json([
                "requests" =>$requests,
                "length" => $length,
                "statusCode" => 200
            ], 200);
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    public function assignedRequests ($supplierId, $offset=0) {
        try{

            $requests= Rrequest::with(["clients", "brands", "suppliers"])->orderBy("created_at", "DESC")->where("supplierId", $supplierId)->limit(6)->offset($offset)->get();
            $length = Rrequest::where("supplierId", $supplierId )->count();
            // if(count($requests) < 1){
            //     $error= new Error(null);
            //     $error->message = "No request is found";
            //     $error->messageInArabic = "لم يتم ايجاد طلب";
            //     $error->statusCode= 404;
            //     throw $error;
            // }

            return response()->json([
                "requests" => $requests,
                "length" => $length,
                "statusCode" => 200
            ], 200);
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    public function singleRequest($requestId) {
        try{
            $request= Rrequest::with(["clients", "brands", "suppliers"])->where("requestId", $requestId)->first();
           
            
            // if(count($requests) < 1){
            //     $error= new Error(null);
            //     $error->message = "No request is found";
            //     $error->messageInArabic = "لم يتم ايجاد طلب";
            //     $error->statusCode= 404;
            //     throw $error;
            // }

            return response()->json([
                "request" => $request,
                "statusCode" => 200
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
