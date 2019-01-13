<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = ['playlist_id', 'spotify_track_id', 'track_name',
        'artist_name', 'album_name', 'length_in_seconds'];

    public function playlist()
    {
        return $this->belongsTo(Playlist::class);
    }
}
