<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ReviewSummary;

class ReviewSummaryController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(ReviewSummary::class, 'summary');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $summaries = ReviewSummary::orderBy('order')->get();
        return view('admin.review-summaries.index', compact('summaries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'header' => 'required|string',
        ]);
        $order = ReviewSummary::orderBy('order', 'DESC')->first();
        $order = empty($order) ? 1 : $order->order + 1;
        ReviewSummary::create([
            'header' => $data['header'],
            'order' => $order 
        ]);
        return redirect()->route('admin.review-summaries.index')->with('status','Summary Added Successfully');;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ReviewSummary $summary)
    {
        return view('admin.review-summaries.show', compact('summary'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(ReviewSummary $reviewSummary)
    {
        return view('admin.review-summaries.edit', ['summary' => $reviewSummary]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update( $request, ReviewSummary $summary)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReviewSummary $reviewSummary)
    {
        $reviewSummary->items()->delete();
        $reviewSummary->delete();
        return redirect()->route('admin.review-summaries.index')->with('status','Delete Successful');
    }
}
