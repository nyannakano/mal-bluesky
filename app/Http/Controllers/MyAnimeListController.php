<?php

namespace App\Http\Controllers;

use App\Services\BlueSkyService;
use App\Services\MyAnimeListService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class MyAnimeListController extends Controller
{
    protected $myAnimeListService;

    public function __construct(MyAnimeListService $myAnimeListService)
    {
        $this->myAnimeListService = $myAnimeListService;
    }

    public function redirectToProvider()
    {
        return redirect($this->myAnimeListService->getAuthorizationUrl());
    }

    public function handleProviderCallback(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return redirect('/')->with('error', 'Authorization code not provided!');
        }

        $tokenData = $this->myAnimeListService->getAccessToken($code);

        if (!isset($tokenData['access_token'])) {
            return redirect('/')->with('error', 'Failed to retrieve access token. Please try again.');
        }

        $accessToken = $tokenData['access_token'];

        session(['mal_access_token' => $accessToken]);

        return redirect('/')->with('success', 'Authenticated with MyAnimeList!');
    }

    public function getUserAnimeList()
    {
        $accessToken = session('mal_access_token');

        if (!$accessToken) {
            return redirect('/auth/mal')->with('error', 'You must authenticate first!');
        }

        $animeList = $this->myAnimeListService->getUserAnimeList($accessToken);

        return view('anime-list', compact('animeList'));
    }

    public function addEpisode($id, $quantity)
    {
        $accessToken = session('mal_access_token');

        if (!$accessToken) {
            return redirect('/auth/mal')->with('error', 'You must authenticate first!');
        }

        $animeId = $id;
        $episode = $quantity;

        $anime = $this->myAnimeListService->getAnime($accessToken, $animeId);

        if (!isset($anime['id'])) {
            return redirect('/')->with('error', 'Anime not found!');
        }

        $url = $anime['main_picture']['large'];

        $numberOfEpisodesWatched = $anime['my_list_status']['num_episodes_watched'];

        $numberOfEpisodesWatchedPlusEpisodesAdded = $numberOfEpisodesWatched + $episode;

        if ($anime['my_list_status']['num_episodes_watched'] >= $anime['num_episodes']) {
            return redirect('/')->with('error', 'You have already watched all episodes!');
        }

        if ($numberOfEpisodesWatchedPlusEpisodesAdded > $anime['num_episodes']) {
            return redirect('/')->with('error', 'You cannot watch more episodes than the anime has!');
        }

        $response = $this->myAnimeListService->addEpisode($accessToken, $animeId, $numberOfEpisodesWatchedPlusEpisodesAdded, $url, $anime['title']);

        return redirect('/user/anime-list')->with('success', 'Episode added successfully!');
    }

    public function getAnime($id)
    {
        $accessToken = session('mal_access_token');

        if (!$accessToken) {
            return redirect('/auth/mal')->with('error', 'You must authenticate first!');
        }

        $animeId = $id;

        $anime = $this->myAnimeListService->getAnime($accessToken, $animeId);

        return $anime;
    }
}
