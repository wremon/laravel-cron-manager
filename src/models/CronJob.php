<?php

use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\SoftDeletingTrait;


class CronJob extends Eloquent {
    use SoftDeletingTrait;
    protected $dates = ['deleted_at'];
    protected $table = 'cron_jobs';
}