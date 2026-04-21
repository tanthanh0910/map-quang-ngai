<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\PlaceType;
use App\Enums\PlaceStatus;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        // optional filtering by type_id
        $q = Place::with('type')->where('status', PlaceStatus::ACTIVE->value);
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

    public function storeSupport(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:100',
            'lat'         => 'required|numeric|between:-90,90',
            'lng'         => 'required|numeric|between:-180,180',
            'description' => 'nullable|string',
            'thumbnail'   => 'nullable|image|max:8192',
        ]);

        $type = PlaceType::firstOrCreate(
            ['name' => 'Cứu hộ'],
            ['icon' => 'cuu_ho.gif', 'priority' => 100]
        );

        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $dir = public_path('images');
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $file->move($dir, $filename);
            $data['thumbnail'] = 'images/' . $filename;
        }

        $place = Place::create(array_merge($data, [
            'type_id' => $type->id,
            'icon'    => $type->icon,
            'status'  => PlaceStatus::ACTIVE->value,
        ]));

        return response()->json([
            'ok'      => true,
            'message' => 'Đã gửi yêu cầu cứu hộ.',
            'id'      => $place->id,
        ], 201);
    }

    public function resolveSupport($id)
    {
        $place = Place::with('type')->findOrFail($id);
        if (!$place->type || $place->type->name !== 'Cứu hộ') {
            return response()->json([
                'ok'      => false,
                'message' => 'Điểm này không phải yêu cầu cứu hộ.',
            ], 422);
        }

        $place->status = PlaceStatus::RESOLVED->value;
        $place->save();

        return response()->json([
            'ok'      => true,
            'message' => 'Đã đánh dấu cứu hộ thành công.',
            'id'      => $place->id,
        ]);
    }
}
