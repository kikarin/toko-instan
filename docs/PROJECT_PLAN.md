# Project Plan — SaaS Toko Instan

> Tracking progress tim. Executor centang `[x]` saat task selesai & push ke GitHub.
>
> **Tim:** Software Engineer (review) + Executor (implementasi)
> **Acuan:** [blueprint.md](./blueprint.md) | **Workflow:** [WORKFLOW.md](./WORKFLOW.md)

---

## Progress Overview


| Phase                     | Task    | Selesai    |
| ------------------------- | ------- | ---------- |
| Phase 0 — Foundation      | 17      | 9/17       |
| Phase 1 — MVP             | 42      | 16/42      |
| Phase 1.5 — Beta & Launch | 10      | 0/10       |
| Phase 2 — Growth          | 28      | 0/28       |
| Phase 3 — Scale           | 18      | 0/18       |
| Phase 4 — Enterprise      | 12      | 0/12       |
| **Total**                 | **127** | **25/127** |


> Update kolom "Selesai" manual saat milestone tercapai.

---



## Cara Pakai

1. Kerjakan task **dari atas ke bawah** dalam satu phase
2. Task dengan ⚠️ = ada dependency — selesaikan task dependency dulu
3. Selesai → commit → push ke GitHub → centang `[x]`

---



# Phase 0 — Foundation

**Tujuan:** Dokumen teknis, scaffold project, dev environment siap coding.
**Milestone:** Login + buat tenant + subdomain resolve + upload test ke R2/CDN.

---



## 0.1 Dokumen Arsitektur

- [x] **P0-001** — Multi-tenant strategy
  - **Deliverable:** `docs/architecture/multi-tenant.md` — strategi tenancy (single DB + tenant_id), subdomain routing, isolasi data, middleware

- [x] **P0-002** — Database schema
  - **Deliverable:** `docs/database/schema.md` — ERD + tabel inti (users, tenants, stores, products, orders, payments, wallets)

- [x] **P0-003** — Money flow
  - **Deliverable:** `docs/architecture/money-flow.md` — escrow, wallet ledger, withdraw fee Rp5.000, premium direct settlement

- [x] **P0-004** — CDN & media
  - **Deliverable:** `docs/architecture/cdn-media.md` — struktur bucket R2, naming convention, image variants (thumbnail/medium/large)

---



## 0.2 Project Scaffold

- [x] **P0-010** — Init Laravel + Inertia + Vue 3 + TypeScript
  - **Deliverable:** Project jalan, halaman welcome Inertia tampil, `npm run dev` + `php artisan serve` OK

- [x] **P0-011** — Setup Tailwind + shadcn-vue
  - **Deliverable:** Tailwind configured, komponen shadcn-vue (Button, Card, Input, Badge) bisa dipakai

- [ ] **P0-012** — Folder structure backend
  - **Deliverable:** Folder sesuai blueprint: `Actions`, `DTO`, `Enums`, `Services`, `Repositories`, dll.

- [ ] **P0-013** — Folder structure frontend
  - **Deliverable:** `resources/js/Pages`, `Components`, `Layouts`, `Composables`, `Stores`, `Types`

---



## 0.3 Dev Environment

- [x] **P0-021** — PostgreSQL schema setup
  - **Deliverable:** Migration schema `public`, `audit`, `logs`; koneksi DB OK

- [ ] **P0-022** — Redis + Horizon
  - **Deliverable:** Horizon jalan, dashboard `/horizon` accessible (dev); `QUEUE_CONNECTION=redis`

- [ ] **P0-023** — S3 / R2 storage config
  - **Deliverable:** Laravel filesystem disk `s3`/`r2` configured, upload test file ke Cloudflare R2 berhasil (URL CDN bisa dibuka)

---



## 0.4 Fondasi Aplikasi

- [x] **P0-030** — Migration tabel inti (users, tenants, stores) ⚠️ butuh P0-002
  - **Deliverable:** Migration + Model + relasi dasar; seeder 1 tenant dev

- [ ] **P0-031** — Tenant middleware & subdomain routing ⚠️ butuh P0-001, P0-030
  - **Deliverable:** Request ke `{tenant}.toko-instan.test` resolve tenant context; data terisolasi (1 storefront = 1 toko)

- [ ] **P0-032** — Layout dasar (auth + dashboard)
  - **Deliverable:** `AuthLayout`, `DashboardLayout`, `StorefrontLayout` — responsive skeleton

