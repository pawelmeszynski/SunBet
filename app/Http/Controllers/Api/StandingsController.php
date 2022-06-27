<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\SchedulesCollection;
use App\Models\Schedule;
use App\Models\Standings;
use Illuminate\Http\Request;

class StandingsController extends Controller
{
    public function index()
    {
//        return new SchedulesCollection(Standings::all()->where('competition_id', '=', '2000'));
        $standings = Standings::all();
        foreach($standings as $standing) {
            if ($standing) {
                return new SchedulesCollection($standings, $standing->teams);
            }

            return [
                'data' => [
                    'status' => 'failed',
                    'error' => 404,
                ]
            ];
        }
    }

    public function show($id)
    {
        $standing = Standings::find($id);
        if ($standing) {
            return new ScheduleResource($standing->teams);
        }

        return [
            'data' => [
                'status' => 'failed',
                'error' => 404,
            ]
        ];
    }

}
