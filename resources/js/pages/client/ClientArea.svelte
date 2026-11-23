<script lang="ts">
  import { router } from "@inertiajs/svelte";
  import ThemeToggle from "@/components/auth/theme-toggle.svelte";
  import { Toast } from "@/components/ui/toast/index.js";
  import { getClientTranslations } from "@/lib/i18n.js";
  import { applyTheme } from "@/lib/theme.js";
  import { getQueryParams, updateQueryParams } from "@/lib/url-state.js";
  import OnboardingDialog from "./OnboardingDialog.svelte";
  import PreferencesPanel from "./PreferencesPanel.svelte";
  import ProfilePanel, { type ClientUser } from "./ProfilePanel.svelte";

  type ClientSection = "profile" | "preferences";

  interface ClientMenuItem {
    id: ClientSection;
    label: string;
  }

  interface Props {
    user: ClientUser;
    showOnboarding?: boolean;
    onboardingCompleted?: boolean;
    status?: string | null;
  }

  let {
    user,
    showOnboarding = false,
    onboardingCompleted = false,
    status = null,
  }: Props = $props();

  const copy = $derived(getClientTranslations(user.locale));

  const menu = $derived<ClientMenuItem[]>([
    { id: "profile", label: copy.menu.profile },
    { id: "preferences", label: copy.menu.preferences },
  ]);

  const initials = $derived(
    user.name
      .split(" ")
      .filter((part) => part !== "")
      .slice(0, 2)
      .map((part) => part.charAt(0).toUpperCase())
      .join("") || "U",
  );

  const tabToSection: Record<string, ClientSection> = {
    profile: "profile",
    preferences: "preferences",
  };

  const sectionToTab: Record<ClientSection, string> = {
    profile: "profile",
    preferences: "preferences",
  };

  function getInitialSection(): ClientSection {
    const tab = getQueryParams().get("tab");
    if (tab && tab in tabToSection) {
      return tabToSection[tab];
    }
    return "profile";
  }

  let active = $state<ClientSection>(getInitialSection());
  let signingOut = $state(false);
  let onboardingOpen = $state(false);
  let toastOpen = $state(false);
  let toastMessage = $state("");
  let homeToastOpen = $state(false);

  function selectSection(section: ClientSection) {
    if (active === section) return;
    active = section;
    updateQueryParams({
      tab: sectionToTab[section],
      action: null,
      id: null,
    });
  }

  $effect(() => {
    applyTheme(user.theme);
  });

  $effect(() => {
    if (status) {
      toastMessage = status;
      toastOpen = true;
    }
  });

  function showStatusToast(message: string) {
    toastMessage = message;
    toastOpen = true;
  }

  $effect(() => {
    if (showOnboarding || onboardingCompleted) {
      onboardingOpen = true;
    }
  });

  function handleBackToHome() {
    router.visit("/");
  }

  function handleLogout() {
    if (signingOut) return;
    signingOut = true;
    router.post("/logout", {}, {
      onFinish: () => {
        signingOut = false;
      },
    });
  }
</script>

<svelte:head>
  <title>Client Area - Nova Backups</title>
</svelte:head>

