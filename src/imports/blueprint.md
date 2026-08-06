# SaaS Instant Store
### Blueprint Project
> Multi-Tenant E-Commerce Platform (Laravel + Inertia + Vue + PostgreSQL)

---

# Overview

Membangun platform **SaaS Toko Online Instan** seperti Shopify, Tokoko, atau Sirclo Store, namun dengan model bisnis:

- Seller dapat membuat toko gratis.
- Free Plan menggunakan sistem saldo (escrow/platform balance).
- Withdraw dikenakan biaya Rp5.000.
- Premium Plan Rp99.000/bulan.
- Premium mendapatkan direct settlement ke rekening sendiri.
- Mendukung PKP & Non-PKP.
- Multi Tenant.
- Subdomain otomatis.
- AI Ready.

---

# Tech Stack

## Backend

- Laravel 12
- PHP 8.4
- PostgreSQL
- Redis
- Horizon
- Queue
- Laravel Scheduler
- Laravel Sanctum
- Laravel Reverb (Realtime)
- Laravel Scout (optional)
- Meilisearch (optional)

---

## Frontend

- Vue 3
- Inertia.js
- TypeScript
- Vite
- Tailwind CSS
- shadcn-vue
- Pinia
- VueUse
- ApexCharts

---

## Database

PostgreSQL

Schema:

- public
- audit
- logs

---

## Storage

Cloudflare R2

atau

S3 Compatible

---

## Image Optimization

spatie/image
Intervention Image

---

## Deployment

Docker

Nginx

Supervisor

Ubuntu 24.04

---

## Payment Gateway

Abstraction Layer

Support:

- Midtrans
- Xendit
- Duitku
- Tripay

---

## Domain

Cloudflare API

Auto create

```
tokokeren.platform.com
```

Premium

```
tokokeren.com
```

---

# Business Plan

## Free

- Unlimited Product
- Unlimited Order
- Platform Settlement
- Withdraw Fee Rp5.000
- Basic Analytics
- Basic Theme

---

## Premium

Rp99.000/bulan

- Direct Settlement
- Custom Domain
- Premium Theme
- AI Description
- SEO
- Advanced Analytics
- Email Notification
- WhatsApp Notification
- No Withdraw Fee

---

# Main Modules

---

## Authentication

- Register
- Login
- OTP
- Google Login
- Email Verification
- Forgot Password

---

## Tenant

- Create Store
- Update Store
- Store Theme
- Store Status
- Subdomain

---

## Product

- Category
- Brand
- Product
- Variant
- Stock
- SKU
- Barcode
- Weight
- Digital Product

---

## Inventory

- Stock In
- Stock Out
- Purchase
- Adjustment
- Warehouse

---

## Customer

- Customer
- Address
- Wishlist
- Review

---

## Order

Status

```
Pending

Paid

Processing

Packed

Shipped

Completed

Cancelled

Refund
```

---

## Checkout

- Voucher
- Discount
- Shipping
- Tax
- Notes
- Invoice

---

## Payment

- Payment Gateway
- Manual Transfer
- COD
- Virtual Account
- QRIS
- E-wallet

---

## Escrow

Untuk Free Plan

```
Customer

↓

Platform Balance

↓

Seller Withdraw

↓

Transfer Seller
```

---

## Withdraw

Status

```
Pending

Approved

Rejected

Transferred
```

Fee

```
Rp5.000
```

---

## Wallet

Seller Wallet

```
Balance

Pending Balance

Withdraw

History
```

---

## Shipping

Support

- RajaOngkir
- Biteship
- Shipper

---

## Tax Engine

Support

- PKP
- Non PKP

---

### Seller Profile

```
NPWP

NIK

Company

PKP Status

Tax Address
```

---

### Tax Calculation

Automatic

```
Subtotal

PPN

Shipping

Discount

Total
```

---

### Reports

- Monthly Tax
- Annual Tax
- CSV
- Excel

Future

- e-Faktur Export
- Coretax Export

---

# Subscription

Plan

Free

Premium

Future

Business

Enterprise

---

## Subscription Feature

- Upgrade
- Downgrade
- Expired
- Renewal

---

# Store CMS

Landing Page

- Hero
- Banner
- Category
- Featured Product
- Testimonial
- About
- Contact

---

# Theme

Theme Engine

- Modern
- Fashion
- Electronics
- Food
- Furniture

Future

Theme Marketplace

---

# AI Module

OpenAI

Gemini

Claude

Feature

- Product Description
- SEO
- Product Tags
- Product Title
- Marketing Caption
- Reply Customer
- FAQ Generator

---

# Analytics

Dashboard

```
Revenue

Orders

Visitors

Conversion

Products

Customers

Withdraw
```

Charts

- Daily
- Weekly
- Monthly

---

# Notification

Email

WhatsApp

Push Notification

Realtime

---

# Review

- Rating
- Review
- Photo Review

---

# Coupon

- Voucher
- Promo
- Cashback
- Flash Sale

---

# Marketing

- Broadcast
- Discount Campaign
- Bundle Product
- Affiliate
- Referral

---

# SEO

- Sitemap
- robots.txt
- Meta Tag
- OpenGraph
- JSON-LD

---

# Blog CMS

- Category
- Post
- Author
- Tag

---

# File Manager

- Image
- Video
- PDF

Cloud Storage

---

# Audit Log

Semua aktivitas dicatat

- Login
- Update Product
- Withdraw
- Payment
- Subscription

---

# Admin Dashboard

Platform Admin

Modules

- Users
- Sellers
- Subscription
- Withdraw
- Tax
- Orders
- Payment
- Reports
- CMS
- Theme
- Support

---

# Customer Support

Ticket

Live Chat

FAQ

Knowledge Base

---

# Database Design

Users

↓

Tenants

↓

Stores

↓

Products

↓

Variants

↓

Orders

↓

Payments

↓

Wallet

↓

Withdraw

↓

Subscription

↓

Invoices

↓

Tax

↓

Reports

---

# Folder Structure

```
app

├── Actions
├── DTO
├── Enums
├── Events
├── Exceptions
├── Helpers
├── Http
├── Jobs
├── Listeners
├── Mail
├── Models
├── Notifications
├── Observers
├── Policies
├── Repositories
├── Rules
├── Services
├── Traits
├── ValueObjects
```

---

# Inertia

```
resources/

vue/

├── Pages
├── Components
├── Layouts
├── Composables
├── Stores
├── Types
├── Utils
├── Plugins
├── Services
├── Assets
```

---

# Security

- CSRF
- XSS Protection
- SQL Injection Protection
- Rate Limiter
- Email Verification
- OTP
- 2FA
- Device Session

---

# Roadmap

## Phase 1 (MVP)

- Authentication
- Multi Tenant
- Product
- Checkout
- Payment
- Wallet
- Withdraw
- Subscription
- Dashboard

---

## Phase 2

- Shipping
- Voucher
- Analytics
- Theme
- Tax Engine
- Blog CMS

---

## Phase 3

- AI
- Marketplace Theme
- Affiliate
- Automation
- WhatsApp
- API Public

---

## Phase 4

- Mobile App
- POS
- ERP
- Accounting
- AI Agent
- Omnichannel