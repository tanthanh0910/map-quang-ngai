<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\PlaceType;

class PlaceController extends Controller
{
    public function index(Request $request)
    {
        $q = Place::with('type');
        if ($request->has('q') && $request->get('q') !== null) {
            $q->where('name', 'like', '%' . $request->get('q') . '%');
        }
        if ($request->has('type_id') && $request->get('type_id') !== null) {
            $q->where('type_id', (int)$request->get('type_id'));
        }
        $items = $q->orderBy('id','desc')->paginate(30);
        $types = PlaceType::orderBy('priority','desc')->get();
        return view('admin.places.index', compact('items','types'));
    }

    public function create()
    {
        $types = PlaceType::orderBy('priority','desc')->get();
        return view('admin.places.create', compact('types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type_id'=>'required|exists:place_types,id',
            'name'=>'required|string',
            'address'=>'nullable|string',
            'phone'=>'nullable|string',
            'lat'=>'nullable|numeric',
            'lng'=>'nullable|numeric',
            'icon'=>'nullable|string',
            'thumbnail'=>'nullable|image|max:4096',
            'time'=>'nullable|string',
            'description'=>'nullable|string',
            'status'=>'nullable|string',
        ]);
        // ensure type_id is present and saved
        $placeData = $data;
        $placeData['type_id'] = $data['type_id'];

        // handle thumbnail upload
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $dir = public_path('images');
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $placeData['thumbnail'] = 'images/' . $filename;
        }

        Place::create($placeData);
        return redirect()->route('admin.places.index');
    }

    public function edit($id)
    {
        $item = Place::findOrFail($id);
        $types = PlaceType::orderBy('priority','desc')->get();
        return view('admin.places.edit', compact('item','types'));
    }

    public function update(Request $request, $id)
    {
        $item = Place::findOrFail($id);
        $data = $request->validate([
            'type_id'=>'required|exists:place_types,id',
            'name'=>'required|string',
            'address'=>'nullable|string',
            'phone'=>'nullable|string',
            'lat'=>'nullable|numeric',
            'lng'=>'nullable|numeric',
            'icon'=>'nullable|string',
            'thumbnail'=>'nullable|image|max:4096',
            'time'=>'nullable|string',
            'description'=>'nullable|string',
            'status'=>'nullable|string',
        ]);
        // handle thumbnail upload and remove old file if replaced
        if ($request->hasFile('thumbnail')) {
            $file = $request->file('thumbnail');
            $dir = public_path('images');
            if (!file_exists($dir)) mkdir($dir, 0755, true);
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $filename);
            // delete old thumbnail if exists and is in images/
            if ($item->thumbnail && file_exists(public_path($item->thumbnail))) {
                @unlink(public_path($item->thumbnail));
            }
            $data['thumbnail'] = 'images/' . $filename;
        }

        $item->update($data);
        return redirect()->route('admin.places.index');
    }

    public function destroy($id)
    {
        Place::destroy($id);
        return redirect()->route('admin.places.index');
    }
}
