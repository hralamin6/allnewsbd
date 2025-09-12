<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\PostUser;
use App\Models\Role;
use App\Models\Tag;
use App\Models\TagPost;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PageSeeder::class);
//        $this->call(CategorySeeder::class);

        User::updateOrCreate([
            'name' => 'admin',
            'email' => 'admin@mail.com'],[
            'email_verified_at' => now(),
            'password' => bcrypt('000000'),
            'role_id' => Role::where('slug', 'admin')->first()->id
        ]);
        User::updateOrCreate([
            'name' => 'user',
            'email' => 'user@mail.com'],[
            'email_verified_at' => now(),
            'password' => bcrypt('000000'),
            'role_id' => Role::where('slug', 'user')->first()->id
        ]);
//        Tag::factory()->count(10)->create();

        // Create 5 parent categories
//        $parentCategories = Category::factory()->count(10)->create();
        // create 10 using catergory seeder
//            $this->call(CategorySeeder::class);
        // Create 10 users        // Create 10 users
//        $users = User::factory()->count(10)->create();

        // For each user, create 10 posts with random child categories
//        $users->each(function ($user) {
//            Post::factory()->count(10)->create([
//                'user_id' => $user->id,
//                'category_id' => Category::inRandomOrder()->first()->id, // Random child category
//            ]);
//        });

//        Post::factory()->count(5)->create([
//            'user_id' => User::inRandomOrder()->first()->id,
//            'category_id' => Category::inRandomOrder()->first()->id, // Random child category
//        ]);
//        PostUser::factory()->count(30)->create();
//        TagPost::factory()->count(30)->create();

    }


}
