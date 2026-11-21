import type { LocalePreference } from "./theme.js";

export interface ClientTranslations {
  eyebrow: string;
  greeting: string;
  subtitle: string;
  menu: {
    backups: string;
    backupSchedules: string;
    backupStorages: string;
    backupStorageProviders: string;
    profile: string;
    preferences: string;
    logout: string;
    signingOut: string;
  };
  profile: {
    title: string;
    description: string;
    memberSince: string;
    status: string;
    verified: string;
    unverified: string;
    fullName: string;
    fullNamePlaceholder: string;
    email: string;
    emailHint: string;
    changePasswordTitle: string;
    setPasswordTitle: string;
    changePasswordDescription: string;
    setPasswordDescription: string;
    currentPassword: string;
    currentPasswordPlaceholder: string;
    newPassword: string;
    newPasswordPlaceholder: string;
    confirmPassword: string;
    confirmPasswordPlaceholder: string;
    save: string;
    saving: string;
    saved: string;
  };
  preferences: {
    title: string;
    description: string;
    themeLabel: string;
    themeDescription: string;
    themes: {
      light: { label: string; description: string };
      dark: { label: string; description: string };
      system: { label: string; description: string };
    };
    languageLabel: string;
    languageDescription: string;
    languages: {
      en: { label: string; description: string };
      id: { label: string; description: string };
    };
    save: string;
    saving: string;
    saved: string;
  };
}

export const clientTranslations: Record<LocalePreference, ClientTranslations> = {
  en: {
    eyebrow: "Client Area",
    greeting: "Welcome back",
    subtitle: "Manage your account, preferences, and security settings.",
    menu: {
      backups: "Backups",
      backupSchedules: "Backup Schedules",
      backupStorages: "Backup Storage",
      backupStorageProviders: "Storage Providers",
      profile: "Profile",
      preferences: "Preferences",
      logout: "Logout",
      signingOut: "Signing out…",
    },
    profile: {
      title: "Profile",
      description: "Manage your account details and password settings.",
      memberSince: "Member since",
      status: "Status",
      verified: "Verified",
      unverified: "Unverified",
      fullName: "Full name",
      fullNamePlaceholder: "Your full name",
      email: "Email",
      emailHint: "Your email address is used for sign-in and verification and cannot be changed here.",
      changePasswordTitle: "Change password",
      setPasswordTitle: "Set a password",
      changePasswordDescription: "Leave blank if you do not want to change your current password.",
      setPasswordDescription: "You do not have a password set yet. Create one to enable password sign-in.",
      currentPassword: "Current password",
      currentPasswordPlaceholder: "Current password",
      newPassword: "New password",
      newPasswordPlaceholder: "Minimum 8 characters",
      confirmPassword: "Confirm password",
      confirmPasswordPlaceholder: "Repeat new password",
      save: "Save changes",
      saving: "Saving…",
      saved: "Profile updated successfully.",
    },
    preferences: {
      title: "Preferences",
      description: "Customize your interface theme and display language.",
      themeLabel: "Appearance",
      themeDescription: "Choose how the interface looks on your screen.",
      themes: {
        light: { label: "Light", description: "Bright surface with high-contrast text." },
        dark: { label: "Dark", description: "Low-glare surface for dark environments." },
        system: { label: "System", description: "Follow your operating system setting." },
      },
      languageLabel: "Language",
      languageDescription: "Select your preferred language for the application interface.",
      languages: {
        en: { label: "English", description: "Default interface language." },
        id: { label: "Bahasa Indonesia", description: "Tampilkan antarmuka dalam Bahasa Indonesia." },
      },
      save: "Save preferences",
      saving: "Saving…",
      saved: "Preferences saved successfully.",
    },
  },
  id: {
    eyebrow: "Area Klien",
    greeting: "Selamat datang kembali",
    subtitle: "Kelola akun, preferensi tampilan, dan pengaturan keamanan Anda.",
    menu: {
      backups: "Backup",
      backupSchedules: "Jadwal Backup",
      backupStorages: "Penyimpanan Backup",
      backupStorageProviders: "Penyedia Storage",
      profile: "Profil",
      preferences: "Preferensi",
      logout: "Keluar",
      signingOut: "Sedang keluar…",
    },
    profile: {
      title: "Profil",
      description: "Kelola detail akun dan pengaturan kata sandi Anda.",
      memberSince: "Bergabung sejak",
      status: "Status",
      verified: "Terverifikasi",
      unverified: "Belum terverifikasi",
      fullName: "Nama lengkap",
      fullNamePlaceholder: "Nama lengkap Anda",
      email: "Email",
      emailHint: "Alamat email digunakan untuk masuk dan verifikasi serta tidak dapat diubah di sini.",
      changePasswordTitle: "Ubah kata sandi",
      setPasswordTitle: "Buat kata sandi",
      changePasswordDescription: "Kosongkan jika tidak ingin mengubah kata sandi saat ini.",
      setPasswordDescription: "Anda belum memiliki kata sandi. Buat kata sandi agar dapat masuk dengan password.",
      currentPassword: "Kata sandi saat ini",
      currentPasswordPlaceholder: "Kata sandi saat ini",
      newPassword: "Kata sandi baru",
      newPasswordPlaceholder: "Minimal 8 karakter",
      confirmPassword: "Konfirmasi kata sandi",
      confirmPasswordPlaceholder: "Ulangi kata sandi baru",
      save: "Simpan perubahan",
      saving: "Menyimpan…",
      saved: "Profil berhasil diperbarui.",
    },
    preferences: {
      title: "Preferensi",
      description: "Sesuaikan tema tampilan dan bahasa antarmuka aplikasi.",
      themeLabel: "Tema Tampilan",
      themeDescription: "Pilih mode tampilan yang paling nyaman untuk Anda.",
      themes: {
        light: { label: "Terang", description: "Tampilan cerah dengan kontras tinggi." },
        dark: { label: "Gelap", description: "Tampilan gelap yang nyaman di malam hari." },
        system: { label: "Sistem", description: "Ikuti pengaturan tema perangkat Anda." },
      },
      languageLabel: "Bahasa",
      languageDescription: "Pilih bahasa yang digunakan pada antarmuka aplikasi.",
      languages: {
        en: { label: "English", description: "Gunakan antarmuka dalam Bahasa Inggris." },
        id: { label: "Bahasa Indonesia", description: "Bahasa antarmuka Indonesia." },
      },
      save: "Simpan preferensi",
      saving: "Menyimpan…",
      saved: "Preferensi berhasil disimpan.",
    },
  },
};

export function getClientTranslations(locale?: string | null): ClientTranslations {
  return locale === "id" ? clientTranslations.id : clientTranslations.en;
}
