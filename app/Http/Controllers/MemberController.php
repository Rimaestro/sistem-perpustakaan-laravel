<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\Member;
use App\Models\User;

class MemberController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('role:admin,staff', except: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Member::with('user');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by join date range
        if ($request->filled('join_from')) {
            $query->where('join_date', '>=', $request->join_from);
        }
        if ($request->filled('join_to')) {
            $query->where('join_date', '<=', $request->join_to);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $members = $query->paginate(10)->withQueryString();

        return view('pages.members.index', compact('members'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.members.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'join_date' => 'required|date',
            'status' => 'required|in:active,inactive,suspended',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'name.required' => 'Nama anggota wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'join_date.required' => 'Tanggal bergabung wajib diisi.',
            'join_date.date' => 'Format tanggal tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'user_id.exists' => 'User tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $member = Member::create($request->all());

            return redirect()->route('members.index')
                ->with('success', 'Anggota berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member)
    {
        $member->load(['user', 'loans.book', 'loans.processedBy']);
        return view('pages.members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member)
    {
        return view('pages.members.edit', compact('member'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'join_date' => 'required|date',
            'status' => 'required|in:active,inactive,suspended',
            'user_id' => 'nullable|exists:users,id',
        ], [
            'name.required' => 'Nama anggota wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'join_date.required' => 'Tanggal bergabung wajib diisi.',
            'join_date.date' => 'Format tanggal tidak valid.',
            'status.required' => 'Status wajib dipilih.',
            'status.in' => 'Status tidak valid.',
            'user_id.exists' => 'User tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $member->update($request->all());

            return redirect()->route('members.index')
                ->with('success', 'Data anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member)
    {
        try {
            // Check if member has active loans
            if ($member->activeLoans()->exists()) {
                return redirect()->back()
                    ->with('error', 'Tidak dapat menghapus anggota yang masih memiliki pinjaman aktif.');
            }

            $member->delete();

            return redirect()->route('members.index')
                ->with('success', 'Anggota berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Generate member card data
     */
    public function card(Member $member)
    {
        $cardData = $member->getCardData();
        return view('pages.members.card', compact('member', 'cardData'));
    }

    /**
     * Get member loan history
     */
    public function loanHistory(Member $member)
    {
        $loans = $member->loans()->with(['book', 'processedBy'])
                        ->orderBy('loan_date', 'desc')
                        ->paginate(10);

        return view('pages.members.loan-history', compact('member', 'loans'));
    }

    /**
     * API: Search members for autocomplete
     */
    public function search(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        $query = $request->get('q', '');
        $limit = $request->get('limit', 10);

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $members = Member::search($query)
                        ->active()
                        ->limit($limit)
                        ->get(['id', 'member_id', 'name', 'email', 'phone'])
                        ->map(function ($member) {
                            return [
                                'id' => $member->id,
                                'member_id' => $member->member_id,
                                'name' => $member->name,
                                'email' => $member->email,
                                'phone' => $member->phone,
                                'display' => $member->name . ' (' . $member->member_id . ')',
                            ];
                        });

        return response()->json($members);
    }

    /**
     * API: Get member statistics
     */
    public function statistics()
    {
        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::where('status', 'active')->count(),
            'inactive_members' => Member::where('status', 'inactive')->count(),
            'suspended_members' => Member::where('status', 'suspended')->count(),
            'members_with_loans' => Member::whereHas('activeLoans')->count(),
        ];

        return response()->json($stats);
    }
}
