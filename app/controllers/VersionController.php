<?php
/**
 * Created by JetBrains PhpStorm.
 * User: roman
 * Date: 12.08.13
 * Time: 11:00
 * To change this template use File | Settings | File Templates.
 */

class VersionController extends BaseController
{
    public function main($build_type, $label)
    {
        $label = Label::where('label_name', '=', $label)->firstOrFail();
        $versions = $label->versions();
        $this->setTitle("Versions with label - $label->label_name");
        $this->layout->versions = $versions->get();

    }
}