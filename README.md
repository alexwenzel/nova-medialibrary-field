# Nova Medialibrary

Laravel Nova field for managing the Spatie media library.

**Important:** This field has different behaviour then the original medialibrary field. I changed some things to fit my own needs.

## Table of Contents

 - [Screenshots](#screenshots)
 - [Installation](#installation)
 - [Usage](#usage)
    - [Methods](#methods)
        - [Attribute](#attribute)
        - [Fields](#fields)
        - [AttachUsing](#attachusing)
        - [ResolveMediaUsing](#resolvemediausing)
        - [AttachExisting](#attachexisting)
        - [MediaOnIndex](#mediaonindex)
        - [DownloadUsing](#downloadusing)
        - [PreviewUsing](#previewusing)
        - [Tooltip](#tooltip)
        - [Title](#title)
        - [CopyAs](#copyAs)
        - [Croppable](#croppable)
        - [Single](#single)
        - [Accept](#accept)
        - [MaxSizeInBytes](#maxsizeinbytes)
        - [AttachOnDetails](#attachondetails)
        - [AttachRules](#attachrules)
        - [Autouploading](#autouploading)
    - [Preview Customization](#preview-customization)
    - [Validation](#validation)
    - [Sorting](#sorting)
    - [Authorization Gates 'view', 'update' and 'delete'](#authorization-gates-view-update-and-delete)
 - [Translations](#translations)
 - [Changelog](#changelog)
 - [Alternatives](#alternatives)
 - [License](#license)

## Installation

This package can be installed via command:

```bash
composer require aqjw/nova-medialibrary-field
```

## Usage

```php
Medialibrary::make($name, $collectionName = '', $diskName = '', $attribute = null),
```

### Methods

#### Attribute

Sometimes you may need to use the same field label (duplicated sections, etc). The attribute must be unique. In this case you can change the default behaviour using the `attribute()` method.

```php
Medialibrary::make('name', 'collection name', 'disk name', 'custom_attribute');
// or
Medialibrary::make('name', 'collection name', 'disk name')->attribute('custom_attribute');
```

#### Fields

Define custom fields for media. [MediaFields](src/Fields/Support/MediaFields.php) is used by default.

```php
Medialibrary::make('Media')->fields(function () {
    return [
        Text::make('File Name', 'file_name')
            ->rules('required', 'min:2'),

        Text::make('Tooltip', 'custom_properties->tooltip')
            ->rules('required', 'min:2'),

        GeneratedConversions::make('Conversions')
            ->withTooltips(),
    ];
});
```
#### ResolveMediaUsing

```php
Medialibrary::make('Media')->resolveMediaUsing(function (HasMedia $model, string $collectionName) {
    return $model->getMedia($collectionName);
});
```

#### AttachUsing

Called inside [AttachController](src/Http/Controllers/AttachController.php#L32). [AttachCallback](src/Fields/Support/AttachCallback.php) is used by default.
It accepts `$fieldUuid` which is used when a resource is not created.
If you want to attach media on the create view, you should keep [these lines](src/Fields/Support/AttachCallback.php#L20-L22) in your callback.

```php
Medialibrary::make('Media')
    ->attachUsing(function (HasMedia $model, UploadedFile $file, string $collectionName, string $diskName, string $fieldUuid) {
        if ($model instanceof TransientModel) {
            $collectionName = $fieldUuid;
        }

        $fileAdder = $model->addMedia($file);

        // do something

        $fileAdder->toMediaCollection($collectionName, $diskName);
    });
```

#### AttachExisting

Allow attaching existing media.

```php
Medialibrary::make('Media')->attachExisting(); // display all media
Medialibrary::make('Media')->attachExisting('collectionName'); // display media from a specific collection
Medialibrary::make('Media')->attachExisting(function (Builder $query, Request $request, HasMedia $model) {
    $query->where(...);
});
```

#### MediaOnIndex

Display media on index

```php
Medialibrary::make('Media')->mediaOnIndex(1);
Medialibrary::make('Media')->mediaOnIndex(function (HasMedia $resource, string $collectionName) {
    return $resource->media()->where('collection_name', $collectionName)->limit(5)->get();
});
```

#### DownloadUsing

```php
Medialibrary::make('Media')->downloadUsing('conversionName');
Medialibrary::make('Media')->downloadUsing(function (Media $media) {
    return $media->getFullUrl();
});
```

#### PreviewUsing

```php
Medialibrary::make('Media')->previewUsing('conversionName');
Medialibrary::make('Media')->previewUsing(function (Media $media) {
    return $media->getFullUrl('preview');
});
```

#### Tooltip

```php
Medialibrary::make('Media')->tooltip('file_name');
Medialibrary::make('Media')->tooltip(function (Media $media) {
    return $media->getCustomProperty('tooltip');
});
```

#### Title

```php
Medialibrary::make('Media')->title('name');
Medialibrary::make('Media')->title(function (Media $media) {
    return $media->name;
});
```

#### CopyAs

```php
Medialibrary::make('Media')->copyAs('Url', function (Media $media) {
    return $media->getFullUrl();
});

Medialibrary::make('Media')->copyAs('Html', function (Media $media) {
    return $media->img();
}, 'custom-icon');

// You can hide default "Copy Url"
Medialibrary::make('Media')->hideCopyUrlAction();
```

#### Croppable

https://github.com/fengyuanchen/cropperjs#options

```php
Medialibrary::make('Media')->croppable('conversionName');
Medialibrary::make('Media')->croppable('conversionName', ['viewMode' => 3]);
Medialibrary::make('Media')->croppable('conversionName', [
    'rotatable' => false,
    'zoomable' => false,
    'cropBoxResizable' => false,
]);
Medialibrary::make('Media')->croppable('conversionName', function (Media $media) {
    return $media->getCustomProperty('croppable') ? ['viewMode' => 3] : null;
});
```
https://docs.spatie.be/laravel-medialibrary/v8/converting-images/defining-conversions/#performing-conversions-on-specific-collections
> {note} If your media in different collection, make sure pass your collectionName to `performOnCollections`

```php
$this->addMediaConversion('conversionName')->performOnCollections('collectionName')
```

#### Single

https://docs.spatie.be/laravel-medialibrary/v7/working-with-media-collections/defining-media-collections/#single-file-collections

```php
Medialibrary::make('Media')->single();
```

#### Accept

```php
Medialibrary::make('Media')->accept('image/*');
```

#### MaxSizeInBytes

```php
Medialibrary::make('Media')->maxSizeInBytes(1024 * 1024);
```

#### AttachOnDetails

Allows attaching files on the details view.

```php
Medialibrary::make('Media')->attachOnDetails();
```

#### AttachRules

```php
Medialibrary::make('Media')->attachRules('image', 'dimensions:min_width=500,min_height=500');
```

#### Autouploading

```php
Medialibrary::make('Media')->autouploading();
```

#### Preview Customization

```php
Medialibrary::make('Media')->withMeta([
    'indexPreviewClassList' => 'rounded w-8 h-8 ml-2',
    'detailsPreviewClassList' => 'w-32 h-24 rounded-b',
]);
```

### Validation

```php
Medialibrary::make('Media')
    ->rules('array', 'required') // applied to the media collection
    ->creationRules('min:2') // applied to the media collection
    ->updateRules('max:4') // applied to the media collection
    ->attachRules('image', 'dimensions:min_width=500,min_height=500'); // applied to media
```

### Sorting

```php
Medialibrary::make('Media')->sortable();
```

### Authorization Gates 'view', 'update' and 'delete'

To view, update and delete uploaded media, you need to setup some gates.
You can use the store and replace callbacks to store additional information to the custom_properties.
The additional information can be used inside the gates for authorization.

```php
Gate::define('view', function ($user, $media) {
    return true; // view granted
});

Gate::define('update', function ($user, $media) {
    return true; // update granted
});

Gate::define('delete', function ($user, $media) {
    return true; // deletion granted
});
```

You can also use the policy.

```php
class MediaPolicy
{
    public function view(User $user, Media $media): bool
    {
        return true;
    }

    public function update(User $user, Media $media): bool
    {
        return true;
    }

    public function delete(User $user, Media $media): bool
    {
        return true;
    }
}

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Media::class => MediaPolicy::class,
    ];

    //...
}
```

## Translations

 - [en](resources/lang/en.json)
 - [fr](resources/lang/fr.json)
 - [pt-BR](resources/lang/pt-BR.json)
 - [ru](resources/lang/ru.json)
 - [tr](resources/lang/tr.json)
 - [uk](resources/lang/uk.json)
 - [zh-CN](resources/lang/zh-CN.json)

## Changelog

Please see the [CHANGELOG](CHANGELOG.md) for more information about the most recent changed.

## Alternatives

 - https://github.com/ebess/advanced-nova-media-library

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
