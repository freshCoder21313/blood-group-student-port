<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\Program;
use Tests\TestCase;

class ApplicationTest extends TestCase
{
    public function test_application_belongs_to_program()
    {
        $application = new Application();
        $relation = $application->program();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $relation);
        $this->assertEquals('program_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }
}
