<?php

class HomeController extends BaseController
{

    protected $layout = "home.main";

    const BUILD_IOS_TYPE_ID = 2;
    const BUILD_ANDROID_TYPE_ID = 1;

    public function main()
    {
        return "";
    }

    public function ios()
    {
        $build_type = BuildType::find(self::BUILD_IOS_TYPE_ID);

        $this->layout->labels = $build_type->labels;

        $this->layout->ios_page = true;
        $this->layout->build_type = $build_type->slug;
    }

    public function android()
    {
        $build_type = BuildType::find(self::BUILD_ANDROID_TYPE_ID);

        $this->layout->labels = $build_type->labels;
        $this->layout->ios_page = false;
        $this->layout->build_type = $build_type->slug;
    }

}