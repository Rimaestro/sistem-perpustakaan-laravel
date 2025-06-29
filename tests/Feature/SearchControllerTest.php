<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Member;
use App\Models\Category;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $category;
    protected $book;
    protected $member;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'role' => 'admin'
        ]);

        // Create test category
        $this->category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test category description',
            'slug' => 'test-category'
        ]);

        // Create test book
        $this->book = Book::create([
            'title' => 'Test Book Title',
            'author' => 'Test Author',
            'isbn' => '978-0123456789',
            'category_id' => $this->category->id,
            'status' => 'available',
            'barcode' => 'BK2025001',
            'publication_year' => 2023,
            'description' => 'Test book description',
            'quantity' => 5,
            'available_quantity' => 5
        ]);

        // Create test member
        $this->member = Member::create([
            'member_id' => 'M2025001',
            'name' => 'Test Member',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'address' => 'Test Address',
            'join_date' => now(),
            'status' => 'active',
            'card_number' => 'CARD2025001'
        ]);
    }

    /** @test */
    public function it_can_perform_global_search_for_books()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/global?q=Test Book&type=books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'type',
                    'id',
                    'title',
                    'subtitle',
                    'category',
                    'status',
                    'url',
                    'display',
                    'icon'
                ]
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('book', $data[0]['type']);
        $this->assertEquals($this->book->title, $data[0]['title']);
    }

    /** @test */
    public function it_can_perform_global_search_for_members()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/global?q=Test Member&type=members');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'type',
                    'id',
                    'title',
                    'subtitle',
                    'phone',
                    'status',
                    'url',
                    'display',
                    'icon'
                ]
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data);
        $this->assertEquals('member', $data[0]['type']);
        $this->assertEquals($this->member->name, $data[0]['title']);
    }

    /** @test */
    public function it_can_perform_global_search_for_all_types()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/global?q=Test&type=all');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotEmpty($data);
        
        // Should contain both books and members
        $types = array_column($data, 'type');
        $this->assertContains('book', $types);
        $this->assertContains('member', $types);
    }

    /** @test */
    public function it_returns_empty_array_for_short_queries()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/global?q=T&type=all');

        $response->assertStatus(200)
            ->assertJson([]);
    }

    /** @test */
    public function it_can_get_search_suggestions()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/suggestions?type=books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'text',
                    'type',
                    'icon'
                ]
            ]);
    }

    /** @test */
    public function it_can_get_filter_options_for_books()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/filter-options?type=books');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'categories',
                'authors',
                'years',
                'statuses'
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['categories']);
        $this->assertNotEmpty($data['authors']);
        $this->assertNotEmpty($data['statuses']);
    }

    /** @test */
    public function it_can_get_filter_options_for_members()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/filter-options?type=members');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'statuses'
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['statuses']);
    }

    /** @test */
    public function it_can_perform_advanced_search_for_books()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/advanced?search=Test&type=books&filters[category_id]=' . $this->category->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total'
                ]
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['data']);
        $this->assertArrayHasKey('pagination', $data);
    }

    /** @test */
    public function it_can_perform_advanced_search_for_members()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/advanced?search=Test&type=members&filters[status]=active');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'pagination'
            ]);

        $data = $response->json();
        $this->assertNotEmpty($data['data']);
    }

    /** @test */
    public function it_requires_ajax_for_api_endpoints()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/search/global?q=test');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_access_advanced_search_page()
    {
        $response = $this->actingAs($this->user)
            ->get('/search');

        $response->assertStatus(200)
            ->assertViewIs('pages.search.advanced');
    }

    /** @test */
    public function it_can_access_advanced_search_page_with_parameters()
    {
        $response = $this->actingAs($this->user)
            ->get('/search?search=Test&type=books');

        $response->assertStatus(200)
            ->assertViewIs('pages.search.advanced');
    }

    /** @test */
    public function it_limits_search_results()
    {
        // Create multiple books
        for ($i = 2; $i <= 16; $i++) {
            Book::create([
                'title' => "Test Book $i",
                'author' => 'Test Author',
                'isbn' => "978-012345678$i",
                'category_id' => $this->category->id,
                'status' => 'available',
                'barcode' => "BK202500$i",
                'publication_year' => 2023,
                'description' => 'Test book description',
                'quantity' => 1,
                'available_quantity' => 1
            ]);
        }

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/global?q=Test Book&type=books&limit=5');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertCount(5, $data);
    }

    /** @test */
    public function it_sorts_results_by_relevance()
    {
        // Create books with different relevance
        $exactMatch = Book::create([
            'title' => 'Exact Match',
            'author' => 'Test Author',
            'isbn' => '978-0123456780',
            'category_id' => $this->category->id,
            'status' => 'available',
            'barcode' => 'BK2025002',
            'publication_year' => 2023,
            'description' => 'Test book description',
            'quantity' => 1,
            'available_quantity' => 1
        ]);

        $partialMatch = Book::create([
            'title' => 'Some Exact Title',
            'author' => 'Test Author',
            'isbn' => '978-0123456781',
            'category_id' => $this->category->id,
            'status' => 'available',
            'barcode' => 'BK2025003',
            'publication_year' => 2023,
            'description' => 'Test book description',
            'quantity' => 1,
            'available_quantity' => 1
        ]);

        $response = $this->actingAs($this->user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson('/api/search/global?q=Exact&type=books');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotEmpty($data);
        
        // Exact match should come first
        $this->assertEquals('Exact Match', $data[0]['title']);
    }
}
