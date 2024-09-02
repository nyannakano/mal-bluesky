@extends('layout')

@section('content')

    <div class="text-center">
        <h1 class="mb-4">Post your MAL updates at BlueSky</h1>
        <div class="d-grid gap-2">
            <a href="/auth/mal" class="btn btn-primary">Login with MyAnimeList</a>
            <a href="/user/anime-list" class="btn btn-secondary">Anime List</a>
        </div>
    </div>

@endsection
