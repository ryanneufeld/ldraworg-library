<?php

namespace App\Http\Controllers\Omr;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Omr\OmrModel;

class OmrModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('omr.list');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Part  $part
     * @return \Illuminate\Http\Response
     */
    public function show(OmrModel $model)
    {
        $model->load('set');
        return view('omr.show', compact('model'));
    }

}
