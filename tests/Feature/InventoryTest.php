<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\Category;
use Modules\Inventory\Models\Unit;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * InventoryTest - Inventory module tests
 * Tests products, stock movements
 */
class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUserWithPermissions(['inventory.manage']);

        // Create test data with correct field names
        Category::create(['name' => 'Test Category', 'code' => 'CAT001']);
        Unit::create([
            'name' => 'Piece',
            'abbreviation' => 'PCS',
            'is_base' => true,
            'conversion_factor' => 1.0,
        ]);
    }

    /**
     * Test products index loads
     */
    public function test_products_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/products');

        $response->assertStatus(200);
    }

    /**
     * Test product creation page loads
     */
    public function test_product_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/products/create');

        $response->assertStatus(200);
    }

    /**
     * Test categories index loads
     */
    public function test_categories_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/categories');

        $response->assertStatus(200);
    }

    /**
     * Test units index loads
     */
    public function test_units_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/units');

        $response->assertStatus(200);
    }

    /**
     * Test stock page loads
     */
    public function test_stock_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/stock');

        $response->assertStatus(200);
    }
}
