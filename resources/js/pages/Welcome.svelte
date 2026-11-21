<script lang="ts">
  import { onMount } from "svelte";
  import { Link } from "@inertiajs/svelte";
  import LocaleToggle from "@/components/auth/locale-toggle.svelte";
  import ThemeToggle from "@/components/auth/theme-toggle.svelte";
  import { landingContent, type LandingFaqItem } from "@/lib/landing.js";
  import { applyTheme, type LocalePreference, type ThemePreference } from "@/lib/theme.js";

  interface Props {
    isAuthenticated?: boolean;
    userLocale?: LocalePreference;
    faqs?: LandingFaqItem[];
  }

  let { isAuthenticated = false, userLocale = "en", faqs = [] }: Props = $props();

  let lang = $state<LocalePreference>("en");
  const content = $derived(landingContent[lang]);
  const showcaseItems = $derived(content.showcase.items);

  let activeShowcase = $state(0);
  const activeItem = $derived(showcaseItems[activeShowcase]);

  let zoomedImage = $state<{ src: string; alt: string; title: string } | null>(null);
  let isFullResolution = $state(false);

  onMount(() => {
    try {
      const savedTheme = localStorage.getItem("nova-theme") as ThemePreference | null;
      if (savedTheme === "light" || savedTheme === "dark" || savedTheme === "system") {
        applyTheme(savedTheme);
      }
      const savedLocale = localStorage.getItem("nova-locale") as LocalePreference | null;
      if (savedLocale === "en" || savedLocale === "id") {
        lang = savedLocale;
      } else if (userLocale === "en" || userLocale === "id") {
        lang = userLocale;
      }
    } catch {
    }

    const handleOutsideClick = (event: MouseEvent) => {
      const target = event.target as Node | null;
      if (target === null) return;
      document.querySelectorAll("header details[open]").forEach((element) => {
        if (!element.contains(target)) {
          (element as HTMLDetailsElement).open = false;
        }
      });
    };

    document.addEventListener("click", handleOutsideClick);
    return () => {
      document.removeEventListener("click", handleOutsideClick);
    };
  });

  function chooseLang(nextLang: LocalePreference, event: MouseEvent) {
    lang = nextLang;
    try {
      localStorage.setItem("nova-locale", nextLang);
    } catch {
    }
    const details = (event.currentTarget as HTMLElement).closest("details") as HTMLDetailsElement | null;
    if (details) {
      details.open = false;
    }
  }

  function openZoom(src: string, alt: string, title: string) {
    zoomedImage = { src, alt, title };
    isFullResolution = false;
  }

  function closeZoom() {
    zoomedImage = null;
    isFullResolution = false;
  }

  function toggleResolution() {
    isFullResolution = !isFullResolution;
  }
</script>

<svelte:head>
  <title>Nova Backups Panel - {content.hero.titleLead} {content.hero.titleAccent}</title>
  <meta name="description" content={content.hero.description} />
</svelte:head>

<svelte:window
  onkeydown={(e) => {
    if (e.key === "Escape" && zoomedImage) {
      closeZoom();
    }
  }}
/>

