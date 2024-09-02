<?php

namespace App\Services;

use potibm\Bluesky\BlueskyApi;
use potibm\Bluesky\BlueskyPostService;
use potibm\Bluesky\Feed\Post;

class BlueSkyService
{
    protected $postService;
    protected $api;

    public function __construct()
    {
        $this->api = new BlueskyApi(
            config('services.bluesky.username'),
            config('services.bluesky.password')
        );

        $this->postService = new BlueskyPostService($this->api);
    }

    public function createPost(string $message, string $path, string $url)
    {
        $post = Post::create($message);

        $post = $this->postService->addWebsiteCard(
            $post,
            $url,
            'MyAnimeList',
            $message,
            $path
        );

        return $this->api->createRecord($post);
    }
}
