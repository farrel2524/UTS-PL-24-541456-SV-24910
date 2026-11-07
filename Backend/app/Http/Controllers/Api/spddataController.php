<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spddata;
use App\Support\LogContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $data = $this->validateOrLog($request, [
        'Username' => ['required', 'string', 'max:255'],
        'Time' => ['required', 'string', 'max:255'],
        'Difficulty' => ['required', 'string', 'max:255'],
        ], 'create spddata record');

        $spddata = Spddata::create($data);

        // Log the record id only, not the submitted values.
        Log::info('spddata.created', [
            'who' => LogContext::who($request->user()?->id),
            'what' => 'created a spddata record',
            'why' => 'an authenticated client submitted a valid payload',
            'spddata_id' => $spddata->id,
        ]);

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
        $data = $this->validateOrLog($request, [
            'Username' => ['sometimes', 'required', 'string', 'max:255'],
            'Time' => ['sometimes', 'required', 'string', 'max:255'],
            'Difficulty' => ['sometimes', 'required', 'string', 'max:255'],
        ], 'update spddata record');

        $spddata->update($data);

        Log::info('spddata.updated', [
            'who' => LogContext::who($request->user()?->id),
            'what' => 'updated a spddata record',
            'why' => 'an authenticated client sent valid changes',
            'spddata_id' => $spddata->id,
            'changed_fields' => array_keys($data),
        ]);

        return $spddata;
    }

    public function destroy(Request $request, Spddata $spddata)
    {
        $spddataId = $spddata->id;

        $spddata->delete();

        Log::info('spddata.deleted', [
            'who' => LogContext::who($request->user()?->id),
            'what' => 'deleted a spddata record',
            'why' => 'an authenticated client requested its removal',
            'spddata_id' => $spddataId,
        ]);

        return response()->noContent();
    }
}
