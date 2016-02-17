<?php
/**
 * Created by JetBrains PhpStorm.
 * User: roman
 * Date: 09.08.13
 * Time: 11:37
 * To change this template use File | Settings | File Templates.
 */

class Build extends Eloquent
{
    protected $fillable = array('bundle', 'name', 'version_id', 'build');

    public function version()
    {
        return $this->belongsTo('Version');
    }
}