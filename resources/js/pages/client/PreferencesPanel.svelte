<script lang="ts">
  import { useForm } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import { getClientTranslations } from "@/lib/i18n.js";
  import { applyTheme, type LocalePreference, type ThemePreference } from "@/lib/theme.js";
  import type { ClientUser } from "./ProfilePanel.svelte";

  interface Props {
    user: ClientUser;
    onstatus?: (message: string) => void;
  }

  let { user, onstatus }: Props = $props();

  const copy = $derived(getClientTranslations(user.locale));

  const themeOptions: ThemePreference[] = ["light", "dark", "system"];
  const localeOptions: LocalePreference[] = ["en", "id"];

  const form = useForm({
    theme: "system" as ThemePreference,
    locale: "en" as LocalePreference,
  });

  $effect(() => {
    form.theme = user.theme;
    form.locale = user.locale;
  });

  function selectTheme(theme: ThemePreference) {
    form.theme = theme;
    applyTheme(theme);
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.put("/client/preferences", {
      preserveScroll: true,
      onSuccess: (page) => {
        applyTheme(form.theme);
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
      },
    });
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex items-start gap-3">
      <span
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
          <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">{copy.preferences.title}</h2>
        <p class="mt-1 text-sm leading-6 text-muted">{copy.preferences.description}</p>
      </div>
    </div>
  </div>

  <form
    class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6"
    onsubmit={handleSubmit}
    novalidate
  >
    <div class="space-y-6">
      <div>
        <h3 class="text-sm font-semibold text-ink">{copy.preferences.themeLabel}</h3>
        <p class="mt-1 text-xs leading-5 text-faint">{copy.preferences.themeDescription}</p>

        <div class="mt-3 grid gap-3 sm:grid-cols-3">
          {#each themeOptions as option (option)}
            {@const selected = form.theme === option}
            {@const meta = copy.preferences.themes[option]}
            <button
              type="button"
              onclick={() => selectTheme(option)}
              disabled={form.processing}
              class="flex cursor-pointer flex-col items-start rounded-lg border p-3.5 text-left transition-colors {selected
                ? 'border-accent bg-accent-soft'
                : 'border-line bg-page hover:border-line-strong'}"
            >
              <span class="text-sm font-semibold text-ink">{meta.label}</span>
              <span class="mt-1 text-xs leading-5 text-muted">{meta.description}</span>
            </button>
          {/each}
        </div>
        {#if form.errors.theme}
          <p class="mt-2 text-xs text-destructive">{form.errors.theme}</p>
        {/if}
      </div>

      <div class="border-t border-line pt-6">
        <h3 class="text-sm font-semibold text-ink">{copy.preferences.languageLabel}</h3>
        <p class="mt-1 text-xs leading-5 text-faint">{copy.preferences.languageDescription}</p>

        <div class="mt-3 grid gap-3 sm:grid-cols-2">
          {#each localeOptions as option (option)}
            {@const selected = form.locale === option}
            {@const meta = copy.preferences.languages[option]}
            <button
              type="button"
              onclick={() => (form.locale = option)}
              disabled={form.processing}
              class="flex cursor-pointer flex-col items-start rounded-lg border p-3.5 text-left transition-colors {selected
                ? 'border-accent bg-accent-soft'
                : 'border-line bg-page hover:border-line-strong'}"
            >
              <span class="text-sm font-semibold text-ink">{meta.label}</span>
              <span class="mt-1 text-xs leading-5 text-muted">{meta.description}</span>
            </button>
          {/each}
        </div>
        {#if form.errors.locale}
          <p class="mt-2 text-xs text-destructive">{form.errors.locale}</p>
        {/if}
      </div>
    </div>

    <div class="mt-6 flex">
      <Button type="submit" class="w-full sm:w-auto" disabled={form.processing}>
        {form.processing ? copy.preferences.saving : copy.preferences.save}
      </Button>
    </div>
  </form>
</div>
