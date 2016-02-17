<?php
/**
 * Created by JetBrains PhpStorm.
 * User: roman
 * Date: 15.08.13
 * Time: 11:40
 * To change this template use File | Settings | File Templates.
 */

class DownloadController extends Controller
{
    public function get()
    {
        return Response::download(public_path() . Input::get("address"));
    }
}