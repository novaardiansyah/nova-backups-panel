<script lang="ts">
  import type { HTMLInputAttributes } from "svelte/elements";
  import { cn } from "@/lib/utils.js";

  let {
    class: className,
    checked = $bindable(false),
    disabled = false,
    ...restProps
  }: Omit<HTMLInputAttributes, "type" | "checked"> & {
    checked?: boolean;
  } = $props();
</script>

<span class={cn("relative inline-flex size-4 shrink-0 items-center justify-center", className)}>
  <input
    type="checkbox"
    bind:checked
    {disabled}
    class="peer sr-only"
    {...restProps}
  />
  <span
    aria-hidden="true"
    class="flex size-4 items-center justify-center rounded-[4px] border border-input bg-page-elevated text-transparent shadow-xs transition-colors peer-checked:border-accent peer-checked:bg-accent peer-checked:text-accent-foreground peer-focus-visible:border-ring peer-focus-visible:ring-[3px] peer-focus-visible:ring-ring/40 peer-disabled:cursor-not-allowed peer-disabled:opacity-50"
  >
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="12"
      height="12"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      stroke-width="3"
      stroke-linecap="round"
      stroke-linejoin="round"
      class="size-3"
    >
      <path d="M20 6 9 17l-5-5" />
    </svg>
  </span>
</span>
