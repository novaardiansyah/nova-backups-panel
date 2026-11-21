import type { LocalePreference } from "./theme.js";

export interface LandingFaqItem {
  uid: string;
  question: Record<LocalePreference, string>;
  answer: Record<LocalePreference, string>;
}

export interface LandingContent {
  nav: {
    showcase: string;
    workflow: string;
    capabilities: string;
    architecture: string;
    faq: string;
    signIn: string;
    getStarted: string;
    clientArea: string;
    language: string;
  };
  hero: {
    titleLead: string;
    titleAccent: string;
    description: string;
    primaryCta: string;
    primaryAuthCta: string;
    secondaryCta: string;
    platforms: string;
    stats: { value: string; label: string }[];
  };
  showcase: {
    eyebrow: string;
    title: string;
    description: string;
    items: {
      title: string;
      label: string;
      image: string;
      alt: string;
      description: string;
      bullets: string[];
    }[];
  };
  workflow: {
    eyebrow: string;
    title: string;
    description: string;
    quote: string;
    quoteSource: string;
    steps: { number: string; title: string; description: string }[];
  };
  capabilities: {
    eyebrow: string;
    title: string;
    description: string;
    patternTitle: string;
    patternDescription: string;
    patternFootnote: string;
    cloudTitle: string;
    cloudDescription: string;
    logsTitle: string;
    logsDescription: string;
    prefsTitle: string;
    prefsDescription: string;
  };
  architecture: {
    eyebrow: string;
    title: string;
    description: string;
    stats: { value: string; label: string }[];
    layers: { label: string; detail: string }[];
  };
  security: {
    eyebrow: string;
    title: string;
    description: string;
    badges: string[];
    items: { title: string; description: string }[];
  };
  faq: {
    eyebrow: string;
    title: string;
    description: string;
  };
  cta: {
    eyebrow: string;
    title: string;
    description: string;
    primaryCta: string;
    primaryAuthCta: string;
    secondaryCta: string;
  };
  footer: {
    description: string;
    productTitle: string;
    workspaceTitle: string;
    legalTitle: string;
    terms: string;
    privacy: string;
    copyright: string;
    license: string;
  };
}

