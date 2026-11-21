<script lang="ts">
  interface Props {
    open: boolean;
    title: string;
    description: string;
    onclose: () => void;
    duration?: number;
  }

  let { open, title, description, onclose, duration = 5000 }: Props = $props();

  $effect(() => {
    if (!open || duration <= 0) {
      return;
    }

    const timer = setTimeout(() => {
      onclose();
    }, duration);

    return () => clearTimeout(timer);
  });
</script>

{#if open}
  <div
    role="status"
    aria-live="polite"
    class="fixed bottom-5 left-4 right-4 z-50 sm:left-auto sm:right-5 sm:w-full sm:max-w-sm"
  >
    <div class="flex items-start gap-3 rounded-xl border border-line-strong bg-surface p-4 text-ink shadow-lg">
      <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-lg bg-accent-soft text-accent">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
          <circle cx="12" cy="12" r="10"/>
          <path d="M12 16v-4"/>
          <path d="M12 8h.01"/>
        </svg>
      </div>
      <div class="grid flex-1 gap-1">
        <p class="text-sm font-semibold text-ink">{title}</p>
        <p class="text-xs leading-relaxed text-muted">{description}</p>
      </div>
      <button
        type="button"
        onclick={onclose}
        aria-label="Close notification"
        class="flex size-6 shrink-0 cursor-pointer items-center justify-center rounded-md text-muted transition-colors hover:bg-surface-muted hover:text-ink"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
          <path d="M18 6 6 18"/>
          <path d="m6 6 12 12"/>
        </svg>
      </button>
    </div>
  </div>
{/if}
