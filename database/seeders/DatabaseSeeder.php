<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Thread;
use App\Models\Reply;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => '管理员',
            'email' => 'admin@mcforum.com',
            'password' => bcrypt('password123'),
            'mc_username' => 'AdminPlayer',
            'mc_uuid' => '069a79f4-44e9-4726-a5be-fca90e38aaf5',
            'mc_verified' => true,
            'bio' => 'MC论坛管理员，欢迎来到我们的社区！',
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $user1 = User::create([
            'name' => '红石工程师',
            'email' => 'redstone@mcforum.com',
            'password' => bcrypt('password123'),
            'mc_username' => 'RedstonePro',
            'mc_uuid' => '853c80ef-3c37-49fd-aa49-938b674adae6',
            'mc_verified' => true,
            'bio' => '热爱红石科技，喜欢研究各种自动化装置',
            'email_verified_at' => now(),
        ]);

        $user2 = User::create([
            'name' => '建筑大师',
            'email' => 'builder@mcforum.com',
            'password' => bcrypt('password123'),
            'mc_username' => 'BuilderMaster',
            'mc_uuid' => 'c42c4286-2a21-48af-b863-89c417278c52',
            'mc_verified' => true,
            'bio' => '专注建筑设计，中世纪风格爱好者',
            'email_verified_at' => now(),
        ]);

        $user3 = User::create([
            'name' => '生存玩家',
            'email' => 'survival@mcforum.com',
            'password' => bcrypt('password123'),
            'mc_username' => null,
            'mc_verified' => false,
            'bio' => '纯生存模式爱好者，挑战各种极限生存',
            'email_verified_at' => now(),
        ]);

        $categories = [
            [
                'name' => '综合讨论',
                'slug' => 'general',
                'description' => '关于 Minecraft 的一切讨论，分享你的游戏经历和想法',
                'icon' => '💬',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => '建筑展示',
                'slug' => 'builds',
                'description' => '展示你的建筑作品，分享建筑技巧和灵感',
                'icon' => '🏰',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => '红石技术',
                'slug' => 'redstone',
                'description' => '红石电路、自动化装置、命令方块技术讨论',
                'icon' => '⚡',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => '服务器交流',
                'slug' => 'servers',
                'description' => '服务器宣传、招募、寻找小伙伴',
                'icon' => '🌐',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        $catGeneral = Category::where('slug', 'general')->first();
        $catBuilds = Category::where('slug', 'builds')->first();
        $catRedstone = Category::where('slug', 'redstone')->first();
        $catServers = Category::where('slug', 'servers')->first();

        $threads = [
            [
                'category_id' => $catGeneral->id,
                'user_id' => $admin->id,
                'title' => '欢迎来到 MC 论坛！新人报到请看这里',
                'slug' => 'welcome-to-mc-forum',
                'body' => "欢迎各位玩家来到 MC 论坛！\n\n这里是 Minecraft 玩家的社区，你可以在这里：\n\n• 分享你的建筑作品\n• 讨论红石技术\n• 寻找游戏伙伴\n• 交流服务器信息\n\n请大家遵守社区规则，友好交流，享受游戏的乐趣！\n\n如有任何问题，欢迎联系管理员。",
                'is_pinned' => true,
                'views_count' => 999,
            ],
            [
                'category_id' => $catGeneral->id,
                'user_id' => $user3->id,
                'title' => '大家都是怎么开始玩 MC 的？',
                'slug' => 'how-did-you-start-playing-mc',
                'body' => "我是在朋友推荐下开始玩的，一开始什么都不会，第一天晚上就被苦力怕炸死了好几次...\n\n现在已经玩了3年了，主要玩生存模式，最近在挑战极限生存。\n\n想问问大家都是怎么入坑的？最喜欢玩什么模式？",
                'is_pinned' => false,
                'views_count' => 156,
            ],
            [
                'category_id' => $catBuilds->id,
                'user_id' => $user2->id,
                'title' => '【建筑展示】我的中世纪城堡建造日志',
                'slug' => 'medieval-castle-build-log',
                'body' => "大家好！这是我最近在建造的中世纪城堡，给大家分享一下建造过程。\n\n城堡采用了经典的中世纪风格，包括：\n• 主塔楼\n• 城墙和护城河\n• 内部庭院\n• 骑士大厅\n\n目前已经完成了主体结构，内饰还在慢慢完善中。\n\n建筑使用了以下材料：\n- 石头和石砖作为主要建材\n- 深色橡木做木结构\n- 玻璃点缀窗户\n\n欢迎大家提出建议！后续会继续更新建造进度。",
                'is_pinned' => false,
                'views_count' => 432,
            ],
            [
                'category_id' => $catBuilds->id,
                'user_id' => $admin->id,
                'title' => '建筑技巧分享：如何让你的建筑更有层次感',
                'slug' => 'building-tips-depth',
                'body' => "很多新手建筑都会遇到一个问题：建筑看起来很平，没有立体感。\n\n今天给大家分享几个增加建筑层次感的技巧：\n\n1. 凹凸变化\n   墙面不要做完全平整的，适当加入一些突出或凹进的结构\n\n2. 材质混合\n   不要只用一种材料，搭配使用不同质感的方块\n\n3. 屋顶设计\n   多样化的屋顶形状能大大提升建筑的视觉效果\n\n4. 细节装饰\n   窗台、屋檐、装饰性的柱子都能增加细节感\n\n希望这些技巧对大家有帮助！",
                'is_pinned' => true,
                'views_count' => 567,
            ],
            [
                'category_id' => $catRedstone->id,
                'user_id' => $user1->id,
                'title' => '【红石教程】零基础入门：从最简单的电路开始',
                'slug' => 'redstone-beginner-tutorial',
                'body' => "很多玩家觉得红石很难，其实只要掌握了基本原理，红石是非常有趣的！\n\n本教程适合完全零基础的玩家：\n\n一、认识红石元件\n• 红石粉：相当于电线\n• 红石火把：电源和信号反转\n• 拉杆/按钮：输入装置\n• 活塞：输出装置\n\n二、最简单的电路\n用一个拉杆控制一盏灯，这是最基础的电路。\n\n三、逻辑门入门\n• 与门：两个开关都打开才亮\n• 或门：任意一个开关打开就亮\n• 非门：开关状态反转\n\n下期给大家介绍更复杂的电路，有问题欢迎在评论区提问！",
                'is_pinned' => true,
                'views_count' => 789,
            ],
            [
                'category_id' => $catRedstone->id,
                'user_id' => $user1->id,
                'title' => '分享一个我做的全自动甘蔗农场',
                'slug' => 'auto-sugar-cane-farm',
                'body' => "给大家分享一个我设计的全自动甘蔗农场，效率还不错。\n\n设计特点：\n• 完全自动化，无需人工干预\n• 收集系统流畅，不会卡顿\n• 建造材料简单易得\n• 效率：约每小时10组甘蔗\n\n工作原理：\n1. 甘蔗长到3格高时被侦测器检测到\n2. 活塞推出破坏甘蔗\n3. 水流把甘蔗冲到收集点\n4. 漏斗收集到箱子里\n\n有需要原理图的可以留言，我后续补充。",
                'is_pinned' => false,
                'views_count' => 234,
            ],
            [
                'category_id' => $catServers->id,
                'user_id' => $admin->id,
                'title' => '【服务器宣传规范】发服务器宣传帖请看这里',
                'slug' => 'server-posting-rules',
                'body' => "为了维护服务器板块的秩序，请大家发服务器宣传帖时遵守以下规范：\n\n✅ 必须包含的内容：\n• 服务器名称\n• 服务器版本\n• 服务器类型（生存/创造/小游戏等）\n• 服务器IP或加入方式\n\n❌ 禁止内容：\n• 虚假宣传\n• 发布恶意软件或病毒\n• 频繁刷屏\n\n违反规范的帖子将被删除，严重者封号处理。",
                'is_pinned' => true,
                'views_count' => 321,
            ],
        ];

        foreach ($threads as $threadData) {
            $thread = Thread::create(array_merge($threadData, [
                'last_reply_at' => now(),
            ]));

            $replyUsers = [$user1, $user2, $user3, $admin];
            $replyCount = rand(2, 5);

            for ($i = 0; $i < $replyCount; $i++) {
                $replyUser = $replyUsers[array_rand($replyUsers)];
                Reply::create([
                    'thread_id' => $thread->id,
                    'user_id' => $replyUser->id,
                    'body' => $this->generateReplyBody($threadData['title'], $i),
                    'created_at' => now()->subMinutes(rand(10, 1000)),
                    'updated_at' => now()->subMinutes(rand(10, 1000)),
                ]);
            }

            $latestReply = $thread->replies()->latest()->first();
            if ($latestReply) {
                $thread->last_reply_at = $latestReply->created_at;
                $thread->save();
            }
        }
    }

    private function generateReplyBody(string $threadTitle, int $index): string
    {
        $replies = [
            "写得太好了！学到了很多，感谢分享！",
            "这个我之前也试过，效果确实不错。",
            "萌新路过，表示受益匪浅！",
            "请问这个在生存模式下也能用吗？",
            "支持楼主！期待更多这样的好内容。",
            "我也做过类似的，不过你的设计更巧妙。",
            "收藏了，以后慢慢看。",
            "终于找到详细的教程了，谢谢楼主！",
        ];

        return $replies[$index % count($replies)];
    }
}
