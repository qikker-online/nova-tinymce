<?php

use QikkerOnline\NovaTinyMCE\Http\Controllers\UploadsController;

Route::post('/upload-image', config('nova-tinymce.upload-image-handler'))
    ->name('nova-tinymce.image-upload');
