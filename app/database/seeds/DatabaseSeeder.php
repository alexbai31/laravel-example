<?php

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $this->call('BuildsSeeder');
    }
}

class BuildsSeeder extends Seeder
{
    public function run()
    {
        $build_1 = BuildType::create(array('type_name' => 'Android', "slug" => "android"));
        $build_2 = BuildType::create(array('type_name' => 'iOS', "slug" => "ios"));

    }
}