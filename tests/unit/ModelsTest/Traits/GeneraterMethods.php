<?php

$path_to_FakesModels = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'FakesModels' . DIRECTORY_SEPARATOR;

include_once ($path_to_FakesModels . 'Post.php');
include_once ($path_to_FakesModels . 'User.php');
include_once ($path_to_FakesModels . 'Comment.php');
include_once ($path_to_FakesModels . 'Like.php');
include_once ($path_to_FakesModels . 'Video.php');

include_once ($path_to_FakesModels . 'myModel.php');
include_once ($path_to_FakesModels . 'myModelWithFillable.php');

trait GeneraterMethods {

    private $rows = array (
        [
            'id' => 1,
            'first_name' => 'esmaeel',
            'last_name' => 'al-hadidi',
            'username' => 'esmaeel',
            'phone' => '0797886161',
            'email' => 'ismaeel1.hadidi@gmail.com'
        ],
        [
            'id' => 2,
            'first_name' => 'esmaeel',
            'last_name' => 'al-hadidi',
            'username' => 'esmaeel2',
            'phone' => '0780001234',
            'email' => 'ismaeel3.hadidi@gmail.com',
        ],
        [
            'id' => 3,
            'first_name' => 'esmaeel',
            'last_name' => 'al-hadidi',
            'username' => 'esmaeel3',
            'phone' => '0777886161',
            'email' => 'ismaeel2.hadidi@gmail.com', 
        ]
    );

    private function createAndReturnThreeRowsOfMyModelAsAssociativeArray() {
        $rows = array();
        $rows[$this->rows[0]['id']] = myModel::create($this->rows[0]);
        $rows[$this->rows[1]['id']] = myModel::create($this->rows[1]);
        $rows[$this->rows[2]['id']] = myModel::create($this->rows[2]);

        $this->assertInstanceOf(myModel::class, $rows[$this->rows[0]['id']]);
        $this->assertInstanceOf(myModel::class, $rows[$this->rows[1]['id']]);
        $this->assertInstanceOf(myModel::class, $rows[$this->rows[2]['id']]);
        
        return $rows;
    }

    private function deleteCreatedRowsOfMyModel() {
        myModel::delete($this->rows[0]['id']);
        myModel::delete($this->rows[1]['id']);
        myModel::delete($this->rows[2]['id']);
    }

    private function genertateFactorRowsOfMyModel() {
        for($i = 1; $i <= 30; $i++) {
            $row = myModel::create ([
                'id' => $i,
                'first_name' => 'esmaeel' . $i,
                'last_name' => 'al-hadidi' . $i,
                'username' => 'esmaeel' . $i,
                'phone' => '07978861' . ( $i < 10 ? '0' . $i : $i),
                'email' => 'ismaeel' . $i . '.hadidi@gmail.com'
            ]);
            $this->assertInstanceOf(myModel::class, $row);
        }
        $rows = myModel::all();
        $this->assertIsArray($rows);
        $this->assertCount(30, $rows);
    }

    private function removeFactorRowsOfMyModel() {
        for($i = 1; $i <= 30; $i++) {
            $this->assertTrue(myModel::delete($i));
        }
    }

    private static function generateDataForRelationshipsTables() {

        $first_user_id = 0;
        $last_user_id = 0;

        for($i = 1; $i <= 30; $i++) {
            $user = User::create([
                'email' => 'e' . time() . $i . '@hotmail.com',
                'first_name' => 'esma' . $i,
                'last_name' => 'al-hadidi' . $i,
                'password' => 'efh' . ( $i * 3 ) . ( $i * 2 ) . ( $i ),
            ]);
            if(isset($this)) $this->assertInstanceOf(User::class, $user);

            for($j = 1; $j <= 30; $j++) {
                if(2 % $j == 0) {
                    $video = Video::create([
                        'dash_path' => 'dash' . $i . '_' . $j . uniqid(),
                        'src' => 'src' . $i . '_' . $j . time()
                    ]);
                    if(isset($this)) $this->assertInstanceOf(Video::class, $video);
        
                    $post = Post::create([
                        'description' => 'description of post ' . $i,
                        'user_id' => $user->id,
                        'video_id' => $video->id,
                        'images_count' => intval($j / 10) + 1,
                    ]);
                    if(isset($this)) $this->assertInstanceOf(Post::class, $post);
                    
                    for($k = 1; $k <= 30; $k++) {
                        $comment = Comment::create([
                            'content' => 'content of comment ' . $k,
                            'user_id' => $user->id,
                            'post_id' => $post->id,
                        ]);
                        if(isset($this)) $this->assertInstanceOf(Comment::class, $comment);
                        $like = Like::create([
                            'user_id' => $user->id,
                            'component_id' => $comment->id,
                            'component_type' => Comment::class,
                        ]);
                        if(isset($this)) $this->assertInstanceOf(Like::class, $like);
                    }

                    $like = Like::create([
                        'user_id' => $user->id,
                        'component_id' => $post->id,
                        'component_type' => Post::class,
                    ]);
                    if(isset($this)) $this->assertInstanceOf(Like::class, $like);
                }
            }
        }
    }
}