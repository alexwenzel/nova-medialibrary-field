<?php declare(strict_types=1);

namespace Aqjw\MedialibraryField\Integrations\NovaFlexibleContent;

use Aqjw\MedialibraryField\Fields\Medialibrary;
use Whitecube\NovaFlexibleContent\Layouts\Layout;

/**
 * @mixin \Whitecube\NovaFlexibleContent\Layouts\Layout
 */
trait HasMedialibraryField
{
    protected function replaceTemporaryKeysWhenFilling(): void
    {
        if (is_null($this->_key) || is_null($this->key)) {
            return;
        }

        /** @var \Aqjw\MedialibraryField\Fields\Medialibrary $field */
        foreach ($this->fields->whereInstanceOf(Medialibrary::class) as $field) {
            $field->fillUsing(function ($request, $model): void {
                if ($model instanceof Layout) {
                    config('media-library.media_model')::query()
                        ->where('custom_properties->flexibleKey', $model->_key)
                        ->update(['custom_properties->flexibleKey' => $model->key]);
                }
            });
        }
    }
}
