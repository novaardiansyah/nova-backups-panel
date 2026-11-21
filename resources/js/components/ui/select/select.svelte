<script lang="ts">
  import type { Snippet } from "svelte";
  import type { HTMLSelectAttributes } from "svelte/elements";
  import { cn } from "@/lib/utils.js";

  let {
    class: className,
    value = $bindable(),
    children,
    ...restProps
  }: Omit<HTMLSelectAttributes, "value"> & {
    value?: string | number | null;
    children?: Snippet;
  } = $props();
</script>

<div class="relative w-full">
  <select
    bind:value
    class={cn(
      "flex h-9 w-full min-w-0 appearance-none rounded-md border border-input bg-page-elevated py-1 pl-3 pr-9 text-sm text-ink shadow-xs outline-none transition-[color,box-shadow]",
      "focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/40",
      "disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50",
      "aria-invalid:border-destructive aria-invalid:ring-destructive/20",
      className,
    )}
    {...restProps}
  >
    {@render children?.()}
  </select>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="16"
    height="16"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    class="pointer-events-none absolute right-2.5 top-1/2 size-4 -translate-y-1/2 text-muted"
  >
    <path d="m6 9 6 6 6-6" />
  </svg>
</div>
