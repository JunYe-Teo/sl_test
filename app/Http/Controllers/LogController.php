<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogRequest;
use App\Http\Resources\LogResource;
use App\Models\Log;
use Illuminate\Support\Facades\DB;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::all();

        return LogResource::collection($logs);
    }

    public function store(StoreLogRequest $request)
    {
        DB::beginTransaction();

        try {
            $log = new Log($request->validated());
            $log->save();

            DB::commit();
        } catch(\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return new LogResource($log);
    }

    public function show($key)
    {
        $timestamp = request('timestamp');
        $log = Log::where('key', $key)
            ->when(!$timestamp, fn($query) => $query->latest())
            ->when($timestamp, fn($query) => $query->where('created_at', $timestamp))
            ->first();

        return !$log ?
            response()->json(['message' => 'No Record Found'], 404) :
            new LogResource($log);
    }
}
