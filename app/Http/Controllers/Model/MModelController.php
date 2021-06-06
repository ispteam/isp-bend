<?php

namespace App\Http\Controllers\Model;

use App\Http\Validation\ValidationError;
use App\Models\Model\MModel;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $models= MModel::with("brands")->get();
            if(count($models) < 1){
                $error= new Error(null);
                $error->message = "No model is found";
                $error->messageInArabic = "لم يتم ايجاد موديل";
                $error->statusCode= 404;
                throw $error;
            }

            return response()->json([
                "brandss" => $models,
                "statusCode" => 200
            ]);
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
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
        try{

        $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;
        $brandId = intval($request->input("brandId")) ? $request->input("brandId") : 0;

        if($supplierId == 0 || $brandId == 0){
            $error = new Error(null);
            $error->errorMessage ="Invalid id for supplier or brand";
            $error->messageInArabic = "معرّف خاطئ للمورّد او المنتج";
            $error->statusCode = 422;
            throw $error;
        }

        $rules = [
            "partNo" => "required|string|min:5|max:15|regex:/^[a-zA-Z][a-zA-Z0-9]*/",
            "partDescription" => "required|string|min:5|max:200|regex:/^[A-Za-z\s]+$/",
            "quantity" =>"required|string|regex:/^[0-9]+/",
        ];

        $validator= ValidationError::validationUserInput($request, $rules);

        if($validator->fails()){
            $error = new Error(null);
            $error->errorMessage = $validator->errors();
            $error->messageInArabic = "";
            $error->statusCode = 422;
            throw $error;
        }

        $model = MModel::create([
            "partNo" => strtoupper($request->input("partNo")),
            "partDescription" => $request->input("partDescription"),
            "brandId" => $brandId,
            "supplierId" => $supplierId,
            "quantity" => $request->input("quantity")
        ]);

        if(count(array($model)) == 0){
            $error = new Error(null);
            $error->errorMessage = "There is something wrong happened";
            $error->messageInArabic = "حصل خطأ";
            $error->statusCode = 500;
            throw $error;
        }

        return response()->json([
            "message" => "model has been registered successfully",
            "messageInArabic" => "تم تسجيل الموديل بنجاح",
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
     * @param  \App\Models\MModel  $mModel
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try{

            /**
             * Here we check the coming brands id wheter a number or not.
             * If a number we will store it in the modelId variable.
             * If not a number we will assign the variable to zero
             */
            $modelId = intval($request->input("modelId")) ? $request->input("modelId") : 0;

            /**
             * System will call the client with the coming id
             */
            $model = MModel::with("brands")->where("modelId", $modelId)->first();

            /**
             * System checks the models if exists or not.
             * If no models is found in the modelss table, system will return an error
             */
            if($model == null){
                $error = new Error(null);
                $error->errorMessage = "There is no models with this id";
                $error->messageInArabic = "لا يوجد موديل مسجل";
                $error->statusCode = 404;
                throw $error;
            }


            return response()->json([
                "models" => $model,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MModel  $mModel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MModel $mModel)
    {
        try{

            $modelId = intval($request->input("modelId")) ? $request->input("modelId") : 0;

            /**
             * System will call the models with the coming id
             */
            $model = MModel::where("modelId", $modelId)->first();

            /**
             * System checks the model if exists or not.
             * If no model is found in the models table, system will return an error
             */
            if($model == null ){
                $error = new Error(null);
                $error->errorMessage = "There is no model with this id";
                $error->messageInArabic = "لا يوجد موديل مسجل";
                $error->statusCode = 404;
                throw $error;
            }
    
            $rules = [
                "partNo" => "required|string|min:5|max:15|regex:/^[a-zA-Z][a-zA-Z0-9]*/",
                "partDescription" => "required|string|min:5|max:200|regex:/^[A-Za-z\s]+$/",
                "quantity" =>"required|string|regex:/^[0-9]+/",
            ];
    
            $validator= ValidationError::validationUserInput($request, $rules);
    
            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = $validator->errors();
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }
    
            $model = MModel::where("modelId", $modelId)->update([
                "partNo" => strtoupper($request->input("partNo")),
                "partDescription" => $request->input("partDescription"),
                "quantity" => $request->input("quantity")
            ]);
    
            if($model == null){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }
    
            return response()->json([
                "message" => "model has been updated successfully",
                "messageInArabic" => "تم تحديث الموديل بنجاح",
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MModel  $mModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{
  
            /**
             * Here we check the coming brand id wheter a number or not.
             * If a number we will store it in the client$modelId variable.
             * If not a number we will assign the variable to zero
             */
              $modelId = intval($request->input("modelId")) ? $request->input("modelId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              $brand = MModel::where("modelId", $modelId)->first();
  
              /**
               * System checks the brand if exists or not.
               * If no brand is found in the brand table, system will return an error
               */
              if($brand == null){
                  $error = new Error(null);
                  $error->errorMessage = "There is no model with this id";
                  $error->messageInArabic = "لا يوجد موديل مسجل";
                  $error->statusCode = 404;
                  throw $error;
              }
             
              $brand = MModel::where("modelId", $modelId)->delete();
              

            /**
             * Here we check if the brand deleted or not.
             * If not deleted successfully. The system returns an error message.
             */
            if($brand == 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "model has been deleted successfully",
                "messageInArabic" => "تم حذف الموديل بنجاح",
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
}
