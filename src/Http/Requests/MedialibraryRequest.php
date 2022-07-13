<?php

declare(strict_types=1);

namespace Aqjw\MedialibraryField\Http\Requests;

use Aqjw\MedialibraryField\Fields\Medialibrary;
use Aqjw\MedialibraryField\MedialibraryFieldResolver;
use Laravel\Nova\Http\Requests\NovaRequest;

class MedialibraryRequest extends NovaRequest
{
    public function medialibraryField(): Medialibrary
    {
        return call_user_func(new MedialibraryFieldResolver($this));
    }

    public function resourceExists(): bool
    {
        $resourceId = $this->route('resourceId');
        return !empty($resourceId) && $resourceId !== "undefined";
    }

    public function fieldUuid(): string
    {
        return $this->input('fieldUuid');
    }
}
