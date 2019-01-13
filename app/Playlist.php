<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    protected $fillable = ['spotify_playlist_id', 'name', 'description',
        'number_of_songs'];

    public function tracks()
    {
        return $this->hasMany(Track::class);
    }
}
