<?php

class BaseController extends Controller
{

    protected $layout = NULL;
    protected $autoRender = true;

    protected function setupLayout()
    {
        if ($this->autoRender) {
            if (!is_null($this->layout)) {
                $this->layout = View::make($this->layout);
            } else {
                $this->layout = View::make($this->_getViewName());
            }

            $this->setTitle();
        }
    }

    function setTitle($title = NULL)
    {
        $defaultTitle = Config::get("app.title");

        if (!is_null($title)) {
            $defaultTitle .= " | $title";
        }

        $this->layout->title = $defaultTitle;
    }

    private function _getViewName()
    {
        $viewName = strtolower(Route::currentRouteAction());
        $viewName = str_replace("@", ".", $viewName);
        $viewName = str_replace("controller", "", $viewName);
        return $viewName;
    }


}