<script lang="ts">
  import { router } from "@inertiajs/svelte";
  import { Button } from "@/components/ui/button/index.js";
  import type { LocalePreference } from "@/lib/theme.js";
  import type { BackupRunItem } from "@/lib/v2-types.js";

  interface Props {
    runs: BackupRunItem[];
    locale?: LocalePreference;
    onstatus?: (message: string) => void;
  }

  let { runs = [], locale = "en", onstatus }: Props = $props();
  const isId = $derived(locale === "id");

  function handleRetry(run: BackupRunItem) {
    router.post(`/client/backup-runs/${run.uid}/retry`, {}, {
      preserveScroll: true,
      onSuccess: (page) => {
        const msg = (page.props as { status?: string | null }).status;
        if (msg) onstatus?.(msg);
      },
    });
  }

  function formatBytes(bytes: number): string {
    if (!bytes) return "0 B";
    const k = 1024;
    const sizes = ["B", "KB", "MB", "GB"];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${(bytes / Math.pow(k, i)).toFixed(1)} ${sizes[i]}`;
  }

  function formatTime(value: string | null): string {
    if (!value) return "—";
    const d = new Date(value);
    return Number.isNaN(d.getTime()) ? "—" : d.toLocaleString();
  }
</script>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex items-start gap-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
          <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
        </svg>
      </span>
      <div class="min-w-0">
        <h2 class="text-lg font-semibold tracking-tight text-ink">
          {isId ? "Riwayat Eksekusi Backup" : "Backup Runs"}
        </h2>
        <p class="mt-1 text-sm leading-6 text-muted">
          {isId
            ? "Pantau status eksekusi backup, checksum SHA-256 arsip RAR, dan status upload ke Google Drive."
            : "Monitor backup execution status, encrypted RAR SHA-256 checksums, and Google Drive upload results."}
        </p>
      </div>
    </div>
  </div>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if runs.length === 0}
      <p class="text-sm text-muted">
        {isId ? "Belum ada riwayat eksekusi backup." : "No backup runs recorded yet."}
      </p>
    {:else}
      <div class="divide-y divide-line">
        {#each runs as run (run.uid)}
          <div class="space-y-2 py-4 first:pt-0 last:pb-0">
            <div class="flex flex-wrap items-center justify-between gap-3">
              <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                  <span
                    class="h-2 w-2 rounded-full {run.status === 'completed'
                      ? 'bg-accent'
                      : run.status === 'failed'
                        ? 'bg-danger'
                        : 'bg-faint'}"
                  ></span>
                  <p class="text-sm font-semibold text-ink">{run.profileName ?? "Profile"}</p>
                  <span class="rounded border border-line bg-page px-2 py-0.5 font-mono text-[11px] uppercase text-muted">
                    {run.status}
                  </span>
                  <span class="rounded border border-line bg-page px-2 py-0.5 font-mono text-[11px] text-faint">
                    {run.triggerType}
                  </span>
                </div>
                <p class="mt-1 text-xs text-muted">
                  {isId ? "Agen" : "Agent"}: {run.agentName ?? "—"} ·
                  {isId ? "Mulai" : "Started"}: {formatTime(run.startedAt)} ·
                  {isId ? "Selesai" : "Completed"}: {formatTime(run.completedAt)}
                </p>
              </div>
              {#if run.status === "failed"}
                <Button type="button" variant="outline" size="sm" onclick={() => handleRetry(run)}>
                  {isId ? "Coba Lagi" : "Retry"}
                </Button>
              {/if}
            </div>

            {#if run.errorMessage}
              <p class="rounded-md border border-danger/40 bg-danger-soft px-3 py-1.5 text-xs text-danger">
                {run.errorMessage}
              </p>
            {/if}

            {#if run.artifacts.length > 0}
              <div class="space-y-1.5 rounded-lg border border-line bg-page p-3">
                {#each run.artifacts as artifact (artifact.uid)}
                  <div class="text-xs">
                    <p class="font-mono text-ink">
                      {artifact.filename} ({formatBytes(artifact.fileSize)})
                    </p>
                    {#if artifact.checksumSha256}
                      <p class="font-mono text-[11px] text-faint">SHA-256: {artifact.checksumSha256}</p>
                    {/if}
                    {#each artifact.uploads as upload (upload.uid)}
                      <p class="mt-1 text-[11px] text-muted">
                        -> {upload.storageAccountName ?? "Google Drive"}:
                        <span class="font-mono">{upload.status}</span>
                        {#if upload.remoteFileId}
                          (ID: <span class="font-mono">{upload.remoteFileId}</span>)
                        {/if}
                      </p>
                    {/each}
                  </div>
                {/each}
              </div>
            {/if}
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>
