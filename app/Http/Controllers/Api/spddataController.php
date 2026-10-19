<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spddata;
use Illuminate\Http\Request;

class spddataController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Spddata::latest()->paginate(15);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
        'Username' => ['required', 'string', 'max:255'],
        'Time' => ['required', 'string', 'max:255'],
        'Difficulty' => ['required', 'string', 'max:255'],
        ]);

        $spddata = Spddata::create($data);

        return response()->json($spddata, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Spddata $spddata)
    {
        return $spddata;
    }

    public function update(Request $request, Spddata $spddata)
    {
        $data = $request->validate([
            'Username' => ['sometimes', 'required', 'string', 'max:255'],
            'Time' => ['sometimes', 'required', 'string', 'max:255'],
            'Difficulty' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $spddata->update($data);

        return $spddata;
    }

    public function destroy(Spddata $spddata)
    {
        $spddata->delete();

        return response()->noContent();
    }
}
