# Workflow Tim

> Tim 2 orang: **Software Engineer** (monitoring & review) + **Executor** (implementasi)

---

## Peran

| Role | Tanggung Jawab |
|------|----------------|
| **Software Engineer** | Review code, monitoring progress, keputusan teknis |
| **Executor** | Ambil task, implementasi, testing lokal, push ke GitHub, centang checkbox |

---

## Alur Kerja Harian

```
1. Buka docs/PROJECT_PLAN.md
2. Ambil task berikutnya yang belum dicentang (urut dari atas)
3. Kerjakan sampai deliverable terpenuhi
4. Test lokal
5. Commit & push ke GitHub
6. Centang checkbox di PROJECT_PLAN.md
7. Software Engineer review commit/PR
```

---

## Git — Push Biasa

Tidak perlu branch khusus per task. Cukup:

```
main  → branch utama, push & pull di sini
```

Alur sederhana:

1. `git pull` — ambil update terbaru
2. Kerjakan task
3. `git add .` → `git commit` → `git push`
4. Centang task di `PROJECT_PLAN.md`, commit & push lagi

Kalau mau review dulu sebelum merge, executor bisa buat **Pull Request ke `main`** — tapi tidak wajib pakai branch terpisah.

---

## Commit Message

Cukup jelas dan sebutkan Task ID:

```
feat: CRUD product dengan upload CDN (P1-022)
```

atau

```
docs: multi-tenant strategy (P0-001)
```

Prefix opsional: `feat`, `fix`, `docs`, `chore`

---

## Pull Request (Opsional)

Kalau pakai PR untuk review:

1. Push ke `main` atau buat PR ke `main`
2. Isi template PR (Task ID wajib diisi)
3. Screenshot wajib untuk task UI
4. Centang checkbox di `PROJECT_PLAN.md`

### Review oleh Software Engineer

- [ ] Kode sesuai arsitektur blueprint
- [ ] Tidak ada hardcoded secret
- [ ] Deliverable di PROJECT_PLAN terpenuhi
- [ ] Bisa di-test sesuai instruksi

---

## Centang Progress

Setiap task selesai, ubah di `docs/PROJECT_PLAN.md`:

```diff
- - [ ] P1-015 — CRUD Product
+ - [x] P1-015 — CRUD Product
```

Commit & push perubahan checkbox ini bareng task, atau commit terpisah.

Software Engineer pantau progress lewat:

- Checkbox di `PROJECT_PLAN.md`
- Commit history di GitHub

---

## Aturan Penting

1. **Satu task selesai = satu commit (atau beberapa commit) + centang checkbox**
2. **Jangan skip urutan Phase** — kerjakan dari atas ke bawah
3. **Jangan commit file sensitif** — `.env`, credentials, API key
4. **Selalu `git pull` dulu** sebelum mulai task baru
5. **Blocker?** Buat GitHub Issue, tag Software Engineer

---

## Milestone & Definition of Done

Task dianggap **selesai** jika:

- [ ] Deliverable di PROJECT_PLAN terpenuhi
- [ ] Checkbox dicentang di PROJECT_PLAN.md
- [ ] Sudah di-push ke GitHub
- [ ] Software Engineer sudah review (jika perlu)

---

## Kontak & Eskalasi

| Situasi | Action |
|---------|--------|
| Task tidak jelas | Tanya Software Engineer sebelum mulai |
| Stuck > 1 hari | Buat GitHub Issue + ping Software Engineer |
| Butuh keputusan arsitektur | Jangan asumsi — tanya dulu |
