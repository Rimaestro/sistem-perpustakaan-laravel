<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Member;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    /**
     * Global search across books and members
     */
    public function global(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);
        $type = $request->get('type', 'all'); // all, books, members

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Search books
        if ($type === 'all' || $type === 'books') {
            $books = Book::with('category')
                ->search($query)
                ->limit($limit)
                ->get(['id', 'title', 'author', 'isbn', 'barcode', 'category_id', 'status', 'available_quantity'])
                ->map(function ($book) {
                    return [
                        'type' => 'book',
                        'id' => $book->id,
                        'title' => $book->title,
                        'subtitle' => 'oleh ' . $book->author,
                        'category' => $book->category->name,
                        'status' => $book->status,
                        'available_quantity' => $book->available_quantity,
                        'url' => route('books.show', $book->id),
                        'display' => $book->title . ' - ' . $book->author,
                        'icon' => 'book'
                    ];
                });

            $results = array_merge($results, $books->toArray());
        }

        // Search members
        if ($type === 'all' || $type === 'members') {
            $members = Member::search($query)
                ->active()
                ->limit($limit)
                ->get(['id', 'member_id', 'name', 'email', 'phone', 'status'])
                ->map(function ($member) {
                    return [
                        'type' => 'member',
                        'id' => $member->id,
                        'title' => $member->name,
                        'subtitle' => $member->member_id . ' - ' . $member->email,
                        'phone' => $member->phone,
                        'status' => $member->status,
                        'url' => route('members.show', $member->id),
                        'display' => $member->name . ' (' . $member->member_id . ')',
                        'icon' => 'user'
                    ];
                });

            $results = array_merge($results, $members->toArray());
        }

        // Sort by relevance (exact matches first)
        usort($results, function ($a, $b) use ($query) {
            $aExact = stripos($a['title'], $query) === 0 ? 1 : 0;
            $bExact = stripos($b['title'], $query) === 0 ? 1 : 0;
            return $bExact - $aExact;
        });

        return response()->json(array_slice($results, 0, $limit));
    }

    /**
     * Get search suggestions based on popular searches
     */
    public function suggestions(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $query = $request->get('q', '');
        $type = $request->get('type', 'all');

        $suggestions = [];

        // Get popular book titles and authors
        if ($type === 'all' || $type === 'books') {
            $popularBooks = Cache::remember('popular_book_searches', 3600, function () {
                return Book::select('title', 'author')
                    ->withCount('loans')
                    ->orderBy('loans_count', 'desc')
                    ->limit(20)
                    ->get()
                    ->flatMap(function ($book) {
                        return [$book->title, $book->author];
                    })
                    ->unique()
                    ->values();
            });

            if (!empty($query)) {
                $bookSuggestions = $popularBooks->filter(function ($item) use ($query) {
                    return stripos($item, $query) !== false;
                })->take(5);
            } else {
                $bookSuggestions = $popularBooks->take(5);
            }

            foreach ($bookSuggestions as $suggestion) {
                $suggestions[] = [
                    'text' => $suggestion,
                    'type' => 'book',
                    'icon' => 'book'
                ];
            }
        }

        // Get popular member names
        if ($type === 'all' || $type === 'members') {
            $popularMembers = Cache::remember('popular_member_searches', 3600, function () {
                return Member::select('name')
                    ->withCount('loans')
                    ->orderBy('loans_count', 'desc')
                    ->limit(10)
                    ->pluck('name')
                    ->unique()
                    ->values();
            });

            if (!empty($query)) {
                $memberSuggestions = $popularMembers->filter(function ($item) use ($query) {
                    return stripos($item, $query) !== false;
                })->take(3);
            } else {
                $memberSuggestions = $popularMembers->take(3);
            }

            foreach ($memberSuggestions as $suggestion) {
                $suggestions[] = [
                    'text' => $suggestion,
                    'type' => 'member',
                    'icon' => 'user'
                ];
            }
        }

        return response()->json($suggestions);
    }

    /**
     * Get filter options for advanced search
     */
    public function filterOptions(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $type = $request->get('type', 'books');

        $options = [];

        if ($type === 'books') {
            // Categories
            $categories = Category::select('id', 'name')
                ->whereHas('books')
                ->orderBy('name')
                ->get()
                ->map(function ($category) {
                    return [
                        'value' => $category->id,
                        'label' => $category->name . ' (' . $category->books()->count() . ')',
                    ];
                });

            // Authors
            $authors = Book::select('author')
                ->groupBy('author')
                ->orderBy('author')
                ->limit(50)
                ->pluck('author')
                ->map(function ($author) {
                    return [
                        'value' => $author,
                        'label' => $author,
                    ];
                });

            // Publication years
            $years = Book::select('publication_year')
                ->whereNotNull('publication_year')
                ->groupBy('publication_year')
                ->orderBy('publication_year', 'desc')
                ->limit(20)
                ->pluck('publication_year')
                ->map(function ($year) {
                    return [
                        'value' => $year,
                        'label' => $year,
                    ];
                });

            $options = [
                'categories' => $categories,
                'authors' => $authors,
                'years' => $years,
                'statuses' => [
                    ['value' => 'available', 'label' => 'Tersedia'],
                    ['value' => 'borrowed', 'label' => 'Dipinjam'],
                    ['value' => 'maintenance', 'label' => 'Maintenance'],
                    ['value' => 'lost', 'label' => 'Hilang'],
                ]
            ];
        } elseif ($type === 'members') {
            $options = [
                'statuses' => [
                    ['value' => 'active', 'label' => 'Aktif'],
                    ['value' => 'inactive', 'label' => 'Tidak Aktif'],
                    ['value' => 'suspended', 'label' => 'Ditangguhkan'],
                ]
            ];
        }

        return response()->json($options);
    }

    /**
     * Advanced search with multiple filters
     */
    public function advanced(Request $request)
    {
        $type = $request->get('type', 'books');
        $search = $request->get('search', '');
        $filters = $request->get('filters', []);
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);

        if ($type === 'books') {
            $query = Book::with('category');

            // Apply search
            if (!empty($search)) {
                $query->search($search);
            }

            // Apply filters
            if (!empty($filters['category_id'])) {
                $query->where('category_id', $filters['category_id']);
            }

            if (!empty($filters['author'])) {
                $query->where('author', 'like', '%' . $filters['author'] . '%');
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['year_from'])) {
                $query->where('publication_year', '>=', $filters['year_from']);
            }

            if (!empty($filters['year_to'])) {
                $query->where('publication_year', '<=', $filters['year_to']);
            }

            $results = $query->paginate($perPage, ['*'], 'page', $page);

        } elseif ($type === 'members') {
            $query = Member::with('user');

            // Apply search
            if (!empty($search)) {
                $query->search($search);
            }

            // Apply filters
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['join_from'])) {
                $query->where('join_date', '>=', $filters['join_from']);
            }

            if (!empty($filters['join_to'])) {
                $query->where('join_date', '<=', $filters['join_to']);
            }

            $results = $query->paginate($perPage, ['*'], 'page', $page);
        }

        if ($request->ajax()) {
            return response()->json([
                'data' => $results->items(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                ]
            ]);
        }

        return view('pages.search.advanced', compact('results', 'type', 'search', 'filters'));
    }
}
