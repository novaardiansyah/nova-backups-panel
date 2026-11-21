<script lang="ts">
  import { router } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { applyTheme, type LocalePreference, type ThemePreference } from "@/lib/theme.js";

  interface Props {
    locale?: LocalePreference;
    persist?: boolean;
    onstatus?: (message: string) => void;
  }

  let { locale, persist = false, onstatus }: Props = $props();

  let isDark = $state(false);

  $effect(() => {
    isDark = document.documentElement.classList.contains("dark");
  });

  function toggle() {
    const nextTheme: ThemePreference = isDark ? "light" : "dark";
    applyTheme(nextTheme);
    isDark = nextTheme === "dark";

    if (persist && locale) {
      router.put(
        "/client/preferences",
        { theme: nextTheme, locale },
        {
          preserveScroll: true,
          preserveState: true,
          onSuccess: (page) => {
            const nextStatus = (page.props as { status?: string | null }).status;
            if (nextStatus) {
              onstatus?.(nextStatus);
            }
          },
        },
      );
    }
  }
</script>

<Button
  variant="ghost"
  size="icon"
  onclick={toggle}
  aria-label={isDark ? "Switch to light mode" : "Switch to dark mode"}
>
  {#if isDark}
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
      <circle cx="12" cy="12" r="4"/>
      <path d="M12 2v2"/>
      <path d="M12 20v2"/>
      <path d="m4.93 4.93 1.41 1.41"/>
      <path d="m17.66 17.66 1.41 1.41"/>
      <path d="M2 12h2"/>
      <path d="M20 12h2"/>
      <path d="m6.34 17.66-1.41 1.41"/>
      <path d="m19.07 4.93-1.41 1.41"/>
    </svg>
  {:else}
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
      <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
    </svg>
  {/if}
</Button>
