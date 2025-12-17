<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Place;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        // optional filtering by type_id
        $q = Place::with('type')->where('status','active');
        if ($request->has('type_id')) {
            // accept type name or id
            $type = $request->get('type_id');
            if (is_numeric($type)) {
                $q->where('type_id', (int)$type);
            } else {
                $q->whereHas('type', function($sub) use ($type){ $sub->where('name', $type); });
            }
        }
        $places = $q->get();
        // map to desired JSON shape
        $out = $places->map(function($p){
            return [
                'id'=>$p->id,
                'type_id'=>$p->type_id,
                'type_name'=>$p->type?->name,
                'icon'=>$p->type?->icon,
                'name'=>$p->name,
                'address'=>$p->address,
                'phone'=>$p->phone,
                'lat'=>$p->lat,
                'lng'=>$p->lng,
                'status'=>$p->status,
                'thumbnail'=>$p->thumbnail,
                'time'=>$p->time,
                'description'=>$p->description,
            ];
        });

        return response()->json($out);
    }
}
