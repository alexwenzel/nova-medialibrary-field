<?php

declare(strict_types=1);

namespace Aqjw\MedialibraryField\Tests;

use Aqjw\MedialibraryField\FieldServiceProvider;
use Aqjw\MedialibraryField\Tests\Fixtures\TestPost;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Actions\ActionResource;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaCoreServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Mockery;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaLibraryServiceProvider;

abstract class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpTestFiles($this->app);
        $this->setUpDatabase($this->app);
        $this->setUpNova($this->app);
    }

    protected function getPackageProviders($app): array
    {
        return [
            NovaServiceProvider::class,
            NovaCoreServiceProvider::class,
            FieldServiceProvider::class,
            MediaLibraryServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase($app): void
    {
        $app['db']->connection()->getSchemaBuilder()->create('test_posts', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->timestamps();
        });

        include_once __DIR__.'/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub';
        include_once __DIR__.'/../vendor/laravel/nova/database/migrations/2018_01_01_000000_create_action_events_table.php';
        include_once __DIR__.'/../vendor/laravel/nova/database/migrations/2019_05_10_000000_add_fields_to_action_events_table.php';

        if (class_exists(\CreateMediaTable::class)) {
            // PHP 7 only
            (new \CreateMediaTable())->up();
        } else {
            // PHP 8+ version of medialibrary uses anonymous class, so we can require and call it
            $mediaTableMigration = require __DIR__.'/../vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub';
            $mediaTableMigration->up();
        }

        (new \CreateActionEventsTable())->up();
        (new \AddFieldsToActionEventsTable())->up();
    }

    protected function setUpNova($app): void
    {
        $this->app['config']->set('nova.actions.resource', ActionResource::class);

        // Middleware required because of impersonation check
        Route::middlewareGroup('nova', [
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        Route::middlewareGroup('nova:api', ['nova']);

        Nova::resources([
            \Aqjw\MedialibraryField\Tests\Fixtures\Nova\TestPost::class,
        ]);

        Nova::$resources = array_filter(Nova::$resources);

        event(new ServingNova(Request::create('/')));
    }

    protected function setUpTestFiles($app): void
    {
        $tmpDirectory = $this->getSupportPath('tmp');

        if ($app['files']->isDirectory($tmpDirectory)) {
            $app['files']->deleteDirectory($tmpDirectory);
        }

        $app['files']->makeDirectory($tmpDirectory);
        $app['files']->copyDirectory($this->getSupportPath('files'), $tmpDirectory);
    }

    public function getSupportPath(string $path = ''): string
    {
        return __DIR__.'/Support'.($path ? '/'.$path : '');
    }

    public function getTextFile(): string
    {
        return $this->getSupportPath('tmp/test.txt');
    }

    public function getIgnoredTextFile(): string
    {
        return $this->getSupportPath('tmp/ignored.txt');
    }

    public function getJpgFile(): string
    {
        return $this->getSupportPath('tmp/test.jpg');
    }

    public function createPost(): TestPost
    {
        return TestPost::create();
    }

    public function createPostWithMedia($media = 1, string $collectionName = 'default', string $file = null): TestPost
    {
        $post = $this->createPost();

        $media = is_array($media)
            ? $media
            : array_pad([], $media, [$collectionName, $file ?: $this->getJpgFile()]);

        foreach ($media as [$collectionName, $file]) {
            $this->addMediaTo($post, $file, $collectionName);
        }

        return $post;
    }

    public function addMediaTo(HasMedia $model, string $file, string $collectionName): Media
    {
        return $model
            ->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection($collectionName);
    }

    public function authenticate(): self
    {
        $this->actingAs($this->authenticatedAs = Mockery::mock(Authenticatable::class));

        $this->authenticatedAs->shouldReceive('getAuthIdentifier')->andReturn(1);

        return $this;
    }
}
