<script lang="ts">
  import { onMount } from "svelte";
  import type { LocalePreference } from "@/lib/theme.js";

  interface Props {
    lang?: LocalePreference;
    onchange?: (lang: LocalePreference) => void;
    label?: string;
  }

  let { lang = $bindable<LocalePreference>("en"), onchange, label = "Language" }: Props = $props();

  let detailsEl = $state<HTMLDetailsElement | null>(null);

  onMount(() => {
    try {
      const saved = localStorage.getItem("nova-locale");
      if (saved === "en" || saved === "id") {
        lang = saved;
      }
    } catch {
    }

    const handleOutsideClick = (event: MouseEvent) => {
      if (!detailsEl?.open) return;
      if (!detailsEl.contains(event.target as Node | null)) {
        detailsEl.open = false;
      }
    };

    document.addEventListener("click", handleOutsideClick);
    return () => {
      document.removeEventListener("click", handleOutsideClick);
    };
  });

  function chooseLang(nextLang: LocalePreference) {
    lang = nextLang;
    try {
      localStorage.setItem("nova-locale", nextLang);
    } catch {
    }
    onchange?.(nextLang);
    if (detailsEl) {
      detailsEl.open = false;
    }
  }
</script>

<details bind:this={detailsEl} class="group relative">
  <summary
    class="flex h-9 w-9 cursor-pointer list-none items-center justify-center rounded-md text-muted transition-colors hover:bg-surface-muted hover:text-ink [&::-webkit-details-marker]:hidden"
    aria-label={label}
    title={label}
  >
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
      <circle cx="12" cy="12" r="10"/>
      <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
      <path d="M2 12h20"/>
    </svg>
  </summary>
  <div class="absolute right-0 top-full z-50 mt-2 w-40 overflow-hidden rounded-lg border border-line bg-page-elevated p-1 shadow-2xl shadow-black/20">
    <button
      type="button"
      class="flex w-full cursor-pointer items-center justify-between rounded-md px-3 py-2 text-left text-xs transition-colors hover:bg-surface-muted {lang === 'en'
        ? 'font-semibold text-ink'
        : 'text-muted'}"
      onclick={() => chooseLang("en")}
    >
      English
      {#if lang === "en"}
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 text-accent">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      {/if}
    </button>
    <button
      type="button"
      class="flex w-full cursor-pointer items-center justify-between rounded-md px-3 py-2 text-left text-xs transition-colors hover:bg-surface-muted {lang === 'id'
        ? 'font-semibold text-ink'
        : 'text-muted'}"
      onclick={() => chooseLang("id")}
    >
      Indonesia
      {#if lang === "id"}
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 text-accent">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      {/if}
    </button>
  </div>
</details>
