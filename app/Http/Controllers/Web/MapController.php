<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\PlaceType;

class MapController extends Controller
{
    public function index(Request $request)
    {
        $filterTypes = PlaceType::orderBy('id','asc')->get();
        return view('web.map.vietnam', compact('filterTypes'));
    }
}
