<?php
/**
 * Created by JetBrains PhpStorm.
 * User: roman
 * Date: 09.08.13
 * Time: 11:29
 * To change this template use File | Settings | File Templates.
 */

class Version extends Eloquent
{

    protected $fillable = array('version', 'label_id');

    public function builds()
    {
        return $this->hasMany('Build');
    }

    public function label()
    {
        return $this->belongsTo('Label');
    }
}