<div class="relative min-h-screen overflow-x-clip bg-page text-ink">
  <div class="pointer-events-none absolute inset-x-0 top-0 h-[960px] overflow-hidden" aria-hidden="true">
    <div class="hero-halo absolute inset-0"></div>
    <div class="grid-lines mask-fade-b absolute inset-0 opacity-40"></div>
  </div>

  <!-- Header -->
  <header class="sticky top-0 z-40 border-b border-line bg-header backdrop-blur-md">
    <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between gap-4 px-5 sm:px-6 lg:px-8">
      <Link href="/" class="flex items-center gap-2.5 text-sm font-semibold tracking-tight text-ink">
        <img
          src="/logo.png"
          alt="Nova Backups"
          width="28"
          height="28"
          class="h-7 w-7 rounded-[7px] object-contain"
        />
        <span>Nova Backups</span>
      </Link>

      <nav class="hidden items-center gap-6 text-sm text-muted md:flex" aria-label="Primary">
        <a href="#product" class="transition-colors hover:text-ink">{content.nav.showcase}</a>
        <a href="#features" class="transition-colors hover:text-ink">{content.nav.workflow}</a>
        <a href="#capabilities" class="transition-colors hover:text-ink">{content.nav.capabilities}</a>
        <a href="#architecture" class="transition-colors hover:text-ink">{content.nav.architecture}</a>
        <a href="#faq" class="transition-colors hover:text-ink">{content.nav.faq}</a>
      </nav>

      <div class="flex items-center gap-1 sm:gap-2">
        <ThemeToggle />
        <LocaleToggle bind:lang label={content.nav.language} />

        {#if isAuthenticated}
          <Link
            href="/client"
            class="ml-1 inline-flex items-center justify-center rounded-md bg-ink px-3.5 py-2 text-xs font-semibold text-page transition-opacity hover:opacity-90 sm:text-sm"
          >
            {content.nav.clientArea}
          </Link>
        {:else}
          <Link
            href="/login"
            class="hidden rounded-md px-3 py-2 text-sm font-medium text-muted transition-colors hover:text-ink sm:inline-flex"
          >
            {content.nav.signIn}
          </Link>
          <Link
            href="/register"
            class="ml-1 inline-flex items-center justify-center rounded-md bg-ink px-3.5 py-2 text-xs font-semibold text-page transition-opacity hover:opacity-90 sm:text-sm"
          >
            {content.nav.getStarted}
          </Link>
        {/if}
      </div>
    </div>
  </header>

  <!-- Hero -->
  <section class="relative pt-12 sm:pt-20 lg:pt-24" aria-labelledby="hero-heading">
    <div class="mx-auto w-full max-w-7xl px-5 text-center sm:px-6 lg:px-8">
      <div class="mx-auto max-w-5xl">
        <h1
          id="hero-heading"
          class="animate-rise-delayed text-balance text-[2rem] font-semibold leading-[1.06] tracking-[-0.045em] text-ink sm:text-6xl lg:text-[4.2rem]"
        >
          {content.hero.titleLead}
          <span class="block text-accent">{content.hero.titleAccent}</span>
        </h1>

        <p class="animate-rise-delayed mx-auto mt-6 max-w-3xl text-base leading-7 text-muted sm:text-lg sm:leading-8">
          {content.hero.description}
        </p>

        <div class="animate-rise-delayed mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
          {#if isAuthenticated}
            <Link
              href="/client"
              class="group inline-flex w-full items-center justify-center gap-2 rounded-md bg-ink px-5 py-3 text-sm font-semibold text-page transition-opacity hover:opacity-90 sm:w-auto"
            >
              {content.hero.primaryAuthCta}
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
              </svg>
            </Link>
          {:else}
            <Link
              href="/register"
              class="group inline-flex w-full items-center justify-center gap-2 rounded-md bg-ink px-5 py-3 text-sm font-semibold text-page transition-opacity hover:opacity-90 sm:w-auto"
            >
              {content.hero.primaryCta}
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 transition-transform duration-200 group-hover:translate-x-0.5">
                <path d="M5 12h14"/>
                <path d="m12 5 7 7-7 7"/>
              </svg>
            </Link>
            <Link
              href="/login"
              class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-line bg-surface/60 px-5 py-3 text-sm font-semibold text-ink transition-colors hover:bg-surface-muted sm:w-auto"
            >
              {content.hero.secondaryCta}
            </Link>
          {/if}
        </div>

        <p class="mt-5 flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-xs text-faint">
          {content.hero.platforms}
        </p>
      </div>
    </div>

    <div class="relative mx-auto mt-10 w-full max-w-7xl px-5 sm:mt-14 sm:px-6 lg:px-8">
      <div
        class="pointer-events-none absolute inset-x-16 top-10 h-52 rounded-full bg-accent-soft blur-[110px]"
        aria-hidden="true"
      ></div>
      <button
        type="button"
        class="animate-rise-delayed group relative block w-full cursor-zoom-in overflow-hidden rounded-xl border border-line-strong bg-surface p-1.5 text-left shadow-2xl shadow-black/20 focus:outline-none focus:ring-2 focus:ring-accent dark:shadow-black/60"
        onclick={() =>
          openZoom(
            "/demo/client-area-backup-schedules.png",
            "Nova Backups Panel Backup Schedules workspace",
            "Nova Backups Panel - Backup Schedules",
          )}
        aria-label="Zoom Nova Backups Panel preview"
      >
        <div class="relative overflow-hidden rounded-lg border border-line bg-surface-muted">
          <div class="flex h-9 items-center gap-2 border-b border-line px-3.5">
            <div class="flex items-center gap-1.5" aria-hidden="true">
              <span class="h-2 w-2 rounded-full bg-[#ff5f57]/80"></span>
              <span class="h-2 w-2 rounded-full bg-[#febc2e]/80"></span>
              <span class="h-2 w-2 rounded-full bg-[#28c840]/80"></span>
            </div>
            <div class="mx-auto flex min-w-0 items-center gap-1.5 text-[10px] font-medium text-faint sm:text-xs">
              <span class="truncate">Nova Backups Panel</span>
              <span class="text-line-strong">/</span>
              <span class="truncate text-muted">{content.showcase.items[0].title}</span>
            </div>
            <span class="flex items-center gap-1 text-[10px] text-faint opacity-0 transition-opacity group-hover:opacity-100">
              Zoom
            </span>
          </div>
          <img
            src="/demo/client-area-backup-schedules.png"
            alt="Nova Backups Panel Backup Schedules workspace"
            class="block h-auto w-full"
            decoding="async"
          />
        </div>
      </button>
    </div>

    <div class="relative mx-auto mt-8 w-full max-w-7xl px-5 sm:mt-10 sm:px-6 lg:px-8">
      <dl class="grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-line bg-line lg:grid-cols-4">
        {#each content.hero.stats as stat}
          <div class="bg-page px-4 py-5 sm:px-5">
            <dt class="text-lg font-semibold tracking-tight text-ink sm:text-xl">
              {stat.value}
            </dt>
            <dd class="mt-1 text-[10px] uppercase leading-4 tracking-[0.08em] text-faint sm:text-xs">
              {stat.label}
            </dd>
          </div>
        {/each}
      </dl>
    </div>
  </section>

  <!-- Product Showcase -->
  <section id="product" class="relative scroll-mt-20 py-14 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-8">
      <div class="grid gap-8 lg:grid-cols-[0.82fr_1.18fr] lg:gap-16">
        <div class="lg:sticky lg:top-24 lg:self-start">
          <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
            {content.showcase.eyebrow}
          </p>
          <h2 class="mt-4 text-balance text-[1.75rem] font-semibold tracking-[-0.03em] text-ink sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
            {content.showcase.title}
          </h2>
          <p class="mt-4 max-w-md text-sm leading-6 text-muted sm:text-base sm:leading-7">
            {content.showcase.description}
          </p>

          <div class="mt-6 sm:mt-8" role="tablist" aria-label="Product views">
            {#each showcaseItems as item, index}
              <button
                type="button"
                role="tab"
                id={`showcase-tab-${index}`}
                aria-controls="showcase-panel"
                aria-selected={activeShowcase === index}
                class="group flex w-full cursor-pointer items-start gap-3 border-t border-l-2 border-line border-l-transparent py-4 pl-3 pr-1 text-left transition-colors {activeShowcase ===
                index
                  ? 'border-l-accent'
                  : 'hover:bg-surface/40'}"
                onclick={() => (activeShowcase = index)}
              >
                <span class="mt-0.5 font-mono text-[10px] {activeShowcase === index ? 'text-accent' : 'text-faint'}">
                  0{index + 1}
                </span>
                <span class="min-w-0 flex-1">
                  <span class="block text-sm font-semibold {activeShowcase === index ? 'text-ink' : 'text-muted group-hover:text-ink'}">
                    {item.title}
                  </span>
                  <span class="mt-1 block text-xs leading-5 text-faint">
                    {item.label}
                  </span>
                </span>
              </button>
            {/each}
          </div>
        </div>

        <div id="showcase-panel" role="tabpanel" aria-labelledby={`showcase-tab-${activeShowcase}`}>
          <figure class="overflow-hidden rounded-xl border border-line-strong bg-surface p-1.5 shadow-xl shadow-black/10 dark:shadow-black/40">
            <div class="overflow-hidden rounded-lg border border-line bg-surface-muted">
              <div class="flex h-9 items-center gap-2 border-b border-line px-3.5">
                <div class="flex items-center gap-1.5" aria-hidden="true">
                  <span class="h-2 w-2 rounded-full bg-[#ff5f57]/80"></span>
                  <span class="h-2 w-2 rounded-full bg-[#febc2e]/80"></span>
                  <span class="h-2 w-2 rounded-full bg-[#28c840]/80"></span>
                </div>
                <span class="mx-auto flex min-w-0 items-center gap-1.5 text-[10px] text-faint sm:text-xs">
                  <span class="truncate">{activeItem.label}</span>
                </span>
              </div>
              <button
                type="button"
                class="group relative block w-full cursor-zoom-in border-0 bg-transparent p-0 text-left focus:outline-none focus:ring-2 focus:ring-accent"
                onclick={() => openZoom(activeItem.image, activeItem.alt, activeItem.title)}
                aria-label={`Zoom ${activeItem.title} screenshot`}
              >
                {#each showcaseItems as item, index}
                  <img
                    src={item.image}
                    alt={activeShowcase === index ? item.alt : ""}
                    aria-hidden={activeShowcase !== index}
                    loading={index === 0 ? "eager" : "lazy"}
                    decoding="async"
                    class={activeShowcase === index ? "block h-auto w-full" : "hidden"}
                  />
                {/each}
                <div class="pointer-events-none absolute bottom-3 right-3 rounded-md border border-line bg-page/85 px-2.5 py-1 text-xs font-medium text-faint shadow-md backdrop-blur-sm opacity-0 transition-opacity group-hover:opacity-100">
                  Zoom
                </div>
              </button>
            </div>
          </figure>

          <div class="mt-5 min-h-14">
            <p class="text-sm leading-6 text-muted">{activeItem.description}</p>
            <ul class="mt-4 flex flex-wrap gap-x-6 gap-y-2">
              {#each activeItem.bullets as bullet}
                <li class="flex items-center gap-1.5 text-xs text-faint">
                  <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                  {bullet}
                </li>
              {/each}
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Workflow -->
  <section id="features" class="relative scroll-mt-20 border-t border-line py-14 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-8">
      <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-20">
        <div class="lg:sticky lg:top-24 lg:self-start">
          <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
            {content.workflow.eyebrow}
          </p>
          <h2 class="mt-4 text-balance text-[1.75rem] font-semibold tracking-[-0.03em] text-ink sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
            {content.workflow.title}
          </h2>
          <p class="mt-4 max-w-md text-sm leading-6 text-muted sm:text-base sm:leading-7">
            {content.workflow.description}
          </p>
          <blockquote class="mt-6 border-l border-accent/40 pl-5 sm:mt-8">
            <p class="text-sm leading-6 text-ink">
              “{content.workflow.quote}”
            </p>
            <footer class="mt-2 text-xs uppercase tracking-[0.12em] text-faint">
              - {content.workflow.quoteSource}
            </footer>
          </blockquote>
        </div>

        <ol class="border-y border-line">
          {#each content.workflow.steps as step}
            <li class="grid grid-cols-[auto_1fr] gap-4 border-b border-line py-6 last:border-b-0 sm:gap-8 sm:py-8">
              <span class="font-mono text-xs text-accent">{step.number}</span>
              <div>
                <h3 class="text-lg font-semibold tracking-tight text-ink">{step.title}</h3>
                <p class="mt-2 max-w-lg text-sm leading-6 text-muted">{step.description}</p>
              </div>
            </li>
          {/each}
        </ol>
      </div>
    </div>
  </section>

  <!-- Capabilities -->
  <section id="capabilities" class="relative scroll-mt-20 border-t border-line py-14 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-8">
      <div class="max-w-3xl">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
          {content.capabilities.eyebrow}
        </p>
        <h2 class="mt-4 text-balance text-[1.75rem] font-semibold tracking-[-0.03em] text-ink sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
          {content.capabilities.title}
        </h2>
        <p class="mt-4 text-sm leading-6 text-muted sm:text-base sm:leading-7">
          {content.capabilities.description}
        </p>
      </div>

      <div class="mt-10 grid gap-4 lg:grid-cols-3">
        <article class="rounded-xl border border-line bg-surface p-5 transition-colors hover:border-line-strong sm:p-6 lg:col-span-2">
          <div class="grid min-w-0 gap-5 sm:grid-cols-[1fr_1.05fr] sm:items-center sm:gap-6">
            <div class="min-w-0">
              <h3 class="text-sm font-semibold text-ink">{content.capabilities.patternTitle}</h3>
              <p class="mt-3 text-sm leading-6 text-muted">
                {content.capabilities.patternDescription}
              </p>
              <p class="mt-4 font-mono text-[10px] uppercase tracking-[0.1em] text-faint">
                {content.capabilities.patternFootnote}
              </p>
            </div>

            <div class="min-w-0 overflow-hidden rounded-lg border border-white/10 bg-code-bg shadow-inner">
              <div class="flex items-center justify-between border-b border-white/10 px-3.5 py-2">
                <span class="flex items-center gap-1.5 font-mono text-[10px] text-code-ink/60">
                  <span class="h-1.5 w-1.5 rounded-full bg-emerald-400/80"></span>
                  schedule.json
                </span>
                <span class="rounded border border-white/10 px-1.5 py-0.5 font-mono text-[9px] uppercase tracking-wider text-code-ink/50">
                  Enabled
                </span>
              </div>
              <pre class="max-w-full overflow-x-auto px-3.5 py-3 font-mono text-[10px] leading-6 text-code-ink sm:text-xs"><code><span class="text-code-ink/60">{"{"}</span>
  <span class="text-sky-300">"type"</span><span class="text-code-ink/60">:</span> <span class="text-emerald-300">"database"</span><span class="text-code-ink/60">,</span>
  <span class="text-sky-300">"pattern"</span><span class="text-code-ink/60">:</span> <span class="text-emerald-300">"backup-&#123;Ymd-His&#125;"</span><span class="text-code-ink/60">,</span>
  <span class="text-sky-300">"interval"</span><span class="text-code-ink/60">:</span> <span class="text-emerald-300">"3 days"</span><span class="text-code-ink/60">,</span>
  <span class="text-sky-300">"sync_cloud"</span><span class="text-code-ink/60">:</span> <span class="text-amber-300">true</span>
<span class="text-code-ink/60">{"}"}</span></code></pre>
            </div>
          </div>
        </article>

        <article class="rounded-xl border border-line bg-surface p-5 transition-colors hover:border-line-strong sm:p-6">
          <h3 class="text-sm font-semibold text-ink">{content.capabilities.cloudTitle}</h3>
          <p class="mt-3 text-sm leading-6 text-muted">
            {content.capabilities.cloudDescription}
          </p>

          <div class="mt-5 space-y-2 font-mono text-[10px]">
            <div class="flex items-center justify-between rounded border border-line bg-page px-2.5 py-2 text-muted">
              <span>Local Archive</span>
              <span class="text-accent">Step 1</span>
            </div>
            <div class="ml-3 space-y-2 border-l border-line pl-3">
              <div class="flex items-center justify-between rounded border border-line bg-page px-2.5 py-2 text-muted">
                <span>S3 Storage</span>
                <span class="text-accent">Cloud</span>
              </div>
              <div class="flex items-center justify-between rounded border border-line bg-page px-2.5 py-2 text-muted">
                <span>Google Drive</span>
                <span class="text-accent">Cloud</span>
              </div>
            </div>
          </div>
        </article>

        <article class="rounded-xl border border-line bg-surface p-5 transition-colors hover:border-line-strong sm:p-6 lg:col-span-2">
          <h3 class="text-sm font-semibold text-ink">{content.capabilities.logsTitle}</h3>
          <p class="mt-3 max-w-xl text-sm leading-6 text-muted">
            {content.capabilities.logsDescription}
          </p>
          <button
            type="button"
            class="group relative mt-5 block w-full cursor-zoom-in overflow-hidden rounded-lg border border-line bg-surface-muted p-0 text-left focus:outline-none focus:ring-2 focus:ring-accent"
            onclick={() => openZoom("/demo/client-area-backups.png", "Backup execution history", content.capabilities.logsTitle)}
            aria-label={`Zoom ${content.capabilities.logsTitle} preview`}
          >
            <img
              src="/demo/client-area-backups.png"
              alt="Backup execution history"
              loading="lazy"
              decoding="async"
              class="h-auto w-full"
            />
          </button>
        </article>

        <article class="flex flex-col rounded-xl border border-line bg-surface p-5 transition-colors hover:border-line-strong sm:p-6">
          <h3 class="text-sm font-semibold text-ink">{content.capabilities.prefsTitle}</h3>
          <p class="mt-3 text-sm leading-6 text-muted">
            {content.capabilities.prefsDescription}
          </p>
          <button
            type="button"
            class="group relative mt-5 block w-full cursor-zoom-in overflow-hidden rounded-lg border border-line bg-surface-muted p-0 text-left focus:outline-none focus:ring-2 focus:ring-accent"
            onclick={() => openZoom("/demo/client-area-preference.png", "Preferences panel", content.capabilities.prefsTitle)}
            aria-label={`Zoom ${content.capabilities.prefsTitle} preview`}
          >
            <img
              src="/demo/client-area-preference.png"
              alt="Preferences panel"
              loading="lazy"
              decoding="async"
              class="h-auto w-full"
            />
          </button>
        </article>
      </div>
    </div>
  </section>

  <!-- Architecture -->
  <section id="architecture" class="relative scroll-mt-20 border-y border-line bg-surface py-14 sm:py-24">
    <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
      <div class="grid-lines absolute inset-0 opacity-20"></div>
    </div>
    <div class="relative mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-8">
      <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr] lg:items-center lg:gap-20">
        <div>
          <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
            {content.architecture.eyebrow}
          </p>
          <h2 class="mt-4 text-balance text-[1.75rem] font-semibold tracking-[-0.03em] text-ink sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
            {content.architecture.title}
          </h2>
          <p class="mt-4 max-w-lg text-sm leading-6 text-muted sm:text-base sm:leading-7">
            {content.architecture.description}
          </p>

          <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-lg border border-line bg-line sm:mt-8">
            {#each content.architecture.stats as stat}
              <div class="bg-page px-4 py-4">
                <dt class="text-lg font-semibold tracking-tight text-ink">{stat.value}</dt>
                <dd class="mt-1 text-[10px] leading-4 text-faint">{stat.label}</dd>
              </div>
            {/each}
          </dl>
        </div>

        <div class="rounded-xl border border-line bg-page p-5 sm:p-6">
          <div class="space-y-2">
            {#each content.architecture.layers as layer, index}
              <div class="rounded-lg border border-line bg-surface px-4 py-3">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
                  <span class="text-[10px] uppercase tracking-[0.14em] text-faint">
                    {layer.label}
                  </span>
                  <span class="font-mono text-[10px] leading-4 text-muted sm:text-right">
                    {layer.detail}
                  </span>
                </div>
              </div>
              {#if index < content.architecture.layers.length - 1}
                <div class="flex justify-center" aria-hidden="true">
                  <span class="h-2 w-px bg-line-strong"></span>
                </div>
              {/if}
            {/each}
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Security -->
  <section id="security" class="relative scroll-mt-20 py-14 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-8">
      <div class="relative overflow-hidden rounded-2xl border border-line bg-surface p-5 sm:p-10 lg:p-14">
        <div
          class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-accent-soft blur-3xl"
          aria-hidden="true"
        ></div>

        <div class="relative grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
          <div>
            <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
              {content.security.eyebrow}
            </p>
            <h2 class="mt-4 text-balance text-[1.75rem] font-semibold tracking-[-0.03em] text-ink sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
              {content.security.title}
            </h2>
            <p class="mt-4 max-w-md text-sm leading-6 text-muted sm:text-base sm:leading-7">
              {content.security.description}
            </p>

            <div class="mt-6 flex flex-wrap gap-2">
              {#each content.security.badges as badge}
                <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-page px-3 py-1 text-xs text-muted">
                  <span class="h-1.5 w-1.5 rounded-full bg-accent"></span>
                  {badge}
                </span>
              {/each}
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            {#each content.security.items as item}
              <div class="rounded-lg border border-line bg-page p-4 sm:p-5">
                <h3 class="text-sm font-semibold text-ink">{item.title}</h3>
                <p class="mt-2 text-xs leading-5 text-muted">{item.description}</p>
              </div>
            {/each}
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="relative scroll-mt-20 border-t border-line py-14 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-8">
      <div class="max-w-2xl">
        <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
          {content.faq.eyebrow}
        </p>
        <h2 class="mt-4 text-balance text-[1.75rem] font-semibold tracking-[-0.03em] text-ink sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
          {content.faq.title}
        </h2>
        <p class="mt-4 text-sm leading-6 text-muted sm:text-base sm:leading-7">
          {content.faq.description}
        </p>
      </div>

      <div class="mt-10 divide-y divide-line overflow-hidden rounded-xl border border-line bg-surface">
        {#each faqs as item (item.uid)}
          <details class="group">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-5 text-left text-sm font-semibold text-ink [&::-webkit-details-marker]:hidden sm:px-6">
              {item.question[lang]}
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0 text-faint transition-transform group-open:rotate-180">
                <path d="m6 9 6 6 6-6"/>
              </svg>
            </summary>
            <p class="px-5 pb-5 text-sm leading-6 text-muted sm:px-6">{item.answer[lang]}</p>
          </details>
        {/each}
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="relative border-t border-line py-14 sm:py-24">
    <div class="mx-auto w-full max-w-7xl px-5 sm:px-6 lg:px-8">
      <div class="relative overflow-hidden rounded-2xl border border-line bg-surface px-5 py-10 text-center sm:px-10 sm:py-16">
        <div class="hero-halo pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="relative mx-auto max-w-2xl">
          <p class="font-mono text-[10px] uppercase tracking-[0.18em] text-accent">
            {content.cta.eyebrow}
          </p>
          <h2 class="mt-4 text-balance text-[1.75rem] font-semibold tracking-[-0.03em] text-ink sm:text-4xl lg:text-[2.65rem] lg:leading-[1.08]">
            {content.cta.title}
          </h2>
          <p class="mt-4 text-sm leading-6 text-muted sm:text-base sm:leading-7">
            {content.cta.description}
          </p>

          <div class="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
            {#if isAuthenticated}
              <Link
                href="/client"
                class="inline-flex w-full items-center justify-center rounded-md bg-ink px-5 py-3 text-sm font-semibold text-page transition-opacity hover:opacity-90 sm:w-auto"
              >
                {content.cta.primaryAuthCta}
              </Link>
            {:else}
              <Link
                href="/register"
                class="inline-flex w-full items-center justify-center rounded-md bg-ink px-5 py-3 text-sm font-semibold text-page transition-opacity hover:opacity-90 sm:w-auto"
              >
                {content.cta.primaryCta}
              </Link>
              <Link
                href="/login"
                class="inline-flex w-full items-center justify-center rounded-md border border-line bg-page px-5 py-3 text-sm font-semibold text-ink transition-colors hover:bg-surface-muted sm:w-auto"
              >
                {content.cta.secondaryCta}
              </Link>
            {/if}
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="border-t border-line bg-page">
    <div class="mx-auto w-full max-w-7xl px-5 py-10 sm:px-6 sm:py-14 lg:px-8">
      <div class="grid gap-10 sm:gap-12 lg:grid-cols-[1.1fr_1.9fr] lg:gap-16">
        <div class="max-w-sm">
          <Link href="/" class="flex w-fit items-center gap-2.5" aria-label="Nova Backups home">
            <img
              src="/logo.png"
              alt=""
              width="28"
              height="28"
              class="h-7 w-7 rounded-[7px] object-contain"
            />
            <span class="text-sm font-semibold tracking-tight text-ink">Nova Backups</span>
          </Link>
          <p class="mt-3 text-sm leading-6 text-muted">
            {content.footer.description}
          </p>
          <p class="mt-3 inline-flex items-center gap-2 text-sm leading-6 text-faint">
            <span class="status-dot h-1.5 w-1.5 shrink-0 rounded-full bg-accent"></span>
            <span class="inline-flex items-center gap-1">
              <span>With</span>
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" class="size-3.5 text-rose-500" aria-hidden="true">
                <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
              </svg>
              <span>by</span>
              <a
                href="https://novaardiansyah.id"
                target="_blank"
                rel="noreferrer"
                class="font-medium text-ink transition-colors hover:text-accent"
              >
                Nova Ardiansyah
              </a>
            </span>
          </p>
        </div>

        <div class="grid gap-8 sm:grid-cols-3 sm:gap-8">
          <div class="min-w-0">
            <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-faint">
              {content.footer.productTitle}
            </h2>
            <ul class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 sm:block sm:space-y-2">
              <li><a href="#product" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.showcase}</a></li>
              <li><a href="#features" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.workflow}</a></li>
              <li><a href="#capabilities" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.capabilities}</a></li>
              <li><a href="#architecture" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.architecture}</a></li>
              <li><a href="#faq" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.faq}</a></li>
            </ul>
          </div>

          <div class="min-w-0">
            <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-faint">
              {content.footer.workspaceTitle}
            </h2>
            <ul class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 sm:block sm:space-y-2">
              <li><Link href="/client" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.clientArea}</Link></li>
              <li><Link href="/login" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.signIn}</Link></li>
              <li><Link href="/register" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.getStarted}</Link></li>
            </ul>
          </div>

          <div class="min-w-0">
            <h2 class="text-xs font-semibold uppercase tracking-[0.12em] text-faint">
              {content.footer.legalTitle}
            </h2>
            <ul class="mt-3 grid grid-cols-2 gap-x-6 gap-y-2 sm:block sm:space-y-2">
              <li><Link href="/terms-of-service" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.footer.terms}</Link></li>
              <li><Link href="/privacy-policy" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.footer.privacy}</Link></li>
              <li>
                <a
                  href="https://novaardiansyah.id"
                  target="_blank"
                  rel="noreferrer"
                  class="block text-sm leading-6 text-muted transition-colors hover:text-ink"
                >
                  novaardiansyah.id
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div class="mt-10 border-t border-line pt-6 sm:mt-12">
        <div class="flex flex-col items-center gap-3 text-center text-sm leading-6 text-faint lg:flex-row lg:items-center lg:justify-between lg:gap-6 lg:text-left">
          <p class="max-w-md text-pretty">{content.footer.copyright}</p>
          <p class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 lg:justify-start lg:gap-x-2.5">
            <span class="whitespace-nowrap">{content.footer.license}</span>
            <span aria-hidden="true" class="hidden text-line-strong lg:inline">&middot;</span>
            <a
              href="https://novaardiansyah.id"
              target="_blank"
              rel="noreferrer"
              class="whitespace-nowrap transition-colors hover:text-ink"
            >
              novaardiansyah.id
            </a>
          </p>
        </div>
      </div>
    </div>
  </footer>
</div>

{#if zoomedImage}
  <div
    class="fixed inset-0 z-50 flex flex-col bg-black/90 backdrop-blur-md"
    role="dialog"
    aria-modal="true"
    aria-label="Image Preview"
  >
    <div class="flex h-14 shrink-0 items-center justify-between border-b border-white/10 px-4 sm:px-6">
      <div class="flex items-center gap-2 text-sm font-medium text-white">
        <span class="truncate">{zoomedImage.title}</span>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          onclick={toggleResolution}
          class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-white/15 bg-white/10 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-white/20"
        >
          {isFullResolution ? "Fit Screen" : "Zoom 100%"}
        </button>
        <button
          type="button"
          onclick={closeZoom}
          aria-label="Close preview"
          class="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-md border border-white/15 bg-white/10 text-white transition-colors hover:bg-white/20"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="relative flex flex-1 items-center justify-center overflow-auto p-4 sm:p-8">
      <button
        type="button"
        class="absolute inset-0 h-full w-full cursor-default border-0 bg-transparent p-0"
        onclick={closeZoom}
        aria-label="Close preview backdrop"
        tabindex="-1"
      ></button>
      <button
        type="button"
        onclick={toggleResolution}
        class="relative z-10 rounded-lg border-0 bg-transparent p-0 focus:outline-none focus:ring-2 focus:ring-accent"
        aria-label={isFullResolution ? "Fit Screen" : "Zoom 100%"}
      >
        <img
          src={zoomedImage.src}
          alt={zoomedImage.alt}
          class="{isFullResolution
            ? 'max-w-none cursor-zoom-out'
            : 'max-h-[85vh] max-w-full cursor-zoom-in object-contain'} rounded-lg shadow-2xl transition-all duration-200"
        />
      </button>
    </div>
  </div>
{/if}
