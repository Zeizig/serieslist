<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Episode.
 *
 * @property int id
 * @property string title
 * @property Season season
 *
 * @package App\Models
 */
class Episode extends Model
{
    protected $fillable = ['title', 'number'];

    /**
     * Make a string path which points to this episode.
     *
     * @return string
     */
    public function path()
    {
        return "/series/{$this->season->series->id}/episodes/{$this->id}";
    }

    /**
     * Set the one to many relationship where an episode belongs to a season.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function season()
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Check if this episode is seen by the user with the
     * given ID.
     * If no ID is given, use authenticated user ID instead.
     *
     * @param int|null $userId
     *
     * @return bool
     */
    public function isSeen($userId = null)
    {
        $userId = $userId ?: auth()->id();

        return !! SeenEpisode::where('user_id', $userId)
            ->where('episode_id', $this->id)
            ->first();
    }

    /**
     * Toggle this episode's seen status for the user with the given id.
     * If no user id is given, use authenticated user instead.
     *
     * @param int|null $userId
     *
     * @return $this
     */
    public function toggleSeen($userId = null)
    {
        $userId = $userId ?: auth()->id();

        $seen = new SeenEpisode;
        $seen->user_id = $userId;
        $seen->episode_id = $this->id;
        $seen->save();

        return $this;
    }
}
