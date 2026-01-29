<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Sales\Models\Customer;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * SalesTest - Sales module tests
 * Tests O2C (Order to Cash) flow
 */
class SalesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create required dependencies
        Category::create(['name' => 'Test', 'code' => 'TST']);
        Unit::create([
            'name' => 'Piece',
            'abbreviation' => 'PCS',
            'is_base' => true,
            'conversion_factor' => 1.0,
        ]);

        // Create test customer
        $this->customer = Customer::create([
            'code' => 'C001',
            'name' => 'Test Customer',
            'phone' => '01234567890',
            'is_active' => true,
        ]);
    }

    /**
     * Test customers index loads
     */
    public function test_customers_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/customers');

        $response->assertStatus(200);
    }

    /**
     * Test customer create page loads
     */
    public function test_customer_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/customers/create');

        $response->assertStatus(200);
    }

    /**
     * Test sales orders index loads
     */
    public function test_sales_orders_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/sales-orders');

        $response->assertStatus(200);
    }

    /**
     * Test sales invoices index loads
     */
    public function test_sales_invoices_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/sales-invoices');

        $response->assertStatus(200);
    }

    /**
     * Test quotations index loads
     */
    public function test_quotations_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/quotations');

        $response->assertStatus(200);
    }
}
