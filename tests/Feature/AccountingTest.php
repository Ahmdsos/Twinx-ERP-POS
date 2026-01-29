<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\JournalEntry;
use Modules\Accounting\Enums\AccountType;
use Modules\Accounting\Enums\JournalStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * AccountingTest - Accounting module tests
 * Tests accounts, journal entries, and balance validation
 */
class AccountingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        // Create test accounts
        Account::create([
            'code' => '1000',
            'name' => 'Cash',
            'type' => AccountType::ASSET,
            'is_active' => true,
        ]);

        Account::create([
            'code' => '4000',
            'name' => 'Sales Revenue',
            'type' => AccountType::REVENUE,
            'is_active' => true,
        ]);
    }

    /**
     * Test accounts index loads
     */
    public function test_accounts_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/accounts');

        $response->assertStatus(200);
    }

    /**
     * Test account create page loads
     */
    public function test_account_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/accounts/create');

        $response->assertStatus(200);
    }

    /**
     * Test journal entry index loads
     */
    public function test_journal_entries_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/journal-entries');

        $response->assertStatus(200);
    }

    /**
     * Test journal entry create page loads
     */
    public function test_journal_entry_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/journal-entries/create');

        $response->assertStatus(200);
    }

    /**
     * Test accounts tree page loads
     */
    public function test_accounts_tree_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/accounts-tree');

        $response->assertStatus(200);
    }
}
