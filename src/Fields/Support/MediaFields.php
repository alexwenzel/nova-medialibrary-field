<?php

declare(strict_types=1);

namespace Aqjw\MedialibraryField\Fields\Support;

use Aqjw\MedialibraryField\Fields\GeneratedConversions;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

class MediaFields
{
    public static function make(): callable
    {
        return function (Request $request) {
            return [
                ID::make(),
                Textarea::make(__('Media Description'), 'custom_properties->description')->alwaysShow(),
            ];
        };
    }
}
