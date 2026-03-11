<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('appointments', function ($user) {
    return $user !== null; // Only authenticated users can listen
});
