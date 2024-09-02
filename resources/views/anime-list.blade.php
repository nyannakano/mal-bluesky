@extends('layout')

@section('content')
    @foreach($animeList as $anime)
        <div class="card mb-3">
            <div class="row g-0">
                <div class="col-md-4">
                    <img src="{{ $anime['node']['main_picture']['medium'] }}" class="img-fluid rounded-start"
                         alt="{{ $anime['node']['title'] }}">
                </div>
                <div class="col-md-8">
                    <div class="card-body">
                        <h5 class="card-title mb-0">{{ $anime['node']['title'] }}</h5>

                        <p class="card-text">
                            <small class="text-muted d-block">Total
                                Episodes: {{ $anime['node']['anime_data']['num_episodes'] }}</small>
                            <small
                                class="text-muted d-block">Score: {{ $anime['node']['anime_data']['my_list_status']['score'] }}</small>
                            <small
                                class="text-muted d-block">Status: {{ $anime['node']['anime_data']['my_list_status']['status'] }}</small>
                            <small class="text-muted d-block">Watched
                                Episodes: {{ $anime['node']['anime_data']['my_list_status']['num_episodes_watched'] }}</small>
                             <form
                                action="{{ route('add-episode', ['id' => $anime['node']['id'], 'quantity' => 1]) }}"
                                method="post">
                                @csrf
                                <button type="submit" class="btn btn-primary">Add Episode</button>

                            </form>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
