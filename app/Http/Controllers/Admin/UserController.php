<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = User::query();
        if ($request->has('q')) {
            $q->where('name', 'like', '%' . $request->get('q') . '%');
        }
        $items = $q->orderBy('id','desc')->paginate(30);
        return view('admin.users.index', compact('items'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name'=>'required','email'=>'required|email','password'=>'required']);
        $data['password'] = bcrypt($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index');
    }

    public function edit($id)
    {
        $item = User::findOrFail($id);
        return view('admin.users.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = User::findOrFail($id);
        $data = $request->validate(['name'=>'required','email'=>'required|email']);
        if ($request->filled('password')) $data['password'] = bcrypt($request->input('password'));
        $item->update($data);
        return redirect()->route('admin.users.index');
    }

    public function destroy($id)
    {
        User::destroy($id);
        return redirect()->route('admin.users.index');
    }
}
