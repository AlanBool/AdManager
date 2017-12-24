<?php

use Illuminate\Database\Seeder;
use App\Admin\Models\Channel;
use Webpatser\Uuid\Uuid;

class ChannelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 添加数据用户id,默认1
        $user_id = 1;
        // 所有分类 ID 数组，如：[1,2,3,4]
        $parent_id = Channel::where('parent_id',0)->pluck('id')->first();
        // 获取 Faker 实例
        $faker = app(Faker\Generator::class);

        if(empty($parent_id)){
            $parent_id = 0;
        }

        $channels = factory(Channel::class)
            ->times(5)
            ->make()
            ->each(function ($channel, $index)
            use ($user_id, $parent_id, $faker)
            {
                // 父ID
                $channel->parent_id = $parent_id;
                // 添加者用户id
                $channel->add_user_id = $user_id;
                $channel->token = Uuid::generate();
            });

        // 插入到数据库中
        Channel::insert($channels->toArray());

    }
}
