<script lang="ts">
  import { Link } from "@inertiajs/svelte";
  import { landingContent } from "@/lib/landing.js";
  import type { LocalePreference } from "@/lib/theme.js";

  interface Props {
    lang?: LocalePreference;
  }

  let { lang = "en" }: Props = $props();

  const content = $derived(landingContent[lang]);
</script>

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
            <li><a href="/#product" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.showcase}</a></li>
            <li><a href="/#features" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.workflow}</a></li>
            <li><a href="/#capabilities" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.capabilities}</a></li>
            <li><a href="/#architecture" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.architecture}</a></li>
            <li><a href="/#faq" class="block text-sm leading-6 text-muted transition-colors hover:text-ink">{content.nav.faq}</a></li>
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
