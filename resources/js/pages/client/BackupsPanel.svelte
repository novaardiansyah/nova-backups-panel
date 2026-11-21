<script lang="ts">
  import { router } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import type { BackupItem } from "@/lib/backup-types.js";

  interface Props {
    backups: BackupItem[];
    onstatus?: (message: string) => void;
  }

  let { backups, onstatus }: Props = $props();

  let deletingId = $state<number | null>(null);

  function formatBytes(bytes: number | null): string {
    if (bytes === null || bytes <= 0) return "—";
    const units = ["B", "KB", "MB", "GB", "TB"];
    const index = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
    const value = bytes / Math.pow(1024, index);
    return `${value.toFixed(index === 0 ? 0 : 2)} ${units[index]}`;
  }

  function formatDateTime(value: string | null): string {
    if (!value) return "—";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "—";
    return date.toLocaleString(undefined, {
      year: "numeric",
      month: "short",
      day: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  function handleDelete(id: number) {
    deletingId = id;
    router.delete(`/client/backups/${id}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
      },
      onFinish: () => {
        deletingId = null;
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
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" x2="12" y1="15" y2="3"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">Backups</h2>
        <p class="mt-1 text-sm leading-6 text-muted">
          View recorded backup runs, file sizes, and execution statuses.
        </p>
      </div>
    </div>
  </div>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if backups.length === 0}
      <div class="rounded-lg border border-dashed border-line p-8 text-center">
        <p class="text-sm font-medium text-ink">No backup records yet</p>
        <p class="mt-1 text-xs text-muted">
          Configure a backup schedule to start tracking backup executions here.
        </p>
      </div>
    {:else}
      <div class="space-y-3">
        {#each backups as backup (backup.id)}
          <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-line bg-page p-4">
            <div class="min-w-0 space-y-1">
              <div class="flex flex-wrap items-center gap-2">
                <p class="truncate text-sm font-semibold text-ink">
                  {backup.fileName || backup.uid}
                </p>
                <span
                  class="rounded-full px-2 py-0.5 text-[11px] font-medium {backup.status === 'success'
                    ? 'bg-accent-soft text-accent'
                    : backup.status === 'failed'
                      ? 'bg-danger-soft text-danger'
                      : 'bg-surface-muted text-muted'}"
                >
                  {backup.status}
                </span>
                {#if backup.type}
                  <span class="rounded-full border border-line px-2 py-0.5 text-[11px] text-muted">
                    {backup.type}
                  </span>
                {/if}
              </div>
              <p class="text-xs text-muted">
                Schedule: {backup.scheduleName ?? "—"} &middot; Storage: {backup.storageName ?? "—"} &middot; Size: {formatBytes(backup.fileSize)} &middot; Started: {formatDateTime(backup.startedAt)}
              </p>
              {#if backup.message}
                <p class="text-xs text-faint">{backup.message}</p>
              {/if}
            </div>

            <Button
              type="button"
              variant="outline"
              size="sm"
              disabled={deletingId === backup.id}
              onclick={() => handleDelete(backup.id)}
            >
              {deletingId === backup.id ? "Deleting…" : "Delete"}
            </Button>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
