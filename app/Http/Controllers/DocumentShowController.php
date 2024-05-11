<?php

namespace App\Http\Controllers;

use App\Models\Document\Document;
use Illuminate\Http\Request;

class DocumentShowController extends Controller
{
    function __invoke(Request $request, Document $document)
    {
        return view('documents.document', compact('document'));
    }
}
