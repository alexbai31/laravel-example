<?php


class ApiController extends Controller
{
    const BUILD_IOS_TYPE_ID = 2;
    const BUILD_ANDROID_TYPE_ID = 1;

    public function getApplications(Version $versionm)
    {
        $apps = $versionm->builds()->get();

        foreach ($apps as $app) {
            $app_address = "/builds/android/{$app->version->label->label_name}/{$app->version->version}/{$app->build}/{$app->bundle}";
            $result[] = array(
                "name" => $app->name,
                "link_to_file" => Config::get('app.base_url') . $app_address . ".apk",
                "icon" => Config::get('app.base_url') . $app_address . ".png",
                "version" => $app->version->version,
                "build" => $app->build,
                "bundle" => $app->bundle,
                "date" => $app->created_at

            );
        }

        return Response::json($result)->header("Content-type", "application/json");
    }

    public function getLabels()
    {
        $labels = Label::where("build_type_id", "=", self::BUILD_ANDROID_TYPE_ID)->get();
        return Response::json($labels->toArray());
    }

    public function getVersions(Label $labelm)
    {
        $versions = $labelm->versions()->get();
        return Response::json($versions->toArray());
    }
}