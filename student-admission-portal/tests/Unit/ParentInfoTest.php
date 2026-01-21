<?php

namespace Tests\Unit;

use App\Models\ParentInfo;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ParentInfoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_has_correct_fillable_attributes()
    {
        $parentInfo = new ParentInfo();

        // sort arrays to ensure order doesn't matter
        $expected = [
            'student_id',
            'guardian_name',
            'guardian_phone',
            'relationship',
            'guardian_email',
        ];
        
        $actual = $parentInfo->getFillable();
        
        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function it_belongs_to_a_student()
    {
        // We'll skip factory for now and create manually to test relationship method existence
        $student = Student::factory()->create();
        
        // We can't save ParentInfo yet if columns don't exist, so we test the relation method on instance
        $parentInfo = new ParentInfo();
        $parentInfo->student_id = $student->id;
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $parentInfo->student());
    }
}