- [x] **P0-033** — CI pipeline dasar
  - **Deliverable:** GitHub Actions — lint (Pint/ESLint) + `php artisan test` + `npm run build`

- [ ] **P0-034** — Environment & config template
  - **Deliverable:** `.env.example` lengkap (pgsql, redis, R2, platform domain); tidak ada secret di repo

---



# Phase 1 — MVP

**Tujuan:** Seller bisa buat toko, jual produk, terima order, uang masuk wallet, withdraw.
**Milestone:** Vertical slice lengkap — register → toko → produk → checkout → bayar → saldo → withdraw.

---



## 1.1 Authentication

- [ ] **P1-001** — Register & login
  - **Deliverable:** Halaman register/login, session auth Laravel yang benar (Firebase bridge verified jika dipakai), redirect ke dashboard

- [ ] **P1-002** — Email verification
  - **Deliverable:** Email verifikasi saat register, halaman "verify email", middleware `verified`

- [ ] **P1-003** — Forgot & reset password
  - **Deliverable:** Flow lupa password via email, halaman reset password

- [ ] **P1-004** — OTP login (opsional MVP)
  - **Deliverable:** Login via OTP ke email/phone; bisa di-skip jika belum ada provider SMS

- [ ] **P1-005** — Google login (opsional MVP)
  - **Deliverable:** OAuth Google via Firebase — verify ID token server-side, jangan pakai UID sebagai password

---



## 1.2 Tenant & Store

- [ ] **P1-010** — Create store (onboarding) ⚠️ butuh P0-031
  - **Deliverable:** Form buat toko (nama, slug/subdomain), auto-create tenant + store

- [x] **P1-011** — Update store profile
  - **Deliverable:** Edit nama toko, deskripsi, logo (upload CDN), kontak

- [ ] **P1-012** — Store status (active/inactive)
  - **Deliverable:** Toggle status toko; storefront nonaktif tampil halaman "toko tutup"

- [ ] **P1-013** — Subdomain storefront
  - **Deliverable:** Halaman publik `{slug}.domain.com` menampilkan toko

---



## 1.3 Product & CDN

- [x] **P1-020** — Migration products, categories, brands ⚠️ butuh P0-030
  - **Deliverable:** Migration + Model + relasi; seeder sample

- [x] **P1-021** — CRUD category & brand
  - **Deliverable:** Dashboard CRUD kategori & brand

- [x] **P1-022** — CRUD product
  - **Deliverable:** Dashboard CRUD produk (nama, deskripsi, harga, status publish)

- [x] **P1-023** — Product variant & SKU
  - **Deliverable:** Variant (ukuran/warna), SKU unik per variant, stock per variant

- [ ] **P1-024** — Image upload → CDN ⚠️ butuh P0-023
  - **Deliverable:** Upload gambar → queue job → resize (thumbnail/medium/large) → push R2 → simpan CDN URL di DB

- [ ] **P1-025** — Storefront product catalog
  - **Deliverable:** Halaman list produk + detail produk di storefront **publik** (tanpa wajib login)

- [ ] **P1-026** — Digital product (basic)
  - **Deliverable:** Tipe produk digital, upload file ke CDN, delivery setelah paid

---



## 1.4 Customer & Cart

- [x] **P1-030** — Customer registration (buyer)
  - **Deliverable:** Buyer bisa register/login terpisah atau unified account

- [x] **P1-031** — Customer address
  - **Deliverable:** CRUD alamat pengiriman buyer

- [x] **P1-032** — Cart
  - **Deliverable:** Add to cart, update qty, remove; cart persist (session/DB)

---



## 1.5 Checkout & Order

- [ ] **P1-040** — Checkout page
  - **Deliverable:** Halaman checkout: **pilih alamat tersimpan**, ringkasan order, notes

- [x] **P1-041** — Create order
  - **Deliverable:** Order creation dengan status `Pending`, **order_items** + snapshot harga per item

- [x] **P1-042** — Order status flow
  - **Deliverable:** Status: Pending → Paid → Processing → Completed (+ Cancelled)

- [ ] **P1-043** — Order history (seller)
  - **Deliverable:** Dashboard seller — list & **detail order termasuk line items**

- [ ] **P1-044** — Order history (buyer)
  - **Deliverable:** Halaman buyer — riwayat pesanan **termasuk line items**

- [ ] **P1-045** — Invoice generation
  - **Deliverable:** Generate invoice PDF per order

