<?php declare(strict_types=1);

namespace Aqjw\MedialibraryField\Integrations\NovaDependencyContainer;

use Aqjw\MedialibraryField\Fields\Medialibrary;
use Laravel\Nova\Fields\FieldCollection;

class ResolveFromDependencyContainerFields
{
    public function __invoke(FieldCollection $fields, string $attribute): ?Medialibrary
    {
        return $fields->map(function ($field) {
            if (! is_a($field, \Epartment\NovaDependencyContainer\NovaDependencyContainer::class)) {
                return $field;
            }

            return $field->meta['fields'];
        })
            ->flatten()
            ->whereInstanceOf(Medialibrary::class)
            ->findFieldByAttribute($attribute);
    }
}
