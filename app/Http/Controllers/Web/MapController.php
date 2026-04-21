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
        $rescueType  = PlaceType::where('name', 'Cứu hộ')->first();
        $filterTypes = PlaceType::when($rescueType, fn($q) => $q->where('id', '!=', $rescueType->id))
            ->orderBy('id', 'asc')
            ->get();
        $rescueCount = $rescueType
            ? Place::where('type_id', $rescueType->id)
                ->where('status', 'active')
                ->count()
            : 0;
        return view('web.map.vietnam', compact('filterTypes', 'rescueType', 'rescueCount'));
    }
}