---



## 1.6 Payment

- [ ] **P1-050** — Payment gateway abstraction
  - **Deliverable:** Interface `PaymentGateway`, config-driven provider

- [ ] **P1-051** — Midtrans integration ⚠️ butuh P1-050
  - **Deliverable:** VA, QRIS, e-wallet via Midtrans sandbox

- [ ] **P1-052** — Payment webhook + idempotency
  - **Deliverable:** Webhook handler, idempotency key, update order status ke `Paid`

- [ ] **P1-053** — Manual transfer (opsional MVP)
  - **Deliverable:** Buyer upload bukti transfer, seller konfirmasi manual

- [ ] **P1-054** — COD (opsional MVP)
  - **Deliverable:** Opsi bayar di tempat, order flow COD

---



## 1.7 Wallet & Escrow

- [x] **P1-060** — Wallet migration & model ⚠️ butuh P0-003
  - **Deliverable:** Tabel `wallets`, `wallet_transactions` — ledger immutable

- [x] **P1-061** — Escrow flow (Free Plan)
  - **Deliverable:** Dana order masuk `pending_balance` → `balance` setelah order completed

- [x] **P1-062** — Wallet dashboard (seller)
  - **Deliverable:** Tampilkan balance, pending balance, history transaksi

---



## 1.8 Withdraw

- [x] **P1-070** — Withdraw request
  - **Deliverable:** Form withdraw, validasi saldo, status: Pending → Approved → Transferred

- [x] **P1-071** — Withdraw fee Rp5.000 (Free Plan)
  - **Deliverable:** Potong fee Rp5.000 saat withdraw untuk Free Plan

- [x] **P1-072** — Admin withdraw approval
  - **Deliverable:** Platform admin approve/reject withdraw

---



## 1.9 Subscription

- [ ] **P1-080** — Subscription plans migration
  - **Deliverable:** Tabel plans (Free, Premium Rp99k), tenant_subscriptions

- [ ] **P1-081** — Upgrade ke Premium
  - **Deliverable:** Flow upgrade, payment subscription, aktivasi fitur premium

- [ ] **P1-082** — Direct settlement (Premium)
  - **Deliverable:** Premium seller — dana langsung ke rekening, skip escrow

- [ ] **P1-083** — Subscription renewal & expiry
  - **Deliverable:** Auto-check expiry, downgrade ke Free, notifikasi renewal

- [ ] **P1-084** — No withdraw fee (Premium)
  - **Deliverable:** Premium seller withdraw tanpa fee Rp5.000

---



## 1.10 Dashboard

- [ ] **P1-090** — Seller dashboard
  - **Deliverable:** Widget: revenue, orders hari ini, total produk, saldo wallet (data real, scoped ke toko seller)

- [ ] **P1-091** — Platform admin dashboard
  - **Deliverable:** Admin: total tenants, orders platform, **pending withdraw**

- [ ] **P1-092** — Admin modules dasar
  - **Deliverable:** CRUD users, **tenants**, lihat **orders**, approve withdraw

---



# Phase 1.5 — Beta & Launch

**Tujuan:** Staging, security check, UAT, deploy production.
**Milestone:** Platform live, seller beta bisa operasional.

---

- [ ] **P1.5-001** — Security hardening
  - **Deliverable:** Rate limiter, CSRF, XSS check, tenant isolation test

- [ ] **P1.5-002** — Staging environment
  - **Deliverable:** Deploy ke staging VPS, env staging, domain staging

- [ ] **P1.5-003** — Error monitoring (Sentry)
  - **Deliverable:** Sentry configured, error tracking aktif

- [ ] **P1.5-004** — Backup & recovery
  - **Deliverable:** Automated DB backup harian, dokumentasi restore

- [ ] **P1.5-005** — UAT dengan seller beta
  - **Deliverable:** 5–10 seller beta test end-to-end, bug list

- [ ] **P1.5-006** — Bugfix sprint (UAT)
  - **Deliverable:** Semua bug critical/high dari UAT fixed

- [ ] **P1.5-007** — Production deploy
  - **Deliverable:** Deploy ke VPS (Nginx, PHP, Supervisor), SSL, domain live

- [ ] **P1.5-008** — DNS & Cloudflare
  - **Deliverable:** DNS platform + wildcard subdomain `*.platform.com`

- [ ] **P1.5-009** — Seller documentation
  - **Deliverable:** `docs/guide/seller.md` — cara buat toko, produk, withdraw

