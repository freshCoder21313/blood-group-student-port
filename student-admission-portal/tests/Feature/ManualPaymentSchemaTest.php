<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ManualPaymentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_table_has_manual_fallback_columns()
    {
        $columns = Schema::getColumnListing('payments');
        
        // Story requires 'transaction_code' and 'proof_document_path'
        $this->assertContains('transaction_code', $columns, "Column 'transaction_code' missing from payments table");
        $this->assertContains('proof_document_path', $columns, "Column 'proof_document_path' missing from payments table");
    }
}
