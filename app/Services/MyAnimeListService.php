<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MyAnimeListService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $codeVerifier;

    public function __construct()
    {
        $this->clientId = config('services.myanimelist.client_id');
        $this->clientSecret = config('services.myanimelist.client_secret');
        $this->redirectUri = config('services.myanimelist.redirect_uri');
    }

    public function getAuthorizationUrl()
    {
        $this->codeVerifier = Str::random(128);

        session(['mal_code_verifier' => $this->codeVerifier]);

        $codeChallenge = $this->codeVerifier;

        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'state' => Str::random(40),
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'plain',
        ]);

        return 'https://myanimelist.net/v1/oauth2/authorize?' . $query;
    }

    public function getAccessToken($code)
    {
        $codeVerifier = session('mal_code_verifier');

        $response = Http::asForm()->post('https://myanimelist.net/v1/oauth2/token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirectUri,
            'code_verifier' => $codeVerifier,
        ]);

        if ($response->failed()) {
            return $response->json();
        }

        return $response->json();
    }


    public function getUserAnimeList($accessToken)
    {
        $response = Http::withToken($accessToken)
            ->get('https://api.myanimelist.net/v2/users/@me/animelist', [
                'status' => 'watching',
            ]);

        $animeListResponse = $response->json();
        $animeList = [];

        foreach ($animeListResponse['data'] as $anime) {
            $id = $anime['node']['id'];

            $anime['node']['anime_data'] = $this->getAnime($accessToken, $id);

            $animeList[] = $anime;
        }

        return $animeList;
    }

    /**
     * @throws GuzzleException
     */
    public function addEpisode($accessToken, $animeId, $episode, $url, $anime)
    {
        $data = [
            'num_watched_episodes' => $episode,
        ];

        $myAnimeListUrl = 'https://myanimelist.net/anime/' . $animeId;

        $response = $this->sendToMalEpisodes($data, $animeId, $accessToken);

        if ($response->successful()) {
            Cache::forget('anime_' . $animeId);

            $this->getAnime($accessToken, $animeId);

            if ($episode == $anime['num_episodes']) {
                $this->downloadImage($url, $animeId . '.jpg');
                $this->blueSkyPost('Finished watching ' . $anime['title'], public_path('images/' . $animeId . '.jpg'), $myAnimeListUrl);
                return $response->json();
            }

            $this->downloadImage($url, $animeId . '.jpg');
            $this->blueSkyPost('Watched ' . $episode . ' episodes of ' . $anime['title'], public_path('images/' . $animeId . '.jpg'), $myAnimeListUrl);
            return $response->json();
        } else {
            return [
                'error' => $response->status(),
                'message' => $response->body(),
            ];
        }
    }

    public function getAnime($accessToken, $animeId)
    {
        $cacheKey = 'anime_' . $animeId;

        $animeData = Cache::remember($cacheKey, 3000, function () use ($accessToken, $animeId) {
            $response = Http::withToken($accessToken)
                ->get('https://api.myanimelist.net/v2/anime/' . $animeId, [
                    'fields' => 'id,title,num_episodes,my_list_status',
                ]);

            return $response->json();
        });

       $animeData['status_title'] = $this->getStatus()[$animeData['my_list_status']['status']];

        return $animeData;
    }

    /**
     * @throws GuzzleException
     */
    public function downloadImage($imageUrl, $filename)
    {
        $client = new Client();

        $response = $client->get($imageUrl);

        $imageContents = $response->getBody()->getContents();

        $filePath = public_path('images/' . $filename);

        file_put_contents($filePath, $imageContents);

        return $filePath;
    }

    public function blueSkyPost($message, $path, $url)
    {
        $blueSkyService = new BlueSkyService();

        $blueSkyService->createPost($message, $path, $url);
    }

    public function removeEpisode($accessToken, $animeId, $episode)
    {
        $data = [
            'num_watched_episodes' => $episode,
        ];

        $response = $this->sendToMalEpisodes($data, $animeId, $accessToken);

        if ($response->successful()) {
            Cache::forget('anime_' . $animeId);

            $this->getAnime($accessToken, $animeId);

            return $response->json();
        } else {
            return [
                'error' => $response->status(),
                'message' => $response->body(),
            ];
        }
    }

    public function updateAnimeStatus($accessToken, $animeId, $status, $score, $url, $anime)
    {
        $data = [
            'status' => $status,
            'score' => $score,
        ];

        $response = $this->sendToMalEpisodes($data, $animeId, $accessToken);

        if ($response->successful()) {
            Cache::forget('anime_' . $animeId);

            $this->getAnime($accessToken, $animeId);

            $this->downloadImage($url, $animeId . '.jpg');

            if ($status == 'completed') {
                $this->blueSkyPost('Finished watching ' . $anime['title'] . ' ' . $score . '/10 ', public_path('images/' . $animeId . '.jpg'), 'https://myanimelist.net/anime/' . $animeId);
            } elseif ($status == 'dropped') {
                $this->blueSkyPost('Dropped ' . $anime['title'], public_path('images/' . $animeId . '.jpg'), 'https://myanimelist.net/anime/' . $animeId);
            }

            return $response->json();
        } else {
            return [
                'error' => $response->status(),
                'message' => $response->body(),
            ];
        }
    }

    public function sendToMalEpisodes($data, $animeId, $accessToken)
    {
        return Http::withToken($accessToken)
            ->asForm()
            ->patch('https://api.myanimelist.net/v2/anime/' . $animeId . '/my_list_status', $data);
    }

    public function getStatus()
    {
        return [
            'watching' => 'Watching',
            'completed' => 'Completed',
            'on_hold' => 'On-Hold',
            'dropped' => 'Dropped',
            'plan_to_watch' => 'Plan to Watch',
        ];
    }
}
