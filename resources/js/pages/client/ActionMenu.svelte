<script lang="ts">
  import { fly } from "svelte/transition";

  interface Props {
    open: boolean;
    style: string;
    deleting?: boolean;
    showDetail?: boolean;
    showEdit?: boolean;
    ondetail?: () => void;
    onedit?: () => void;
    ondelete: () => void;
    onclose: () => void;
  }

  let {
    open,
    style,
    deleting = false,
    showDetail = false,
    showEdit = true,
    ondetail,
    onedit,
    ondelete,
    onclose,
  }: Props = $props();

  let menuEl = $state<HTMLDivElement | null>(null);
</script>

<svelte:window
  onclick={(e) => {
    if (!open) return;
    if (menuEl?.contains(e.target as Node | null)) return;
    onclose();
  }}
  onkeydown={(e) => {
    if (e.key === "Escape" && open) {
      onclose();
    }
  }}
  onresize={() => {
    if (open) onclose();
  }}
  onscroll={() => {
    if (open) onclose();
  }}
/>

{#if open}
  <div
    bind:this={menuEl}
    role="menu"
    {style}
    transition:fly={{ y: -2, duration: 100 }}
    class="fixed z-[60] w-40 rounded-md border border-line bg-page-elevated p-1 shadow-lg"
  >
    {#if showDetail && ondetail}
      <button
        type="button"
        role="menuitem"
        onclick={ondetail}
        class="flex w-full cursor-pointer items-center gap-2 rounded-sm px-2.5 py-1.5 text-left text-sm text-ink hover:bg-surface-muted"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 text-muted">
          <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/>
          <circle cx="12" cy="12" r="3"/>
        </svg>
        <span>Detail</span>
      </button>
    {/if}

    {#if showEdit && onedit}
      <button
        type="button"
        role="menuitem"
        onclick={onedit}
        class="flex w-full cursor-pointer items-center gap-2 rounded-sm px-2.5 py-1.5 text-left text-sm text-ink hover:bg-surface-muted"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 text-muted">
          <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
          <path d="m15 5 4 4"/>
        </svg>
        <span>Edit</span>
      </button>
    {/if}

    <button
      type="button"
      role="menuitem"
      disabled={deleting}
      onclick={ondelete}
      class="flex w-full cursor-pointer items-center gap-2 rounded-sm px-2.5 py-1.5 text-left text-sm text-danger hover:bg-danger-soft disabled:cursor-not-allowed disabled:opacity-50"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
        <path d="M3 6h18"/>
        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
      </svg>
      <span>{deleting ? "Deleting…" : "Delete"}</span>
    </button>
  </div>
{/if}
