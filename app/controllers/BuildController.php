<?php

use Illuminate\Filesystem\Filesystem;

//use ApkParser;

class BuildController extends BaseController
{
    const BUILD_IOS_TYPE_ID = 2;
    const BUILD_ANDROID_TYPE_ID = 1;

    public function main($build_type, $label, $version)
    {
        $version = Label::where("label_name", "=", $label)
            ->firstOrFail()
            ->versions()
            ->where("version", "=", $version)
            ->firstOrFail();


        $builds = $version->builds()->get();
        $this->layout->builds = $builds;
    }

    public function showiOSBuild($label, $version, $build_number)
    {
        $base_url = Config::get('app.base_url');
        $this->layout->base_url = $base_url;
        $build = Label::where("label_name", "=", $label)
            ->firstOrFail()
            ->versions()
            ->where("version", "=", $version)
            ->firstOrFail()
            ->builds()
            ->where("build", "=", $build_number)
            ->firstOrFail();

        $build_address = "/builds/ios/{$build->version->label->label_name}/{$build->version->version}/{$build->build}/{$build->bundle}";
        $filesystem = new Filesystem;
        if ($filesystem->exists(public_path() . $build_address . ".ipa")) {
            $build_size = filesize(public_path() . "$build_address.ipa") / 1024 / 1024;
            $this->layout->build = $build;
            $this->layout->build_address = $build_address;
            $this->layout->build_size = round($build_size, 2);
            if (!$filesystem->exists(public_path() . $build_address . ".plist")) {
                $plist = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<!DOCTYPE plist PUBLIC \"-//Apple//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">
<plist version=\"1.0\">
<dict>
    <key>items</key>
    <array>
        <dict>
            <key>assets</key>
            <array>
                <dict>
                    <key>kind</key>
                    <string>software-package</string>
                    <key>url</key>
                    <string>{$base_url}/download?address={$build_address}.ipa</string>
                </dict>
            </array>
            <key>metadata</key>
            <dict>
                <key>bundle-identifier</key>
                <string>{$build->bundle}</string>
                <key>kind</key>
                <string>software</string>
                <key>title</key>
                <string>{$build->name} {$build->version->version}</string>
            </dict>
        </dict>
    </array>
</dict>
</plist>";
                $filesystem->put(public_path() . $build_address . ".plist", $plist);
            }
        } else {
            throw new \Illuminate\Filesystem\FileNotFoundException("Can't find $build->bundle.ipa");
        }
    }

    public function showAndroidBuild($label, $version, $build_number)
    {
        $base_url = Config::get('app.base_url');
        $this->layout->base_url = $base_url;
        $build = Label::where("label_name", "=", $label)
            ->firstOrFail()
            ->versions()
            ->where("version", "=", $version)
            ->firstOrFail()
            ->builds()
            ->where("build", "=", $build_number)
            ->firstOrFail();

        $build_address = "/builds/android/{$build->version->label->label_name}/{$build->version->version}/{$build->build}/{$build->bundle}";
        $filesystem = new Filesystem;
        if ($filesystem->exists(public_path() . $build_address . ".apk")) {
            $build_size = filesize(public_path() . "$build_address.apk") / 1024 / 1024;
            $this->layout->build = $build;
            $this->layout->build_address = $build_address;
            $this->layout->build_size = round($build_size, 2);
        } else {
            throw new \Illuminate\Filesystem\FileNotFoundException("Can't find $build->bundle.apk");
        }
    }


    public function uploadApk()
    {
        $this->layout->ios_page = false;
        $filePath = "";

        if (Input::hasFile("file") && Input::hasFile("icon")) {
            $filePath = $this->saveApk(Input::file("file"), Input::get("label"), Input::get("name"), Input::file("icon"));
        }

        if (!empty($filePath))
            return Redirect::to($filePath);
    }

    public function saveApk($file, $label, $name, $icon)
    {
        $fileSystem = new Filesystem;

        $parser = new ApkParser\Parser($file->getRealPath());

        $version = $parser->getManifest()->getVersionCode();
        $bundle = $parser->getManifest()->getPackageName();

        if (!$fileSystem->exists(public_path() . '/builds')) {
            $fileSystem->makeDirectory(public_path() . '/builds');
        }
        if (!$fileSystem->exists(public_path() . '/builds/android')) {
            $fileSystem->makeDirectory(public_path() . '/builds/android');
        }
        if (!$fileSystem->exists(public_path() . '/builds/android/' . $label)) {
            $fileSystem->makeDirectory(public_path() . '/builds/android/' . $label);
        }
        if (!$fileSystem->exists(public_path() . '/builds/android/' . $label . '/' . $version)) {
            $fileSystem->makeDirectory(public_path() . '/builds/android/' . $label . '/' . $version);
        }

        $label_model = Label::where('label_name', '=', $label)->where('build_type_id', '=', self::BUILD_ANDROID_TYPE_ID)->first();
        if ($label_model != null) {
            $version_model = $label_model->versions()->where('version', '=', $version)->first();
            if ($version_model != null) {
                $build_version_count = Build::where('version_id', '=', $version_model->id)->count();
                $build = Build::create(array('bundle' => $bundle, 'name' => $name, 'version_id' => $version_model->id,
                    'build' => $build_version_count + 1));
            } else {
                $version_model = Version::create(array('version' => $version, 'label_id' => $label_model->id));
                $build = Build::create(array('bundle' => $bundle, 'name' => $name, 'version_id' => $version_model->id,
                    'build' => 1));
            }
        } else {
            $label_model = Label::create(array('label_name' => $label, 'build_type_id' => self::BUILD_ANDROID_TYPE_ID));
            $version_model = Version::create(array('version' => $version, 'label_id' => $label_model->id));
            $build = Build::create(array('bundle' => $bundle, 'name' => $name, 'version_id' => $version_model->id,
                'build' => 1));
        }

        $fn = public_path() . '/builds/android/' . $label . '/' . $version . '/' . $build->build . '/' . $bundle . '.apk';
        if (!$fileSystem->exists(public_path() . '/builds/android/' . $label . '/' . $version . '/' . $build->build)) {
            $fileSystem->makeDirectory(public_path() . '/builds/android/' . $label . '/' . $version . '/' . $build->build);
        }
        $fileSystem->move($file->getRealPath(), $fn);
        $fileSystem->move($icon->getRealPath(), public_path() . '/builds/android/' . $label . '/' . $version . '/' . $build->build . '/' . $bundle . '.png');

        return Config::get("app.domain") . "/android/builds/$label/$version/{$build->build}";
    }
}