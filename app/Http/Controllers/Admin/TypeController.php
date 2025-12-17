<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlaceType;

class TypeController extends Controller
{
    public function index(Request $request)
    {
        $q = PlaceType::query();
        if ($request->has('q')) {
            $q->where('name', 'like', '%' . $request->get('q') . '%');
        }
        $items = $q->orderBy('id','asc')->paginate(30);
        
        return view('admin.types.index', compact('items'));
    }

    public function create()
    {
        return view('admin.types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([ 'name'=>'required|string', 'icon'=>'nullable|string', 'priority'=>'nullable|integer' ]);
        PlaceType::create($data);
        return redirect()->route('admin.types.index');
    }

    public function edit($id)
    {
        $item = PlaceType::findOrFail($id);
        return view('admin.types.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = PlaceType::findOrFail($id);
        $data = $request->validate([ 'name'=>'required|string', 'icon'=>'nullable|string', 'priority'=>'nullable|integer' ]);
        $item->update($data);
        return redirect()->route('admin.types.index');
    }

    public function destroy($id)
    {
        PlaceType::destroy($id);
        return redirect()->route('admin.types.index');
    }
}
