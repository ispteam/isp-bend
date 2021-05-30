<?php



namespace App\Http\Controllers\Client;
use App\Http\Requests\ClientValidation;
use App\Http\Resources\ClientResource;
use App\Http\Validation\ValidationError;
use App\Models\Client\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function App\Http\Validation\validationUserInput;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function index()
    {

        $clients= Client::all();

        if(count($clients) < 5){
            return response()->json([
                "message" => "Failed",
                "messageInArabic" => "لم تنجح",
                "statusCode" => 404,
            ], 404);
        }
        return response()->json([
            "message" => "Succeed",
            "messageInArabic" => "تنجح",
            "statusCode" => 200,
            "clients" => $clients
        ], 200);
        // $clients= Client::all();
        // if(count($clients) < 3 ){
        //     return response()->json([
        //         "messageInEnglish" => "Data not fetched",
        //         "messageInArabic" => "لم يتم بنجاح",
        //         "statusCode" => 404,
        //     ]);
        // }
        // return response()->json([
        //     "messageInEnglish" => "Data fetched",
        //     "messageInArabic" => "تم بنجاح",
        //     "statusCode" => 200,
        //     "clients" => $clients,
        // ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            "name" => "required|string|min:3|max:10",
            "email"=> "required|email|unique:clients,email"
        ];

        $validation= ValidationError::validationUserInput($request, $rules);

        if($validation->fails()){
            return response()->json($validation->errors(), 400);
        }
        $client= Client::create([
            "name" => $request->input("name"),
            "phone" => $request->input("phone"),
            "email" => $request->input("email"),
            "address" => json_encode($request->input("address")),
            "password" => Hash::make($request->input("password")),
        ]);

        return response()->json([
            "messageInEnglish" => "Data fetched",
            "messageInArabic" => "تم بنجاح",
            "statusCode" => 201,
            "clients" => $client,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {

        $clientId = $request->input("clientId");
        $client= Client::where("clientId", $clientId)->get();
        if(count($client) < 1){
            return response()->json([
                "message" => "Failed",
                "messageInArabic" => "لم تنجح",
                "statusCode" => 404,
            ], 404);
        }
        return response()->json([
                "messageInEnglish" => "Data fetched",
                "messageInArabic" => "تم بنجاح",
                "statusCode" => 200,
                "clients" => $client,
            ]);

    //   $clientId= $request->input("clientId");
    //   $client = Client::where("clientId", $clientId)->get();
    //   if(count($client) < 1 ){
    //     return response()->json([
    //         "messageInEnglish" => "Data not fetched",
    //         "messageInArabic" => "لم يتم بنجاح",
    //         "statusCode" => 404,
    //     ]);
    // }
    // return response()->json([
    //     "messageInEnglish" => "Data fetched",
    //     "messageInArabic" => "تم بنجاح",
    //     "statusCode" => 200,
    //     "clients" => $client,
    // ]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $clientId= $request->input("clientId");
        $rules = [
            "name" => "required|string|min:3|max:10"
        ];

        $validation= ValidationError::validationUserInput($request, $rules);

        if($validation->fails()){
            return response()->json($validation->errors(), 400);
        }
        $client= Client::where("clientId", $clientId)->update([
            "name" => $request->input("name"),
        ]);

        if($client < 1){
            return response()->json([
                "message" => "Failed to update",
                "messageInArabic" => "لم تنجح",
                "statusCode" => 404,
            ], 404);
        }

        return response()->json([
            "messageInEnglish" => "Data fetched",
            "messageInArabic" => "تم بنجاح",
            "statusCode" => 201,
            "clients" => $client,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $clientId= $request->input("clientId");
        $client= Client::where("clientId", $clientId)->delete();
        if($client < 1){
            return response()->json([
                "message" => "Failed to delete",
                "messageInArabic" => "لم تنجح",
                "statusCode" => 404,
            ], 404);
        }
        return response()->json([
            "messageInEnglish" => "Data fetched",
            "messageInArabic" => "تم بنجاح",
            "statusCode" => 201,
            "clients" => $client,
        ], 200);

    }
}
