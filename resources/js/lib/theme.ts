export type ThemePreference = "light" | "dark" | "system";
export type LocalePreference = "en" | "id";

export function resolveIsDark(theme: ThemePreference): boolean {
  if (theme === "dark") return true;
  if (theme === "light") return false;
  if (typeof window !== "undefined" && typeof window.matchMedia === "function") {
    return window.matchMedia("(prefers-color-scheme: dark)").matches;
  }
  return false;
}

export function applyTheme(theme: ThemePreference): void {
  if (typeof document === "undefined") return;

  const isDark = resolveIsDark(theme);
  document.documentElement.classList.toggle("dark", isDark);

  try {
    localStorage.setItem("nova-theme", theme);
  } catch {
  }
}
