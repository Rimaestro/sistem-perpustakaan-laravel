# Database ERD Documentation
## Sistem Perpustakaan SMA Negeri 1 Sampang

### Overview
Database ini dirancang untuk mendukung sistem manajemen perpustakaan dengan fitur:
- Manajemen buku dan kategori
- Manajemen anggota perpustakaan
- Transaksi peminjaman dan pengembalian
- Sistem authentication dengan role-based access
- Tracking denda dan status peminjaman

### Tables Structure

#### 1. USERS
**Purpose**: Authentication dan authorization sistem
```sql
- id (bigint, PK, auto_increment)
- name (varchar 255, not null)
- email (varchar 255, unique, not null)
- email_verified_at (timestamp, nullable)
- password (varchar 255, not null)
- role (enum: 'admin', 'staff', 'member', default: 'member')
- remember_token (varchar 100, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

#### 2. CATEGORIES
**Purpose**: Kategorisasi buku perpustakaan
```sql
- id (bigint, PK, auto_increment)
- name (varchar 255, not null)
- description (text, nullable)
- slug (varchar 255, unique, not null)
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable) -- Soft delete
```

#### 3. BOOKS
**Purpose**: Koleksi buku perpustakaan
```sql
- id (bigint, PK, auto_increment)
- title (varchar 255, not null)
- author (varchar 255, not null)
- isbn (varchar 20, unique, nullable)
- category_id (bigint, FK to categories.id)
- status (enum: 'available', 'borrowed', 'maintenance', 'lost', default: 'available')
- barcode (varchar 255, unique, nullable)
- publication_year (year, nullable)
- description (text, nullable)
- quantity (int, default: 1)
- available_quantity (int, default: 1)
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable) -- Soft delete
```

#### 4. MEMBERS
**Purpose**: Data anggota perpustakaan
```sql
- id (bigint, PK, auto_increment)
- member_id (varchar 20, unique, not null) -- Generated ID like "M2025001"
- name (varchar 255, not null)
- email (varchar 255, unique, not null)
- phone (varchar 20, nullable)
- address (text, nullable)
- join_date (date, not null)
- status (enum: 'active', 'inactive', 'suspended', default: 'active')
- card_number (varchar 50, unique, nullable)
- user_id (bigint, FK to users.id, nullable) -- Optional link to user account
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable) -- Soft delete
```

#### 5. LOANS
**Purpose**: Transaksi peminjaman buku
```sql
- id (bigint, PK, auto_increment)
- book_id (bigint, FK to books.id, not null)
- member_id (bigint, FK to members.id, not null)
- loan_date (date, not null)
- due_date (date, not null)
- return_date (date, nullable)
- fine_amount (decimal 8,2, default: 0.00)
- status (enum: 'active', 'returned', 'overdue', default: 'active')
- notes (text, nullable)
- processed_by (bigint, FK to users.id, not null) -- Staff/Admin yang memproses
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable) -- Soft delete
```

### Relationships

1. **Categories → Books** (One to Many)
   - Satu kategori dapat memiliki banyak buku
   - Foreign key: books.category_id → categories.id

2. **Books → Loans** (One to Many)
   - Satu buku dapat dipinjam berkali-kali (history)
   - Foreign key: loans.book_id → books.id

3. **Members → Loans** (One to Many)
   - Satu member dapat meminjam banyak buku
   - Foreign key: loans.member_id → members.id

4. **Users → Members** (One to One, Optional)
   - User account dapat terhubung dengan member
   - Foreign key: members.user_id → users.id (nullable)

5. **Users → Loans** (One to Many)
   - User (staff/admin) yang memproses transaksi
   - Foreign key: loans.processed_by → users.id

### Indexes
```sql
-- Primary Keys (auto-created)
-- Unique constraints (auto-indexed)
-- Additional indexes for performance:
CREATE INDEX idx_books_category_id ON books(category_id);
CREATE INDEX idx_books_status ON books(status);
CREATE INDEX idx_loans_book_id ON loans(book_id);
CREATE INDEX idx_loans_member_id ON loans(member_id);
CREATE INDEX idx_loans_status ON loans(status);
CREATE INDEX idx_loans_due_date ON loans(due_date);
CREATE INDEX idx_members_status ON members(status);
```

### Business Rules

1. **Book Availability**
   - available_quantity harus <= quantity
   - Status 'borrowed' ketika available_quantity = 0
   - Status 'available' ketika available_quantity > 0

2. **Loan Rules**
   - Member hanya bisa meminjam jika status = 'active'
   - Buku hanya bisa dipinjam jika status = 'available'
   - Due date default: loan_date + 7 hari
   - Fine calculation: (current_date - due_date) * Rp 1000/hari

3. **Member ID Generation**
   - Format: M + YYYY + sequential number (M2025001, M2025002, etc.)
   - Auto-generated saat create member baru

4. **Soft Deletes**
   - Categories, Books, Members, Loans menggunakan soft delete
   - Data tidak benar-benar dihapus untuk audit trail
