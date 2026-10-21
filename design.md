---
version: alpha
name: Minimal-dark-ui-design
description: A clean, high-contrast interface with a pure white background, utilizing dark, dark gray, light gray, and white tones for UI elements and buttons. Typography is driven exclusively by the Geist font family.

colors:
  # Base Background
  canvas: "#ffffff"
  
  # UI & Button Colors
  ink-dark: "#111111"       # Warna gelap utama untuk button & teks utama
  gray-dark: "#444444"      # Abu tua untuk komponen sekundar / text muted
  gray-light: "#f0f0f0"     # Abu muda untuk background chip, input, atau border
  canvas-pure: "#ffffff"    # Putih untuk elemen di dalam komponen gelap
  
  # Status & Borders
  border-soft: "#e5e5e5"
  border-dark: "#111111"

typography:
  hero-display:
    fontFamily: "Geist, system-ui, sans-serif"
    fontSize: 52px
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: -0.02em
  display-lg:
    fontFamily: "Geist, system-ui, sans-serif"
    fontSize: 38px
    fontWeight: 600
    lineHeight: 1.2
    letterSpacing: -0.01em
  title-md:
    fontFamily: "Geist, system-ui, sans-serif"
    fontSize: 24px
    fontWeight: 600
    lineHeight: 1.3
  body-strong:
    fontFamily: "Geist, system-ui, sans-serif"
    fontSize: 16px
    fontWeight: 600
    lineHeight: 1.5
  body:
    fontFamily: "Geist, system-ui, sans-serif"
    fontSize: 16px
    fontWeight: 400
    lineHeight: 1.5
  caption:
    fontFamily: "Geist, system-ui, sans-serif"
    fontSize: 14px
    fontWeight: 400
    lineHeight: 1.4
  button-text:
    fontFamily: "Geist, system-ui, sans-serif"
    fontSize: 15px
    fontWeight: 500
    lineHeight: 1.0

rounded:
  none: 0px
  sm: 4px
  md: 8px
  lg: 12px
  pill: 9999px

spacing:
  xs: 8px
  sm: 12px
  md: 16px
  lg: 24px
  xl: 40px
  xxl: 80px

components:
  global-nav:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink-dark}"
    typography: "{typography.caption}"
    borderBottom: "1px solid {colors.border-soft}"
    height: 56px

  button-dark-primary:
    backgroundColor: "{colors.ink-dark}"
    textColor: "{colors.canvas-pure}"
    typography: "{typography.button-text}"
    rounded: "{rounded.md}"
    padding: 12px 24px
  
  button-dark-primary-active:
    backgroundColor: "{colors.gray-dark}"
    textColor: "{colors.canvas-pure}"
    rounded: "{rounded.md}"

  button-gray-secondary:
    backgroundColor: "{colors.gray-light}"
    textColor: "{colors.ink-dark}"
    typography: "{typography.button-text}"
    rounded: "{rounded.md}"
    padding: 12px 24px

  button-white-outline:
    backgroundColor: "{colors.canvas-pure}"
    textColor: "{colors.ink-dark}"
    border: "1px solid {colors.border-dark}"
    typography: "{typography.button-text}"
    rounded: "{rounded.md}"
    padding: 12px 24px

  content-card:
    backgroundColor: "{colors.canvas-pure}"
    textColor: "{colors.ink-dark}"
    border: "1px solid {colors.border-soft}"
    rounded: "{rounded.lg}"
    padding: "{spacing.lg}"

  ui-chip:
    backgroundColor: "{colors.gray-light}"
    textColor: "{colors.gray-dark}"
    typography: "{typography.caption}"
    rounded: "{rounded.pill}"
    padding: 6px 12px

  form-input:
    backgroundColor: "{colors.canvas-pure}"
    textColor: "{colors.ink-dark}"
    border: "1px solid {colors.border-soft}"
    typography: "{typography.body}"
    rounded: "{rounded.md}"
    padding: 12px 16px
    height: 44px

  footer:
    backgroundColor: "{colors.gray-light}"
    textColor: "{colors.gray-dark}"
    typography: "{typography.caption}"
    padding: "{spacing.xl}"
---

## Overview

Sistem desain ini berfokus pada estetika minimalis, bersih, dan bertumpu pada layout kontras tinggi. Menggunakan warna latar belakang putih bersih (`{colors.canvas}`) sebagai fondasi utama, seluruh elemen UI, navigasi, dan tombol menggunakan spektrum warna gelap, abu-abu tua, abu-abu muda, dan putih untuk menciptakan hirarki visual yang jelas tanpa gangguan warna dekoratif lainnya [source: 1].

Tipografi sepenuhnya mengandalkan **Geist Font**, memberikan kesan modern, geometris, namun tetap sangat terbaca baik pada ukuran *display* besar maupun teks aplikasi yang padat [source: 1].

**Karakteristik Kunci:**
- **White Canvas Base:** Latar belakang utama halaman web/aplikasi selalu putih bersih (`#ffffff`).
- **Dark UI & Buttons:** Seluruh tombol utama dan kontrol UI menggunakan warna gelap (`{colors.ink-dark}`) untuk kontras instan [source: 1].
- **4-Tone Palette:** Hanya menggunakan warna gelap, abu tua, abu muda, dan putih [source: 1]. Tidak ada warna aksen tambahan seperti biru atau hijau.
- **Geist Typography:** Konsistensi penuh menggunakan font Geist di semua komponen [source: 1].
- **Zero Decorative Shadow:** Tidak ada bayangan (*drop-shadow*) pada komponen UI ataupun kartu untuk menjaga tampilan tetap *flat* dan modern. Kedalaman komponen dibentuk melalui perbedaan warna permukaan (`{colors.gray-light}`) atau garis pembatas (`{colors.border-soft}`).

