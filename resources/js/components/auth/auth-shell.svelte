<script lang="ts">
  import type { Snippet } from "svelte";
  import { Link } from "@inertiajs/svelte";
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card/index.js";
  import LocaleToggle from "./locale-toggle.svelte";
  import ThemeToggle from "./theme-toggle.svelte";

  interface Props {
    title: string;
    description?: string;
    children: Snippet;
  }

  let { title, description, children }: Props = $props();
</script>

<div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-page px-4 py-10 text-ink">
  <div class="pointer-events-none absolute inset-0" aria-hidden="true">
    <div class="absolute left-1/2 top-[-12rem] h-[30rem] w-[30rem] -translate-x-1/2 rounded-full bg-accent-soft blur-3xl"></div>
  </div>

  <div class="absolute left-4 top-4">
    <Link
      href="/"
      class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-xs font-medium text-muted transition-colors hover:bg-surface-muted hover:text-ink"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
        <path d="m12 19-7-7 7-7"/>
        <path d="M19 12H5"/>
      </svg>
      <span>Back to home</span>
    </Link>
  </div>

  <div class="absolute right-4 top-4 flex items-center gap-1">
    <ThemeToggle />
    <LocaleToggle />
  </div>

  <div class="relative w-full max-w-md">
    <Card class="shadow-md">
      <CardHeader>
        <CardTitle>{title}</CardTitle>
        {#if description}
          <CardDescription>{description}</CardDescription>
        {/if}
      </CardHeader>
      <CardContent>
        {@render children()}
      </CardContent>
    </Card>

    <p class="mt-6 text-center text-xs text-faint">
      &copy; {new Date().getFullYear()} Nova Backups. All rights reserved.
    </p>
  </div>
</div>
