<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    private function outputLog(Request $request, null|string $id = null)
    {
        $log = [
            'method' => $request->method(),
            'url' => $request->url(),
            'ip' => $request->ip(),
            'agent' => $request->header('User-Agent'),
            'request' => $request->all(),
            'id' => $id ?? "nothing",
        ];
        // Log::info(print_r($log , true));
        Log::info(json_encode($log , JSON_PRETTY_PRINT));
        return $log;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->outputLog($request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return $this->outputLog($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return $this->outputLog($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        return $this->outputLog($request,$id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        return $this->outputLog($request,$id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return $this->outputLog($request,$id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        return $this->outputLog($request,$id);
    }
}