## Colors

### Surface & Base
- **Pure White Canvas** (`{colors.canvas}` — #ffffff): Dasar utama dari seluruh halaman web atau aplikasi [source: 1].

### UI & Buttons
- **Ink Dark** (`{colors.ink-dark}` — #111111): Warna utama untuk elemen gelap [source: 1]. Digunakan pada *primary buttons*, teks judul utama, dan ikon navigasi aktif.
- **Gray Dark** (`{colors.gray-dark}` — #444444): Digunakan untuk teks sekunder, label pembantu, serta state aktif/tekan pada tombol utama [source: 1].
- **Gray Light** (`{colors.gray-light}` — #f0f0f0): Warna abu-abu muda untuk latar belakang komponen pasif seperti *chips*, kolom input, atau area kontainer terpisah (seperti footer) [source: 1].
- **Canvas Pure** (`{colors.canvas-pure}` — #ffffff): Warna putih bersih yang digunakan khusus untuk teks atau ikon di dalam komponen gelap (misal: teks di dalam *dark primary button*) [source: 1].

## Typography

### Font Family
- **Geist**: `Geist, system-ui, sans-serif` — Digunakan untuk seluruh tingkatan tipografi dari judul terbesar hingga teks legal terkecil [source: 1].

### Hierarchy

| Token | Size | Weight | Line Height | Letter Spacing | Use |
|---|---|---|---|---|---|
| `{typography.hero-display}` | 52px | 700 | 1.1 | -0.02em | Judul utama halaman landing / Hero section |
| `{typography.display-lg}` | 38px | 600 | 1.2 | -0.01em | Judul utama bagian / Section headers |
| `{typography.title-md}` | 24px | 600 | 1.3 | 0 | Judul di dalam kartu kompenen / Sub-section |
| `{typography.body-strong}` | 16px | 600 | 1.5 | 0 | Teks tebal untuk penekanan inline / Label form |
| `{typography.body}` | 16px | 400 | 1.5 | 0 | Teks paragraf utama standar |
| `{typography.caption}` | 14px | 400 | 1.4 | 0 | Teks sekunder, label input, isi footer, teks *chip* |
| `{typography.button-text}` | 15px | 500 | 1.0 | 0 | Seluruh teks di dalam komponen tombol |

## Shapes & Radii

- `{rounded.none}` (0px): Untuk elemen yang harus berbatasan langsung dengan tepi layar.
- `{rounded.sm}` (4px): Untuk elemen kecil seperti indikator atau sub-komponen.
- `{rounded.md}` (8px): Ukuran standar untuk tombol (`button-dark-primary`, `button-gray-secondary`) dan kolom input (`form-input`).
- `{rounded.lg}` (12px): Radius untuk komponen kontainer yang lebih besar seperti `{component.content-card}`.
- `{rounded.pill}` (9999px): Digunakan khusus untuk komponen `{component.ui-chip}` agar berbentuk kapsul penuh.

## Layout & Spacing

- **Sistem Grid:** Layout menggunakan kelipatan base unit 8px (`{spacing.xs}`: 8px, `{spacing.sm}`: 12px, `{spacing.md}`: 16px, `{spacing.lg}`: 24px).
- **Section Padding:** Menggunakan `{spacing.xxl}` (80px) untuk jarak vertikal antar section halaman agar memberikan ruang bernapas yang cukup pada latar belakang putih.
- **Card Padding:** Menggunakan `{spacing.lg}` (24px) untuk padding merata di dalam kontainer kartu.

## Do's and Don'ts

### Do
- Pastikan warna dasar latar belakang halaman selalu putih bersih (`#ffffff`) [source: 1].
- Gunakan font Geist di setiap elemen tanpa terkecuali [source: 1].
- Gunakan tombol utama berwarna gelap (`{colors.ink-dark}`) dengan teks putih (`{colors.canvas-pure}`) untuk aksi utama pengguna [source: 1].
- Manfaatkan `{colors.gray-light}` untuk memisahkan area konten sekunder (seperti form input atau area footer) dari background putih utama [source: 1].
- Gunakan interaksi `active` dengan mengecilkan skala tombol (`transform: scale(0.98)`) atau mengubah warna ke abu tua (`{colors.gray-dark}`) saat ditekan [source: 1].

### Don't
- Jangan memasukkan warna cerah atau warna aksen pihak ketiga (seperti biru, hijau, atau merah) ke dalam komponen UI.
- Jangan menambahkan efek bayangan (*box-shadow*) pada kartu atau tombol; struktur dan batasan komponen harus murni menggunakan warna (`{colors.gray-light}`) atau border tipis (`{colors.border-soft}`) [source: 1].
- Jangan menggunakan teks berwarna murni hitam (`#000000`) pada background putih; gunakan elemen gelap terukur dari sistem (`{colors.ink-dark}`) agar teks lebih nyaman dibaca [source: 1].
- Jangan mencampur radius kelengkungan secara acak di luar token skala `rounded` yang telah ditentukan.