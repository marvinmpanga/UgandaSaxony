<?php

namespace App\Policies;

use App\Models\MediaItem;
use App\Models\User;

class MediaItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('media.view');
    }

    public function view(User $user, MediaItem $mediaItem): bool
    {
        return $user->can('media.view');
    }

    public function create(User $user): bool
    {
        return $user->can('media.upload');
    }

    public function update(User $user, MediaItem $mediaItem): bool
    {
        return $user->can('media.edit');
    }

    public function delete(User $user, MediaItem $mediaItem): bool
    {
        return $user->can('media.delete');
    }
}
