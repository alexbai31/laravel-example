<?php
/**
 * Created by JetBrains PhpStorm.
 * User: roman
 * Date: 09.08.13
 * Time: 11:22
 * To change this template use File | Settings | File Templates.
 */

class Label extends Eloquent
{
    protected $fillable = array('label_name', 'build_type_id');

    public function versions()
    {
        return $this->hasMany('Version');
    }

    public function build_type()
    {
        return $this->belongsTo('BuildType');
    }
}