- [ ] **P1.5-010** — Go-live checklist
  - **Deliverable:** `docs/launch-checklist.md` — semua item tercentang

---



# Phase 2 — Growth

**Tujuan:** Fitur lengkap untuk seller serius — shipping, promo, tax, theme, analytics.
**Milestone:** Toko terasa "matang", bukan sekadar MVP.

---



## 2.1 Shipping

- [ ] **P2-001** — RajaOngkir / Biteship integration
  - **Deliverable:** Cek ongkir by kota, pilih kurir di checkout

- [ ] **P2-002** — Shipping di checkout
  - **Deliverable:** Hitung ongkir real-time, tambah ke total order

- [ ] **P2-003** — Order status shipping
  - **Deliverable:** Status Packed → Shipped → Completed, input resi

---



## 2.2 Promo & Customer

- [ ] **P2-010** — Voucher & discount
  - **Deliverable:** CRUD voucher (% / nominal), apply di checkout

- [x] **P2-011** — Wishlist
  - **Deliverable:** Buyer simpan produk ke wishlist

- [ ] **P2-012** — Review & rating
  - **Deliverable:** Buyer review produk, rating 1–5, foto review (CDN)

---



## 2.3 Tax Engine

- [ ] **P2-020** — Seller tax profile
  - **Deliverable:** Form NPWP, NIK, PKP status, alamat faktur

- [ ] **P2-021** — PPN calculation
  - **Deliverable:** Auto hitung PPN di checkout untuk seller PKP

- [ ] **P2-022** — Tax reports
  - **Deliverable:** Laporan pajak bulanan/tahunan, export CSV/Excel

---



## 2.4 Theme & Store CMS

- [x] **P2-030** — Theme engine
  - **Deliverable:** Sistem swap theme per store, config theme (warna, font)

- [x] **P2-031** — Theme: Modern
  - **Deliverable:** 1 theme lengkap — homepage, katalog, detail, checkout

- [ ] **P2-032** — Theme: Fashion & Food
  - **Deliverable:** 2 theme tambahan

- [x] **P2-033** — Store CMS (landing page)
  - **Deliverable:** Edit hero, banner, featured product, testimonial, about, contact

---



## 2.5 Analytics & Notification

- [ ] **P2-040** — Analytics dashboard
  - **Deliverable:** Chart revenue, orders, visitors — daily/weekly/monthly

- [ ] **P2-041** — Email notification
  - **Deliverable:** Email: order baru, withdraw approved, subscription expiry

- [ ] **P2-042** — Realtime notification (Reverb)
  - **Deliverable:** Notifikasi realtime di dashboard seller

---



## 2.6 Inventory

- [x] **P2-050** — Stock in/out
  - **Deliverable:** Catat stock masuk/keluar, history per produk

- [x] **P2-051** — Stock adjustment
  - **Deliverable:** Adjustment manual + alasan

- [x] **P2-052** — Low stock alert
  - **Deliverable:** Notifikasi jika stock di bawah threshold

---



## 2.7 Blog CMS

- [ ] **P2-060** — Blog migration & CRUD
  - **Deliverable:** Category, post, author, tag — dashboard CMS

- [ ] **P2-061** — Blog storefront
  - **Deliverable:** Halaman blog di storefront, SEO meta

---



## 2.8 SEO

- [ ] **P2-070** — Sitemap & robots.txt
  - **Deliverable:** Auto-generate sitemap per toko, robots.txt

- [ ] **P2-071** — Meta tag & OpenGraph
  - **Deliverable:** Meta title/description per halaman, OG image

- [ ] **P2-072** — JSON-LD structured data
  - **Deliverable:** Product schema markup di halaman produk

---



# Phase 3 — Scale

**Tujuan:** Differentiation — AI, API, affiliate, automation.
**Milestone:** Platform siap scale, monetisasi premium kuat.

---



## 3.1 AI Module

- [ ] **P3-001** — AI service abstraction
  - **Deliverable:** Interface AI provider (OpenAI/Gemini), config-driven

- [ ] **P3-002** — Generate product description
  - **Deliverable:** Tombol "Generate" di form produk, output deskripsi AI

- [ ] **P3-003** — AI SEO & tags
  - **Deliverable:** Generate meta title, description, product tags

- [ ] **P3-004** — AI marketing caption
  - **Deliverable:** Generate caption promosi untuk social media

