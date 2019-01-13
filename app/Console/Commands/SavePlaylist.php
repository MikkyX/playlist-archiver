<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Playlist;
use App\Track;
use Illuminate\Support\Facades\Cache;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class SavePlaylist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:playlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save playlist from Spotify into the local database';

    protected $spotifyApi;
    protected $spotifyClient;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (!Cache::has('accessToken')) {
            $this->spotifyClient = new Session(
                config('spotify.client_id'),
                config('spotify.client_secret')
            );

            if ($this->spotifyClient->requestCredentialsToken()) {
                $tokenExpiryMinutes = floor(($this->spotifyClient->getTokenExpiration() - time()) / 60);

                Cache::put(
                    'accessToken',
                    $this->spotifyClient->getAccessToken(),
                    $tokenExpiryMinutes
                );
            }
        }

        $accessToken = Cache::get('accessToken');

        $this->spotifyApi = new SpotifyWebAPI();
        $this->spotifyApi->setAccessToken($accessToken);

        $playlist = $this->spotifyApi->getPlaylist(config('spotify.playlist_id'));

        $local_playlist = Playlist::create([
            'spotify_playlist_id' => $playlist->id,
            'name' => $playlist->name,
            'description' => $playlist->description,
            'number_of_songs' => count($playlist->tracks->items),
        ]);

        collect($playlist->tracks->items)->each(function ($track) use ($local_playlist) {
            // TODO: Refactor this to use map
            $track = $track->track;

            Track::create([
                'playlist_id' => $local_playlist->id,
                'spotify_track_id' => $track->id,
                'track_name' => $track->name,
                'artist_name' => $track->artists[0]->name,
                'album_name' => $track->album->name,
                'length_in_seconds' => floor($track->duration_ms / 1000),
            ]);
        });
    }
}
