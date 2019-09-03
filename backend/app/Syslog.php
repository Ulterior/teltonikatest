<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Syslog extends Model
{
    protected $table = 'syslogs';
    public $timestamps = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function register($message)
    {
        $this->details = substr($message, 0, 255);

        list($usec, $sec) = explode(" ", microtime());
        $dt = Carbon::instance(new \DateTime(date('Y-m-d\TH:i:s', $sec) . substr($usec, 1)))->tz('Europe/London');
        $this->recorded_on = $dt->format('Y-m-d H:i:s.u');

        $this->save();
    }
}
