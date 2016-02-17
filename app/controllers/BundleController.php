<?php
use Illuminate\Filesystem\Filesystem;

class BundleController extends Controller
{
    const BUILD_IOS_TYPE_ID = 2;
    const BUILD_ANDROID_TYPE_ID = 1;


    public function addBundle()
    {
        if (!Input::hasFile('ipa')) {
            exit(0);
        } else {
            $ipa = Input::file('ipa');
        }
        $payload = exec("unzip -l " . $ipa->getRealPath() . " | sed -e 's/ /\\n/g' | grep app/Info.plist | sed -e 's/Info.plist//g'");

        $default_icon = public_path() . "/images/default_icon.png";

        $fileSystem = new Filesystem;
        if (!$fileSystem->exists('/tmp/bundle')) {
            $fileSystem->makeDirectory('/tmp/bundle');
        }
        if (!$fileSystem->exists('/tmp/bundle/tmp')) {
            $fileSystem->makeDirectory('/tmp/bundle/tmp');
        }
        $path = "/tmp/bundle" . $ipa->getRealPath();
        if ($fileSystem->exists($path)) {
            $fileSystem->deleteDirectory($path);
        }

        $fileSystem->makeDirectory($path);

        $zip = new ZipArchive;
        $res = $zip->open($ipa->getRealPath());
        if ($res === TRUE) {
            $zip->extractTo($path);
            $zip->close();
        }

        $dirs = scandir($path);
        array_shift($dirs);
        array_shift($dirs);

        $APP_PATH = $path . "/" . $dirs[0];

        $dirs = scandir($APP_PATH);
        array_shift($dirs);
        array_shift($dirs);

        $APP_PATH = $APP_PATH . "/" . $dirs[0];
        $plist = CFPropertyList::getInstance();
        $plist->setFile($APP_PATH . "/Info.plist");
        $plist->load();

        $info = $plist->toArray();

        $name = $info['CFBundleName'];
        $build = isset($info['CFBundleVersion']) && array_key_exists("CFBundleVersion", $info) ? $info['CFBundleVersion'] : 1;
        $version = isset($info['CFBundleShortVersionString']) && array_key_exists("CFBundleShortVersionString", $info) ? $info['CFBundleShortVersionString'] : 0;

        if (array_key_exists("CFBundleIconFiles", $info))
            $icons = $info['CFBundleIconFiles'];
        else if (array_key_exists("CFBundleIcons", $info))
            $icons = $info["CFBundleIcons"]["CFBundlePrimaryIcon"]["CFBundleIconFiles"];
        else
            $icons = array();

        $bundle = $info['CFBundleIdentifier'];
        $label = $_POST['label'];
        if (!$fileSystem->exists(public_path() . '/builds')) {
            $fileSystem->makeDirectory(public_path() . '/builds');
        }
        if (!$fileSystem->exists(public_path() . '/builds/ios')) {
            $fileSystem->makeDirectory(public_path() . '/builds/ios');
        }
        if (!$fileSystem->exists(public_path() . '/builds/ios/' . $label)) {
            $fileSystem->makeDirectory(public_path() . '/builds/ios/' . $label);
        }
        if (!$fileSystem->exists(public_path() . '/builds/ios/' . $label . '/' . $version)) {
            $fileSystem->makeDirectory(public_path() . '/builds/ios/' . $label . '/' . $version);
        }


        $icons_ready = array();

        foreach ($icons as $icon) {
            $img = "$path/tmp5646431.png";
            $icon = str_replace(".png", "", $icon) . ".png";
            $processor = PPngUncrush::getInstance();
            if (is_file("$APP_PATH/$icon")) {
                $processor->setFilePath("$APP_PATH/$icon");
                try {
                    $processor->decode($img . $icon);

                } catch (ErrorException  $e) {
                    $img = $default_icon;
                    $icon = "";
                }
                $sz = getimagesize($img . $icon);
                $icons_ready[] = array("image" => $img . $icon, "size" => $sz);
            }
//            $fileSystem->copy($img.$icon ,public_path().'/builds/ios/'.$label.'/'.$version.'/'.$bundle.$sz[0].'x'.$sz[1].'.png');
        }
        $label_model = Label::where('label_name', '=', $label)->where('build_type_id', '=', self::BUILD_IOS_TYPE_ID)->first();
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
            $label_model = Label::create(array('label_name' => $label, 'build_type_id' => self::BUILD_IOS_TYPE_ID));
            $version_model = Version::create(array('version' => $version, 'label_id' => $label_model->id));
            $build = Build::create(array('bundle' => $bundle, 'name' => $name, 'version_id' => $version_model->id,
                'build' => 1));
        }
        $fn = public_path() . '/builds/ios/' . $label . '/' . $version . '/' . $build->build . '/' . $bundle . '.ipa';
        if (!$fileSystem->exists(public_path() . '/builds/ios/' . $label . '/' . $version . '/' . $build->build)) {
            $fileSystem->makeDirectory(public_path() . '/builds/ios/' . $label . '/' . $version . '/' . $build->build);
        }
        $fileSystem->move($ipa->getRealPath(), $fn);

        $max_size = 0;

        foreach ($icons_ready as $icon) {
            if ($icon["size"][0] > $max_size)
                $max_size = $icon["size"][0];
        }

        foreach ($icons_ready as $icon) {
            if ($icon["size"][0] == $max_size)
                $fileSystem->copy($icon["image"], public_path() . '/builds/ios/' . $label . '/' . $version . '/' . $build->build . '/' . $bundle . '.png');
        }

        if (empty($icons_ready)) {
            $fileSystem->copy($default_icon, public_path() . '/builds/ios/' . $label . '/' . $version . '/' . $build->build . '/' . $bundle . '.png');
        }

        $fileSystem->deleteDirectory('/tmp/bundle/tmp');

        echo Config::get("app.domain") . "/ios/builds/$label/$version/{$build->build}\n";

        return "";
    }

}