<div class="min-h-screen bg-page text-ink">
  <section class="mx-auto w-full max-w-7xl px-5 py-10 sm:px-6 lg:px-8 lg:py-14">
    <header class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex min-w-0 flex-1 items-center gap-4">
        <span
          class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-line bg-page-elevated text-sm font-semibold text-accent"
        >
          {initials}
        </span>
        <div class="min-w-0">
          <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
            {copy.eyebrow}
          </p>
          <h1 class="mt-1 break-words [overflow-wrap:anywhere] text-2xl font-semibold tracking-tight text-ink sm:text-3xl">
            {copy.greeting}, {user.name}
          </h1>
          <p class="mt-1 text-sm leading-6 text-muted">
            {copy.subtitle}
          </p>
        </div>
      </div>

      <ThemeToggle locale={user.locale} persist={true} onstatus={showStatusToast} />
    </header>

    <div class="mt-8 grid grid-cols-[minmax(0,1fr)] gap-6 lg:grid-cols-[240px_minmax(0,1fr)] lg:gap-8">
      <aside class="min-w-0 lg:sticky lg:top-24 lg:self-start">
        <div class="hidden rounded-xl border border-line bg-page-elevated p-2 lg:block">
          <nav class="grid gap-0.5" aria-label={copy.eyebrow}>
            {#each menu as item (item.id)}
              <button
                type="button"
                class="flex w-full cursor-pointer items-center gap-2.5 rounded-md px-3 py-2.5 text-left text-sm font-medium transition-colors {active === item.id
                  ? 'bg-surface-muted text-ink'
                  : 'text-muted hover:bg-surface hover:text-ink'}"
                aria-current={active === item.id ? "page" : undefined}
                onclick={() => selectSection(item.id)}
              >
                {#if item.id === "profile"}
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 {active === item.id ? 'text-accent' : 'text-faint'}">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                {:else}
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 {active === item.id ? 'text-accent' : 'text-faint'}">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                {/if}
                {item.label}
              </button>
            {/each}
          </nav>
          <div class="mt-1 border-t border-line pt-1">
            <button
              type="button"
              class="flex w-full cursor-pointer items-center gap-2.5 rounded-md px-3 py-2.5 text-left text-sm font-medium text-muted transition-colors hover:bg-surface hover:text-ink"
              onclick={handleBackToHome}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0 text-faint">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              {copy.menu.backToHome}
            </button>
            <button
              type="button"
              class="flex w-full cursor-pointer items-center gap-2.5 rounded-md px-3 py-2.5 text-left text-sm font-medium text-danger transition-colors hover:bg-danger-soft disabled:cursor-not-allowed disabled:opacity-60"
              onclick={handleLogout}
              disabled={signingOut}
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" x2="9" y1="12" y2="12"/>
              </svg>
              {signingOut ? copy.menu.signingOut : copy.menu.logout}
            </button>
          </div>
        </div>

        <nav class="grid grid-cols-2 gap-1.5 sm:grid-cols-3 lg:hidden" aria-label={copy.eyebrow}>
          {#each menu as item (item.id)}
            <button
              type="button"
              class="inline-flex min-w-0 cursor-pointer items-center justify-center gap-1.5 rounded-md border px-2 py-2.5 text-xs font-semibold transition-colors {active === item.id
                ? 'border-line bg-surface-muted text-ink'
                : 'border-transparent text-muted hover:text-ink'}"
              aria-current={active === item.id ? "page" : undefined}
              onclick={() => (active = item.id)}
            >
              <span class="truncate">{item.label}</span>
            </button>
          {/each}
          <button
            type="button"
            class="col-span-2 inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-line px-2 py-2.5 text-xs font-semibold text-muted transition-colors hover:bg-surface hover:text-ink sm:col-span-3"
            onclick={handleBackToHome}
          >
            {copy.menu.backToHome}
          </button>
          <button
            type="button"
            class="col-span-2 inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-md border border-line px-2 py-2.5 text-xs font-semibold text-danger transition-colors hover:bg-danger-soft disabled:opacity-60 sm:col-span-3"
            onclick={handleLogout}
            disabled={signingOut}
          >
            {signingOut ? copy.menu.signingOut : copy.menu.logout}
          </button>
        </nav>
      </aside>

      <div class="min-w-0">
        {#if active === "profile"}
          <ProfilePanel {user} onstatus={showStatusToast} />
        {:else}
          <PreferencesPanel {user} onstatus={showStatusToast} />
        {/if}
      </div>
    </div>
  </section>
</div>

<OnboardingDialog
  open={onboardingOpen}
  initialName={user.name}
  completed={onboardingCompleted}
  onclose={() => (onboardingOpen = false)}
/>

<Toast
  open={toastOpen}
  title={user.locale === "id" ? "Notifikasi" : "Notification"}
  description={toastMessage}
  onclose={() => (toastOpen = false)}
/>

<Toast
  open={homeToastOpen}
  title={copy.menu.homeUnavailableTitle}
  description={copy.menu.homeUnavailableDescription}
  onclose={() => (homeToastOpen = false)}
/>
