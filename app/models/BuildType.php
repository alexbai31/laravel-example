<?php

class BuildType extends Eloquent
{
    public function labels()
    {
        return $this->hasMany('Label');
    }
}