<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReviewSummary;

class ReviewSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', [ReviewSummary::class]);
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
        $this->authorize('create', [ReviewSummary::class]);
        $data = $request->validate([
            'header' => 'required|string',
        ]);
        $order = ReviewSummary::orderBy('order', 'DESC')->first();
        $order = empty($order) ? 1 : $order->order + 1;
        ReviewSummary::create([
            'header' => $data['header'],
            'order' => $order 
        ]);
        return redirect()->route('admin.review-summaries.index')->with('status', 'Summary Added Successfully');
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
        $this->authorize('update', [ReviewSummary::class, $reviewSummary]);
        return view('admin.review-summaries.edit', ['summary' => $reviewSummary]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReviewSummary $reviewSummary)
    {
        $this->authorize('delete', [ReviewSummary::class, $reviewSummary]);
        $reviewSummary->items()->delete();
        $reviewSummary->delete();
        return redirect()->route('admin.review-summaries.index')->with('status', 'Delete Successful');
    }
}
