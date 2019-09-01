<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyslogsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * All todos
     *
     * @return json
     */
     public function syslogs(Request $request) {

        $syslogs_raw = DB::Table('syslogs')->orderBy('id')->select('syslogs.*', DB::raw("CONCAT(recorded_on) as recorded_on"))->get();
        $syslogs = array_map(function ($logline)
        {
          return [
            'id' => $logline->id,
            'recorded_on' => substr(Carbon::parse($logline->recorded_on)->format('Y-m-d H:i:s.u'), 0, -3),
            'details' => $logline->details
          ];
        }, $syslogs_raw->toArray());

        return response()->json($syslogs);
    }
}