---



## 3.2 WhatsApp & Communication

- [ ] **P3-010** — WhatsApp notification (Premium)
  - **Deliverable:** Notif order & withdraw via WhatsApp API

- [ ] **P3-011** — AI reply customer
  - **Deliverable:** Auto-reply chat customer (basic)

---



## 3.3 Public API

- [ ] **P3-020** — API authentication (Sanctum)
  - **Deliverable:** API key per tenant, rate limiting

- [ ] **P3-021** — REST API (products, orders)
  - **Deliverable:** CRUD products & read orders via API

- [ ] **P3-022** — API documentation (Swagger)
  - **Deliverable:** Swagger UI di `/api/docs`

---



## 3.4 Affiliate & Automation

- [ ] **P3-030** — Referral system
  - **Deliverable:** Kode referral, tracking, komisi

- [ ] **P3-031** — FAQ generator (AI)
  - **Deliverable:** Generate FAQ otomatis dari data produk

---



## 3.5 Platform Maturity

- [ ] **P3-040** — Audit log
  - **Deliverable:** Semua aksi penting tercatat di schema `audit`

- [ ] **P3-041** — Customer support (ticket)
  - **Deliverable:** Ticket system: buat, balas, status

- [ ] **P3-042** — Knowledge base & FAQ
  - **Deliverable:** Halaman FAQ & knowledge base untuk seller

- [ ] **P3-043** — Payment gateway #2 (Xendit/Duitku)
  - **Deliverable:** Provider kedua via abstraction layer

- [ ] **P3-044** — Custom domain (Premium)
  - **Deliverable:** Premium seller pasang domain sendiri via Cloudflare API

---



# Phase 4 — Enterprise (Opsional)

**Tujuan:** Ekspansi ke mobile, POS, ERP. Mulai hanya jika sudah ada traction.
**Milestone:** v2.0 — platform enterprise-ready.

---

- [ ] **P4-001** — Mobile app (React Native / Flutter)
  - **Deliverable:** App seller: dashboard, orders, notifikasi

- [ ] **P4-002** — POS module
  - **Deliverable:** Point of sale untuk toko offline

- [ ] **P4-003** — Multi-warehouse
  - **Deliverable:** Inventory per warehouse, transfer antar gudang

- [ ] **P4-004** — ERP basic
  - **Deliverable:** Purchase order, supplier management

- [ ] **P4-005** — Accounting integration
  - **Deliverable:** Export jurnal ke format accounting software

- [ ] **P4-006** — AI agent (customer service)
  - **Deliverable:** Chatbot AI handle customer inquiry otomatis

- [ ] **P4-007** — Omnichannel
  - **Deliverable:** Sync produk ke marketplace (Tokopedia/Shopee)

- [ ] **P4-008** — Theme marketplace
  - **Deliverable:** Seller beli/jual theme

- [ ] **P4-009** — e-Faktur / Coretax export
  - **Deliverable:** Export faktur pajak format DJP

- [ ] **P4-010** — 2FA & device session
  - **Deliverable:** Two-factor auth, kelola sesi perangkat

- [ ] **P4-011** — Push notification
  - **Deliverable:** Web push notification untuk seller & buyer

- [ ] **P4-012** — Bundle & upsell product
  - **Deliverable:** Paket produk bundle, rekomendasi upsell

---



## Changelog


| Tanggal    | Update                                                                                                                                                    |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-08-06 | Initial project plan dibuat                                                                                                                               |
| 2026-08-06 | Hapus branch naming & Docker; simplify git workflow                                                                                                       |
| 2026-08-06 | Update progress: Foundation & Auth MVP tasks                                                                                                              |
| 2026-08-07 | Rampung: P0-032/034, P1-030/043/044/090/091/092; P0-031 dikembalikan ke pending                                                                           |
| 2026-08-07 | Phase 0 tuntas (17/17): P0-022 Redis+Horizon, P0-023 R2/S3 storage, P0-031 tenant middleware & subdomain routing, P0-033 CI pipeline                      |
| 2026-08-07 | Backbone uang: P1-042/060/061/062/070/071/072 — wallet ledger immutable (schema.md), escrow, withdraw fee & approval; payment ditunda (provider-agnostic) |
| 2026-08-08 | Audit SE: uncentang PARTIAL/FAIL; centang yang sudah jalan tapi belum dicentang (P1-011/021/023/031). Progress jujur: 23/127 |


