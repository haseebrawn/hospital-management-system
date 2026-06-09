<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('appointments', function ($user) {
    return $user !== null; // Only authenticated users can listen
});

Broadcast::channel('App.Models.User.{id}', function ($user, int $id) {
    return (int) $user->id === $id;
});
