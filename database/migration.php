<?php 
return [

    'users' => 'CREATE TABLE users (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        email VARCHAR(255) NOT NULL UNIQUE,
        first_name VARCHAR(255) NOT NULL,
        last_name VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255) NULL,
        PRIMARY KEY (id)
    );',

    'categories' => 'CREATE TABLE categories (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL UNIQUE,
        PRIMARY KEY (id)
    );',

    'videos' => 'CREATE TABLE videos (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        dash_path VARCHAR(255) NULL,
        src VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    );',
    
    'posts' => 'CREATE TABLE posts (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT(10) UNSIGNED NOT NULL,
        category_id INT(10) UNSIGNED NOT NULL,
        video_id INT(10) UNSIGNED NULL,
        images_count TINYINT UNSIGNED DEFAULT 0,
        CONSTRAINT fk_user_post FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_category_post FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        CONSTRAINT fk_video_post FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE,
        PRIMARY KEY (id)
    );',

    'comments' => 'CREATE TABLE comments (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        content TEXT NULL,
        user_id INT(10) UNSIGNED NOT NULL,
        post_id INT(10) UNSIGNED NOT NULL,
        CONSTRAINT fk_user_comment FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_post_comment FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,
        PRIMARY KEY (id)
    );',

    'replies' => 'CREATE TABLE replies (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        content TEXT NULL,
        user_id INT(10) UNSIGNED NOT NULL,
        comment_id INT(10) UNSIGNED NOT NULL,
        CONSTRAINT fk_user_replay FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_comment_replay FOREIGN KEY(comment_id) REFERENCES comments(id) ON DELETE CASCADE,
        PRIMARY KEY (id) 
    );',

    'likes' => 'CREATE TABLE likes (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        user_id INT(10) UNSIGNED NOT NULL,
        component_id INT (10) UNSIGNED NOT NULL,
        component_type VARCHAR(10),
        CONSTRAINT fk_user_like FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        PRIMARY KEY (id)
    );',

    'notifications' => 'CREATE TABLE notifications (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT (10) UNSIGNED NOT NULL,
        component_id INT (10) UNSIGNED NOT NULL,
        component_type VARCHAR(10),
        readed BOOLEAN,
        opened BOOLEAN,
        CONSTRAINT fk_user_notification FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        PRIMARY KEY (id)
    );',

    'images' => 'CREATE TABLE images (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        src VARCHAR(255) NOT NULL,
        post_id INT(10) UNSIGNED NOT NULL,
        CONSTRAINT fk_post_image FOREIGN KEY(post_id) REFERENCES posts(id) ON DELETE CASCADE,
        PRIMARY KEY (id)
    );',

    'followings' => 'CREATE TABLE followings (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        follower_id INT (10) UNSIGNED NOT NULL,
        followed_id INT (10) UNSIGNED NOT NULL,
        CONSTRAINT fk_followed FOREIGN KEY(followed_id) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_follower FOREIGN KEY(follower_id) REFERENCES users(id) ON DELETE CASCADE,
        PRIMARY KEY (id)
    );',
    
];
