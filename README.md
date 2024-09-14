# MAL-Bluesky

**MAL-Bluesky** is a tool that fetches ongoing anime progress from MyAnimeList (MAL) and allows you to update your progress via the application while simultaneously posting updates to Bluesky. With OAuth2 integration for MAL authentication and a user-friendly interface for managing anime lists, this application makes it easy to keep your followers on Bluesky informed about your anime watching progress.


## Features

- **OAuth2 Authentication with MyAnimeList**: Log in with your MAL account to sync and manage your anime lists.
- **Anime List Management**: View your anime lists directly within the application and add newly watched episodes.
- **Automatic Posting to Bluesky**: After updating your anime progress, status or score, the tool automatically posts a message to Bluesky in the format "Watched X episodes of ANIME NAME". The same happens to Finished, and Dropped.
- **Redis Caching**: Caching is implemented to reduce the number of requests to the MAL API.

## Images
<details>

![Lists](https://i.imgur.com/O4ueWcM.png)

![Update](https://i.imgur.com/Ir9uOxM.png)

</details>

## Prerequisites

- MyAnimeList API credentials. You can obtain them by creating a new application [here](https://myanimelist.net/apiconfig). For the redirect URI, use `http://localhost:80/auth/mal/callback`.
- Bluesky account
- Docker

## Installation

1. **Clone the repository:**

    ```bash
    git clone https://github.com/nyannakano/mal-bluesky
    ```

2. **Navigate to the project directory:**

    ```bash
    cd mal-bluesky
    ```

3. **Start Laravel Sail:**

   Make sure you have Docker installed. Start Sail by running:

    ```bash
    ./vendor/bin/sail up
    ```

4. **Install the dependencies:**

   Inside the Sail container, run:

    ```bash
    ./vendor/bin/sail composer install
    ```

5. **Configure the `.env` file:**

   Copy the example environment file and set up your credentials:

    ```bash
    cp .env.example .env
    ```

   Open the `.env` file and configure it with your credentials:

    ```plaintext
    MAL_CLIENT_ID=your-client-id
    MAL_CLIENT_SECRET=your-client-secret
    BLUESKY_USERNAME=username.bsky.social
    BLUESKY_PASSWORD=password
    ```

6. **Access the application:**

   You can now access your application at `http://localhost` or `http://127.0.0.1` depending on your Sail configuration.


## Usage

1. **Authenticate with MyAnimeList**: Access the MyAnimeList login screen and log in using OAuth2. You will be redirected to the application with your anime lists synced.

2. **Manage Lists**: On the list management screen, you can view your animes and add watched episodes.

3. **Post Updates to Bluesky**: With each progress update, a message will be automatically posted to Bluesky in the format "Watched X episodes of ANIME NAME".

## Contributing

1. Fork the repository.
2. Create a feature branch (`git checkout -b feature/YourFeature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/YourFeature`).
5. Create a new Pull Request.
