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
                                class="text-muted d-block">Status: {{ $anime['node']['anime_data']['status_title'] }}</small>
                            <small class="text-muted d-block">Watched
                                Episodes: {{ $anime['node']['anime_data']['my_list_status']['num_episodes_watched'] }}</small>
                            <button type="button" class="btn btn-warning mt-2 mb-2 text-white"
                                    onclick="toggleStatus('{{ $anime['node']['id'] }}')">Change Status
                            </button>
                            <br>

                        <form action="{{ route('update-status', ['id' => $anime['node']['id']]) }}" method="post"
                              class="mt-2" id="status-{{ $anime['node']['id'] }}" hidden>
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-select mb-2 mt-2">
                                @foreach($status as $key => $value)
                                    <option value="{{ $key }}"
                                            @if($key == $anime['node']['anime_data']['my_list_status']['status']) selected @endif>{{ $value }}</option>
                                @endforeach
                            </select>
                            <input type="number" name="score" class="form-control mb-2" placeholder="Score"
                                   value="{{ $anime['node']['anime_data']['my_list_status']['score'] }}">
                            <button type="submit" class="btn btn-primary mt-2 mb-2">Save</button>
                        </form>
                        <form
                            action="{{ route('add-episode', ['id' => $anime['node']['id'], 'quantity' => 1]) }}"
                            method="post" class="mt-4">
                            @csrf
                            <button type="submit" class="btn btn-primary">Add Episode</button>
                        </form>
                        <form
                            action="{{ route('remove-episode', ['id' => $anime['node']['id'], 'quantity' => 1]) }}"
                            method="post">
                            @csrf
                            <button type="submit" class="btn btn-danger">Remove Episode</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        function toggleStatus(animeId) {
            const statusSelect = document.getElementById(`status-${animeId}`);
            statusSelect.hidden = !statusSelect.hidden;
        }
    </script>

@endsection
