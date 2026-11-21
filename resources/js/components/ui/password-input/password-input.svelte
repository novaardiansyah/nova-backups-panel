<script lang="ts">
  import type { HTMLInputAttributes } from "svelte/elements";
  import { Input } from "@/components/ui/input/index.js";

  let {
    value = $bindable(""),
    ...restProps
  }: Omit<HTMLInputAttributes, "value" | "type"> & { value?: string } = $props();

  let visible = $state(false);
</script>

<div class="relative">
  <Input type={visible ? "text" : "password"} class="pl-3 pr-10" bind:value {...restProps} />
  <button
    type="button"
    class="absolute inset-y-0 right-0 flex cursor-pointer items-center justify-center px-3 text-muted transition-colors hover:text-ink focus-visible:outline-none"
    onclick={() => (visible = !visible)}
    aria-label={visible ? "Hide password" : "Show password"}
    aria-pressed={visible}
  >
    {#if visible}
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
        <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
        <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
        <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
        <line x1="2" x2="22" y1="2" y2="22"/>
      </svg>
    {:else}
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
    {/if}
  </button>
</div>
