<?php

use PHPUnit\Framework\TestCase;
use App\Models\Model;
use App\Config\Database;
use App\Helpers\Paginator;
use App\Exceptions\ModelException;

include_once ('Traits/HelperMethods.php');
include_once ('Traits/GeneraterMethods.php');

include_once ('FakesModels/Post.php');
include_once ('FakesModels/User.php');
include_once ('FakesModels/Comment.php');
include_once ('FakesModels/Like.php');
include_once ('FakesModels/Video.php');

class HasRelationshipsTest extends TestCase {

    use HelperMethods, GeneraterMethods;

    public static function setUpBeforeClass() : void {

        self::executeQuery("
            CREATE TABLE IF NOT EXISTS users_for_test_relationships (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                email VARCHAR(255) NOT NULL UNIQUE,
                first_name VARCHAR(255) NOT NULL,
                last_name VARCHAR(255) NOT NULL,
                password VARCHAR(255) NOT NULL,
                profile_picture VARCHAR(255) NULL,
                PRIMARY KEY (id)
            );
        ");

        self::executeQuery("
            CREATE TABLE IF NOT EXISTS videos_for_test_relationships (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                dash_path VARCHAR(255) NULL,
                src VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            );
        ");

        self::executeQuery("
            CREATE TABLE IF NOT EXISTS posts_for_test_relationships (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                description TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                user_id INT(10) UNSIGNED NOT NULL,
                video_id INT(10) UNSIGNED NULL,
                images_count TINYINT UNSIGNED DEFAULT 0,
                CONSTRAINT fk_user_post_for_test_relationships FOREIGN KEY (user_id) REFERENCES users_for_test_relationships(id) ON DELETE CASCADE,
                CONSTRAINT fk_video_post_for_test_relationships FOREIGN KEY (video_id) REFERENCES videos_for_test_relationships(id) ON DELETE CASCADE,
                PRIMARY KEY (id)
            );
        ");

        self::executeQuery("
            CREATE TABLE IF NOT EXISTS comments_for_test_relationships (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                content TEXT NULL,
                user_id INT(10) UNSIGNED NOT NULL,
                post_id INT(10) UNSIGNED NOT NULL,
                CONSTRAINT fk_user_comment_for_test_relationships FOREIGN KEY (user_id) REFERENCES users_for_test_relationships(id) ON DELETE CASCADE,
                CONSTRAINT fk_post_comment_for_test_relationships FOREIGN KEY(post_id) REFERENCES posts_for_test_relationships(id) ON DELETE CASCADE,
                PRIMARY KEY (id)
            );
        ");

        self::executeQuery("
            CREATE TABLE IF NOT EXISTS likes_for_test_relationships (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                user_id INT(10) UNSIGNED NOT NULL,
                component_id INT (10) UNSIGNED NOT NULL,
                component_type VARCHAR(10),
                CONSTRAINT fk_user_like_for_test_relationships FOREIGN KEY(user_id) REFERENCES users_for_test_relationships(id) ON DELETE CASCADE,
                PRIMARY KEY (id)
            );
        ");
        if(User::all() == null || count(User::all()) < 30) {
            static::generateDataForRelationshipsTables();
            echo "\n##################### TEST TABLES CREATED  #####################";
            echo "\nPLEASE REMOVE THESE TABLES FROM PRODUCT\n";
        }
    }

/*
    public static function tearDownAfterClass() : void {

        self::executeQuery("SET FOREIGN_KEY_CHECKS = 0;");

        self::executeQuery("TRUNCATE TABLE users_for_test_relationships;");

        self::executeQuery("TRUNCATE TABLE videos_for_test_relationships;");

        self::executeQuery("TRUNCATE TABLE posts_for_test_relationships;");

        self::executeQuery("TRUNCATE TABLE comments_for_test_relationships;");

        self::executeQuery("TRUNCATE TABLE likes_for_test_relationships;");

        self::executeQuery("SET FOREIGN_KEY_CHECKS = 1;");

    }
*/

    public function test_one_to_one_relation() {

        $posts = Post::where('id', '>', 7)->get();
        foreach($posts as $post) {
            $user_of_post = $post->user;
            $video_of_post = $post->video;

            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $post['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $user = $user[0];

            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $post['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $video = $video[0];

            $this->assertTrue($this->rowEquals($user_of_post, $user));

            $this->assertTrue($this->rowEquals($video_of_post, $video));
        }


        $posts = Post::select(['description'])->where('id', '>', 7)->get();
        foreach($posts as $post) {
            $user_of_post = $post->user;
            $video_of_post = $post->video;

            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $post->getKeys()['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $user = $user[0];

            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $post->getKeys()['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $video = $video[0];

            $this->assertTrue($this->rowEquals($user_of_post, $user));

            $this->assertTrue($this->rowEquals($video_of_post, $video));
        }
    }

    public function test_pre_load_one_to_one_relation() {

        $posts = Post::with('user')->get();
        $this->assertIsArray($posts);
        $this->assertCount(count(Post::all()), $posts);
        foreach($posts as $post) {

            $keys = array_keys($post->get());
            
            $this->assertTrue(array_key_exists('user', $post->get()));

            $this->assertEquals(count($keys), count(Post::orderBy(['id', 'ASC'])->first()->get()) + 1);

            foreach(Post::orderBy(['id', 'ASC'])->first() as $key => $value){
                $this->assertTrue(array_key_exists($key, $post->get()));
            }
        }

        $records = $this->executeQueryForResult(
            'select * from posts_for_test_relationships;'
        );
        $this->assertIsArray($records);
        $this->assertCount(count($records), $posts);
        $records_with_child = [];
        foreach($records as $record) {
            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $record['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $record['user'] = $user[0];
            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::with('video')->get();
        $this->assertIsArray($posts);
        $this->assertCount(count(Post::all()), $posts);
        foreach($posts as $post) {

            $keys = array_keys($post->get());
            
            $this->assertTrue(array_key_exists('video', $post->get()));

            $this->assertEquals(count($keys), count(Post::orderBy(['id', 'ASC'])->first()->get()) + 1);

            foreach(Post::orderBy(['id', 'ASC'])->first() as $key => $value){
                $this->assertTrue(array_key_exists($key, $post->get()));
            }
        }

        $records = $this->executeQueryForResult(
            'select * from posts_for_test_relationships;'
        );
        $this->assertIsArray($records);
        $this->assertCount(count($records), $posts);
        $records_with_child = [];
        foreach($records as $record) {
            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $record['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $record['video'] = $video[0];
            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::with(['video', 'user'])->get();
        $this->assertIsArray($posts);
        $this->assertCount(count(Post::all()), $posts);
        foreach($posts as $post) {

            $keys = array_keys($post->get());
            
            $this->assertTrue(array_key_exists('video', $post->get()));
            $this->assertTrue(array_key_exists('user', $post->get()));

            $this->assertEquals(count($keys), count(Post::orderBy(['id', 'ASC'])->first()->get()) + 2);

            foreach(Post::orderBy(['id', 'ASC'])->first() as $key => $value){
                $this->assertTrue(array_key_exists($key, $post->get()));
            }
        }

        $records = $this->executeQueryForResult(
            'select * from posts_for_test_relationships;'
        );
        $this->assertIsArray($records);
        $this->assertCount(count($records), $posts);
        $records_with_child = [];
        foreach($records as $record) {
            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $record['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $record['video'] = $video[0];

            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $record['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $record['user'] = $user[0];
            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::select(['description', 'id'])
            ->where('id', '>', 10)->where('id', '<', 30)
            ->with(['user', 'video'])->orderBy(['id', 'DESC'])->limit(5, 10)->get();
        $this->assertIsArray($posts);
        $this->assertCount(
            count (
                Post::select(['description', 'id'])->where('id', '>', 10)->where('id', '<', 30)->limit(5, 10)->get()
            ), $posts
        );
        foreach($posts as $post) {

            $keys = array_keys($post->get());
            
            $this->assertTrue(array_key_exists('user', $post->get()));
            $this->assertTrue(array_key_exists('video', $post->get()));
            
            $this->assertEquals(count($keys), 
                count(Post::select(['description', 'id'])->orderBy(['id', 'ASC'])->first()->get()) + 2
            );

            foreach(Post::select(['description', 'id'])->orderBy(['id', 'ASC'])->first() as $key => $value){
                $this->assertTrue(array_key_exists($key, $post->get()));
            }
        }

        $records = $this->executeQueryForResult(
            'select description, id, user_id, video_id from posts_for_test_relationships ' .
            'where id > 10 && id < 30 order by id DESC limit 10, 5;'
        );
        $this->assertIsArray($records);
        $this->assertCount(count($records), $posts);
        $records_with_child = [];
        foreach($records as $record) {
            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $record['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $record['user'] = $user[0];
            unset($record['user_id']);

            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $record['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $record['video'] = $video[0];
            unset($record['video_id']);

            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));
    }

    public function test_one_to_many_relation() {

        $posts = Post::where('id', '>', 7)->get();
        foreach($posts as $post) {
            $user_of_post = $post->user;
            $video_of_post = $post->video;
            $comments_of_post = $post->comments;

            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $post['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $user = $user[0];

            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $post['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $video = $video[0];

            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $post['id'] . ';'
            );
            $this->assertIsArray($comments);

            $this->assertTrue($this->rowEquals($user_of_post, $user));

            $this->assertTrue($this->rowEquals($video_of_post, $video));

            $this->assertTrue($this->rowEquals($comments_of_post, $comments));
        }


        $posts = Post::select(['description'])->where('id', '>', 7)->get();
        foreach($posts as $post) {
            $user_of_post = $post->user;
            $video_of_post = $post->video;
            $comments_of_post = $post->comments;

            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $post->getKeys()['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $user = $user[0];

            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $post->getKeys()['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $video = $video[0];

            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $post->getKeys()['id'] . ';'
            );
            $this->assertIsArray($comments);

            $this->assertTrue($this->rowEquals($user_of_post, $user));

            $this->assertTrue($this->rowEquals($video_of_post, $video));

            $this->assertTrue($this->rowEquals($comments_of_post, $comments));
        }

        
    }
    public function test_pre_load_one_to_many_relation() {

        $posts = Post::with(['comments'])->get();
        $this->assertIsArray($posts);

        $records = $this->executeQueryForResult(
            'select * from posts_for_test_relationships;'
        );
        $this->assertIsArray($records);
        $records_with_child = [];
        foreach($records as $record) {
            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $record['id'] . ';'
            );
            $this->assertIsArray($comments);
            $record['comments'] = $comments;
            array_push($records_with_child, $record);
        }

        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::select(['description', 'created_at'])->with(['comments'])->get();
        $this->assertIsArray($posts);

        $records = $this->executeQueryForResult(
            'select description, created_at, id from posts_for_test_relationships;'
        );
        $this->assertIsArray($records);
        $records_with_child = [];
        foreach($records as $record) {
            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $record['id'] . ';'
            );
            $this->assertIsArray($comments);
            $record['comments'] = $comments;
            unset($record['id']);
            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::select(['description', 'created_at'])
            ->orderBy(['id', 'DESC'])->with(['comments'])
            ->where('id', '>', 10)->where('id', '<', 40)->get();
        $this->assertIsArray($posts);

        $records = $this->executeQueryForResult(
            'select description, created_at, id from posts_for_test_relationships ' .
            'where id>10 && id<40 order by id DESC;'
        );
        $this->assertIsArray($records);
        $records_with_child = [];
        foreach($records as $record) {
            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $record['id'] . ';'
            );
            $this->assertIsArray($comments);
            $record['comments'] = $comments;
            unset($record['id']);
            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::select(['description', 'created_at'])
            ->orderBy(['id', 'DESC'])->with(['comments'])->limit(10 ,15)
            ->where('id', '>', 10)->where('id', '<', 40)->get();
        $this->assertIsArray($posts);

        $records = $this->executeQueryForResult(
            'select description, created_at, id from posts_for_test_relationships ' .
            'where id>10 && id<40 order by id DESC limit 15, 10;'
        );
        $this->assertIsArray($records);
        $records_with_child = [];
        foreach($records as $record) {
            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $record['id'] . ';'
            );
            $this->assertIsArray($comments);
            $record['comments'] = $comments;
            unset($record['id']);
            array_push($records_with_child, $record);
        }

        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::select(['description', 'created_at'])
            ->orderBy(['description', 'DESC'])->with(['comments', 'video', 'user'])->limit(5 ,7)
            ->where('id', '>', 10)->where('id', '<', 40)->get();
        $this->assertIsArray($posts);

        $records = $this->executeQueryForResult(
            'select description, created_at, id, user_id, video_id from posts_for_test_relationships ' .
            'where id>10 && id<40 order by description DESC limit 7, 5;'
        );
        $this->assertIsArray($records);
        $records_with_child = [];
        foreach($records as $record) {
            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $record['id'] . ';'
            );
            $this->assertIsArray($comments);
            $record['comments'] = $comments;
            unset($record['id']);

            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $record['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $record['user'] = $user[0];
            unset($record['user_id']);

            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $record['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $record['video'] = $video[0];
            unset($record['video_id']);

            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));
    }
    public function test_morph_to() {

        $posts = Post::select(['description'])->where('id', '>', 7)->get();
        foreach($posts as $post) {
            $user_of_post = $post->user;
            $video_of_post = $post->video;
            $comments_of_post = $post->comments;
            $likes_of_post = $post->likes;

            $user = $this->executeQueryForResult(
                'select * from users_for_test_relationships where id=' . $post->getKeys()['user_id'] . ';'
            );
            $this->assertIsArray($user);
            $this->assertCount(1, $user);
            $user = $user[0];

            $video = $this->executeQueryForResult(
                'select * from videos_for_test_relationships where id=' . $post->getKeys()['video_id'] . ';'
            );
            $this->assertIsArray($video);
            $this->assertCount(1, $video);
            $video = $video[0];

            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $post->getKeys()['id'] . ';'
            );
            $this->assertIsArray($comments);

            $likes = $this->executeQueryForResult(
                'select * from likes_for_test_relationships where component_id=' . $post->getKeys()['id'] . 
                ' and component_type=\'' . Post::class . '\';'
            );
            $this->assertIsArray($likes);

            $this->assertTrue($this->rowEquals($user_of_post, $user));

            $this->assertTrue($this->rowEquals($video_of_post, $video));

            $this->assertTrue($this->rowEquals($comments_of_post, $comments));

            $this->assertTrue($this->rowEquals($likes_of_post, $likes));
        }

    }

    public function test_pre_load_morph_to() {

        $posts = Post::with(['likes'])->get();
        $this->assertIsArray($posts);

        $records = $this->executeQueryForResult(
            'select * from posts_for_test_relationships;'
        );
        $this->assertIsArray($records);
        $records_with_child = [];
        foreach($records as $record) {
            $likes = $this->executeQueryForResult(
                'select * from likes_for_test_relationships where component_id=' .
                $record['id'] . ' and component_type=\'' . Post::class . '\';'
            );
            $this->assertIsArray($likes);
            $record['likes'] = $likes;
            array_push($records_with_child, $record);
        }
        
        $this->assertTrue($this->rowsEquals($records_with_child, $posts));


        $posts = Post::select(['description', 'created_at'])
            ->orderBy(['description', 'DESC'])->with(['comments', 'likes'])->limit(3)
            ->where('id', '<', 5)->where('id', '>', 1)->get();
        $this->assertIsArray($posts);
        
        $records = $this->executeQueryForResult(
            'select description, created_at, id from posts_for_test_relationships ' .
            'where id<5 && id>1 order by description DESC limit 3;'
        );
        $this->assertIsArray($records);
        $records_with_child = [];

        foreach($records as $record) {
            
            $comments = $this->executeQueryForResult(
                'select * from comments_for_test_relationships where post_id=' . $record['id'] . ';'
            );
            $this->assertIsArray($comments);
            $record['comments'] = $comments;

            $likes = $this->executeQueryForResult(
                'select * from likes_for_test_relationships where component_id=' .
                $record['id'] . ' and component_type=\'' . Post::class . '\';'
            );
            $this->assertIsArray($likes);
            $record['likes'] = $likes;
            unset($record['id']);

            array_push($records_with_child, $record);
        }

        $this->assertTrue($this->rowsEquals($records_with_child, $posts));

        if(Post::getColumns() == null) {

            $this->expectException(ModelException::class);
            $posts = Post::select(['description', 'created_at'])
                ->orderBy(['description', 'DESC'])->with(['comments', 'video', 'user', 'likes'])->limit(3)
                ->where('id', '<', 5)->where('id', '>', 1)->get();
            $this->assertIsArray($posts);

        } else {

            $posts = Post::select(['description', 'created_at'])
                ->orderBy(['description', 'DESC'])->with(['comments', 'video', 'user', 'likes'])->limit(3)
                ->where('id', '<', 5)->where('id', '>', 1)->get();
            $this->assertIsArray($posts);

            $records = $this->executeQueryForResult(
                'select description, created_at, id, user_id, video_id from posts_for_test_relationships ' .
                'where id<5 && id>1 order by description DESC limit 3;'
            );
            $this->assertIsArray($records);
            $records_with_child = [];
    
            foreach($records as $record) {
                
                $comments = $this->executeQueryForResult(
                    'select * from comments_for_test_relationships where post_id=' . $record['id'] . ';'
                );
                $this->assertIsArray($comments);
                $record['comments'] = $comments;
    
                $likes = $this->executeQueryForResult(
                    'select * from likes_for_test_relationships where component_id=' .
                    $record['id'] . ' and component_type=\'' . Post::class . '\';'
                );
                $this->assertIsArray($likes);
                $record['likes'] = $likes;
                unset($record['id']);
    
                $user = $this->executeQueryForResult(
                    'select * from users_for_test_relationships where id=' . $record['user_id'] . ';'
                );
                $this->assertIsArray($user);
                $this->assertCount(1, $user);
                $record['user'] = $user[0];
                unset($record['user_id']);
    
                $video = $this->executeQueryForResult(
                    'select * from videos_for_test_relationships where id=' . $record['video_id'] . ';'
                );
                $this->assertIsArray($video);
                $this->assertCount(1, $video);
                $record['video'] = $video[0];
                unset($record['video_id']);

                array_push($records_with_child, $record);
            }
    
            $this->assertTrue($this->rowsEquals($records_with_child, $posts));
        }
        
    }
}