export const landingContent: Record<LocalePreference, LandingContent> = {
  en: {
    nav: {
      showcase: "Showcase",
      workflow: "Workflow",
      capabilities: "Capabilities",
      architecture: "Architecture",
      faq: "FAQ",
      signIn: "Sign in",
      getStarted: "Get started",
      clientArea: "Client Area",
      language: "Language",
    },
    hero: {
      titleLead: "Automate every backup.",
      titleAccent: "Control it from one panel.",
      description:
        "Configure file and database backup schedules, storage targets, and cloud providers in one calm workspace, ready for your Backup Agent to execute and upload directly to S3 Storage or Google Drive.",
      primaryCta: "Get started",
      primaryAuthCta: "Open Client Area",
      secondaryCta: "Sign in to panel",
      platforms: "Files & MySQL Backups · Amazon S3 & Google Drive · Agent-Executed Schedules",
      stats: [
        { value: "Files & DB", label: "Supported Backup Targets" },
        { value: "S3 & Drive", label: "Cloud Upload Destinations" },
        { value: "UUID v7", label: "Public URL Identifiers" },
        { value: "EN & ID", label: "Bilingual & Theme Modes" },
      ],
    },
    showcase: {
      eyebrow: "Workspace Tour",
      title: "Every backup setting in one structured panel.",
      description:
        "Switch between schedules, execution logs, storage destinations, and provider templates without losing context.",
      items: [
        {
          title: "Backup Schedules",
          label: "Automated file & database schedules",
          image: "/demo/client-area-backup-schedules.webp",
          alt: "Nova Backups Panel Backup Schedules view",
          description:
            "Configure recurring file or database backup jobs with custom filename patterns, include/exclude filters, retention limits, and cloud sync destinations.",
          bullets: [
            "Files & MySQL database backup types",
            "Custom filename patterns & retention counts",
            "Read-only Detail modal with one-click UID copy",
          ],
        },
        {
          title: "Execution History",
          label: "Recorded runs, sizes & statuses",
          image: "/demo/client-area-backups.webp",
          alt: "Nova Backups Panel Backups history view",
          description:
            "Track every backup run executed by your agent with clear status indicators, archive file sizes, timestamps, and contextual cleanup actions.",
          bullets: [
            "Color-coded status & backup type badges",
            "Archive size & execution timestamp tracking",
            "Right-click context menu & confirmation dialogs",
          ],
        },
        {
          title: "Storage Destinations",
          label: "Local & cloud target registry",
          image: "/demo/client-area-backup-storage.webp",
          alt: "Nova Backups Panel Backup Storage view",
          description:
            "Organize concrete storage destinations linked to your providers, toggle active availability, and manage target credentials from one place.",
          bullets: [
            "Active and inactive destination toggles",
            "Provider-linked credential key pairs",
            "URL-persistent edit & delete workflows",
          ],
        },
        {
          title: "Storage Providers",
          label: "S3 Storage, Google Drive & custom templates",
          image: "/demo/client-area-backup-storage-providers.webp",
          alt: "Nova Backups Panel Storage Providers view",
          description:
            "Define reusable storage provider templates for Amazon S3, S3-compatible buckets, or Google Drive with structured default configuration keys.",
          bullets: [
            "Reusable templates for S3 & Google Drive",
            "Dynamic key-value configuration rows",
            "Consistent context-menu actions across tabs",
          ],
        },
        {
          title: "Preferences & Localization",
          label: "Light, dark & bilingual workspace",
          image: "/demo/client-area-preference.webp",
          alt: "Nova Backups Panel Preferences view",
          description:
            "Tailor the control plane to your workflow with persisted Light, Dark, and System themes alongside English and Indonesian language support.",
          bullets: [
            "Database-persisted theme preference",
            "English (en) & Indonesian (id) translations",
            "Instant header theme toggle synchronization",
          ],
        },
      ],
    },
    workflow: {
      eyebrow: "Agent-Ready Workflow",
      title: "Prepared in the panel. Executed by your agent.",
      description:
        "Separate scheduling and credential management from the worker runtime so servers stay predictable and easy to audit.",
      quote:
        "Define your backup rules once, and let the agent archive, prune, and upload to S3 Storage or Google Drive automatically.",
      quoteSource: "Control Plane Architecture",
      steps: [
        {
          number: "01",
          title: "Register Storage Providers",
          description:
            "Define provider templates such as Amazon S3, S3-compatible object storage, or Google Drive along with their required credential keys.",
        },
        {
          number: "02",
          title: "Configure Storage Destinations",
          description:
            "Bind providers into active storage targets with bucket names, endpoints, or folder keys ready for your agent to authenticate against.",
        },
        {
          number: "03",
          title: "Schedule File & Database Backups",
          description:
            "Set backup intervals, retention limits, filename patterns, and source paths or database names with full read-only detail inspection.",
        },
        {
          number: "04",
          title: "Agent Executes & Uploads to Cloud",
          description:
            "Your companion Backup Agent reads the configured schedules, creates compressed archives, prunes old runs, and syncs to S3 or Google Drive.",
        },
      ],
    },
    capabilities: {
      eyebrow: "Capabilities",
      title: "Built for clarity, retention control, and cloud sync.",
      description:
        "Every panel shares consistent context menus, URL-persistent state, and structured metadata ready for agent execution.",
      patternTitle: "Schedule Patterns & Retention",
      patternDescription:
        "Configure dynamic filename patterns, interval units from minutes to months, include/exclude rules, and max backup counts per schedule.",
      patternFootnote: "Files & MySQL · Retention Pruning",
      cloudTitle: "Cloud Sync Pipeline",
      cloudDescription:
        "Route archives to local retention paths, Amazon S3 buckets, or Google Drive folders.",
      logsTitle: "Execution Logs & Status Badges",
      logsDescription:
        "Inspect backup runs with consistent status and type badges, file sizes, schedule associations, and safe delete confirmations.",
      prefsTitle: "Themes & Localization",
      prefsDescription:
        "Database-persisted Light, Dark, and System themes with instant English and Indonesian language switching.",
    },
    architecture: {
      eyebrow: "Architecture",
      title: "A clean control plane built on Laravel & Svelte 5.",
      description:
        "Server-driven Inertia.js v3 routing meets reactive Svelte 5 runes, UUID v7 record identifiers, and URL-synchronized navigation.",
      stats: [
        { value: "Laravel 13", label: "Backend & Auth Control Plane" },
        { value: "Svelte 5", label: "Reactive Runes UI" },
        { value: "UUID v7", label: "Time-Ordered Public UIDs" },
        { value: "URL State", label: "Refresh-Safe Tabs & Dialogs" },
      ],
      layers: [
        { label: "Web Control Plane", detail: "Laravel 13 + Inertia.js v3 + Svelte 5 UI" },
        { label: "Schedule & Storage Registry", detail: "UUID v7 identifiers + structured JSON keys" },
        { label: "Backup Agent Runner", detail: "Executes file & database dumps on schedule" },
        { label: "Cloud & Local Targets", detail: "Amazon S3, Google Drive & local retention" },
      ],
    },
    security: {
      eyebrow: "Security & Safety",
      title: "Designed to prevent accidental changes.",
      description:
        "From OTP-verified accounts to read-only schedule inspection and explicit delete confirmations, every action is deliberate.",
      badges: ["OTP Sign-In", "Verified Accounts", "UUID v7 UIDs", "Safe Confirmations"],
      items: [
        {
          title: "Password & OTP Authentication",
          description:
            "Sign in with your password or a 6-digit email OTP, backed by verified email checks and rate-limited resend protection.",
        },
        {
          title: "UUID v7 Public Identifiers",
          description:
            "Every table generates a time-ordered UUID v7 uid for URLs and UI actions while keeping numeric primary keys internal.",
        },
        {
          title: "Read-Only Schedule Inspection",
          description:
            "Review complete schedule configurations in a dedicated Detail modal with one-click UID copy to prevent accidental edits.",
        },
        {
          title: "Explicit Destructive Safeguards",
          description:
            "Delete actions across providers, storages, schedules, and backups require explicit confirmation modals before execution.",
        },
      ],
    },
    faq: {
      eyebrow: "FAQ",
      title: "Common questions",
      description: "How Nova Backups Panel prepares schedules and storage targets for your Backup Agent.",
    },
    cta: {
      eyebrow: "Get Started",
      title: "Ready to organize your backup schedules?",
      description:
        "Set up your storage providers, configure destinations for S3 Storage or Google Drive, and keep every backup run visible.",
      primaryCta: "Create an account",
      primaryAuthCta: "Go to Client Area",
      secondaryCta: "Sign in",
    },
    footer: {
      description:
        "Centralized backup schedule and cloud storage control plane for automated file and database backups.",
      productTitle: "Product",
      workspaceTitle: "Workspace",
      legalTitle: "Legal & Connect",
      terms: "Terms of Service",
      privacy: "Privacy Policy",
      copyright: "© 2026 Nova Backups Panel. All rights reserved.",
      license: "MIT License",
    },
  },
  id: {
    nav: {
      showcase: "Tampilan",
      workflow: "Alur Kerja",
      capabilities: "Kemampuan",
      architecture: "Arsitektur",
      faq: "FAQ",
      signIn: "Masuk",
      getStarted: "Mulai",
      clientArea: "Client Area",
      language: "Bahasa",
    },
    hero: {
      titleLead: "Otomatiskan setiap backup.",
      titleAccent: "Kelola dari satu panel.",
      description:
        "Atur jadwal backup file dan database, destinasi penyimpanan, serta cloud provider dalam satu workspace yang rapi, siap dijalankan oleh Backup Agent dan diunggah langsung ke S3 Storage atau Google Drive.",
      primaryCta: "Mulai sekarang",
      primaryAuthCta: "Buka Client Area",
      secondaryCta: "Masuk ke panel",
      platforms: "Backup File & MySQL · Amazon S3 & Google Drive · Dieksekusi Agent",
      stats: [
        { value: "File & DB", label: "Target Backup Didukung" },
        { value: "S3 & Drive", label: "Destinasi Upload Cloud" },
        { value: "UUID v7", label: "Identifier URL Publik" },
        { value: "EN & ID", label: "Mode Bahasa & Tema" },
      ],
    },
    showcase: {
      eyebrow: "Tur Workspace",
      title: "Semua pengaturan backup dalam satu panel terstruktur.",
      description:
        "Berpindah antar jadwal, riwayat eksekusi, destinasi penyimpanan, dan template provider tanpa kehilangan konteks.",
      items: [
        {
          title: "Jadwal Backup",
          label: "Jadwal otomatis file & database",
          image: "/demo/client-area-backup-schedules.webp",
          alt: "Tampilan Jadwal Backup Nova Backups Panel",
          description:
            "Atur backup file atau database berkala dengan pola nama file kustom, filter include/exclude, batas retensi, dan sinkronisasi cloud.",
          bullets: [
            "Tipe backup file & database MySQL",
            "Pola nama file & batas jumlah retensi",
            "Dialog Detail read-only dengan salin UID sekali klik",
          ],
        },
        {
          title: "Riwayat Eksekusi",
          label: "Catatan eksekusi, ukuran & status",
          image: "/demo/client-area-backups.webp",
          alt: "Tampilan Riwayat Backup Nova Backups Panel",
          description:
            "Pantau setiap eksekusi backup dari agent Anda dengan indikator status yang jelas, ukuran arsip, waktu eksekusi, dan aksi pembersihan.",
          bullets: [
            "Badge status & tipe backup berwarna",
            "Pelacakan ukuran arsip & waktu eksekusi",
            "Menu klik-kanan & dialog konfirmasi aman",
          ],
        },
        {
          title: "Destinasi Penyimpanan",
          label: "Registri target lokal & cloud",
          image: "/demo/client-area-backup-storage.webp",
          alt: "Tampilan Backup Storage Nova Backups Panel",
          description:
            "Kelola destinasi penyimpanan yang terhubung ke provider Anda, atur status aktif/nonaktif, dan simpan kredensial target di satu tempat.",
          bullets: [
            "Toggle status destinasi aktif dan nonaktif",
            "Pasangan key kredensial terhubung provider",
            "Alur edit & hapus persisten di URL",
          ],
        },
        {
          title: "Storage Provider",
          label: "S3 Storage, Google Drive & template kustom",
          image: "/demo/client-area-backup-storage-providers.webp",
          alt: "Tampilan Storage Providers Nova Backups Panel",
          description:
            "Buat template provider penyimpanan yang dapat digunakan ulang untuk Amazon S3, storage kompatibel S3, atau Google Drive dengan key konfigurasi terstruktur.",
          bullets: [
            "Template ulang pakai untuk S3 & Google Drive",
            "Baris konfigurasi key-value dinamis",
            "Menu aksi kontekstual konsisten di tiap tab",
          ],
        },
        {
          title: "Preferensi & Bahasa",
          label: "Workspace terang, gelap & dwibahasa",
          image: "/demo/client-area-preference.webp",
          alt: "Tampilan Preferensi Nova Backups Panel",
          description:
            "Sesuaikan tampilan panel dengan tema Terang, Gelap, dan Sistem yang tersimpan, serta dukungan bahasa Inggris dan Indonesia.",
          bullets: [
            "Preferensi tema tersimpan di database",
            "Dukungan bahasa Inggris (en) & Indonesia (id)",
            "Sinkronisasi instan dari tombol tema di header",
          ],
        },
      ],
    },
    workflow: {
      eyebrow: "Alur Kerja Agent",
      title: "Disiapkan di panel. Dijalankan oleh agent Anda.",
      description:
        "Pisahkan penjadwalan dan manajemen kredensial dari runtime worker agar server tetap terprediksi dan mudah diaudit.",
      quote:
        "Atur aturan backup sekali, dan biarkan agent mengarsipkan, memangkas retensi, serta mengunggah ke S3 Storage atau Google Drive secara otomatis.",
      quoteSource: "Arsitektur Control Plane",
      steps: [
        {
          number: "01",
          title: "Daftarkan Storage Provider",
          description:
            "Buat template provider seperti Amazon S3, object storage kompatibel S3, atau Google Drive beserta key kredensial yang dibutuhkan.",
        },
        {
          number: "02",
          title: "Konfigurasi Destinasi Penyimpanan",
          description:
            "Hubungkan provider menjadi target penyimpanan aktif dengan nama bucket, endpoint, atau folder key untuk autentikasi agent.",
        },
        {
          number: "03",
          title: "Jadwalkan Backup File & Database",
          description:
            "Tentukan interval backup, batas retensi, pola nama file, dan path sumber atau nama database dengan inspeksi detail read-only.",
        },
        {
          number: "04",
          title: "Agent Menjalankan & Mengunggah ke Cloud",
          description:
            "Backup Agent membaca jadwal yang tersimpan, membuat arsip terkompresi, memangkas arsip lama, dan menyinkronkan ke S3 atau Google Drive.",
        },
      ],
    },
    capabilities: {
      eyebrow: "Kemampuan",
      title: "Dirancang untuk kejelasan, kontrol retensi, dan sinkronisasi cloud.",
      description:
        "Setiap panel memiliki menu kontekstual yang konsisten, state URL persisten, dan metadata terstruktur untuk eksekusi agent.",
      patternTitle: "Pola Jadwal & Retensi",
      patternDescription:
        "Atur pola nama file dinamis, satuan interval dari menit hingga bulan, aturan include/exclude, dan jumlah maksimum arsip per jadwal.",
      patternFootnote: "File & MySQL · Pemangkasan Retensi",
      cloudTitle: "Alur Sinkronisasi Cloud",
      cloudDescription:
        "Arahkan arsip ke path retensi lokal, bucket Amazon S3, atau folder Google Drive.",
      logsTitle: "Log Eksekusi & Badge Status",
      logsDescription:
        "Periksa riwayat backup dengan badge status dan tipe yang konsisten, ukuran file, relasi jadwal, dan konfirmasi hapus yang aman.",
      prefsTitle: "Tema & Bahasa",
      prefsDescription:
        "Tema Terang, Gelap, dan Sistem yang tersimpan di database dengan pergantian bahasa Inggris dan Indonesia secara instan.",
    },
    architecture: {
      eyebrow: "Arsitektur",
      title: "Control plane yang bersih di atas Laravel & Svelte 5.",
      description:
        "Routing Inertia.js v3 berbasis server berpadu dengan Svelte 5 runes yang reaktif, identifier UUID v7, dan navigasi tersinkronisasi URL.",
      stats: [
        { value: "Laravel 13", label: "Backend & Control Plane Auth" },
        { value: "Svelte 5", label: "UI Reaktif dengan Runes" },
        { value: "UUID v7", label: "UID Publik Terurut Waktu" },
        { value: "State URL", label: "Tab & Dialog Tahan Refresh" },
      ],
      layers: [
        { label: "Web Control Plane", detail: "Laravel 13 + Inertia.js v3 + UI Svelte 5" },
        { label: "Registri Jadwal & Storage", detail: "Identifier UUID v7 + key JSON terstruktur" },
        { label: "Backup Agent Runner", detail: "Menjalankan dump file & database sesuai jadwal" },
        { label: "Target Cloud & Lokal", detail: "Amazon S3, Google Drive & retensi lokal" },
      ],
    },
    security: {
      eyebrow: "Keamanan & Keselamatan",
      title: "Dirancang untuk mencegah perubahan tidak sengaja.",
      description:
        "Mulai dari akun terverifikasi OTP hingga inspeksi jadwal read-only dan konfirmasi hapus eksplisit.",
      badges: ["Login OTP", "Akun Terverifikasi", "UID UUID v7", "Konfirmasi Aman"],
      items: [
        {
          title: "Autentikasi Password & OTP",
          description:
            "Masuk menggunakan password atau kode OTP email 6 digit dengan verifikasi email dan pembatasan kirim ulang.",
        },
        {
          title: "Identifier Publik UUID v7",
          description:
            "Setiap tabel menghasilkan uid UUID v7 terurut waktu untuk URL dan aksi UI sambil menjaga primary key numerik tetap internal.",
        },
        {
          title: "Inspeksi Jadwal Read-Only",
          description:
            "Tinjau konfigurasi jadwal lengkap di dialog Detail khusus dengan salin UID sekali klik agar terhindar dari salah edit.",
        },
        {
          title: "Pengaman Aksi Hapus Eksplisit",
          description:
            "Penghapusan pada provider, storage, jadwal, dan backup memerlukan konfirmasi eksplisit sebelum dijalankan.",
        },
      ],
    },
    faq: {
      eyebrow: "FAQ",
      title: "Pertanyaan umum",
      description: "Bagaimana Nova Backups Panel menyiapkan jadwal dan target penyimpanan untuk Backup Agent Anda.",
    },
    cta: {
      eyebrow: "Mulai",
      title: "Siap merapikan jadwal backup Anda?",
      description:
        "Siapkan storage provider, atur destinasi untuk S3 Storage atau Google Drive, dan pantau setiap eksekusi backup.",
      primaryCta: "Buat akun",
      primaryAuthCta: "Buka Client Area",
      secondaryCta: "Masuk",
    },
    footer: {
      description:
        "Control plane terpusat untuk jadwal backup otomatis file dan database serta penyimpanan cloud.",
      productTitle: "Produk",
      workspaceTitle: "Workspace",
      legalTitle: "Legal & Tautan",
      terms: "Syarat & Ketentuan",
      privacy: "Kebijakan Privasi",
      copyright: "© 2026 Nova Backups Panel. Seluruh hak cipta dilindungi.",
      license: "Lisensi MIT",
    },
  },
};
