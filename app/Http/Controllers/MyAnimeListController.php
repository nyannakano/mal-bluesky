<?php

namespace App\Http\Controllers;

use App\Services\MyAnimeListService;
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

    /**
     * Get user's anime list
     *
     * Connects to MyAnimeList API to get user's anime list
     */
    public function getUserAnimeList()
    {
        $accessToken = $this->accessToken();

        $animeList = $this->myAnimeListService->getUserAnimeList($accessToken);

        $status = $this->myAnimeListService->getStatus();

        return view('anime-list', compact('animeList', 'status'));
    }

    /**
     * Add episode to anime on MyAnimeList and create a new post at BlueSky
     *
     * @param $id
     * @param $quantity
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function addEpisode($id, $quantity)
    {
        $accessToken = $this->accessToken();

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

        $response = $this->myAnimeListService->addEpisode($accessToken, $animeId, $numberOfEpisodesWatchedPlusEpisodesAdded, $url, $anime);

        return redirect('/user/anime-list')->with('success', 'Episode added successfully!');
    }

    /**
     * Remove episode from anime on MyAnimeList
     *
     * @param $id
     * @param $quantity
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function removeEpisode($id, $quantity)
    {
        $accessToken = $this->accessToken();

        $animeId = $id;

        $episode = $quantity;

        $anime = $this->myAnimeListService->getAnime($accessToken, $animeId);

        if (!isset($anime['id'])) {
            return redirect('/')->with('error', 'Anime not found!');
        }

        $numberOfEpisodesWatched = $anime['my_list_status']['num_episodes_watched'];
        $numberOfEpisodesWatchedPlusEpisodesAdded = $numberOfEpisodesWatched - $episode;

        if ($numberOfEpisodesWatchedPlusEpisodesAdded < 0) {
            return redirect('/')->with('error', 'You cannot remove more episodes than you have watched!');
        }

        $response = $this->myAnimeListService->removeEpisode($accessToken, $animeId, $numberOfEpisodesWatchedPlusEpisodesAdded);

        return redirect('/user/anime-list')->with('success', 'Episode removed successfully!');
    }

    public function accessToken()
    {
        $accessToken = session('mal_access_token');

        if (!$accessToken) {
            return redirect('/auth/mal')->with('error', 'You must authenticate first!');
        }

        return $accessToken;
    }

    /**
     * Update anime status on MyAnimeList, and depending on the status, create a new post at BlueSky
     *
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function updateStatus($id, Request $request)
    {
        $accessToken = $this->accessToken();

        $animeId = $id;

        $status = $request->input('status');
        $score = $request->score;

        $anime = $this->myAnimeListService->getAnime($accessToken, $animeId);

        if (!isset($anime['id'])) {
            return redirect('/')->with('error', 'Anime not found!');
        }

        $url = $anime['main_picture']['large'];

        $response = $this->myAnimeListService->updateAnimeStatus($accessToken, $animeId, $status, $score, $url, $anime);

        return redirect('/user/anime-list')->with('success', 'Status updated successfully!');
    }
}
