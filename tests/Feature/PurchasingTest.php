<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Purchasing\Models\Supplier;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * PurchasingTest - Purchasing module tests
 * Tests P2P (Purchase to Pay) flow
 */
class PurchasingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(['purchases.manage']);

        // Create required dependencies
        Category::create(['name' => 'Test', 'code' => 'TST']);
        Unit::create([
            'name' => 'Piece',
            'abbreviation' => 'PCS',
            'is_base' => true,
            'conversion_factor' => 1.0,
        ]);

        // Create test supplier
        $this->supplier = Supplier::create([
            'code' => 'S001',
            'name' => 'Test Supplier',
            'phone' => '01234567890',
            'is_active' => true,
        ]);
    }

    /**
     * Test suppliers index loads
     */
    public function test_suppliers_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/suppliers');

        $response->assertStatus(200);
    }

    /**
     * Test supplier create page loads
     */
    public function test_supplier_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/suppliers/create');

        $response->assertStatus(200);
    }

    /**
     * Test purchase orders index loads
     */
    public function test_purchase_orders_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/purchase-orders');

        $response->assertStatus(200);
    }

    /**
     * Test GRN index loads
     */
    public function test_grn_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/grns');

        $response->assertStatus(200);
    }

    /**
     * Test purchase invoices index loads
     */
    public function test_purchase_invoices_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/purchase-invoices');

        $response->assertStatus(200);
    }

    /**
     * Test supplier payments index loads
     */
    public function test_supplier_payments_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/supplier-payments');

        $response->assertStatus(200);
    }
}
