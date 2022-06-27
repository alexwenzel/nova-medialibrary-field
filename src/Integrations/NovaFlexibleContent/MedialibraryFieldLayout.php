<?php declare(strict_types=1);

namespace Aqjw\MedialibraryField\Integrations\NovaFlexibleContent;

use Whitecube\NovaFlexibleContent\Layouts\Layout;

class MedialibraryFieldLayout extends Layout
{
    use HasMedialibraryField;

    public function __construct($title = null, $name = null, $fields = null, $key = null, $attributes = [])
    {
        parent::__construct($title, $name, $fields, $key, $attributes);

        $this->replaceTemporaryKeysWhenFilling();
    }
}
