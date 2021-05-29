<?php

namespace App\Http\Controllers\Model;

use App\Models\Model\MModel;
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
        return response()->json([
            "message" => "Working",
            "statusCode" => 200
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MModel  $mModel
     * @return \Illuminate\Http\Response
     */
    public function show(MModel $mModel)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MModel  $mModel
     * @return \Illuminate\Http\Response
     */
    public function destroy(MModel $mModel)
    {
        //
    }
}
