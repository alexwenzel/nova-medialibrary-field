<?php

declare(strict_types=1);

namespace Aqjw\MedialibraryField\Tests\Integration;

use Aqjw\MedialibraryField\Tests\TestCase;

class CreationFieldControllerTest extends TestCase
{
    /** @test */
    public function test_can_retrieve_resource_creation_fields(): void
    {
        $response = $this
            ->getJson('nova-api/test-posts/creation-fields?editing=true&editMode=create')
            ->assertSuccessful();

        foreach ($response->decodeResponseJson()['fields'] as $field) {
            if ($field['component'] === 'medialibrary-field') {
                $this->assertNotNull($field['value']);
            }
        }
    }
}
